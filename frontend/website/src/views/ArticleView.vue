<script>
import ArticleBanner from "@/components/ArticleBanner.vue";
import SharePopup from "@/components/SharePopup.vue";
import LuckySharerPopup from "@/components/LuckySharerPopup.vue";

export default {
  data() {
    return {
      articleId: Number(this.$route.params.articleId),
      article: null,
      user: null,
      image_url: "",
      user_wallet_balance: "",
      share_description:
        "Each time you share this unique link, you start a <b>sharing chain</b>. Once somebody pays, everybody in that chain can read the article for free. You can share this link with up to <b>eight</b> friends.",
      share_link: "",
      showPaytoReadConfirmation: false,
      is_author_article: false,
      is_already_free: false,
      referral_token: this.$route.params.referralToken,
      showSuccessPopup: false,

    };
  },
  created() {
    // this.storeReferralToken(this.referral_token);

    this.sendApiRequest("get_article_homepage", {
      article_id: this.articleId,
      // referral_token: this.getReferralToken(),
      referral_token: this.referral_token,
    }).then((responses) => {
      this.article = responses[0];
      this.image_url = this.article.image_url;
      this.user = responses[1][0];
      this.user_wallet_balance = responses[1][1];
      this.is_author_article =
        this.getAuthId() == this.article.user_id ? true : false;
      this.is_already_free = this.isArticleFree(this.article);

      if(responses[3]) {
        this.showSuccessPopup = true;
        this.sendApiRequest("lucky_day_seen", {article_id: this.articleId});
      }

      // article share link
      let params = {
        articleId: this.articleId,
      };

      if(!this.is_already_free) {
        params.referralToken = responses[2];
      }

      this.share_link = this.getFullUrl(
        this.$router.resolve({
          name: "article_homepage",
          params: params,
        }).fullPath
      );
    });
  },
  methods: {
    processToPayment(article) {
      this.showPaytoReadConfirmation = false;

      let routeParam = {
        article_id: article.id,
      };

      this.sendApiRequest("pay_article", routeParam, true).then(() => {
        this.$router.push({ name: "full_article_homepage" });
      });
    },
    closeSuccessPopup() {
      this.showSuccessPopup = false;
    }
  },
  components: {
    ArticleBanner,
    SharePopup,
    LuckySharerPopup
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content">
      <div v-if="!isError && article">
        <ArticleBanner :image_url="image_url" :user_wallet_balance="user_wallet_balance" :back_url="{ name: 'home' }" />

        <div class="i-wrap--v2">
          <div class="container">
            <div class="text-center mb-4">
              <h1 class="mb-3">{{ article.title }}</h1>
              <div class="mb-3">
                <RouterLink :to="getUserProfileRoute(article.user_id)"
                  class="inline-flex items-center i-wrap--v2__profile">
                  <img :src="getPfpImage(article.user.pfp)" alt="" class="mr-4 rounded-full" width=" 35" />
                  {{ article.user.name }}
                </RouterLink>
              </div>
              <p class="mb-6">
                {{ article.description }}
              </p>
              <template v-if="is_already_free">
                <div>
                  <div>
                    <RouterLink :to="{
                      name: 'full_article_homepage',
                      params: { articleId: article.id },
                    }" class="btn i-wrap--v2__btn">Read</RouterLink>
                  </div>
                  <div class="mt-6" v-if="article.share_to_read">
                    <SharePopup share_btn="Share" share_heading="Share" :share_description="share_description"
                      :share_link="share_link" />
                  </div>
                </div>
              </template>
              <template v-else>
                <div>
                  <div class="mb-6">
                    <a href="#" class="btn i-wrap--v2__btn" @click="showPaytoReadConfirmation = 1">Pay to Read ({{
                    article.price }} {{ this.currency }})</a>
                  </div>
                  <SharePopup v-if="article.share_to_read" share_btn="Share to Read" share_heading="Share to Read"
                    :share_description="share_description" :share_link="share_link" />
                </div>
              </template>
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
              <div class="flex">
                <button @click="processToPayment(article)" class="cn-btn ml-7 f-13 font-semibold">
                  Yes
                </button>
                <button @click="showPaytoReadConfirmation = 0" class="cn-btn ml-6 f-13 font-semibold">
                  No
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="stats mb-8 border-b-0" v-if="is_author_article">
          <div class="container">
            <div class="mb-8 mt-2"><b>Article Statistics</b></div>
            <div class="flex items-center">
              <div class="mr-6">
                <img src="@/assets/img/stats-icon--v4.svg" alt="" class="ml-auto" />
              </div>
              <div class="stats__right">
                <div>
                  {{ article.total_reads_count
                  }}<span class="aquamarine-color ml-1.5">Reads</span>
                </div>
                <div>
                  <span class="aquamarine-color mr-1.5">{{
                  article.total_shares_count
                  }}</span>Shares
                </div>
                <div>
                  {{ toFixedAmount2(article.total_investments) }} Tokens <span class="aquamarine-color ml-1.5">earned</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="stats mb-12" v-else-if="is_already_free">
          <div class="container">
            <div class="mb-8 mt-2"><b>Article Statistics</b></div>
            <div class="flex items-center">
              <div class="mr-6">
                <img src="@/assets/img/stats-icon--v4.svg" alt="" class="ml-auto" />
              </div>
              <div class="stats__right">
                <template v-if="!is_already_free">
                  <div>
                    <span class="aquamarine-color mr-1.5">{{
                    article.remaining_liquidation_days
                    }}</span>Days until liquidation
                  </div>
                </template>
                <div>
                  <span class="aquamarine-color mr-1.5">{{
                  article.total_reads_count
                  }}</span>Reads
                </div>
                <div>
                  <span class="aquamarine-color mr-1.5">{{
                  article.total_shares_count
                  }}</span>Shares
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="stats mb-12" v-else>
          <div class="container">
            <div class="flex items-center">
              <div class="mr-6">
                <img src="@/assets/img/stats-icon.svg" alt="" class="ml-auto" />
              </div>
              <div class="stats__right">
                <template v-if="!is_already_free">
                  <div>
                    <span class="aquamarine-color mr-1.5">{{
                      article.remaining_liquidation_days
                    }}</span>Days until liquidation
                  </div>
                </template>
                <div>
                  <span class="aquamarine-color mr-1.5">{{
                  article.total_reads_count
                  }}</span>Reads
                </div>
                <div>
                  <span class="aquamarine-color mr-1.5">{{
                  article.total_shares_count
                  }}</span>Shares
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <LuckySharerPopup :showSuccessPopup="showSuccessPopup" @showpopup="closeSuccessPopup" />

      <Error />

    </div>
  </div>
</template>
