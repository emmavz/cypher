# Overview

# List of articles
Input url is /home?uid=[userid]

<img width="451" alt="Screen Shot 2022-04-20 at 9 16 32 AM" src="https://user-images.githubusercontent.com/17632589/164276399-763af5bb-1370-4ae9-98cb-bc98133aada0.png">

Assume user has logged in. User will see a list of articles here.

API call is 
query_article_list_and_view

Request value 'user_id', 'start_index', 'number_of_articles' //if window size/card height > 10, number_of_articles = that number. Else number_of_articles = 10

Response value: 'article_title' 'author_name' 'author_pfp' 'date_posted' 'total_invested' 'image_url' 'tags' //for each article_id

The UI element mapping to the response value:
![article_list_view_UI elements](https://user-images.githubusercontent.com/17632589/164275991-4e42b97e-9f6e-47d1-87f9-0b7da5d94c79.jpeg)


In case of error response. show the error message. then an empty article list page

## Clickable area 
![article_list_view_clickablearea](https://user-images.githubusercontent.com/17632589/164275987-ac3b92e1-49ba-47bd-8b7e-2e392eea8258.jpg)

# check_already_paid //article_readpage

If clicked, trigger API: check_already_paid. 

The URL to the already_paid page is /article/[article_id]?uid=[userid]

Request: 'article_id', 'user_id'

Response:

{
    paid_or_not: boolean //true = has paid, false = unpaid
    paid:{
    //article_readpage properties
        'article_id',
        'article_image_url', 
        'article_title', 
        'article_author_name', 
        'author_pfp', 
        'article_body' 
    },
    unpaid:{ 
    //article_homepage properties
    }
}

If has_paid is true, only render paid properties
if has_paid is false, only render unpaid properties



<img width="423" alt="Screen Shot 2022-04-19 at 8 10 55 AM" src="https://user-images.githubusercontent.com/17632589/164036348-3353b27e-6b4c-42c4-b0c7-162b382fd5ce.png">





UI elements:
![read_page UI elements](https://user-images.githubusercontent.com/17632589/164042410-42f8efab-0ea9-4fb3-9edb-eeae9230548f.jpg)

If API failed with an error message, go to article_list_view


# Empty article list page
If there is anything wrong that we cannot show the list, show this page.
The text on this page is "error. Sorry! This request couldn't be processed right now. Please try again later!"

<img width="275" alt="Screen Shot 2022-04-13 at 5 51 49 PM" src="https://user-images.githubusercontent.com/17632589/163292806-a1798f1e-9a38-4741-809a-b0535c8c3a1c.png">



### Clickable area

![read_page clickable area](https://user-images.githubusercontent.com/17632589/164042420-18210448-99fe-4b89-a784-5df14572da0b.jpg)


If 'x' clicked, trigger API: get_statistics

If 'upvote' clicked, trigger API: upvote_command

If 'share' clicked, trigger API: share_to_read

If 'author_pfp' clicked, trigger API: user_profile

If 'statistics' clicked, trigger API: get_statistics

If 'home button' clicked, trigger API: get_article_list_and_view

If 'search button' clicked, trigger API: search_page

If 'add article button' clicked, trigger API: article_prep_page

If 'activity wall button' clicked, trigger API: activity_wall

If 'user profile page button' clicked, trigger API: user_profile_page

