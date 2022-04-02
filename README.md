# quillmates
Database tables + commands

users

| Property  | Type |
| ------------- | ------------- |
| user_id  | PK  |
| name | String |
| status  | Boolean  |
| wallet | FK |
| balance | |
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

transactions

| Property  | Type |
| ------------- | ------------- |
| tx_id | PK  |
| article_id  | FK  |
| user_id | FK |
| tx_time | datetime |
| tx_amount | # |
| tx_type | string |

bonding curve

| Property  | Type |
| ------------- | ------------- |
| token_tx_id | PK  |
| article_id  | FK  |
| user_id | FK |
| token_tx_amount | # |
| total_tokens | # |
| token_tx | Boolean |
| token_tx_time | datetime |
