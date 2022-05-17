<script>
import Article from '@/components/Article.vue';
import Author from '@/components/Author.vue';
import Tabs from '@/components/Tabs.vue';

export default ({
  data() {
    return {
        author: {
            "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
            "name": "Jessica Covington",
            "followers": "456",
            "following": "23",
            "description": "She turned her can't into can and her dreams into plans.",
        },
        articles: [
            {
                "article_id": 0,
                "article_title": "Tokenomics",
                "date_posted": 1651335773,
                "image_url": "http://localhost:8080/dynamic/post-6.png",
                "name": "Jessica Covington",
                "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
            },
            {
                "article_id": 1,
                "article_title": "School’s Out!",
                "date_posted": 1651335773,
                "image_url": "http://localhost:8080/dynamic/post-7.png",
                "name": "Jessica Covington",
                "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
            }
        ],
        investments: [
            {
                "article_id": 0,
                "article_title": "Desiree King",
                "image_url": "http://localhost:8080/dynamic/post-8.png",
                "name": "Writes about",
                "pfp": "http://localhost:8080/dynamic/profile-6.png",
                "hashtag": "Luxary, Cats",
                "stakes": "25",
                "amount": "185/740",
                "followers": "9236",
                "following": "648",
            },
        ],
    }
  },
  components: {
    Article,
    Author,
    Tabs,
  }
})
</script>

<template>

    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content i-wrap">

            <Author :author="author" />

            <Tabs :tabList="profileTabs.slice().reverse()" tabRightButton="currency" >
                <template v-slot:tabPanel-1>
                    <div class="w-full flex justify-center" v-for="(article,index) in articles" :key="index">
                        <Article :article="article" class="blog-post--user-article" />
                    </div>
                </template>

                <template v-slot:tabPanel-2>
                    <div class="w-full flex justify-center" v-for="(investment,index) in investments" :key="index">
                        <Article :article="investment" class="blog-post--user-ivestment" />
                    </div>
                </template>
            </Tabs>

            <Error/>
        </div>

    </div>

</template>