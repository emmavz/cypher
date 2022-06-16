mod tokendb;
mod error;
#[macro_use]
extern crate log;
extern crate chrono;
use chrono::prelude::*;

use tower_http::cors::{CorsLayer, Origin};
use axum::{
    http::{header, Method, StatusCode},
    response::{Html, IntoResponse},
    routing::{get,post, get_service},
    extract::{Extension},
    Json, Router,
};
use std::sync::{Arc, RwLock};
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
    pub glue: RwLock<SledGlue>,
}
impl AppState{
    pub fn new(glue_path: &str)->Self{
        AppState{
            glue: RwLock::new(tokendb::init_glue(glue_path).unwrap()),
        }
    }
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
            .route("/api/get_article_homepage", post(article_homepage))
            .route("/api/check_already_paid", post(check_already_paid))
            .route("/api/get_user_profile", post(get_user_profile))
            .route("/api/save_article", post(save_article))
            .route("/api/get_recommendations", post(get_recommendations))
            .route("/api/search_articles", post(search_articles))
            .route("/api/search_authors", post(search_authors))
            .route("/api/follow", post(follow))
            .route("/api/get_user_profile_articles", post(get_user_profile_articles))
            .route("/api/get_notifications", post(get_notifications))
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
                .allow_origin(Origin::exact("http://localhost:8888".parse().unwrap()))
                .allow_methods(vec![Method::GET, Method::POST]),
            )
            .layer(Extension(Arc::new(AppState::new(&glue_path))));
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


pub async fn sql_test(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        let sqls = input.get("sql").unwrap().as_array().unwrap();

        let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
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

        let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
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

fn article_list_and_view_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let offset = input.get("offset")
        .ok_or(anyhow!("input has no offset"))?
        .as_i64()
        .ok_or(anyhow!("offset is not a number"))?;
    let limit = input.get("limit")
        .ok_or(anyhow!("input has no limit"))?
        .as_i64()
        .ok_or(anyhow!("limit is not a number"))?;
    // let _user_id = input.get("user_id")
    //     .ok_or(anyhow!("input has no user_id"))?
    //     .as_i64()
    //     .ok_or(anyhow!("user_id is not a number"))?;//we have not used it yet

    let sql=  format!(r#"SELECT a.article_id, a.article_title,
        b.name, b.pfp, b.total_invested, a.image_url, a.hashtag, a.date_posted
        FROM articles a INNER JOIN users b
        ON b.id = a.author_id WHERE is_published = {} ORDER BY a.date_posted DESC LIMIT {} OFFSET {}"#, true, limit, offset);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}

pub async fn article_list_and_view(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, article_list_and_view_inner).await
}

fn article_homepage_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let article_id = input.get("article_id")
        .ok_or(anyhow!("input has no article_id"))?
        .as_i64()
        .ok_or(anyhow!("article_id is not a number"))?;

    let sql=  format!(r#"SELECT a.article_title,
        b.name as article_author, b.pfp as author_pfp, b.total_invested, a.image_url,
        a.price as article_price, a.liquidation_days as article_liquidation_time,
        a.article_total_reads, a.article_total_shares, a.article_description
        FROM articles a INNER JOIN users b
        ON b.id = a.author_id WHERE a.article_id = {} AND is_published = {}"#, article_id, true);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn article_homepage(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, article_homepage_inner).await
}
fn check_already_paid_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let article_id = input.get("article_id")
        .ok_or(anyhow!("input has no article_id"))?
        .as_i64()
        .ok_or(anyhow!("article_id is not a number"))?;
    let user_id = input.get("user_id")
        .ok_or(anyhow!("input has no user_id"))?
        .as_i64()
        .ok_or(anyhow!("user_id is not a number"))?;

    let sql=  format!(r#"
        SELECT b.article_id, b.image_url AS article_image_url, b.article_title,
        c.name AS article_auther_name, c.pfp AS author_pfp, b.content AS article_body
        FROM pay_read_tx a
        INNER JOIN articles b
        ON a.article_id = b.article_id
        INNER JOIN users c
        ON a.payer_id = c.id
        WHERE a.article_id = {} AND a.payer_id={}"#, article_id, user_id);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn check_already_paid(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, check_already_paid_inner).await
}
// fn get_user_profile_inner(input: &serde_json::Value, state: Arc<AppState>)
//         ->Result<Payload>{
//     let user_id = input.get("user_id")
//         .ok_or(anyhow!("input has no user_id"))?
//         .as_i64()
//         .ok_or(anyhow!("user_id is not a number"))?;

//     let sql=  format!(r#"
//         SELECT balance as user_wallet_balance
//         FROM users
//         WHERE id = {} "#, user_id);

//     let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
//     exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
// }
// pub async fn get_user_profile(
//     Extension(state):Extension<Arc<AppState>>,
//     axum::extract::Json(input): axum::extract::Json<serde_json::Value>
// ) -> axum::extract::Json<Value> {
//     run_sql_json(state, input, get_user_profile_inner).await
// }

fn get_user_profile_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let user_id = input.get("user_id")
        .ok_or(anyhow!("input has no user_id"))?
        .as_i64()
        .ok_or(anyhow!("user_id is not a number"))?;

    let sql=  format!(r#"
        SELECT name, bio, pfp, balance as user_wallet_balance,
        (SELECT COUNT(*) FROM follows WHERE users.id = follower_id AND followed_id = {}) as followers,
        (SELECT COUNT(*) FROM follows WHERE users.id = followed_id AND follower_id = {}) as followings
        FROM users
        WHERE id = {} "#, user_id, user_id, user_id);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn get_user_profile(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, get_user_profile_inner).await
}

fn get_user_profile_articles_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let user_id = input.get("user_id")
        .ok_or(anyhow!("input has no user_id"))?
        .as_i64()
        .ok_or(anyhow!("user_id is not a number"))?;//we have not used it yet

    let sql=  format!(r#"SELECT a.article_id, a.article_title,
        b.name, b.pfp, a.image_url, a.date_posted
        FROM articles a INNER JOIN users b
        ON b.id = a.author_id WHERE a.author_id = {} AND is_published = {}"#, user_id, true);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}

pub async fn get_user_profile_articles(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, get_user_profile_articles_inner).await
}

pub async fn run_sql_json<F>(
    state: Arc<AppState>,
    input: serde_json::Value,
    sql_json: F
) -> axum::extract::Json<Value>
where F: FnOnce(&serde_json::Value, Arc<AppState>)->Result<Payload> + Send + 'static{
    thread::spawn(move || {
        match sql_json(&input, state){
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

fn get_recommendations_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let offset = input.get("offset")
        .ok_or(anyhow!("input has no offset"))?
        .as_i64()
        .ok_or(anyhow!("offset is not a number"))?;
    let limit = input.get("limit")
        .ok_or(anyhow!("input has no limit"))?
        .as_i64()
        .ok_or(anyhow!("limit is not a number"))?;

    let sql=  format!(r#"SELECT a.article_id, a.article_title,
        b.name, b.pfp, b.total_invested, a.image_url, a.hashtag, a.date_posted
        FROM articles a INNER JOIN users b
        ON b.id = a.author_id WHERE is_published = {} ORDER BY a.date_posted DESC LIMIT {} OFFSET {}"#, true, limit, offset);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn get_recommendations(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, get_recommendations_inner).await
}

fn search_articles_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let q = input.get("q")
        .ok_or(anyhow!("input has no query"))?;

    let sql=  format!(r#"SELECT a.article_id, a.article_title,
        b.name, b.pfp, b.total_invested, a.image_url, a.hashtag, a.date_posted
        FROM articles a INNER JOIN users b
        ON b.id = a.author_id WHERE a.article_title LIKE {} AND is_published = {}"#, q , true);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn search_articles(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, search_articles_inner).await
}

fn search_authors_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let q = input.get("q")
        .ok_or(anyhow!("input has no query"))?;
    let follower_id = input.get("follower_id")
        .ok_or(anyhow!("input has no follower_id"))?;

    let sql=  format!(r#"SELECT users.id, name, bio, pfp, (EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follower_id = {})) as is_followed FROM users WHERE name LIKE {}"#, follower_id, q);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn search_authors(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, search_authors_inner).await
}

fn follow_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let follower_id = input.get("follower_id")
        .ok_or(anyhow!("input has no follower_id"))?;
    let followed_id = input.get("followed_id")
        .ok_or(anyhow!("input has no followed_id"))?;

    let sql=  format!(r#"INSERT INTO follows(follower_id, followed_id) VALUE({},{})"#, follower_id, followed_id);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn follow(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, follow_inner).await
}

fn get_notifications_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let user_id = input.get("user_id")
        .ok_or(anyhow!("input has no user_id"))?;

    let sql=  format!(r#"SELECT text,read_at FROM user_notification INNER JOIN notifications ORDER BY created_at DESC WHERE user_id = {}"#, user_id);

    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_query(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}
pub async fn get_notifications(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, get_notifications_inner).await
}

fn save_article_inner(input: &serde_json::Value, state: Arc<AppState>)
        ->Result<Payload>{
    let article_title = input.get("article_title")
        .ok_or(anyhow!("input has no article_title"))?;
    let article_description = input.get("article_description")
        .ok_or(anyhow!("input has no article_description"))?;
    let user_id = input.get("user_id")
        .ok_or(anyhow!("input has no user_id"))?;
    let image_url = input.get("image_url")
        .ok_or(anyhow!("input has no image_url"))?;

    // Save image to some folder and store path in a variable

    let sql=  format!(r#"INSERT INTO articles (article_id, author_id, article_title, article_description, image_url, is_published) VALUES (5,{},{},{},{},{})
         "#, user_id, article_title, article_description, image_url, false);

    // Run this query and get `id` of the record and store in a variable
    let notification_text = "Some random text";
    let sql=  format!(r#"INSERT INTO notifications (text) VALUES ({})
         "#, notification_text);

    let notification_id = "";
    let local: DateTime<Local> = Local::now();

    let sql=  format!(r#"INSERT INTO user_notification (notification_id, user_id, created_at) VALUES ({},{},{})
         "#, notification_id, user_id, local.timestamp());


    let mut glue = state.glue.write().unwrap();//tokendb::init_glue(&state.glue_path).unwrap();
    exec_cmd(&mut glue, &sql).map_err(|e| anyhow!("GlueSQL error: {}", e.to_string()))
}

pub async fn save_article(
    Extension(state):Extension<Arc<AppState>>,
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    run_sql_json(state, input, save_article_inner).await
}