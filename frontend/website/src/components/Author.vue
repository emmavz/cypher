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
                            <button><b>follow</b></button>
                        </div>
                    </div>
                    <div class="flex mt-1 mb-1">
                        <div><span>{{ author.followers }}</span> followers</div>
                        <div class="ml-2.5"><span>{{ author.following }}</span> following</div>
                    </div>
                    <div class="u-gap mt-2.5 mb-2.5"></div>
                    <p class="f-13 userprofile__right__p">
                        {{ author.description }}
                    </p>
                </div>
                <div>
                    <button class="relative -top-2"><img src="@/assets/img/horizontal-dots-icon.svg" alt=""></button>
                </div>
            </div>
        </div>
    </div>
</template>