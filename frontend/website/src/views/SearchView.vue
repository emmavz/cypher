<script>
import Category from '@/components/Category.vue';
import Article from '@/components/Article.vue';
export default ({
  data() {
    return {
      articles: [
        {
            "article_id": 0,
            "article_title": "Camping is Actually Fun??!!",
            "date_posted": 1651335773,
            "hashtag": "camping",
            "image_url": "http://localhost:8080/dynamic/post-11.png",
            "name": "Cassandra Mendez",
            "pfp": "http://localhost:8080/dynamic/profile-7.png",
            "total_invested": 3286
        },
        {
            "article_id": 1,
            "article_title": "My Trip to Europe: History",
            "date_posted": 1651335773,
            "hashtag": "Travel",
            "image_url": "http://localhost:8080/dynamic/post-10.png",
            "name": "John Lee",
            "pfp": "http://localhost:8080/dynamic/profile-8.png",
            "total_invested": 537
        },
        {
            "article_id": 2,
            "article_title": "Web3 and Its Pros & Cons",
            "date_posted": 1651335773,
            "hashtag": "for you, business",
            "image_url": "http://localhost:8080/dynamic/post-12.png",
            "name": "Tyler Hogle",
            "pfp": "http://localhost:8080/dynamic/profile-9.png",
            "total_invested": 537
        }
      ],
      categories: [
        {
          name: 'Fashion',
          url: '#'
        },
        {
          name: 'Beauty',
          url: '#'
        },
        {
          name: 'Writing',
          url: '#'
        },
        {
          name: 'Plants',
          url: '#'
        },
        {
          name: 'Knitting',
          url: '#'
        },
        {
          name: 'Gaming',
          url: '#'
        },
        {
          name: 'Movies',
          url: '#'
        },
        {
          name: 'Books',
          url: '#'
        },
      ],
    }
  },
  components: {
    Category,
    Article
  }
})
</script>


<template>

  <div class="app-wp">
    <Header searchLoaded="true" categories="categories" />

    <!-- Content -->
    <div class="content i-wrap">

      <div class="container">
        <ul class="categories recommendations whitespace-normal flex flex-wrap">
          <li v-for="(category, index) in categories" :key="index">
            <Category :category="category" class="mb-3.5" />
          </li>
        </ul>

        <h3 class="mt-2.5 mb-4">Recommended</h3>

      </div>

      <div class="blog-post-wrap container flex flex-wrap" v-if="articles.length">
          <div class="w-full flex justify-center" v-for="(article,index) in articles" :key="index">

            <Article :article="article" />

          </div>
      </div>

      <Error/>

    </div>

  </div>

</template>