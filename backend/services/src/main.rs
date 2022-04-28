mod tokendb;
mod error;
#[macro_use]
extern crate log;
use tower_http::cors::{CorsLayer, Origin};
use axum::{
    http::{header, Method, StatusCode},
    response::{Html, IntoResponse},
    routing::{get,post, get_service},
    extract::{Extension},
    Json, Router,
};
use std::sync::Arc;
use std::net::SocketAddr;
use serde_json::{json, Value, Number};
use tower_http::{services::{ServeDir, ServeFile}, trace::TraceLayer};
use tracing_subscriber::{layer::SubscriberExt, util::SubscriberInitExt};
use gluesql::{
    prelude::{Payload, Value as GlueValue}
};
use anyhow::Result;
use anyhow::anyhow;
use tokendb::{
    SledGlue, exec_cmd, begin_transaction, rollback_transaction, 
    dump_single_table, commit_transaction, exec_query,
};

pub struct AppState {
    pub glue_path: String,
}

#[tokio::main]
async fn main(){     // create our static file handler
    tracing_subscriber::registry()
        .with(tracing_subscriber::EnvFilter::new(
            std::env::var("RUST_LOG")
                .unwrap_or_else(|_| "example_static_file_server=debug,tower_http=debug".into()),
        ))
        .with(tracing_subscriber::fmt::layer())
        .init();

    let glue_path: String = std::env::current_dir().unwrap().to_str().unwrap().into();
    let frontend = async {
        let app = Router::new()
            .route(
                "/",
                get_service(ServeDir::new("../../frontend/website/dist/index.html"))
                .handle_error(|error: std::io::Error| async move {
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        format!("Unhandled internal error: {}", error),
                    )
                }),
            )
            .route(
                "/article/:id",
                get_service(ServeFile::new("../../frontend/website/dist/index.html"))
                .handle_error(|error: std::io::Error| async move {
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        format!("Unhandled article internal error: {}", error),
                    )
                }),
            )
            .nest(
                "/dist",
                get_service(ServeDir::new("../../frontend/website/dist/"))
                .handle_error(|error: std::io::Error| async move {
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        format!("Unhandled internal error: {}", error),
                    )
                }),
            )
            .nest(
                "/assets",
                get_service(ServeDir::new("../../frontend/website/dist/assets/"))
                .handle_error(|error: std::io::Error| async move {
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        format!("Unhandled internal error: {}", error),
                    )
                }),
            )
            .nest(
                "/dynamic",
                get_service(ServeDir::new("../../frontend/website/dist/dynamic/"))
                .handle_error(|error: std::io::Error| async move {
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        format!("Unhandled internal error: {}", error),
                    )
                }),
            )
            .layer(TraceLayer::new_for_http());
        serve(app, "static file",  8080).await;
    };
    let backend = async {
        let app = Router::new()
            .route("/api/get_article_list_and_view", post(article_list_and_view))
            .route("/api/check_already_paid", post(check_already_paid))
            .route("/api/get_article_homepage", post(article_homepage))
            .route("/api/sql_test", post(sql_test))
            .route("/api/sql_query", post(sql_query))
            .layer(
            // see https://docs.rs/tower-http/latest/tower_http/cors/index.html
            // for more details
            //
            // pay attention that for some request types like posting content-type: application/json
            // it is required to add ".allow_headers(vec![http::header::CONTENT_TYPE])"
            // or see this issue https://github.com/tokio-rs/axum/issues/849
            CorsLayer::new()
                // .allow_origins(AllowedOrigins::Any { allow_null: false })
                .allow_headers([
                    header::ACCEPT,
                    header::CONTENT_TYPE,
                    header::CONTENT_LENGTH,
                    header::ACCEPT_ENCODING,
                    header::ACCEPT_LANGUAGE,
                    header::AUTHORIZATION,
                ])
                .allow_origin(Origin::exact("http://localhost:8080".parse().unwrap()))
                .allow_methods(vec![Method::GET, Method::POST]),
            )
            .layer(Extension(Arc::new(AppState {glue_path})));
        serve(app, "ajax api", 3000).await;
    };
    tokio::join!( frontend, backend);
}
async fn serve(app: Router, server_name:&str, port: u16) {
    let addr = SocketAddr::from(([127, 0, 0, 1], port));
    info!("Run our application as a {} server on http://localhost:{}.", server_name, port);
    axum::Server::bind(&addr)
        .serve(app.into_make_service())
        .await
        .unwrap();
}

/// Use Thread for spawning a thread e.g. to acquire our crate::DATA mutex lock.
use std::thread;

fn article_list_and_view_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let id_start = input.get("start_index")
        .ok_or(anyhow!("input has no start_index"))?
        .as_i64()
        .ok_or(anyhow!("start_index is not a number"))?;
    let id_end = input.get("number_of_article")
        .ok_or(anyhow!("input has no number_of_article"))?
        .as_i64()
        .ok_or(anyhow!("number_of_article is not a number"))?
        + id_start;
    let _user_id = input.get("user_id")
        .ok_or(anyhow!("input has no user_id"))?
        .as_i64()
        .ok_or(anyhow!("user_id is not a number"))?;//we have not used it yet

    let sql=  format!(r#"SELECT a.article_id, a.article_title, 
        b.name, b.pfp, b.total_invested, a.image_url, a.hashtag
        FROM articles a LEFT JOIN users b 
        ON b.id = a.author_id WHERE a.article_id >= {} AND 
        a.article_id < {}"#, id_start, id_end);

    let mut glue = tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}

pub async fn article_list_and_view(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        match article_list_and_view_inner(&input, state){
            Ok(result) =>{
                payload_to_json(&result).unwrap_or_else(|e|
                    json!({
                        "error": e.to_string()
                    })
                )
            },
            Err(e) => json!({
                "error": e.to_string(),
            })
        }
    }).join().unwrap().into()
}



pub async fn check_already_paid(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        // let name = input.get("name");
        json!(
            {
                "paid_or_not": true, //true = paid, false = haven't paid
                "paid": {
                    "article_id": 1,
                    "article_image_url": "http://backgroundImage",
                    "article_title": "The History of Fashion",
                    "article_author_name": "Violet Lee",
                    "author_pfp": "http://authorpfp",
                    "article_body": "Hi guys! I don’t know about you, but I am SO ready for spring! I’ve been posting some of my recent spring finds and what I’ve ordered and wanted to share last months most loved aka best-sellers!"
                }
            }
    )
    }).join().unwrap().into()
}

fn article_homepage_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let article_id = input.get("article_id")
        .ok_or(anyhow!("input has no article_id"))?
        .as_i64()
        .ok_or(anyhow!("article_id is not a number"))?;

    let sql=  format!(r#"SELECT a.article_id, a.article_title, 
        b.name, b.pfp, b.total_invested, a.image_url, 
        a.hashtag, a.article_total_reads, a.article_total_shares 
        FROM articles a LEFT JOIN users b 
        ON b.id = a.author_id WHERE a.article_id = {}"#, article_id);

    let mut glue = tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn article_homepage(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        match article_homepage_inner(&input, state){
            Ok(result) =>{
                payload_to_json(&result).unwrap_or_else(|e|
                    json!({
                        "error": e.to_string()
                    })
                )
            },
            Err(e) => json!({
                "error": e.to_string(),
            })
        }
    }).join().unwrap().into()
}
pub async fn sql_test(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        let sqls = input.get("sql").unwrap().as_array().unwrap();

        let mut glue = tokendb::init_glue(&state.glue_path).unwrap();
        let mut results = Vec::new();
        for sql in sqls{
            println!("sql is {}", &sql);
            println!("sql.as_str() is {:?}", &sql.as_str());
            let result = match exec_cmd(
                &mut glue,
                sql.as_str().unwrap(),
            ){
                Ok(r)=> json!(
                    {
                        "result": true,
                    }
                ),
                Err(e)=> json!(
                    {
                        "error": e.to_string(),
                    }
                )
            };
            results.push(result.clone());
            if result.get("error").is_some(){
                break;
            }
        };
        json!(results)
    }).join().unwrap().into()
}
pub fn payload_to_json(payload: &Payload)->Result<serde_json::Value>{
    println!("payload is {:?}", &payload);
    match payload{
        Payload::Select{ labels, rows} =>{
            let mut obj_array = Vec::new();
            for row in rows{
                let mut map = serde_json::Map::new();
                for i in 0..row.len() {
                    println!("____");
                    // println!("key:{:?} : {:?}", &labels[i], &row[i]);
                    match &row[i]{
                        GlueValue::I64(num)=>{
                            let num_i64: i64 = *num;
                            map.insert(labels[i].to_owned(), Value::Number(num_i64.into()));
                        },
                        GlueValue::Str(s)=>{
                            map.insert(labels[i].to_owned(), Value::String(s.to_string()));
                        },
                        GlueValue::F64(f)=>{
                            let num_float: f64 = *f;
                            map.insert(labels[i].to_owned(), Value::Number(Number::from_f64(num_float).unwrap()));
                        }
                        _ =>{
                            println!("Not supported GlueValue {:?}", &row[i]);
                        }
                    }
                }
                println!("map {:?}", &map);
                obj_array.push(map);
            }
            Ok(json!(obj_array))
        },
        _ => {
            Ok(json!({
                "error": "not payload::select result"
            }))
        }
    }
}
pub async fn sql_query(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        let sqls = input.get("sql").unwrap().as_array().unwrap();

        let mut glue = tokendb::init_glue(&state.glue_path).unwrap();
        let mut results = Vec::new();
        for sql in sqls{
            println!("sql is {}", &sql);
            println!("sql.as_str() is {:?}", &sql.as_str());
            let result = match exec_query(
                &mut glue,
                sql.as_str().unwrap(),
            ){
                Ok(payload) => {
                    payload_to_json(&payload).unwrap_or_else(|e|
                        json!({
                            "error": e.to_string()
                        })
                    )
                },
                Err(e)=> json!(
                    {
                        "error": e.to_string(),
                    }
                )
            };
            results.push(result.clone());
            if result.get("error").is_some(){
                break;
            }
        };
        json!(results)
    }).join().unwrap().into()
}