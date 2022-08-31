<script>
export default {
    props: ['statsInvestment'],
}
</script>

<template>
    <div class="flex items-center">
        <div class="mr-6">
            <img src="@/assets/img/stats-icon.svg" alt="" class="ml-auto">
        </div>

        <div class="font-bold">
            <div class="mb-1">{{ toFixedAmount2(this.statsInvestment.amount) }} Tokens Invested</div>
            <div class="aquamarine-color">{{ this.statsInvestment.investors }} Investors</div>
        </div>
    </div>
</template>