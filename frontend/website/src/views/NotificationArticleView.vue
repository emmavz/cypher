<script>
import ArticleBanner from '@/components/ArticleBanner.vue';

export default ({
  data() {
    return {
      article: [
          {
            "article_author": "Jessica Covington",
            "article_description": "Hey, school’s out! Are you wondering what you should do this summer? This article will give you a few options.",
            "article_liquidation_time": 7,
            "article_price": 15,
            "article_title": "School’s Out",
            "article_total_reads": 182,
            "article_total_shares": 835,
            "author_pfp": "http://localhost:8080/dynamic/userprofile-1.png",
            "image_url": "http://localhost:8080/dynamic/post-3-single.png",
            "total_invested": 524
        }
      ],
      userWalletBalance: [
        {
          "user_wallet_balance": 1256
        }
      ],
      image_url: 'http://localhost:8080/dynamic/post-3-single.png',
      user_wallet_balance: 1256,
    }
  },
  components: {
      ArticleBanner
  }
})
</script>

<template>

    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content">

            <ArticleBanner :image_url="image_url" :user_wallet_balance="user_wallet_balance" />

            <div v-for="(article, index) in article" :key="index">

                <div class="i-wrap--v2 i-wrap--v3">
                    <div class="container">
                        <div class="text-center mb-4">
                            <h1 class="mb-3">{{ article.article_title }}</h1>
                            <div class="mb-3">
                                <a href="#" class="inline-flex items-center i-wrap--v2__profile">
                                    <img :src="article.author_pfp" alt="" class="mr-4" width="35">
                                    {{ article.article_author }}
                                </a>
                            </div>
                            <p class="mb-6">
                                {{ article.article_description }}
                            </p>
                            <div><a href="#" class="btn i-wrap--v3__btn">Read</a></div>
                        </div>
                    </div>
                </div>

                <div class="stats mb-8 border-b-0">
                    <div class="container">
                        <div class="mb-8 mt-2"><b>Article Statistics</b></div>
                        <div class="flex items-center">
                            <div class="mr-6">
                                <img src="@/assets/img/stats-icon--v4.svg" alt="" class="ml-auto">
                            </div>
                            <div class="stats__right">
                                <div>{{ article.article_total_reads }}<span class="aquamarine-color ml-1.5">Reads</span></div>
                                <div><span class="aquamarine-color mr-1.5">{{ article.article_total_shares }}</span>Shares</div>
                                <div>{{ article.total_invested }} {{ this.currency }}<span class="aquamarine-color ml-1.5">earned</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Error/>
        </div>

    </div>

</template>