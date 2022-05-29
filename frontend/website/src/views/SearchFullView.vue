<script>
import Article from '@/components/Article.vue';
import Search from '@/components/Search.vue';
import Tabs from '@/components/Tabs.vue';
export default ({
  data() {
    return {
      articles: [
        {
            "article_id": 0,
            "article_title": "My Trip to Europe: History",
            "date_posted": 1651335773,
            "hashtag": "travel",
            "image_url": "http://localhost:8080/dynamic/post-10.png",
            "name": "John Lee",
            "pfp": "http://localhost:8080/dynamic/profile-8.png",
            "total_invested": 537
        },
      ],
      authors: [
        {
            "article_id": 0,
            "article_title": "Camping is Actually Fun??!!",
            "date_posted": 1651335773,
            "hashtag": "camping",
            "image_url": "http://localhost:8080/dynamic/post-11.png",
            "name": "John Lee",
            "pfp": "http://localhost:8080/dynamic/profile-8.png",
            "total_invested": 3286,
            "intro": "Lover of life, art, and writing. My work revolves around the many adventures of my twenties."
        },
        {
            "article_id": 1,
            "article_title": "My Trip to Europe: History",
            "date_posted": 1651335773,
            "hashtag": "Travel",
            "image_url": "http://localhost:8080/dynamic/post-10.png",
            "name": "Lily Farrell",
            "pfp": "http://localhost:8080/dynamic/profile-10.png",
            "total_invested": 537,
            "intro": "Not a hard gal to please. All I want is to travel and find the secrets of the world."
        },
        {
            "article_id": 2,
            "article_title": "Web3 and Its Pros & Cons",
            "date_posted": 1651335773,
            "hashtag": "for you, business",
            "image_url": "http://localhost:8080/dynamic/post-12.png",
            "name": "Eliza Darcy",
            "pfp": "http://localhost:8080/dynamic/profile-11.png",
            "total_invested": 537,
            "intro": "Did my mother name me after a Jane Austen heroine? Yes. Did she also pass down her Jane Austen and Europe obsession to me?  Absolutely. I travel and write for a living now, so it all worked out."
        }
      ],
    }
  },
  components: {
    Article,
    Search,
    Tabs
  }
})
</script>


<template>

  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content i-wrap">

        <Search triggerSearch="true" class="mb-1 -mt-5" />

        <Tabs :tabList="searchTabs" v-if="$route.query.q">

            <template v-slot:tabPanel-1>
                <div class="w-full flex justify-center container" v-for="(article,index) in articles" :key="index">
                    <Article :article="article" />
                </div>
            </template>

            <template v-slot:tabPanel-2>
                <div class="w-full author-container" v-for="(author,i) in authors" :key="i">
                    <div :class="['flex pb-7 pt-7 container', i == 0 ? '-mt-9': '-mt-6']">
                        <div>
                            <a href="#">
                                <img :src="author.pfp" alt="" width="40" height="40" class="rounded-full">
                            </a>
                        </div>
                        <div class="pl-5">
                            <div class="flex justify-between">
                                <div class="f-20 font-semibold mb-2 mr-2"><a href="#">{{ author.name }}</a></div>
                                <div><button class="f-13 font-semibold bg-white primary-color border-radius px-1 py-1 m-w-76 h-27 text-center">Follow</button></div>
                            </div>
                            <p class="f-14">
                                {{ author.intro }}
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </Tabs>

        <Error/>

    </div>

  </div>

</template>