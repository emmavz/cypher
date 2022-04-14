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
![article_list clickable area](https://user-images.githubusercontent.com/17632589/163292189-bd1755cd-507e-416e-82f9-39ab6716466b.jpeg)

### Image area

If clicked, trigger paid_or_not API. 

If paid_or_not API = false, go to article_homepage

The URL to the article_homepage page is /article/[article_id]?uid=[userid]


# Empty article list page
If there is anything wrong that we cannot show the list, show this page.
The text on this page is "error. Sorry! This request couldn't be processed right now. Please try again later!"

<img width="275" alt="Screen Shot 2022-04-13 at 5 51 49 PM" src="https://user-images.githubusercontent.com/17632589/163292806-a1798f1e-9a38-4741-809a-b0535c8c3a1c.png">

# Article_homepage page

URL /article/[article_id]/uid=[userid]

Input value: article_id is the article_id

uid is the user_id

<img width="420" alt="article_homepage" src="https://user-images.githubusercontent.com/17632589/163291830-904ef29a-4128-4152-8253-59ae128daf44.png">

API: get_article_homepage

Request: 'article_id'

Response: 'image_url', 'article_title', 'article_author', 'author_pfp', 'article_description', 'article_price', 'article_liquidation_time', 'article_total_invested'

UI elements:
![article_homepage_UI elements](https://user-images.githubusercontent.com/17632589/163291846-afc8f054-d6a8-4df5-a4af-35cab5fea582.jpeg)


If API failed with an error message, go to article_list_view


## Clickable area

pay_to_read button

![article_homepage clickable area](https://user-images.githubusercontent.com/17632589/163291842-b3e7c0a0-3e47-478c-bee2-d3bfd2d5ba58.jpeg)


### Clickable area 1.
If clicked, trigger API: pay_to_read command, 
//'are you sure?' button
<img width="472" alt="are you sure" src="https://user-images.githubusercontent.com/17632589/163291813-0111bf74-5b2d-4a7b-b678-10e393be5a26.png">

When response received, and if response = empty, trigger get_read_page API query, and render get_read_page

read_page screenshot:
<img width="473" alt="article read page" src="https://user-images.githubusercontent.com/17632589/163291858-bb93fc56-3a41-4abf-b9c3-b0dbb3ac6594.png">

