<script>
export default {
    props: {
        contentLoaded: {
            default: false,
        }
    }
}
</script>

<template>
    <header :class="['header', contentLoaded ? '': 'header--mr']">
        <ul class="categories" v-if="contentLoaded">
            <li class="categories__plus"><a href="#"><img src="@/assets/img/plus-icon.svg" alt=""></a></li>
            <li><a href="#">For You</a></li>
            <li><a href="#">Coding</a></li>
            <li><a href="#">Business</a></li>
            <li><a href="#">Other Categories</a></li>
        </ul>
    </header>
</template>