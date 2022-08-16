<script>
import CopyLinkInput from "@/components/CopyLinkInput.vue";

export default {
    props: ['share_btn', 'share_heading', 'share_description', 'share_link'],
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
    components: {
        CopyLinkInput
    }
}
</script>

<template>

    <div>
        <div><button class="btn i-wrap--v2__btn" @click.stop="showModal">{{ share_btn }}</button></div>

        <div class="default-modal-styles share-modal text-black" v-show="isModalVisible">
            <div class="pt-5 pb-6 px-10" v-click-outside="closeModal">
                <h2 class="mb-1.5">{{ share_heading }}</h2>

                <p class="f-14 mb-4" v-html="share_description">
                </p>

                <CopyLinkInput :url="share_link" />

            </div>
        </div>
    </div>
</template>