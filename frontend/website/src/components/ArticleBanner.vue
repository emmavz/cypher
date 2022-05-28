<script>
export default {
    props: ['image_url', 'user_wallet_balance']
}
</script>

<template>
    <div class="relative flex justify-center banner_img">
        <img :src="this.image_url" alt="" class="w-full">
        <span class="currency-tag">{{ this.user_wallet_balance }} {{ this.currency }}</span>
        <button class="close-icon"><img src="/src/assets/img/close-icon.svg" alt="" width="34"></button>
    </div>
</template>