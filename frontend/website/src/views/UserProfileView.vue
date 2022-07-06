<script>
import Article from '@/components/Article.vue';
import Author from '@/components/Author.vue';
import Tabs from '@/components/Tabs.vue';

export default ({
  data() {
    return {
        author: {},
        articles: [],
        // author: {
        //     "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
        //     "name": "Jessica Covington",
        //     "followers": "456",
        //     "following": "23",
        //     "description": "She turned her can't into can and her dreams into plans.",
        // },
        // articles: [
        //     {
        //         "article_id": 0,
        //         "article_title": "Tokenomics",
        //         "date_posted": 1651335773,
        //         "image_url": "http://localhost:8080/dynamic/post-6.png",
        //         "name": "Jessica Covington",
        //         "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
        //     },
        //     {
        //         "article_id": 1,
        //         "article_title": "School’s Out!",
        //         "date_posted": 1651335773,
        //         "image_url": "http://localhost:8080/dynamic/post-7.png",
        //         "name": "Jessica Covington",
        //         "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
        //     }
        // ],
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
  async created() {

    this.sendAllMultiApiRequests([
        {
            url: 'get_user_profile',
            data: {
                "user_id": window.user_id,
                "auth_id": window.user_id,
            }
        },
        {
            url: 'get_user_profile_articles',
            data: {
                "user_id": window.user_id
            }
        },
    ])
    .then((reponses) => {
        this.author = reponses[0][0];
        this.articles = reponses[1];
        // this.authors = reponses[1];
        // this.image_url = this.article[0].image_url;
        // this.userWalletBalance = reponses[1];
        // this.user_wallet_balance = this.userWalletBalance[0].user_wallet_balance;
    });
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

        <Header />

        <!-- Content -->
        <div class="content i-wrap">

            <div v-if="!isError">
                <Author :author="author" />

                <Tabs :tabList="profileTabs.slice().reverse()">
                    <template v-slot:btns>
                        <span class="currency-tag currency-tag--opacity-70">{{ author.balance }} {{
                            this.currency }}</span>
                    </template>
                    <template v-slot:tabPanel-1>
                        <template v-if="articles.length">
                            <div class="w-full flex justify-center container" v-for="(article,index) in articles"
                                :key="index">
                                <Article :article="article"
                                    :url="{ name: 'article_homepage', params: { articleId: article.id  } }"
                                    class="blog-post--user-article" />
                            </div>
                        </template>
                        <template v-else-if="isError == 0">
                            <div class="text-center">No article found!</div>
                        </template>
                    </template>

                    <template v-slot:tabPanel-2>
                        <div class="w-full flex justify-center container" v-for="(investment,index) in investments"
                            :key="index">
                            <Article :article="investment" class="blog-post--user-ivestment" />
                        </div>
                    </template>
                </Tabs>
            </div>

            <Error />
        </div>

    </div>

</template>