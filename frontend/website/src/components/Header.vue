<script>
import Category from '@/components/Category.vue'
export default {
    props: {
        contentLoaded: {
            default: false,
        },
        searchLoaded: {
            default: false
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
                    <Category :category="category" bright="true" />
                </li>
            </ul>

            <form action="" method="GET" class="searchbar" v-if="searchLoaded">
                <div class="relative searchbar__div">
                    <label for="search" class="pos-middle">
                        <img src="@/assets/img/search-icon--dark.svg" alt="">
                    </label>
                    <input type="text" id="search" name="q">
                </div>
            </form>
        </div>

    </header>
</template>