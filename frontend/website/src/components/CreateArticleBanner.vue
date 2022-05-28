<script>
export default {
    props: ['image_url'],
    data() {
        return {
            image: this.image_url,
            image_selected: false
        }
    },
    methods: {
        onFileChange(e) {
            if(e.target.files.length) {
                const file = e.target.files[0];
                this.image = URL.createObjectURL(file);
                this.image_selected = true;
            }
            else {
                this.image = this.image_url;
                this.image_selected = false;
            }
        }
    }
}
</script>

<template>
    <div class="relative flex justify-center banner_img">
        <input type="file" accept="image/*" id="banner_image" class="input-hide" @change="onFileChange">
        <label for="banner_image" :class="['w-full relative overflow-hidden banner_img__label', {'banner_img__label--active': image_selected }]"><img :src="image" alt="" class="w-full cursor-pointer"></label>
        <div>
            <div class="flex banner_img__btns">
                <RouterLink :to="{name: 'drafts'}" class="ar-btn mr-2">Save</RouterLink>
                <button class="ar-btn" @click.stop="$emit('showPublish')">Publish</button>
            </div>
        </div>
        <button class="close-icon"><img src="/src/assets/img/close-icon--v2.svg" alt="" width="34"></button>
    </div>
</template>