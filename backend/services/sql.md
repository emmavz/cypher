### query article list
select distinct a.title, a.postdate, substring(a.content,1,50), u.name, u.profile_img, a.invested
from `QM`.`articles` a left join `QM`.`users` u on a.user_id=u.user_id
left join `QM`.`transactions` t on a.article_id=t.article_id
order by a.postdate

### query article homepage
select a.header_img, a.title,u.name,u.profile_img,a.description,a.price,a.invested,a.liquidation_days
from `QM`.`articles`a left join  `QM`.`users` u on a.user_id=u.user_id
where a.article_id=10

### payCommand


Insert into QM.transactions (article_id,user_id,tx_time,tx_amount,tx_type)
values (10, 1, auto, 20, 'upvote')

### readPage

select a.header_img, a.title,u.name,u.profile_img, a.content
from `QM`.`articles`a left join  `QM`.`users` u on a.user_id=u.user_id
where a.article_id=10

### statistics

select a.liquidation_days,a.invested,ua.invested, ua.invested/a.invested
from `QM`.`articles`a left join  `QM`.`user_article` ua on a.article_id=ua.article_id
where ua.user_id=1


### upvote

select a.liquidation_days,a.invested,ua.invested, ua.invested/a.invested
from `QM`.`articles`a left join  `QM`.`user_article` ua on a.article_id=ua.article_id
where ua.user_id=1

### checkTransactionBoolean

u.balance>a.price 

### user profile page

select distinct
u.user_id, u.name,u.bio,u.profile_img,u.balance,a.title,u.profile_img,a.header_img,
a.invested,a.postdate
from `QM`.`users` u left join `QM`.`articles` a on u.user_id=a.user_id
where u.user_id = 1

Following:
select count(distinct follower_id)
from `QM`.`follows`
where followee_id=1

Follower：
select count(distinct followee_id)
from `QM`.`follows` where follower_id=1

### SearchPage

SELECT distinct
a.title, u.name,u.profile_img,a.header_img,a.category, a.invested,a.postdate,a.minutes
FROM articles a left join users u on a.user_id=u.user_id
where category = '%%' or hashtag='%%';

### investmentsPage

select distinct
a.title,u.name,u.profile_img,a.category,a.header_img,a.invested,
ua.invested,a.liquidation_days,a.postdate
from users u left join user_article ua on u.user_id=ua.user_id
 left join articles a on u.user_id=a.user_id
where u.user_id = 1

### cashOut

select distinct
title,header_img,u.name,u.profile_img,a.description,a.price,
a.invested,a.liquidation_days
from articles a left join users u on a.user_id=u.user_id
where a.article_id= 1


### article prep page


select distinct
title,liquidation_days,invested
from articles 

### add article

insert into articles (title,description,content,liquidation_days,price,category,
hashtag,mintues) values('abc','blueblueblue','i like swiming','7','10','hobby',
'swim','1')
