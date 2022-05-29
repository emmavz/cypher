<script>
export default {
    props: ['statsStakes'],
}
</script>

<template>
    <div class="flex items-center">
        <div class="mr-6">
            <img src="@/assets/img/stats-icon--v3.svg" alt="" class="ml-auto">
        </div>

        <div class="font-bold">
            <div class="mb-1">You Own {{  this.statsStakes.stakes }}% Stake</div>
            <div class="aquamarine-color">{{ this.statsStakes.amount }} {{ this.currency }} Invested</div>
        </div>
    </div>
</template>