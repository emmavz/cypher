<script>
import Category from "@/components/Category.vue";
import Article from "@/components/Article.vue";
import Search from "@/components/Search.vue";
export default {
  data() {
    return {
      articlesOffset: 0,
      articlesLimit: 5,
      stopscrollAjax: false,
      articles: [],
      categories: [],
    };
  },
  async created() {
    this.sendApiRequest("get_tags", {}, false, {
      removeLoaderAfterApi: false,
    }).then((tags) => {
      this.categories = tags;
    });

    this.getArticles();
  },
  mounted() {
    const contentElm = document.querySelector(".content");
    contentElm.onscroll = () => {
      if (!this.stopscrollAjax) {
        let bottomOfWindow =
          contentElm.scrollTop + contentElm.clientHeight >=
          contentElm.scrollHeight - window.bottomGap;
        if (bottomOfWindow) {
          this.articlesOffset += this.articlesLimit;
          this.getArticles();
        }
      }
    };
  },
  methods: {
    async getArticles() {
      this.stopscrollAjax = true;
      this.sendApiRequest("get_recommendations", {
        offset: this.articlesOffset,
        limit: this.articlesLimit,
      })
        .then((articles) => {
          if (articles.length) {
            this.articles = this.articles.concat(articles);
            this.stopscrollAjax = false;
          } else {
            this.stopscrollAjax = true;
          }
        })
        .catch(() => {
          this.stopscrollAjax = false;
        });
    },
  },
  components: {
    Category,
    Article,
    Search,
  },
};
</script>

<template>
  <div class="app-wp">
    <Header categories="categories">
      <template v-slot:search>
        <Search />
      </template>
    </Header>

    <!-- Content -->
    <div class="content i-wrap">
      <div class="container">
        <ul class="categories recommendations whitespace-normal flex flex-wrap">
          <li v-for="(category, index) in categories" :key="index">
            <Category :category="category" class="mb-3.5" />
          </li>
        </ul>

        <h3 class="mt-2.5 mb-4" v-if="articles.length">Recommended</h3>
      </div>

      <div
        class="blog-post-wrap container flex flex-wrap"
        v-if="articles.length"
      >
        <div
          class="w-full flex justify-center"
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
      </div>

      <Error />
    </div>
  </div>
</template>
