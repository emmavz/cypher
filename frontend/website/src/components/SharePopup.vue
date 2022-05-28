<script>

export default {
    props: {
        share_link: String,
        name: {
            default: 'Share to Read'
        }
    },
    data() {
        return {
            isModalVisible: false,
        }
    },
    methods: {
      showModal() {
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
      },
    },
}
</script>

<template>

<div>
    <div><button class="btn i-wrap--v2__btn" @click.stop="showModal">{{ name }}</button></div>

    <div class="share-modal text-black" v-show="isModalVisible">
        <div class="pt-6 pb-5 px-10" v-click-outside="closeModal">
            <h2 class="mb-1.5">Share to Read</h2>

            <p class="f-14 mb-2">
                Each time you share this unique link, you start a <b>sharing chain</b>. Once somebody pays, everybody in that chain can read the article for free. You can share this link with up to <b>eight</b> friends.
            </p>

            <div class="relative f-14">
                <input type="text" v-model="share_link" readonly>
                <img src="@/assets/img/copy-icon.svg" alt="" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer" @click="$copyText(share_link)" >
            </div>

        </div>
    </div>
</div>
</template>