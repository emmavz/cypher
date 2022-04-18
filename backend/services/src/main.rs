// mod tokendb;
// mod error;

/// Use axum capabities.
use axum::routing::{get, post};
use serde_json::{json, Value};


#[tokio::main]
async fn main() {
    // Build our application by creating our router.
    let app = axum::Router::new()
        .route("/",
        get(home)
        )
        .route("/api/get_article_list_and_view",
            // get(get_books)
            post(article_list_and_view)
        )
        .route("/api/check_already_paid",
            post(check_already_paid)
        );

    println!("Run our application as a hyper server on http://localhost:3000.");
    axum::Server::bind(&"0.0.0.0:3000".parse().unwrap())
        .serve(app.into_make_service())
        .await
        .unwrap();
}

/// See file book.rs, which defines the `Book` struct.
mod book;
use crate::book::Book;

/// See file data.rs, which defines the DATA global variable.
mod data;
use crate::data::DATA;

/// Use Thread for spawning a thread e.g. to acquire our crate::DATA mutex lock.
use std::thread;

/// To access data, create a thread, spawn it, then get the lock.
/// When you're done, then join the thread with its parent thread.
#[allow(dead_code)]
async fn print_data() {
    thread::spawn(move || {
        let data = DATA.lock().unwrap();
        println!("data: {:?}" ,data);
    }).join().unwrap()
}

/// axum handler for "GET /books" which responds with a resource page.
/// This demo uses our DATA; a production app could use a database.
/// This demo must clone the DATA in order to sort items by title.
pub async fn home() -> axum::response::Html<String> {
    thread::spawn(move || {
        let data = DATA.lock().unwrap();
        let mut books = data.values().collect::<Vec<_>>().clone();
        books.sort_by(|a, b| a.title.cmp(&b.title));
        books.iter().map(|&book|
            format!("<p>Your index.html goes here</p><p>{}</p>\n", &book)
        ).collect::<String>()
    }).join().unwrap().into()
}


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
        "author_pfp": "www.ejpfp.com",
        "date_posted": "1/03/2021 15:19:00",
        "total_invested": 10025,
        "image_url": "http://backgroundImage",
        "tags": "for you, coding"
    },
    {
        "article_id": 2,
        "article_title": "Super Chewy Cookies Recipe",
        "author_name": "Eliza Mae",
        "author_pfp": "www.empfp.com",
        "date_posted": "1/21/2021 15:19:00",
        "total_invested": 7342,
        "image_url": "http://backgroundImage",
        "tags": "for you, baking"
    },
    {
        "article_id": 3,
        "article_title": "The Go-To-Market Guide",
        "author_name": "Cecelia Hong",
        "author_pfp": "www.chpfp.com",
        "date_posted": "1/07/2021 15:19:00",
        "total_invested": 8961,
        "image_url": "http://backgroundImage",
        "tags": "for you, business"
    },
    {
        "article_id": 4,
        "article_title": "The Rules of Digital Marketing",
        "author_name": "Melissa Shen",
        "author_pfp": "www.mspfp.com",
        "date_posted": "1/19/2021 15:19:00",
        "total_invested": 9456,
        "image_url": "http://backgroundImage",
        "tags": "for you, marketing"
    },
    {
        "article_id": 5,
        "article_title": "Building muscle the right way",
        "author_name": "Darren Jones",
        "author_pfp": "www.djpfp.com",
        "date_posted": "1/24/2021 15:19:00",
        "total_invested": 11275,
        "image_url": "http://backgroundImage",
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

