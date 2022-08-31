<script>
export default {
  props: ["image_url", "user_wallet_balance", "back_url"],
};
</script>

<template>
  <div class="relative flex justify-center banner_img">
    <img :src="this.image_url" alt="" class="w-full article-banner-img" />
    <span class="currency-tag" v-if="user_wallet_balance"
      >{{ this.toFixedAmount(this.user_wallet_balance) }} {{ this.currency }}</span
    >
    <button class="close-icon" @click="$router.push(back_url)">
      <img src="/src/assets/img/close-icon.svg" alt="" width="34" />
    </button>
  </div>
</template>
