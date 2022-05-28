<script>
import CreateArticleBanner from '@/components/CreateArticleBanner.vue';
import Category from '@/components/Category.vue';

export default {
    data() {
        return {
            'image_url': new URL('../assets/img/img-icon.svg', import.meta.url).href,
            publish_description: '',
            publish_description_max: 200,
            publish_price: '0',
            publish_theta: '0',
            showPublish: 0,
            publish_confirmation: 0,
            categories: [
                {
                    name: 'Fashion',
                    url: '#'
                },
            ]
        }
    },
    created() {
        this.$watch('publish_price', function(value){
            this.setInputDynamicWidth(this.$refs.publish_price);
        });
        this.$watch('publish_theta', function(value){
            this.setInputDynamicWidth(this.$refs.publish_theta);
        });
    },
    methods: {
        publish() {
            this.showPublish = 0;
            this.$router.push({ name: 'create_article_published' });
        },
        hidePublish() {
            this.showPublish = 0;
        },
    },
    components: {
        CreateArticleBanner,
        Category,
    },
}
</script>

<template>
    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content">

            <CreateArticleBanner :image_url="image_url" @show-publish="showPublish = 1;publish_confirmation = 0" />

            <div class="py-5">
                <div class="container create_article__form">
                    <form action="javascript:void(0)">
                        <div class="br-b">
                            <input type="text" placeholder="Insert Title" class="f-20 font-bold pb-1 text-center">
                        </div>
                        <div class="mt-6">
                            <textarea rows="10" placeholder="Start writing" class="f-13 resize-none"></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <div :class="['create_article__publish', {'create_article__publish--visible': showPublish}]" v-click-outside="hidePublish">
                <div class="create_article__publish__header">
                    <div class="container">
                        <div class="flex justify-between">
                            <button><img src="@/assets/img/close-icon--v3.svg" alt="" @click="showPublish = 0;"></button>
                            <button class="font-semibold" @click="publish_confirmation = 1">Publish</button>
                        </div>
                    </div>
                </div>
                <div class="py-5 create_article__publish__body">
                    <div class="container">

                        <div v-if="!publish_confirmation">
                            <div class="pb-4 font-semibold">Description</div>
                            <textarea v-model="publish_description" :maxlength="publish_description_max" class="w-full bg-transparent border-white border f-13"></textarea>
                            <div class="flex justify-end f-13">{{ publish_description.length }}/{{ publish_description_max }}</div>

                            <div class="row mt-6">
                                <div class="w-6/12">
                                    <div class="font-semibold mb-6">Price</div>
                                    <div class="flex items-center">
                                        <input type="number" class="f-20 font-bold bg-transparent aquamarine-color opacity-80" min="0" v-model.number="publish_price" @keypress="onlyNumeric" ref="publish_price">
                                        <span class="aquamarine-color opacity-80 f-20 font-bold mr-2">{{ this.currency }}</span>
                                        <div>
                                            <div><img src="@/assets/img/polygon-up.svg" alt="" class="cursor-pointer mb-1" @click="publish_price++"></div>
                                            <div><img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer" @click="publish_price > 0 ? publish_price-- : ''"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-6/12">
                                    <div class="font-semibold mb-6">Theta</div>
                                    <div class="flex items-center">
                                        <input type="number" class="f-20 font-bold bg-transparent aquamarine-color opacity-80" min="0" v-model.number="publish_theta" @keypress="onlyNumeric" ref="publish_theta">
                                        <span class="aquamarine-color opacity-80 f-20 font-bold mr-2">%</span>
                                        <div>
                                            <div><img src="@/assets/img/polygon-up.svg" alt="" class="cursor-pointer mb-1" @click="publish_theta++"></div>
                                            <div><img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer" @click="publish_theta > 0 ? publish_theta-- : ''"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <div class="font-semibold mb-5">Tags</div>
                                <ul class="categories whitespace-normal">
                                    <li class="categories__plus categories__plus--white mb-4"><a href="#"><img src="@/assets/img/plus-icon--v2.svg" alt=""></a></li>
                                    <li v-for="(category, index) in categories" :key="index" class="mb-4">
                                        <Category :category="category" bright="true" />
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div v-else class="create_article__publish__body__confirmation text-center">
                            <div class="font-semibold mb-5">Are you sure?</div>
                            <div class="flex justify-center">
                                <button class="f-13 font-semibold bg-white primary-color border-radius mr-5" @click="publish_confirmation = 0">No</button>
                                <button class="f-13 font-semibold bg-white primary-color border-radius" @click="publish">Yes</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <Error/>
        </div>

    </div>
</template>