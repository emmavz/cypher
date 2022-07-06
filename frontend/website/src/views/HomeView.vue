<script>
import Article from '@/components/Article.vue';

export default ({
  data() {
    return {
      articlesOffset: 0,
      articlesLimit: 5,
      stopscrollAjax: false,
      articles: [],
      categories: [{
          name: 'For You',
          url: '#'
        },{
          name: 'Coding',
          url: '#'
        },
        {
          name: 'Business',
          url: '#'
        },
        {
          name: 'Other Categories',
          url: '#'
        }
      ]
    }
  },
  created() {
    this.getArticles();
  },
  mounted() {
    const contentElm = document.querySelector('.content');
    contentElm.onscroll = () => {
      if (!this.stopscrollAjax) {
        let bottomOfWindow = contentElm.scrollTop + contentElm.clientHeight >= contentElm.scrollHeight - window.bottomGap;
        if (bottomOfWindow) {
          this.articlesOffset += this.articlesLimit;
          this.getArticles();
        }
      }
    }
  },
  methods: {
    async getArticles() {
      this.stopscrollAjax = true;
      this.sendApiRequest('get_article_list_and_view', {
        "offset": this.articlesOffset,
        "limit": this.articlesLimit
      })
      .then(articles => {
        if (articles.length) {
          this.articles = this.articles.concat(articles);
          this.stopscrollAjax = false;
        }
        else {
          this.stopscrollAjax = true;
        }
      }).catch(() => {
        this.stopscrollAjax = false;
      });
    }
  },
  components: {
    Article
  }
})
</script>


<template>

  <div class="app-wp">
    <Header contentLoaded="true" :categories="categories" />

    <!-- Content -->
    <div class="content i-wrap">
      <div class="blog-post-wrap container flex flex-wrap" v-if="articles.length">

        <div class="w-full flex justify-center" v-for="(article,index) in articles" :key="index">

          <Article :article="article"
            :url="{ name: 'article_homepage', params: { articleId: article.id  } }" />

        </div>

      </div>

      <Error />

    </div>

  </div>

</template>