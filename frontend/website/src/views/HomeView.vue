<script>
import Vibrant from 'node-vibrant/dist/vibrant.min.js';
import moment from 'moment';
export default ({
  data() {
    return {
      isLoading: true,
      articles: [],
    }
  },
  async created() {

    const response = fetch('http://localhost:3000/api/get_article_list_and_view', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
          "user_id": 1,
          "start_index": "1",
          "number_of_article": "5"
      })
    })
    .then(response => response.json())
    .then(articles => {
      return (async () => {

        articles.map(article => {
          article.palette = '#000000';
          article.tags = article.tags.split(', ');
          article.date_posted = moment(new Date(article.date_posted)).format('MMMM DD, YYYY');
        });

        await Promise.allSettled(articles.map(async (article) => {
          let palette = await Vibrant.from(article.image_url).quality(1).clearFilters().getPalette()
          let rgb = palette.Muted._rgb;
          rgb = 'rgb('+rgb[0]+', '+rgb[1]+', '+rgb[2]+')';
          article.palette = rgb;
        }));

        return articles;
      })();
    })
    .then(articles => {
      this.articles = articles;
      this.isLoading = false;
    })
    .catch(() => this.isLoading = false);
  },
})
</script>


<template>

  <!-- App -->
	<div class="app">
		<!-- Header -->
		<header class="header">
			<ul class="categories">
				<li class="categories__plus"><a href="#"><img src="@/assets/img/plus-icon.svg" alt=""></a></li>
				<li><a href="#">For You</a></li>
				<li><a href="#">Coding</a></li>
				<li><a href="#">Business</a></li>
				<li><a href="#">Other Categories</a></li>
			</ul>
		</header>

		<!-- Content -->
		<div class="content i-wrap">
			<div class="blog-post-wrap container flex flex-wrap" v-if="articles.length">

				<div class="w-full flex justify-center" v-for="(article,index) in articles" :key="index">

          <RouterLink :to="{name: 'article', params: {articleId: article.article_id } }" class="blog-post inline-flex flex-wrap justify-between">
						<span :style="{'background': 'linear-gradient(206.14deg, '+article.palette+' 0%, #4F4D55 145.34%)'}"></span>
						<div class="blog-post__left">
							<div class="blog-post__left__header flex items-center mb-4">
								<div class="blog-post__left__header__img">
									<img :src="article.author_pfp" alt="">
								</div>
								<div class="pl-3">
									<h2 class="mb-1">{{ article.article_title }}</h2>
									<div class="flex blog-post__left__header__author">
										<span>{{ article.author_name }}</span>
									</div>
								</div>
							</div>

							<ul class="categories mb-4">
								<li v-for="(tag, i) in article.tags" :key="i"><span>{{ tag }}</span></li>
							</ul>

							<div class="blog-post__left__meta flex mb-2.5">
								<div class="mr-3">{{ article.date_posted }}</div>
							</div>

							<div class="blog-post__left__stock">
								{{ article.total_invested }} CPHR Invested
							</div>
						</div>

						<div class="blog-post__right" :style="{'background-image': 'url('+article.image_url+')'}">&nbsp;
							<span :style="{'background': 'linear-gradient(206.14deg, '+article.palette+' 0%, #4F4D55 145.34%)'}"></span>
						</div>
          </RouterLink>

				</div>

			</div>

      <div v-if="!isLoading && !articles.length" class="error-container">
        <h2>Error</h2>
        <p>
          This request couldn't be processed right now. Please try again later!
        </p>
      </div>

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