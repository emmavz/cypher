<script>
import Article from '@/components/Article.vue';
import Author from '@/components/Author.vue';
import Tabs from '@/components/Tabs.vue';
import StatsInvestment from '@/components/StatsInvestment.vue';
import StatsStakes from '@/components/StatsStakes.vue';

export default ({
  data() {
    return {
        author: {
            "pfp": "http://localhost:8080/dynamic/profile-2.png",
            "name": "Eliza Mae",
            "followers": "987",
            "following": "53",
            "description": "Just another baker blogger",
        },
        articles: [
            {
                "article_id": 0,
                "article_title": "Super Chewy Cookies Recipe",
                "date_posted": 1651335773,
                "hashtag": "for you, baking",
                "image_url": "http://localhost:8080/dynamic/post-2.png",
                "name": "Eliza Mae",
                "pfp": "http://localhost:8080/dynamic/profile-2.png",
                "total_invested": 7342
            },
            {
                "article_id": 1,
                "article_title": "The ORIGINAL Quillmates?!",
                "date_posted": 1651335773,
                "hashtag": "for you, Gossip",
                "image_url": "http://localhost:8080/dynamic/post-9.png",
                "name": "Eliza Mae",
                "pfp": "http://localhost:8080/dynamic/profile-2.png",
                "total_invested": 7342
            }
        ],
        statsInvestment: {
            amount: 7342,
            investors: 43
        },
        statsStakes: {
            amount: 20,
            stakes: 0.2724
        }
    }
  },
  components: {
    Article,
    Author,
    Tabs,
    StatsInvestment,
    StatsStakes
  }
})
</script>

<template>

    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content i-wrap">

            <Author :author="author" anotherProfile="true" />

            <Tabs :tabList="profileTabs" tabRightButton="upvote">
                <template v-slot:tabPanel-1>
                     <StatsInvestment :statsInvestment="statsInvestment" />
                     <StatsStakes :statsStakes="statsStakes" class="mt-4" />
                </template>

                <template v-slot:tabPanel-2>
                     <div class="w-full flex justify-center" v-for="(article,index) in articles" :key="index">
                        <Article :article="article" />
                    </div>
                </template>
            </Tabs>

            <Error/>
        </div>

    </div>

</template>