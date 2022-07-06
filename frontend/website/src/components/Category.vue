<script>
export default {
    props: ['category', 'class_name', 'choose']
}
</script>

<template>
    <a v-if="typeof category.url !== 'undefined'" :href="category.url" :class="['category', class_name]">{{
        category.name
        }}</a>
    <span v-else
        :class="['category', class_name, { 'cursor-pointer': choose }, { 'category--primary': category.selected && choose }]"
        @click="choose ? $emit('category_id', category.id) : ''">{{
        category.name }}</span>
</template>