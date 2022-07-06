<script>
import Article from '@/components/Article.vue';

export default {
    data() {
        return {
            // articles: [
            //     {
            //         "article_id": 0,
            //         "article_title": "Business Ideas",
            //         "image_url": "http://localhost:8080/dynamic/post-13.png",
            //         "name": "Jessica Covington",
            //         "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
            //     },
            //     {
            //         "article_id": 1,
            //         "article_title": "The History of Fashion",
            //         "image_url": "http://localhost:8080/dynamic/post-14.png",
            //         "name": "Jessica Covington",
            //         "pfp": "http://localhost:8080/dynamic/userprofile-1.png",
            //     },
            // ],
            articles: []
        }
    },
    created() {
        this.getArticles();
    },
    methods: {
        async getArticles() {
            this.sendApiRequest('get_draft_articles', {
                "user_id": window.user_id,
            })
            .then(articles => {
                if (articles.length) {
                    this.articles = this.articles.concat(articles);
                }
            });
        }
    },
    components: {
        Article
    }
}
</script>

<template>
    <div class="app-wp">

        <Header />

        <!-- Content -->
        <div class="content i-wrap flex flex-col pb-0">

            <div class="max-h-full overflow-y-auto -mt-4">
                <div class="container">
                    <h2 class="mb-4 ml-2">Drafts</h2>
                </div>

                <div v-if="!isError">
                    <div class="blog-post-wrap">
                        <template v-if="articles.length">
                            <div class="w-full flex justify-center container mb-5" v-for="(article,index) in articles"
                                :key="index">
                                <Article :article="article"
                                    :url="{ name: 'create_article', params: { articleId: article.id } }"
                                    class="blog-post--user-article" />
                            </div>
                        </template>
                        <template v-else>
                            <div class="text-center container mb-4">No draft found!</div>
                        </template>
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

            <Error />
        </div>

    </div>
</template>