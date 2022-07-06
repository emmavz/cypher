<script>
import Category from '@/components/Category.vue';
import Vibrant from 'node-vibrant/dist/vibrant.min.js';
export default ({
    props: ['article', 'url'],
    data() {
        return {
            newArticle: ''
        }
    },
    async created() {

        this.article.palette = 'rgb(0,0,0)';

        // if('hashtag' in this.article) {
        //     this.article.hashtags = this.article.hashtag.split(', ');
        // }

        // if('date_posted' in this.article && typeof this.article.date_posted !== 'string') {
        //     this.article.date_posted = this.moment(this.article.date_posted * 1000).format('MMMM DD, YYYY');
        // }

        if (this.article.date_posted != null) {
            this.article.date_posted = this.moment(this.article.date_posted).format('MMMM DD, YYYY');
        }

        new Promise(async (resolve, reject) => {
            await Vibrant.from(this.article.image_url).quality(1).clearFilters().getPalette().
            then((palette) => {
                let rgb = palette.Muted._rgb;
                rgb = 'rgb('+rgb[0]+', '+rgb[1]+', '+rgb[2]+')';
                this.article.palette = rgb;
                return resolve(this.article);
            }).catch(() => {
                return reject(false);
            });
        }).then(article => {
            this.newArticle = article;
        }).catch(() => {
            this.newArticle = this.article;
        });
    },
    components: {
        Category
    }
})
</script>

<template>
    <RouterLink :to="url"
        v-if="newArticle" class="blog-post inline-flex flex-wrap justify-between">
        <span :style="{'background': 'linear-gradient(206.14deg, '+newArticle.palette+' 0%, #4F4D55 145.34%)'}"></span>
        <div class="blog-post__left">
            <div class="blog-post__left__header flex items-center mb-4">
                <div class="blog-post__left__header__img">
                    <img :src="newArticle.user.pfp" alt="" width="40">
                </div>
                <div class="pl-3">
                    <h2 class="mb-1">{{ newArticle.title }}</h2>
                    <div class="flex blog-post__left__header__author">
                        <span>{{ newArticle.user.name }}</span>
                    </div>
                </div>
            </div>

            <ul :class="['categories mb-4', {'mt-6': 'stakes' in newArticle }]">
                <li v-for="(tag, i) in newArticle.tags.slice(0,2)" :key="i">
                    <Category :category="tag" />
                </li>
            </ul>

            <div class="blog-post__left__meta flex mb-2.5" v-if="'date_posted' in newArticle">
                <div class="mr-3">{{ newArticle.date_posted }}</div>
            </div>

            <div class="blog-post__left__stock" v-if="'total_invested' in newArticle.user">
                {{ newArticle.user.total_invested }} {{ this.currency }} Invested
            </div>

            <div class="flex f-13 items-center mb-3" v-if="'stakes' in newArticle">
                <div>
                    <img src="@/assets/img/stats-icon--v2.svg" alt="">
                </div>
                <div class="pl-3 opacity-80">
                    <div><b>{{ newArticle.stakes }}% Stake</b></div>
                    <div class="mt-1.5"><b>{{ newArticle.amount }} {{ this.currency }}</b></div>
                </div>
            </div>

            <div class="f-11 opacity-80" v-if="'followers' in newArticle">
                <b>{{ newArticle.followers }}</b> Followers
            </div>

            <div class="f-11 pt-1 opacity-80" v-if="'following' in newArticle">
                <b>{{ newArticle.following }}</b> Following
            </div>
        </div>

        <div class="blog-post__right" :style="{'background-image': 'url('+newArticle.image_url+')'}">&nbsp;
            <span
                :style="{'background': 'linear-gradient(206.14deg, '+newArticle.palette+' 0%, #4F4D55 145.34%)'}"></span>
        </div>
    </RouterLink>
</template>