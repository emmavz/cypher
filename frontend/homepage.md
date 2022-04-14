# Overview

# List of articles
Input url is /home?uid=[userid]
<img width="413" alt="get_article_list_view" src="https://user-images.githubusercontent.com/17632589/163287942-eb1ba918-756d-40c2-9fe5-b5de84098d96.png">
Assume user has logged in. User will see a list of articles here.
API call is query_article_list_properties
Request value 'id_x' //list of article ids
Respones value 'article_title' 'author_name' 'author_pfp' 'date_posted' 'total_invested' 'image_url'

The UI element mapping to the response value:
![article_list UI elements](https://user-images.githubusercontent.com/17632589/163289703-5ac353a4-500d-46f1-8f96-edb3b9fd318b.jpeg)


In case of error response. show the error message. then an empty article list page

## Clickable area 
### Button TODO...

### Image area
Show in the screenshot...
If clicked, trigger paid_or_not API. If paid_or_not API = false, go to article_homepage
The URL to the article_homepage page is /article/[article_id]?uid=[userid]


# Empty article list page
If there is anything wrong that we cannot show the list, show this page.
The text on this page is "error"

TODO Screenshot here...

# Article_homepage page
URL /article/[article_id]/uid=[userid]
Input value: article_id is the article_id
uid is the user_id

Screenshot here ... TODO...
API: get_article_homepage
Request: 'article_id'
Response: 'image_url', 'article_title', 'article_author', 'author_pfp', 'article_description', 'article_price', 'article_liquidation_time', 'article_total_invested'

UI element mapping to response data property... TODO...

If API failed with an error message, go to article_list_view

## Clickable area
//pay to read button
Screenshot of clickable area


### Clickable area 1.
If clicked, trigger API: pay_to_read command, 
//'are you sure?' button
When response received, and if response = empty, trigger get_read_page API query, and render get_read_page
If UI has change, take screenshot here. 


