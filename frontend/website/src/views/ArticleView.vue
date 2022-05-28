<script>
import ArticleBanner from '@/components/ArticleBanner.vue';
import SharePopup from '@/components/SharePopup.vue';

export default ({
  data() {
    return {
      article: [],
      userWalletBalance: [],
      image_url: '',
      user_wallet_balance: '',
      share_link: 'https://insert-link-here.com',
      showPaytoReadConfirmation: false
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
        this.image_url = this.article[0].image_url;
        this.userWalletBalance = reponses[1];
        this.user_wallet_balance = this.userWalletBalance[0].user_wallet_balance;
    });

  },
  methods: {
      processToPayment() {
          this.showPaytoReadConfirmation = false;
          this.$router.push({ name: 'full_article_homepage' });
      }
  },
  components: {
      ArticleBanner,
      SharePopup
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

                <div class="i-wrap--v2">

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
                            <div class="mb-6"><a href="#" class="btn i-wrap--v2__btn" @click="showPaytoReadConfirmation = 1">Pay to Read (20 {{ this.currency }})</a></div>
                            <SharePopup :share_link="share_link" />
                        </div>
                    </div>

                </div>

                <!-- Pay to read confirmation  -->
                <div class="confirmation-popup" v-if="showPaytoReadConfirmation">
                    <div class="container">
                        <div class="flex justify-center items-center w-full">
                            <div>
                                <b class="f-18 primary-color">Are you sure?</b>
                            </div>
                            <div>
                                <button @click="processToPayment()" class="cn-btn ml-7 f-13 font-semibold">Yes</button>
                                <button @click="showPaytoReadConfirmation = 0" class="cn-btn ml-6 f-13 font-semibold">No</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats mb-16">
                    <div class="container">
                        <div class="flex items-center justify-center">
                            <div class="mr-6">
                                <img src="@/assets/img/stats-icon.svg" alt="" class="ml-auto">
                            </div>
                            <div class="stats__right">
                                <div><span class="aquamarine-color mr-1.5">{{ article.article_liquidation_time }}</span>Days until liquidation</div>
                                <div><span class="aquamarine-color mr-1.5">{{ article.article_total_reads }}</span>Reads</div>
                                <div><span class="aquamarine-color mr-1.5">{{ article.article_total_shares }}</span>Shares</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <Error/>
        </div>

    </div>

</template>