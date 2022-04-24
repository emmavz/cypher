<script>
export default ({
  data() {
    return {
      isLoading: true,
      article: null,
    }
  },
  created() {

    const response = fetch('http://localhost:3000/api/get_article_homepage', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
          "article_id": this.$route.params.articleId,
      })
    })
    .then(response => response.json())
    .then(article => {
      this.article = article;
    })
    .finally(() => this.isLoading = false);
  },
})
</script>

<template>
    <!-- App -->
    <div class="app">
        <!-- Header -->
        <header class="header"></header>

        <!-- Content -->
        <div class="content">

            <template v-if="article">
                <div class="relative flex justify-center banner_img">
                    <img :src="article.image_url" alt="" class="w-full">
                    <span>{{ article.user_wallet_balance }} CPHR</span>
                    <button class="close-icon"><img src="/src/assets/img/close-icon.svg" alt="" width="34"></button>
                </div>

                <div class="container">

                    <div class="text-center i-wrap--v2 mb-4">
                        <h1 class="mb-3">{{ article.article_title }}</h1>
                        <div class="mb-3">
                            <a href="#" class="inline-flex items-center i-wrap--v2__profile">
                                <img :src="article.author_pfp" alt="" class="mr-4">
                                {{ article.article_author }}
                            </a>
                        </div>
                        <p class="mb-6">
                            {{ article.article_description }}
                        </p>
                        <div class="mb-6"><a href="#" class="btn">Pay to Read (20T)</a></div>
                        <div><a href="#" class="btn">Share to Read</a></div>
                    </div>
                </div>
                <div class="stats mb-16">
                    <div class="container">
                        <div class="flex items-center justify-center">
                            <div class="mr-6">
                                <img src="/src/assets/img/stats-icon.svg" alt="" class="ml-auto">
                            </div>
                            <div class="stats__right">
                                <div><span>{{ article.article_liquidation_time }}</span>Days until liquidation</div>
                                <div><span>{{ article.article_total_reads }}</span>Reads</div>
                                <div><span>{{ article.article_total_shares }}</span>Shares</div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer__controls">
                    <ul class="flex justify-center items-center">
                        <li><RouterLink :to="{name: 'home'}"><img src="@/assets/img/home-icon.svg" alt=""></RouterLink></li>
                        <li><a href="#"><img src="/src/assets/img/search-icon.svg" alt=""></a></li>
                        <li><a href="#" class="active"><img src="/src/assets/img/plus-white-icon.svg" alt=""></a></li>
                        <li><a href="#"><img src="/src/assets/img/notification-icon.svg" alt=""></a></li>
                        <li><a href="#"><img src="/src/assets/img/profile-icon.svg" alt=""></a></li>
                    </ul>
                </div>
            </div>
        </footer>

    </div>

    <div class="f-spinner" v-if="isLoading"><div></div></div>
</template>