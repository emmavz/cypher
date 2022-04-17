# Overview

# List of articles
Input url is /home?uid=[userid]

<img width="413" alt="get_article_list_view" src="https://user-images.githubusercontent.com/17632589/163287942-eb1ba918-756d-40c2-9fe5-b5de84098d96.png">

Assume user has logged in. User will see a list of articles here.

API call is 
query_article_list_and_view

Request value 'user_id', 'start_index', 'number_of_articles' //if window size/card height > 10, number_of_articles = that number. Else number_of_articles = 10

Response value: 'article_title' 'author_name' 'author_pfp' 'date_posted' 'total_invested' 'image_url' 'tags' //for each article_id

The UI element mapping to the response value:
![article_list UI elements](https://user-images.githubusercontent.com/17632589/163302454-950cb39d-931d-4596-9558-190145325b16.jpeg)


In case of error response. show the error message. then an empty article list page

## Clickable area 
![article_list clickable area](https://user-images.githubusercontent.com/17632589/163292189-bd1755cd-507e-416e-82f9-39ab6716466b.jpeg)

# already_paid //article_readpage

If clicked, trigger API: already_paid. 

The URL to the already_paid page is /article/[article_id]?uid=[userid]

Request: 'article_id', 'user_id'

Response: 'article_image_url', 'article_title', 'article_author_name', 'author_pfp', 'article_body' //article_readpage

<img width="473" alt="article read page" src="https://user-images.githubusercontent.com/17632589/163291858-bb93fc56-3a41-4abf-b9c3-b0dbb3ac6594.png">


UI elements:
![read_page UI](https://user-images.githubusercontent.com/17632589/163301836-677bec4e-3220-4fb0-9cbc-32f8a2b6af83.jpeg)

If API failed with an error message, go to article_list_view


# Empty article list page
If there is anything wrong that we cannot show the list, show this page.
The text on this page is "error. Sorry! This request couldn't be processed right now. Please try again later!"

<img width="275" alt="Screen Shot 2022-04-13 at 5 51 49 PM" src="https://user-images.githubusercontent.com/17632589/163292806-a1798f1e-9a38-4741-809a-b0535c8c3a1c.png">



### Clickable area

![read_page clickable](https://user-images.githubusercontent.com/17632589/163301826-d8641e23-d9dd-47ee-877e-213f48c0d5c2.jpeg)


If 'statistics' clicked, trigger API: get_statistics

If 'upvote' clicked, trigger API: upvote_command

If 'downvote' clicked, trigger API: cash_out_command



