<script>
import Article from '@/components/Article.vue';

export default {
    data() {
        return {
            articles: [
                {
                    "article_id": 0,
                    "article_title": "Business Ideas",
                    "image_url": "http://localhost:8080/dynamic/post-13.png",
                    "name": "Jessica Covington",
                    "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
                },
                {
                    "article_id": 1,
                    "article_title": "The History of Fashion",
                    "image_url": "http://localhost:8080/dynamic/post-14.png",
                    "name": "Jessica Covington",
                    "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
                },
            ],
        }
    },
    components: {
        Article
    }
}
</script>

<template>
    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content i-wrap flex flex-col pb-0">

            <div class="max-h-full overflow-y-auto">
                <div class="container">
                    <h2 class="mb-6 ml-2">Drafts</h2>
                </div>

                <div class="blog-post-wrap">
                    <div class="w-full flex justify-center container mb-5" v-for="(article,index) in articles" :key="index">
                        <Article :article="article" class="blog-post--user-article" />
                    </div>
                </div>
            </div>

            <div class="br-b br-t py-3">
                <div class="container">
                    <RouterLink :to="{name: 'create_article'}" class="f-15 font-semibold">
                        Create a new article
                    </RouterLink>
                </div>
            </div>

            <Error/>
        </div>

    </div>
</template>