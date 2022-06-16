<script>
export default {
    props: {
        triggerSearch: {
            default: 0
        }
    },
    data() {
        return {
            q: ''
        }
    },
    created() {
        if(this.$route.query.q) {
            this.q = this.$route.query.q;
        }
    },
    mounted() {
        if(!this.$route.query.q) {
            this.searchAutofocus();
        }
    },
    methods: {
        searchAutofocus() {
            if(this.triggerSearch) {
               setTimeout(() => {
                    this.$refs.search.focus();
               }, 200);
            }
        },
        goToSearch() {
            this.$router.push({ query: { q: this.q } });
        }
    }
}
</script>

<template>
    <div class="container">
        <form action="javascript:void(0)" method="GET" :class="['searchbar flex', {'searchbar--trigger': triggerSearch}]" @submit="goToSearch">
            <button v-if="triggerSearch" type="button" class="close-icon" @click="$router.push({name: 'search'})"><img src="/src/assets/img/close-icon--v2.svg" alt="" width="34"></button>
            <div class="relative searchbar__div">
                <label for="search" class="pos-middle">
                    <img src="@/assets/img/search-icon--dark.svg" alt="">
                </label>
                <input type="text" id="search" name="q" ref="search" v-model="q" class="text-black" @focus="!triggerSearch ? $router.push({name: 'search_full'}) : ''" >
            </div>
            <button v-if="triggerSearch && q" type="button" class="f-18 font-semibold opacity-60" @click="q = '';searchAutofocus()">Cancel</button>
        </form>
    </div>
</template>