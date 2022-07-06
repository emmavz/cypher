<script>

export default {
    data() {
        return {
            article_id: '0', // 0 is required otherwise it will throw an error in $router.resolve
            title: '',
            image_url: '',
        }
    },
    async created() {
        this.sendApiRequest('get_article_homepage', {
            "article_id": Number(this.$route.params.articleId),
            "auth_id": window.user_id
        })
        .then(response => {
            if (response[0].length) {
                this.article_id = response[0][0].id;
                this.title = response[0][0].title;
                this.image_url = response[0][0].image_url;
            }
        });
    },
}
</script>

<template>
    <div class="app-wp">

        <Header />

        <!-- Content -->
        <div class="content text-center">

            <div v-if="!isError">
                <div class="font-semibold f-18 br-b py-5 banner_img">
                    <button class="close-icon"><img src="/src/assets/img/close-icon--v2.svg" alt="" width="34"></button>
                    Congratulations!
                </div>

                <div class="pt-8 pb-12">
                    <div class="container">
                        <h2 class="f-18 mb-7">
                            You just published<br>
                            ‘<span>{{ title }}</span>’
                        </h2>
                    </div>

                    <div>
                        <img :src="image_url" alt="" class="w-full mb-11">
                    </div>

                    <div class="br-t">
                        <div class="container">
                            <div class="f-18 font-semibold mt-9 mb-5">Share your story now</div>
                            <div class="flex justify-center">
                                <div>
                                    <ShareNetwork network="facebook"
                                        :url="getFullUrl($router.resolve({ name: 'article_homepage', params: { articleId: article_id } }).fullPath)"
                                        title="Say hi to Vite! A brand new, extremely fast development setup for Vue."
                                        description="This week, I’d like to introduce you to 'Vite', which means 'Fast'. It’s a brand new development setup created by Evan You.">
                                        <button><img src="@/assets/img/facebook-icon.svg" alt=""></button>
                                    </ShareNetwork>
                                </div>
                                <div><button class="ml-6"
                                        @click="$copyText(getFullUrl($router.resolve({ name: 'article_homepage', params: { articleId: article_id } }).fullPath))"><img
                                            src="@/assets/img/website-icon.svg" alt=""></button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Error />
        </div>

    </div>
</template>