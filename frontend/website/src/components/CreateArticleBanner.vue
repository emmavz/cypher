<script>
export default {
    props: ['article_image', 'article_image_form_input', 'back_url'],
    methods: {
        onFileChange(e) {
            if(e.target.files.length) {
                const file = e.target.files[0];
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => {
                    this.$emit('article_image', reader.result);
                    this.$emit('article_image_form_input', file);
                };
                // this.$emit('article_image', URL.createObjectURL(file));
                // this.$emit('article_image', file);
            }
            else {
                this.$emit('article_image', '');
                this.$emit('article_image_form_input', '');
            }
        },
        getDefaultImage() {
            return new URL('../assets/img/img-icon.svg', import.meta.url).href;
        }
    },
}
</script>

<template>
    <div class="relative flex justify-center banner_img">
        <input type="file" accept="image/*" id="banner_image" class="input-hide" @change="onFileChange">
        <label for="banner_image"
            :class="['w-full relative overflow-hidden banner_img__label', {'banner_img__label--active': article_image }]"><img
                :src="article_image ? article_image : getDefaultImage()" alt="" class="w-full cursor-pointer"></label>
        <div>
            <div class="flex banner_img__btns">
                <button class="ar-btn mr-2" @click="$emit('saveArticle')">Save</button>
                <button class="ar-btn" @click.stop="$emit('showPublish')">Publish</button>
            </div>
        </div>
        <button class="close-icon" @click="$router.back()" v-if="checkPreviousPage"><img
                src="/src/assets/img/close-icon--v2.svg" alt="" width="34"></button>
    </div>
</template>