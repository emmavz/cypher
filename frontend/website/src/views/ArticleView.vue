<script>
export default ({
  data() {
    return {
      article: [],
      userWalletBalance: [],
    }
  },
  created() {

    this.sendAllMultiApiRequests([
        {
            url: 'get_article_homepage',
            data: {
                "article_id": Number(this.$route.params.articleId)
            }
        },
        {
            url: 'get_user_profile',
            data: {
                "user_id": 2
            }
        },
    ])
    .then((reponses) => {
        this.article = reponses[0];
        this.userWalletBalance = reponses[1];
    });

  },
})
</script>

<template>

    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content">

            <div v-for="(article, index) in article" :key="index">
                <div class="relative flex justify-center banner_img">
                    <img :src="article.image_url" alt="" class="w-full">
                    <span>{{ userWalletBalance[0].user_wallet_balance }} {{ this.currency }}</span>
                    <button class="close-icon"><img src="/src/assets/img/close-icon.svg" alt="" width="34"></button>
                </div>

                <div class="container">

                    <div class="text-center i-wrap--v2 mb-4">
                        <h1 class="mb-3">{{ article.article_title }}</h1>
                        <div class="mb-3">
                            <a href="#" class="inline-flex items-center i-wrap--v2__profile">
                                <img :src="article.author_pfp" alt="" class="mr-4">
                                {{ article.article_author }}
                            </a>
                        </div>
                        <p class="mb-6">
                            {{ article.article_description }}
                        </p>
                        <div class="mb-6"><a href="#" class="btn f-16">Pay to Read (20 {{ this.currency }})</a></div>
                        <div><a href="#" class="btn">Share to Read</a></div>
                    </div>
                </div>
                <div class="stats mb-16">
                    <div class="container">
                        <div class="flex items-center justify-center">
                            <div class="mr-6">
                                <img src="/src/assets/img/stats-icon.svg" alt="" class="ml-auto">
                            </div>
                            <div class="stats__right">
                                <div><span>{{ article.article_liquidation_time }}</span>Days until liquidation</div>
                                <div><span>{{ article.article_total_reads }}</span>Reads</div>
                                <div><span>{{ article.article_total_shares }}</span>Shares</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Error/>
        </div>

    </div>

</template>