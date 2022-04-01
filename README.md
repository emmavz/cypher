# quillmates
Database tables + commands

| Property  | Type |
| ------------- | ------------- |
| User_ID  | PK  |
| Username | String |
| Author status  | Boolean  |
| Wallet Addresss | FK |
| Balance | |
| pfp_image | URL |
| Followers | # |
| Following | # |
| User bio | String |
| datestamp | time |

article

| Property  | Type |
| ------------- | ------------- |
| Article_ID  | PK  |
| User_ID  | FK  |
| post_date | datetime |
| Title | String |
| header_img | URL |
| Description | String |
| Content_body | String |
| liquidation_days | String |
| price | # |
| Invested_T | # |
| updated_date | datetime |

article_token

| Property  | Type |
| ------------- | ------------- |
| Article_ID  | PK  |
| num_tokens  | #  |
| timestamp | datetime |


follower_id

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
| invested_T | # |
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
