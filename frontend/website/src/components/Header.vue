<script>
import Category from '@/components/Category.vue'
export default {
    props: {
        contentLoaded: {
            default: false,
        },
        categories: {
            default: []
        },
    },
    components: {
        Category,
    },
}
</script>

<template>
    <header :class="['header', contentLoaded ? '': 'header--mr', contentLoaded ? 'header--pr-0' : '' ]">

        <div>
            <ul class="categories" v-if="contentLoaded">
                <li class="categories__plus"><a href="#"><img src="@/assets/img/plus-icon.svg" alt=""></a></li>
                <li v-for="(category, index) in categories" :key="index">
                    <Category :category="category" class_name="category--bright" />
                </li>
            </ul>

            <slot name="search" />
        </div>

    </header>
</template>