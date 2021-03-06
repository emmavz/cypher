<script>
import ArticleBanner from '@/components/ArticleBanner.vue';
import SharePopup from '@/components/SharePopup.vue';
import UpvotePopup from '@/components/UpvotePopup.vue';
import Tabs from '@/components/Tabs.vue';

export default ({
  data() {
    return {
      articleId: Number(this.$route.params.articleId),
      article: null,
      user: null,
      auth_id: window.user_id,
      image_url: '',
      share_btn: 'Share',
      share_heading: 'Share Article',
      share_description: 'Thanks for helping an author out! Remember, the more people who invest in this author, the more CPHR you can earn! appy sharing!',
      share_link: '',
      isModalVisible: false,
      liquidation_days: 0,
      stats: null,
    }
  },
  created() {

      this.share_link = this.getFullUrl(this.$router.resolve({ name: 'article_homepage', params: { articleId: this.articleId } }).fullPath);

    this.sendApiRequest('get_full_article', {
        "article_id": this.articleId,
        "auth_id": this.auth_id,
        'referral_token': this.getReferralToken()
    })
    .then((responses) => {

        if (typeof responses[0].is_article_paid !== 'undefined' && !responses[0].is_article_paid) {
            return this.$router.push({ name: 'article_homepage', params: { articleId: this.articleId } });
        }

        this.article = responses[0];
        this.image_url = this.article.image_url;

        this.user = responses[1];

        this.stats = responses[2];

        this.liquidation_days = this.getLiquidationDays(this.current_time, this.article.date_posted);

        this.share_link = this.getFullUrl(this.$router.resolve({ name: 'article_homepage', params: { articleId: this.articleId, referralToken: this.user.referral_token } }).fullPath);
    });

  },
  methods: {
      showModal() {
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
      },
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

        <Header />

        <!-- Content -->
        <div class="content">

            <div v-if="!isError && article">
                <ArticleBanner :image_url="image_url"
                    :back_url="{ name: 'article_homepage', params: { articleId: article.id  } }" />

                <div class="i-wrap--v2 border-b-0 -mb-4">

                    <div class="container">

                        <div class="text-center mb-0">
                            <h1 class="mb-3">{{ article.title }}</h1>
                            <div class="mb-0">
                                <RouterLink :to="getUserProfileRoute(article.user_id)"
                                    class="inline-flex items-center i-wrap--v2__profile">
                                    <img :src="article.user.pfp" alt="" class="mr-4" width="35">
                                    {{ article.user.name }}
                                </RouterLink>
                            </div>
                        </div>
                    </div>

                </div>

                <Tabs :tabList="articleTabs">

                    <template v-slot:btns>
                        <div class="flex">
                            <div class="sm-btns-container" v-if="article.user_id != auth_id">
                                <RouterLink :to="getUserProfileRoute(article.user_id, {v: 1})">
                                    <button class=" currency-tag currency-tag--opacity-70" @click="$r">Upvote</button>
                                </RouterLink>
                            </div>
                            <SharePopup :share_btn="share_btn" :share_heading="share_heading"
                                :share_description="share_description" :share_link="share_link"
                                class="sm-btns-container ml-1.5" />
                        </div>
                    </template>

                    <template v-slot:tabPanel-1>
                        <div class="mb-10 f-14 container w_template" v-html="article.content"></div>
                        <!-- <p>
                                Hiya there!<br><br>
                                If you asked me how many recipes I’ve tried to get super chewy, PERFECT cookies, I couldn’t even tell you. But what I can tell you, is the recipe that FINALLY worked for me.
                            </p>
                            <p class="mt-7 mb-7 f-17 font-semibold">
                                IT’S ALL ABOUT THE BROWN SUGAR!
                            </p>
                            <p>
                                Brown sugar is where a lot of people get it wrong. Before you buy brown sugar from the store, make sure to look at the consistency. It’s suuuuper important that you get the right kind or else your cookies are going to be suuuuper NOT chewy. Trust me. Been there, done that. Below, you’ll find a list of things to look out for when buying brown sugar during your next trip to the grocery.
                            </p> -->
                    </template>

                    <template v-slot:tabPanel-2>
                        <div class="stats mb-8 border-b-0 -mt-10">
                            <div class="container">
                                <div class="mb-6 mt-2 f-20"><b>Article Statistics</b></div>
                                <div class="flex items-center ">
                                    <div class="mr-6">
                                        <img src="@/assets/img/stats-icon.svg" alt="" class="ml-auto">
                                    </div>
                                    <div class="stats__right">
                                        <div><span class="aquamarine-color mr-1.5">{{
                                                liquidation_days
                                                }}</span>Days until liquidation</div>
                                        <div><span class="aquamarine-color mr-1.5">{{ article.total_reads_count
                                                }}</span>Reads</div>
                                        <div><span class="aquamarine-color mr-1.5">{{ article.total_shares_count
                                                }}</span>Shares</div>
                                    </div>
                                </div>

                                <template v-if="stats">
                                    <div>
                                        <div class="mb-6 mt-10 f-20"><b>Your investment in
                                                <RouterLink :to="getUserProfileRoute(article.user_id)">
                                                    <span class="aquamarine-color">{{
                                                        article.user.name }}</span>
                                                </RouterLink>
                                            </b></div>
                                        <div class="flex items-center">
                                            <div class="mr-6">
                                                <img src="@/assets/img/stats-icon--v3.svg" alt="" class="ml-auto">
                                            </div>
                                            <div class="stats__right">
                                                <div>You Own<span class="aquamarine-color mr-1.5 ml-1.5 mb-1">{{
                                                        stats.total_stakes }}%</span>Stake
                                                </div>
                                                <div class="-mt-2"><span class="aquamarine-color mr-1.5">{{
                                                        stats.user_total_investments
                                                        }}</span><span class="mr-1.5">{{this.currency}}</span>Invested
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </template>
                </Tabs>

            </div>

            <Error />
        </div>

    </div>

</template>