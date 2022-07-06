<script>
export default {
    props: {
        author: {
            default: {}
        },
        anotherProfile: {
            default: false
        },
    },
    methods: {
        async follow(author_index, followed_id) {
            this.sendApiRequest('do_follow_toggle', {
                "follower_id": window.user_id,
                "followed_id": followed_id,
            }, true)
            .then(() => {
                this.author.is_followed = this.author.is_followed ? 0 : 1;
                this.author.followers_count = this.author.is_followed ? this.author.followers_count+1 : this.author.followers_count-1;
            });
        },
    }
}
</script>

<template>
    <div class="container">
        <div class="userprofile flex mb-5">
            <div class="userprofile__left">
                <img :src="author.pfp" alt="" class="relative top-2">
            </div>
            <div class="userprofile__right flex justify-between pl-4">
                <div>
                    <div class="flex">
                        <h2>{{ author.name }}</h2>
                        <div v-if="anotherProfile" class="f-16 ml-9 relative top-1">
                            <button :class="[author.is_followed ? 'primary-color' : 'text-white']"
                                @click="follow(ai , author.id)"><b>{{ author.is_followed ? 'followed' : 'follow'
                                }}</b></button>
                        </div>
                    </div>
                    <div class="flex mt-1 mb-1">
                        <div><span>{{ author.followers_count }}</span> followers</div>
                        <div class="ml-2.5"><span>{{ author.followed_count }}</span> following</div>
                    </div>
                    <div class="u-gap mt-2.5 mb-2.5"></div>
                    <p class="f-13 userprofile__right__p">
                        {{ author.bio }}
                    </p>
                </div>
                <div>
                    <button class="relative -top-2" v-if="!anotherProfile"><img
                            src="@/assets/img/horizontal-dots-icon.svg" alt=""></button>
                </div>
            </div>
        </div>
    </div>
</template>