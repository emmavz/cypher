<script>
import Article from "@/components/Article.vue";
import Search from "@/components/Search.vue";
import Tabs from "@/components/Tabs.vue";
export default {
  data() {
    return {
      q: this.$route.query.q,
      articles: [],
      authors: [],
    };
  },
  watch: {
    "$route.query.q": {
      handler: function () {
        this.q = this.$route.query.q;
        this.init();
      },
      deep: true,
      immediate: true,
    },
  },
  methods: {
    async init() {
      if (this.q) {
        this.sendAllMultiApiRequests([
          {
            url: "search_articles",
            data: {
              q: this.q,
            },
          },
          {
            url: "search_authors",
            data: {
              q: this.q,
              follower_id: this.getAuthId(),
            },
          },
        ]).then((reponses) => {
          this.articles = reponses[0];
          this.authors = reponses[1];
        });
      }
    },
    async follow(author_index, followed_id) {
      this.sendApiRequest(
        "do_follow_toggle",
        {
          follower_id: this.getAuthId(),
          followed_id: followed_id,
        },
        true
      ).then(() => {
        this.authors[author_index].is_followed = this.authors[author_index]
          .is_followed
          ? 0
          : 1;
      });
    },
  },
  components: {
    Article,
    Search,
    Tabs,
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content i-wrap">
      <Search triggerSearch="true" class="mb-1 -mt-5" />

      <Tabs :tabList="searchTabs" v-if="q && !isError">
        <template v-slot:tabPanel-1>
          <template v-if="articles.length">
            <div
              class="w-full flex justify-center container"
              v-for="(article, index) in articles"
              :key="index"
            >
              <Article
                :article="article"
                :url="{
                  name: 'article_homepage',
                  params: { articleId: article.id },
                }"
              />
            </div>
          </template>
          <template v-else-if="isError == 0">
            <div class="text-center">No article found!</div>
          </template>
        </template>

        <template v-slot:tabPanel-2>
          <template v-if="authors.length">
            <div
              class="w-full author-container"
              v-for="(author, ai) in authors"
              :key="ai"
            >
              <div
                :class="[
                  'flex pb-7 pt-7 container',
                  ai == 0 ? '-mt-9' : '-mt-6',
                ]"
              >
                <div>
                  <RouterLink :to="getUserProfileRoute(author.id)">
                    <img
                      :src="getPfpImage(author.pfp)"
                      alt=""
                      width="40"
                      height="40"
                      class="rounded-full"
                    />
                  </RouterLink>
                </div>
                <div class="pl-5">
                  <div class="flex justify-between">
                    <div class="f-20 font-semibold mb-2 mr-2">
                      <RouterLink :to="getUserProfileRoute(author.id)">
                        {{ author.name }}
                      </RouterLink>
                    </div>
                    <div>
                      <button
                        :class="[
                          'f-13 font-semibold border-radius px-1 py-1 m-w-76 h-27 text-center',
                          author.is_followed
                            ? 'primary-bg text-white'
                            : 'bg-white primary-color',
                        ]"
                        @click="follow(ai, author.id)"
                      >
                        {{ author.is_followed ? "Followed" : "Follow" }}
                      </button>
                    </div>
                  </div>
                  <p class="f-14">
                    {{ author.bio }}
                  </p>
                </div>
              </div>
            </div>
          </template>
          <template v-else-if="isError == 0">
            <div class="text-center">No author found!</div>
          </template>
        </template>
      </Tabs>

      <Error />
    </div>
  </div>
</template>
