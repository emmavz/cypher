mod tokendb;
mod error;
#[macro_use]
extern crate log;
use tower_http::cors::{CorsLayer, Origin};
use axum::{
    http::{header, Method, StatusCode},
    response::{Html, IntoResponse},
    routing::{get,post, get_service},
    Json, Router,
};
use std::net::SocketAddr;
use serde_json::{json, Value};
use tower_http::{services::ServeDir, trace::TraceLayer};
use tracing_subscriber::{layer::SubscriberExt, util::SubscriberInitExt};

#[tokio::main]
async fn main(){     // create our static file handler
    tracing_subscriber::registry()
        .with(tracing_subscriber::EnvFilter::new(
            std::env::var("RUST_LOG")
                .unwrap_or_else(|_| "example_static_file_server=debug,tower_http=debug".into()),
        ))
        .with(tracing_subscriber::fmt::layer())
        .init();
    let frontend = async {
        let app = Router::new()
            .nest(
                "/",
                get_service(ServeDir::new("../../frontend/website/"))
                .handle_error(|error: std::io::Error| async move {
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        format!("Unhandled internal error: {}", error),
                    )
                }),
            )
            .layer(TraceLayer::new_for_http());
        serve(app, 8080).await;
    };
    let backend = async {
        let app = Router::new()
            .route("/api/get_article_list_and_view", post(article_list_and_view))
            .route("/api/check_already_paid", post(check_already_paid))
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
                .allow_origin(Origin::exact("http://localhost:3000".parse().unwrap()))
                .allow_methods(vec![Method::GET, Method::POST]),
        );
        serve(app, 3000).await;
    };

    tokio::join!(frontend, backend);

}
async fn serve(app: Router, port: u16) {
    let addr = SocketAddr::from(([127, 0, 0, 1], port));
    println!("Run our application as a hyper server on http://localhost:{}.", port);
    axum::Server::bind(&addr)
        .serve(app.into_make_service())
        .await
        .unwrap();
}

/// Use Thread for spawning a thread e.g. to acquire our crate::DATA mutex lock.
use std::thread;

pub async fn article_list_and_view(
    axum::extract::Json(input): axum::extract::Json<serde_json::Value>
) -> axum::extract::Json<Value> {
    thread::spawn(move || {
        // let name = input.get("name");
        json!(
[
    {
        "article_id": 1,
        "article_title": "Why Python is The Future",
        "author_name": "Ephraim Jones",
        "author_pfp": "./img/dynamic/profile-1.png",
        "date_posted": "1/03/2021 15:19:00",
        "total_invested": 10025,
        "image_url": "./img/dynamic/post-1.jpg",
        "tags": "for you, coding"
    },
    {
        "article_id": 2,
        "article_title": "Super Chewy Cookies Recipe",
        "author_name": "Eliza Mae",
        "author_pfp": "./img/dynamic/profile-2.png",
        "date_posted": "1/21/2021 15:19:00",
        "total_invested": 7342,
        "image_url": "./img/dynamic/post-2.jpg",
        "tags": "for you, baking"
    },
    {
        "article_id": 3,
        "article_title": "The Go-To-Market Guide",
        "author_name": "Cecelia Hong",
        "author_pfp": "./img/dynamic/profile-3.png",
        "date_posted": "1/07/2021 15:19:00",
        "total_invested": 8961,
        "image_url": "./img/dynamic/post-3.jpg",
        "tags": "for you, business"
    },
    {
        "article_id": 4,
        "article_title": "The Rules of Digital Marketing",
        "author_name": "Melissa Shen",
        "author_pfp": "./img/dynamic/profile-4.png",
        "date_posted": "1/19/2021 15:19:00",
        "total_invested": 9456,
        "image_url": "./img/dynamic/post-4.jpg",
        "tags": "for you, marketing"
    },
    {
        "article_id": 5,
        "article_title": "Building muscle the right way",
        "author_name": "Darren Jones",
        "author_pfp": "./img/dynamic/profile-5.png",
        "date_posted": "1/24/2021 15:19:00",
        "total_invested": 11275,
        "image_url": "./img/dynamic/post-5.jpg",
        "tags": "for you, fitness"
    }
]
    )
    }).join().unwrap().into()
}


pub async fn check_already_paid(
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
