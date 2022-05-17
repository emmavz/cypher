<script>
export default {
    props: ['category', 'bright']
}
</script>

<template>
    <a v-if="typeof category.name !== 'undefined'" :href="category.url" :class="['category', {'category--bright' : bright}]">{{ category.name }}</a>
    <span v-else :class="['category', {'category--bright' : bright}]">{{ category }}</span>
</template>