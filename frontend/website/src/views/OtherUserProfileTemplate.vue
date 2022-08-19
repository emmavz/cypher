<script>
import Article from "@/components/Article.vue";
import Author from "@/components/Author.vue";
import UpvotePopup from "@/components/UpvotePopup.vue";
import CashoutPopup from "@/components/CashoutPopup.vue";
import Tabs from "@/components/Tabs.vue";
import StatsInvestment from "@/components/StatsInvestment.vue";
import StatsStakes from "@/components/StatsStakes.vue";

export default {
  data() {
    return {
      user_id: Number(this.$route.params.userId),
      author: {},
      articles: [],
      statsInvestment: {},
      statsStakes: {},
      votes: "",
      cashout: "",
      author_balance: 0,
    };
  },
  async created() {
    this.sendAllMultiApiRequests([
      {
        url: "get_user_profile_with_balance",
        data: {
          user_id: this.user_id,
        },
      },
      {
        url: "get_user_profile_articles",
        data: {
          user_id: this.user_id,
        },
      },
      {
        url: "get_other_user_investments",
        data: {
          user_id: this.user_id,
        },
      },
    ]).then((reponses) => {
      this.author = reponses[0][0];
      this.author_balance = reponses[0][1];
      this.articles = reponses[1];
      this.initStats(reponses[2]);
    });
  },
  methods: {
    updateVotes(votes) {
      this.votes = votes;

      if (this.votes) {
        this.sendApiRequest(
          "upvote",
          {
            user_id: this.user_id,
            amount: this.votes,
          },
          true
        ).then((response) => {
          this.initStats(response);
          this.author_balance -= this.votes;
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
            user_id: this.user_id,
          },
          true
        ).then((response) => {
          this.initStats(response);
          this.author_balance += Number(this.cashout);
        });
      }
    },
    initStats(user_stats) {
      this.statsInvestment.amount = user_stats.total_investments;
      this.statsInvestment.investors = user_stats.total_investors;
      this.statsStakes.amount = user_stats.user_total_investments;
      this.statsStakes.stakes = user_stats.total_stakes;
    },
  },
  components: {
    Article,
    Author,
    Tabs,
    StatsInvestment,
    StatsStakes,
    UpvotePopup,
    CashoutPopup,
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content i-wrap">
      <div v-if="!isError">
        <Author :author="author" anotherProfile="true">
          <template v-slot:btns>
            <span class="currency-tag currency-tag--opacity-70">{{ author_balance }} {{ this.currency }}</span>
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

          <template v-slot:tabPanel-2>
            <div class="container">
              <StatsInvestment :statsInvestment="statsInvestment" class="mt-4" />
              <StatsStakes :statsStakes="statsStakes" class="mt-8" />
            </div>
          </template>

          <template v-slot:tabPanel-1>

            <template v-if="articles.length">
              <div class="w-full flex justify-center container" v-for="(article, index) in articles" :key="index">
                <Article :article="article" :url="{
                  name: 'article_homepage',
                  params: { articleId: article.id },
                }" />
              </div>
            </template>
            <template v-else-if="isError == 0">
              <div class="text-center">No article found!</div>
            </template>

          </template>
        </Tabs>
      </div>

      <Error />
    </div>
  </div>
</template>
