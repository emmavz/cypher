<script>
import Article from '@/components/Article.vue';
import Author from '@/components/Author.vue';
import UpVotePopup from '@/components/UpVotePopup.vue';
import Tabs from '@/components/Tabs.vue';
import StatsInvestment from '@/components/StatsInvestment.vue';
import StatsStakes from '@/components/StatsStakes.vue';

export default ({
    data() {
        return {
            // author: {
            //     "pfp": "http://localhost:8080/dynamic/profile-2.png",
            //     "name": "Eliza Mae",
            //     "followers": "987",
            //     "following": "53",
            //     "description": "Just another baker blogger",
            // },
            // articles: [
            //     {
            //         "article_id": 0,
            //         "article_title": "Super Chewy Cookies Recipe",
            //         "date_posted": 1651335773,
            //         "hashtag": "for you, baking",
            //         "image_url": "http://localhost:8080/dynamic/post-2.png",
            //         "name": "Eliza Mae",
            //         "pfp": "http://localhost:8080/dynamic/profile-2.png",
            //         "total_invested": 7342
            //     },
            //     {
            //         "article_id": 1,
            //         "article_title": "The ORIGINAL Quillmates?!",
            //         "date_posted": 1651335773,
            //         "hashtag": "for you, Gossip",
            //         "image_url": "http://localhost:8080/dynamic/post-9.png",
            //         "name": "Eliza Mae",
            //         "pfp": "http://localhost:8080/dynamic/profile-2.png",
            //         "total_invested": 7342
            //     }
            // ],
            user_id: Number(this.$route.params.userId),
            author: {},
            articles: [],
            statsInvestment: { },
            statsStakes: { },
            votes: '',
        }
    },
    async created() {

        this.sendAllMultiApiRequests([
            {
                url: 'get_user_profile',
                data: {
                    "user_id": this.user_id,
                    "auth_id": window.user_id,
                }
            },
            {
                url: 'get_user_profile_articles',
                data: {
                    "user_id": this.user_id,
                }
            },
            {
                url: 'get_other_user_investments',
                data: {
                    "user_id": this.user_id,
                    "auth_id": window.user_id,
                }
            },
        ])
            .then((reponses) => {
                this.author = reponses[0];
                this.articles = reponses[1];
                this.initStats(reponses[2]);
            });
    },
    methods: {
        updateVotes(votes) {
            this.votes = votes;

            if (this.votes) {
                this.sendApiRequest('upvote', {
                    "user_id": this.user_id,
                    "auth_id": window.user_id,
                    "amount": this.votes
                }, true).then((response) => {
                    this.initStats(response);
                });
            }
        },
        initStats(user_stats) {
            this.statsInvestment.amount = user_stats.total_investments;
            this.statsInvestment.investors = user_stats.total_investors;
            this.statsStakes.amount = user_stats.user_total_investments;
            this.statsStakes.stakes = user_stats.total_stakes;
        },
    },
    components: {
        Article,
        Author,
        Tabs,
        StatsInvestment,
        StatsStakes,
        UpVotePopup
    }
})
</script>

<template>

    <div class="app-wp">

        <Header />

        <!-- Content -->
        <div class="content i-wrap">

            <div v-if="!isError">
                <Author :author="author" anotherProfile="true" />

                <Tabs :tabList="profileTabs">
                    <template v-slot:btns>
                        <UpVotePopup @votes="updateVotes" :showpopup="$route.query.v" />
                    </template>

                    <template v-slot:tabPanel-1>
                        <div class="container">
                            <StatsInvestment :statsInvestment="statsInvestment" class="mt-4" />
                            <StatsStakes :statsStakes="statsStakes" class="mt-8" />
                        </div>
                    </template>

                    <template v-slot:tabPanel-2>
                        <div class="w-full flex justify-center container" v-for="(article, index) in articles"
                            :key="index">
                            <Article :article="article"
                                :url="{ name: 'article_homepage', params: { articleId: article.id } }" />
                        </div>
                    </template>
                </Tabs>
            </div>

            <Error />
        </div>

    </div>

</template>