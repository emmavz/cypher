<script>
import ArticleBanner from '@/components/ArticleBanner.vue';
import SharePopup from '@/components/SharePopup.vue';
import UpvotePopup from '@/components/UpvotePopup.vue';
import Tabs from '@/components/Tabs.vue';

export default ({
  data() {
    return {
      article: [],
      userWalletBalance: [],
      image_url: '',
      user_wallet_balance: '',
      share_link: 'https://insert-link-here.com',
      isModalVisible: false,
      showPaytoReadConfirmation: false,
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
      showModal() {
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
      },
      processToPayment() {
          this.showPaytoReadConfirmation = false;
          this.$router.push({ name: 'routename' });
      }
  },
  components: {
      ArticleBanner,
      SharePopup,
      UpvotePopup,
      Tabs
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

                <div class="i-wrap--v2 border-b-0">

                    <div class="container">

                        <div class="text-center mb-0">
                            <h1 class="mb-3">{{ article.article_title }}</h1>
                            <div class="mb-0">
                                <a href="#" class="inline-flex items-center i-wrap--v2__profile">
                                    <img :src="article.author_pfp" alt="" class="mr-4" width="35">
                                    {{ article.article_author }}
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <Tabs :tabList="articleTabs">

                    <template v-slot:btns>
                        <div class="flex">
                            <UpvotePopup class="sm-btns-container" />
                            <SharePopup :share_link="share_link" name="Share" class="sm-btns-container ml-1.5" />
                        </div>
                    </template>

                    <template v-slot:tabPanel-1>
                        <div class="mb-10 f-14 container">
                            <p>
                                Hiya there!<br><br>
                                If you asked me how many recipes I’ve tried to get super chewy, PERFECT cookies, I couldn’t even tell you. But what I can tell you, is the recipe that FINALLY worked for me.
                            </p>
                            <p class="mt-7 mb-7 f-17 font-semibold">
                                IT’S ALL ABOUT THE BROWN SUGAR!
                            </p>
                            <p>
                                Brown sugar is where a lot of people get it wrong. Before you buy brown sugar from the store, make sure to look at the consistency. It’s suuuuper important that you get the right kind or else your cookies are going to be suuuuper NOT chewy. Trust me. Been there, done that. Below, you’ll find a list of things to look out for when buying brown sugar during your next trip to the grocery.
                            </p>
                        </div>
                    </template>

                    <template v-slot:tabPanel-2>
                        <div class="stats mb-8 border-b-0 -mt-10">
                            <div class="container">
                                <div class="mb-4 mt-2 f-20"><b>Article Statistics</b></div>
                                <div class="flex items-center ">
                                    <div class="mr-6">
                                        <img src="@/assets/img/stats-icon.svg" alt="" class="ml-auto">
                                    </div>
                                    <div class="stats__right">
                                        <div><span class="aquamarine-color mr-1.5">{{ article.article_liquidation_time }}</span>Days until liquidation</div>
                                        <div><span class="aquamarine-color mr-1.5">{{ article.article_total_reads }}</span>Reads</div>
                                        <div><span class="aquamarine-color mr-1.5">{{ article.article_total_shares }}</span>Shares</div>
                                    </div>
                                </div>

                                <div class="mb-4 mt-10 f-20"><b>Your investment in <span class="aquamarine-color">Eliza Mae</span></b></div>
                                <div class="flex items-center">
                                    <div class="mr-6">
                                        <img src="@/assets/img/stats-icon--v3.svg" alt="" class="ml-auto">
                                    </div>
                                    <div class="stats__right">
                                        <div>You Own<span class="aquamarine-color mr-1.5 ml-1.5 mb-1">16.67%</span>Stake</div>
                                        <div><span class="aquamarine-color mr-1.5">20{{this.currency}}</span>Invested</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Tabs>

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

            </div>

            <Error/>
        </div>

    </div>

</template>