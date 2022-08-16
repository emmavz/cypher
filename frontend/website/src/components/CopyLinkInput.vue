<script>
export default {
    props: ["url"],
};
</script>

<template>
    <div class="relative f-14 copy-link-input">
        <input type="text" v-model="url" readonly>
        <span class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer">
            <img src="@/assets/img/copy-icon.svg" alt="" @click="$copyText(url)">
        </span>
    </div>
</template>
