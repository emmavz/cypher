<script>
export default {
    props: ["showpopup", "successTitle", "successMsg", "successButton"],
    data() {
        return {
            cashout: "",
        };
    },
    methods: {
        closeModal() {
            this.$emit("showpopup", "");
        }
    },
};
</script>

<template>
    <div class="default-modal-styles share-modal text-black" v-if="showpopup">
        <div class="pt-5 pb-6 px-10 text-center" v-click-outside="closeModal">
            <img src="@/assets/img/success-tick-icon.svg" class="mx-auto mb-4" />
            <h2 class="mb-3">{{ successTitle }}</h2>

            <p class="f-14 mb-6" v-html="successMsg"></p>

            <div>
                <button class="csp-btn" @click="closeModal" v-if="!successButton">Finished!</button>
                <router-link :to="successButton.url" v-else><button class="csp-btn">{{ successButton.text }}</button>
                </router-link>

            </div>

        </div>
    </div>
</template>
