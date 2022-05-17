<script>
import Category from '@/components/Category.vue';
import Article from '@/components/Article.vue';

export default ({
  data() {
    return {
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
  async created() {

    this.sendApiRequest('get_article_list_and_view', {
          "user_id": 1,
          "start_index": 0,
          "number_of_article": 5
      })
    .then(articles => {
      this.articles = articles;
    }).catch(error => this.isError = true );
  },
  components: {
    Category,
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

          <Article :article="article" />

        </div>

      </div>

      <Error/>

    </div>

  </div>

</template>