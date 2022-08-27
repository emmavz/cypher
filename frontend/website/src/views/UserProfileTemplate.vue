<script>
import Article from "@/components/Article.vue";
import Author from "@/components/Author.vue";
import Tabs from "@/components/Tabs.vue";
import UpvotePopup from "@/components/UpvotePopup.vue";
import CashoutPopup from "@/components/CashoutPopup.vue";
import CopyLinkInput from "@/components/CopyLinkInput.vue";

export default {
  data() {
    return {
      author: {},
      articles: [],
      investments: [],
      votes: "",
      cashout: "",
      author_balance: 0,
      delete_article_confirmation: false,
      article_controls_active: false,
      current_article: '',
      componentKey: 0,
    };
  },
  async created() {
    await this.init();
  },
  methods: {
    async init() {
      this.sendAllMultiApiRequests([
        {
          url: "get_auth_user_profile",
          data: {},
        },
        {
          url: "get_user_profile_articles",
          data: {
            user_id: this.getAuthId(),
          },
        },
        {
          url: "get_user_investments",
          data: {},
        },
      ]).then((reponses) => {
        this.author = reponses[0];
        this.author_balance = this.author.balance;
        this.articles = reponses[1];
        this.investments = reponses[2];
      });
    },
    updateVotes(votes) {
      this.votes = votes;

      if (this.votes) {
        this.sendApiRequest(
          "upvote",
          {
            user_id: this.getAuthId(),
            amount: this.votes,
          },
          true
        ).then((response) => {
          this.author_balance -= this.votes;
          this.investments = response;
          this.forceRerender();
        });
      }
    },
    updateCashouts(cashout) {
      this.cashout = cashout;

      if (this.cashout) {
        this.sendApiRequest(
          "cashout",
          {
            amount: this.cashout,
            user_id: this.getAuthId(),
          },
          true
        ).then((response) => {
          this.author_balance += Number(this.cashout);
          this.investments = response;
          this.forceRerender();
        });
      }
    },
    showArticleControls(article) {
      this.article_controls_active = true;
      this.current_article = article;
    },
    closeArticleControls() {
      this.article_controls_active = false;
      this.delete_article_confirmation = false;
    },
    deleteArticle() {
      let currentArticle = this.current_article;
      this.sendApiRequest(
        "delete_article",
        {
          article_id: currentArticle.id,
        },
        true
      ).then(() => {
        this.article_controls_active = false;
        this.delete_article_confirmation = false;

        for (let index = 0; index < this.articles.length; index++) {
          const article = this.articles[index];
          if (article.id == currentArticle.id) {
            this.articles.splice(index, 1);
            break;
          }
        }
      });
    },
    forceRerender() {
      this.componentKey += 1;
    }
  },
  components: {
    Article,
    Author,
    Tabs,
    UpvotePopup,
    CashoutPopup,
    CopyLinkInput
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content i-wrap">
      <div v-if="!isError">
        <Author :author="author">
          <template v-slot:btns>
            <span class="currency-tag currency-tag--opacity-70">{{ this.user_balance(author_balance) }} {{ this.currency }}</span>
          </template>
        </Author>

        <Tabs :tabList="profileTabs">
          <template v-slot:btns>
            <li>
              <UpvotePopup @votes="updateVotes" :showpopup="$route.query.v" />
            </li>
            <li>
              <CashoutPopup @cashout="updateCashouts" />
            </li>
          </template>

          <template v-slot:tabPanel-1>
            <template v-if="articles.length">
              <div class="w-full flex justify-center container" v-for="(article) in articles" :key="article.id">
                <Article :article="article" url="" @article="showArticleControls" class="blog-post--user-article" />
              </div>
            </template>
            <template v-else-if="isError == 0">
              <div class="text-center">No article found!</div>
            </template>
          </template>

          <template v-slot:tabPanel-2>
            <template v-if="investments.length">
              <div class="w-full flex justify-center container" v-for="(investment) in investments" :key="investment.author_id">
                <Article :article="investment" :url="getUserProfileRoute(investment.author_id)"
                  class="blog-post--user-ivestment" :key="componentKey" />
              </div>
            </template>
            <template v-else-if="isError == 0">
              <div class="text-center">No investment found!</div>
            </template>
          </template>
        </Tabs>


        <div class="empty-layer" @click="closeArticleControls" v-if="article_controls_active"></div>

        <div :class="['article_controls', { 'article_controls--active': article_controls_active }]">
          <div class="container">

            <div v-if="!delete_article_confirmation">
              <div class="article_controls__line mb-6"></div>
              <div class="article_controls__link mb-9">
                <CopyLinkInput
                  :url="current_article ? getFullUrl($router.resolve({ name: 'article_homepage', params: { articleId: current_article.id } }).fullPath) : ''" />
              </div>

              <div class="article_controls__li article_controls__li--border">
                <ul>
                  <li>
                    <router-link :to="{ name: 'create_article', params: { articleId: current_article.id }, query: {edit: true} }">Edit
                    </router-link>
                  </li>
                  <li>
                    <button class="red" @click="delete_article_confirmation = 1">Delete</button>
                  </li>
                </ul>
              </div>
            </div>

            <div v-else>
              <div class="article_controls__line"></div>
              <p class="f-14 font-bold text-center mt-10 mb-8">
                Are you sure you want to delete this article?
              </p>
              <div class="article_controls__li">
                <ul>
                  <li>
                    <button @click="delete_article_confirmation = 0">Cancel</button>
                  </li>
                  <li>
                    <button class="red" @click="deleteArticle">Delete</button>
                  </li>
                </ul>
              </div>
            </div>

          </div>
        </div>

      </div>

      <Error />
    </div>
  </div>
</template>
