API:

**getArticleListAndView(user ID, starting ID index, numberOfArticles) //algorithm, window size**

Return:
- Article IDs
- Title (String)
- author
- author pic
- image.url
- Total TEA invested
- Date posted

//query article list

select distinct a.title, a.postdate, substring(a.content,1,50), u.name, u.profile_img, a.invested
from `QM`.`articles` a left join `QM`.`users` u on a.user_id=u.user_id
left join `QM`.`transactions` t on a.article_id=t.article_id
order by a.postdate

**getArticleHomepage(article ID)//query article homepage**
Return
- article.imageurl
- article.title
- article.author
- article.pfp
- article.description
- article.price
- article.liquidationtime
- Article.totalinvested
select a.header_img, a.title,u.name,u.profile_img,a.description,a.price,a.invested,a.liquidation_days
from `QM`.`articles`a left join  `QM`.`users` u on a.user_id=u.user_id
where a.article_id=10


**//query CheckTransactionStatus**
checkTransactionStatus(article ID, user ID)
Return
- ArticleReadPage properties
- ArticleHomepage properties
- Boolean (transaction status)


**//payCommand**
payCommand(article ID, amount, transaction type, user balance)
Return
- true
- false (error msg)
Insert into QM.transactions (article_id,user_id,tx_time,tx_amount,tx_type)
values (10, 1, auto, 20, 'upvote')

**//readPage**
getReadPage(article ID, user ID, transaction status)
Return
- Article.imageurl
- Article.title
- Article.authorName
- Author.pfp
- Article.body
select a.header_img, a.title,u.name,u.profile_img, a.content
from `QM`.`articles`a left join  `QM`.`users` u on a.user_id=u.user_id
where a.article_id=10

**//statistics**
getStatistics(article ID, time, user)
Return
- article.liquidationTime
- article.totalinvested
- article.userStake
- article.userInvestment

select a.liquidation_days,a.invested,ua.invested, ua.invested/a.invested
from `QM`.`articles`a left join  `QM`.`user_article` ua on a.article_id=ua.article_id
where ua.user_id=1


**//upvote**
upvoteCommand(article ID, amount, user balance)
Return
- True
- False
getStatistics(article ID, time, user)
Return
- article.liquidationtime
- article.totalinvested
- article.userStake
- article.userInvestment

select a.liquidation_days,a.invested,ua.invested, ua.invested/a.invested
from `QM`.`articles`a left join  `QM`.`user_article` ua on a.article_id=ua.article_id
where ua.user_id=1

**//checkTransactionBoolean**
checkTxBoolean(amount, transaction type, user ID)
Return
- User wallet balance
- u.balance>a.price 

**//user profile page**
userProfilePage(User ID)
Return:
- username
- #followers
- #following
- User bio
- Pfp
- wallet balance
- Articles written
- Article IDs
- Title (String)
- Author
- author pic
- Image.url
- Total TEA invested
- Date posted

select distinct
u.user_id, u.name,u.bio,u.profile_img,u.balance,a.title,u.profile_img,a.header_img,
a.invested,a.postdate
from `QM`.`users` u left join `QM`.`articles` a on u.user_id=a.user_id
where u.user_id = 1

**Following:**
select count(distinct follower_id)
from `QM`.`follows`
where followee_id=1

**Follower**：
select count(distinct followee_id)
from `QM`.`follows` where follower_id=1

**//SearchPage**
getArticleListandView(user ID, starting ID index, numberOfArticles) 
//algorithm, window size
Return:
- Article IDs
- Title (String)
- author
- author pic
- image.url
- Total TEA invested
- Date posted
Search by category or hashtag
SELECT distinct
a.title, u.name,u.profile_img,a.header_img,a.category, a.invested,a.postdate,a.minutes
FROM articles a left join users u on a.user_id=u.user_id
where category = '%%' or hashtag='%%';

**//investmentsPage**
InvestmentsPage(User ID)
Return for each invested article:
- Article IDs
- Title (String)
- Author
- author pic
- Tags
- Image.url
- Total TEA invested
- User stake
- Liquidation time
- Date posted
select distinct
a.title,u.name,u.profile_img,a.category,a.header_img,a.invested,
ua.invested,a.liquidation_days,a.postdate
from users u left join user_article ua on u.user_id=ua.user_id
 left join articles a on u.user_id=a.user_id
where u.user_id = 1

**//cashOut**
cashOutCommand(article ID, amount)
Return
- True
- False

getArticleHomepage(article ID, time, user)
Return
- article.imageurl
- article.title
- article.author
- author.pfp
- article.description
- article.price
- article.liquidationtime
- Article.totalinvested



select distinct
title,header_img,u.name,u.profile_img,a.description,a.price,
a.invested,a.liquidation_days
from articles a left join users u on a.user_id=u.user_id
where a.article_id= 1



//article prep page
articlePrepPage(User ID)
Strings
“Enter title”
“Enter description”
“Enter body”
“Set liquidation time”
“Set price”

insert into articles (title,description,content,liquidation_days,price,category,
hashtag,mintues) values('abc','blueblueblue','i like swiming','7','10','hobby',
'swim','1')
//article confirmation page
articleConfirmationPage(Article ID)
Return
- Article title
- Liquidation time
- Total TEA invested

select distinct
title,liquidation_days,invested
from articles 

