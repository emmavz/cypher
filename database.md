Database tables + commands

users

| Property  | Type |
| ------------- | ------------- |
| user_id  | PK  |
| name | String |
| status  | Boolean  |
| wallet | FK |
| balance | # |
| profile_img | URL |
| bio | String |
| datestamp | time |



articles

| Property  | Type |
| ------------- | ------------- |
| article_id | PK  |
| user_id  | FK  |
| postdate | datetime |
| title | String |
| header_img | URL |
| description | String |
| category | String |
| hashtag | String |
| minutes | # |
| content | String |
| liquidation_days | String |
| price | # |
| invested | # |
| updatedate | datetime |

article_token

| Property  | Type |
| ------------- | ------------- |
| article_id  | PK  |
| num_tokens  | #  |
| timestamp | datetime |


follows

| Property  | Type |
| ------------- | ------------- |
| follower_id  | PK  |
| followee_id  | FK  |
| datestamp | datetime |

user_article

| Property  | Type |
| ------------- | ------------- |
| user_id | PK  |
| article_id  | FK  |
| invetsed | # |
| timestamp | datetime |
# articles

"CREATE TABLE articles ( article_id INTEGER, article_title TEXT ,  author_id INTEGER, article_description TEXT null, content TEXT null,  liquidation_days INTEGER, price FLOAT, category INTEGER, hashtag TEXT null,image_url TEXT, data_posted INTEGER, article_total_reads INTEGER, article_total_shares INTEGER);",
# users

"CREATE TABLE users (id INTEGER, name TEXT, status INTEGER, pfp TEXT, total_invested INTEGER, wallet_address TEXT, balance INTEGER, profile_url TEXT null, bio TEXT null)"
# pay_read_tx

"CREATE TABLE pay_read_tx (id INTEGER, payer_id INTEGER, article_id INTEGER, tx_time TEXT, tx_amount INTEGER"
transactions


bonding curve (NO USE)

| Property  | Type |
| ------------- | ------------- |
| token_tx_id | PK  |
| article_id  | FK  |
| user_id | FK |
| token_tx_amount | # |
| total_tokens | # |
| token_tx | Boolean |
| token_tx_time | datetime |
