<script>
import CreateArticleBanner from '@/components/CreateArticleBanner.vue';
import Category from '@/components/Category.vue';

import { QuillEditor, Quill } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

import BlotFormatter from 'quill-blot-formatter';
import MagicUrl from 'quill-magic-url';

Quill.register("modules/blotFormatter", BlotFormatter);
Quill.register("modules/magicUrl", MagicUrl);

import * as yup from 'yup';

export default {
    data() {
        return {
            article: null,
            article_id: this.$route.params.articleId ? Number(this.$route.params.articleId) : '',
            article_image: '',
            article_image_form_input: '',
            title: '',
            description: '',
            quill: null,
            publish_description: '',
            publish_description_max: 200,
            publish_price: '0',
            publish_theta: '0',
            showPublish: 0,
            publish_confirmation: 0,
            show_categories: 0,
            search_category: '',
            showQuillEditor: 0,
            categories: [],
            categoriesFiltered: [],
            options: {
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons

                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent

                        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                        [{ 'align': [] }],
                        ['link', 'image'],
                        [{ 'direction': 'rtl' }],                         // text direction,
                        ['clean'],
                    ],
                    blotFormatter: {},
                    magicUrl: {}
                },
                placeholder: '',
                theme: 'snow',
            },
        }
    },
    async created() {
        this.$watch('publish_price', function(value){
            this.setInputDynamicWidth(this.$refs.publish_price);
        });
        this.$watch('publish_theta', function(value){
            this.setInputDynamicWidth(this.$refs.publish_theta);
        });

        if (this.article_id) await this.getArticle();

        this.sendApiRequest('get_tags', {}, false, { removeLoaderAfterApi: this.article_id ? false : true })
        .then(categories => {
            categories.forEach(category => {
                category.selected = false;

                if (this.article) {
                    this.article.tags.forEach(tag => {
                        if (tag.id == category.id) {
                            category.selected = true;
                        }
                    });
                }

            });
            this.categories = categories;
            this.categoriesFiltered = this.categories;
        });
    },
    methods: {
        async getArticle() {
            this.sendApiRequest('get_user_draft_article', {
                "user_id": window.user_id,
                "article_id": this.article_id
            })
            .then(article => {
                if (article.length) {
                    this.article = article[0];
                    this.title = article[0].title;
                    this.description = article[0].description;
                    this.quill.container.firstChild.innerHTML = this.description;
                    this.article_image = article[0].image_url;

                    if (article[0].content) this.publish_description = article[0].content;
                    if (article[0].price) this.publish_price = article[0].price;
                }
            });
        },
        async save(should_publish) {

            let categoryIds = []
            this.categories.forEach(category => {
                if (category.selected) {
                    categoryIds.push(category.id);
                }
            });

            let validations = {
                title: yup.string().required(),
            };

            if(should_publish) {
                validations.description = yup.string().required();
                validations.publish_description =  yup.string().required();
                validations.publish_price =  yup.string().required();
                validations.publish_theta =  yup.string().required();
                validations.tags = yup.array().required().min(1);

                if (!this.article_id || (this.article_id && !this.article_image)) {
                    validations.article_image_form_input = yup.string().required().label('article image');
                }
            }

            // if (!this.article_id || (this.article_id && !this.article_image)) {
            //     validations.article_image_form_input = yup.string().required().label('article image');
            // }

            const schema = yup.object().shape(validations);

            await this.validate(schema, { title: this.title, description: this.description, article_image_form_input: this.article_image_form_input,
                publish_description: this.publish_description, publish_price: this.publish_price, publish_theta: this.publish_theta, tags: categoryIds });

            let formData = new FormData();
            if (this.article_id) formData.append("article_id", this.article_id);
            formData.append("image_url", this.article_image_form_input);
            formData.append("title", this.title);
            formData.append("description", this.description);
            formData.append("user_id", window.user_id);

            formData.append("p_description", this.publish_description);
            formData.append("price", this.publish_price);
            formData.append("theta", this.publish_theta);
            formData.append("tags[]", categoryIds);
            formData.append("should_publish", should_publish ? 1 : 0);

            this.sendApiRequest('save_draft_article', formData, true)
            .then((articleId) => {
                if (!should_publish) this.$router.push({ name: 'drafts' });
                else this.$router.push({ name: 'create_article_published', params: { articleId: articleId[0].id } });
            });

        },
        async publish() {

            this.showPublish = 0;
            this.save(1);
        },
        hidePublish() {
            this.showPublish = 0;
        },
        editorReady(quill) {
            this.quill = quill;
            if (this.article_id) quill.setText(this.description);
            else quill.focus();
        },
        editorBlur(quill) {
        },
        editorUpdate() {
            if( this.quill ) {
                this.description = this.quill.container.firstChild.innerHTML;
            }
        },
        toggleCategorySelection(categoryId) {
            this.categoriesFiltered = [];

            this.categories.forEach(category => {
                if (category.id == categoryId) {
                    category.selected = !category.selected;
                    this.show_categories = 0;
                    this.publish_confirmation = 0;
                }
            });

            this.categoriesFiltered = this.categories;
        },
        searchCategory() {
            this.categoriesFiltered = [];

            this.categories.forEach(category => {
                if (category.name.toLowerCase().indexOf(this.search_category.toLowerCase()) >= 0) {
                    this.categoriesFiltered.push(category);
                }
            });
        }
    },
    components: {
        CreateArticleBanner,
        Category,
        QuillEditor,
    },
}
</script>

<template>
    <div class="app-wp">

        <Header />

        <!-- Content -->
        <div class="content">

            <CreateArticleBanner :article_image="article_image" :article_image_form_input="article_image_form_input"
                @show-publish="showPublish = 1;publish_confirmation = 0" @save-article="save(0)"
                @article_image="(ar_img) => article_image = ar_img"
                @article_image_form_input="(ar_img) => article_image_form_input = ar_img" />

            <div class="py-5">
                <div class="container create_article__form">
                    <form action="javascript:void(0)">
                        <div class="br-b">
                            <!-- <input type="text" placeholder="Insert Title" v-model="title"
                                class="f-20 font-bold pb-1 text-center"> -->
                            <input name="title" rules="required" placeholder="Insert Title" v-model="title"
                                class="f-20 font-bold pb-1 text-center" />
                        </div>
                        <div class="mt-6">
                            <div class="faker f-13 text-center opacity-80 editor_height" @click="showQuillEditor = 1"
                                v-if="showQuillEditor == 0 && !this.article_id">Start Writing</div>
                            <template v-if="showQuillEditor == 1 || this.article_id">
                                <QuillEditor :options="options" @ready="editorReady($event)" @blur="editorBlur"
                                    @update:content="editorUpdate" class="editor_height" />
                            </template>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showPublish" class="overlay"></div>

            <div :class="['create_article__publish', {'create_article__publish--visible': showPublish}]"
                v-click-outside="hidePublish">
                <div class="create_article__publish__header">
                    <div class="container">
                        <div class="flex justify-between">
                            <button><img src="@/assets/img/close-icon--v3.svg" alt=""
                                    @click="showPublish = 0;"></button>
                            <button class="font-semibold"
                                @click="publish_confirmation = 1;show_categories = 0">Publish</button>
                        </div>
                    </div>
                </div>
                <div class="py-5 create_article__publish__body">
                    <div class="container">

                        <div v-if="!publish_confirmation">
                            <div class="pb-4 font-semibold">Description</div>
                            <textarea v-model="publish_description" :maxlength="publish_description_max"
                                class="w-full bg-transparent border-white border f-13"></textarea>
                            <div class="flex justify-end f-13">{{ publish_description.length }}/{{
                                publish_description_max }}</div>

                            <div class="row mt-6">
                                <div class="w-6/12">
                                    <div class="font-semibold mb-6">Price</div>
                                    <div class="flex items-center">
                                        <input type="number"
                                            class="create_article__publish__body__input f-20 font-bold bg-transparent aquamarine-color opacity-80"
                                            min="0" v-model.number="publish_price" @keypress="onlyNumeric"
                                            ref="publish_price">
                                        <span class="aquamarine-color opacity-80 f-20 font-bold mr-2">{{ this.currency
                                            }}</span>
                                        <div>
                                            <div><img src="@/assets/img/polygon-up.svg" alt=""
                                                    class="cursor-pointer mb-1" @click="publish_price++"></div>
                                            <div><img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer"
                                                    @click="publish_price > 0 ? publish_price-- : ''"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-6/12">
                                    <div class="font-semibold mb-6">Theta</div>
                                    <div class="flex items-center">
                                        <input type="number"
                                            class="create_article__publish__body__input f-20 font-bold bg-transparent aquamarine-color opacity-80"
                                            min="0" v-model.number="publish_theta" @keypress="onlyNumeric"
                                            ref="publish_theta">
                                        <span class="aquamarine-color opacity-80 f-20 font-bold mr-2">%</span>
                                        <div>
                                            <div><img src="@/assets/img/polygon-up.svg" alt=""
                                                    class="cursor-pointer mb-1" @click="publish_theta++"></div>
                                            <div><img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer"
                                                    @click="publish_theta > 0 ? publish_theta-- : ''"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <div class="font-semibold mb-5">Tags</div>
                                <ul class="categories whitespace-normal">
                                    <li class="categories__plus categories__plus--white mb-4"
                                        @click="show_categories = 1;publish_confirmation = 1"><a
                                            href="javascript:void(0)"><img src="@/assets/img/plus-icon--v2.svg"
                                                alt=""></a></li>
                                    <li v-for="(category, index) in categories" :key="index">
                                        <Category :category="category" class="mb-3.5" class_name="category--bright"
                                            v-if="category.selected" />
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div v-else-if="show_categories == 1">

                            <form action="javascript:void(0)" method="GET" :class="['searchbar flex mb-8']"
                                @submit="searchCategory">
                                <div class="relative create_article__publish__body__searchbar">
                                    <label for="search" class="pos-middle">
                                        <img src="@/assets/img/search-icon--dark.svg" alt="">
                                    </label>
                                    <input type="text" placeholder="Search for a tag" v-model="search_category"
                                        class="text-black">
                                </div>
                            </form>

                            <template v-if="categoriesFiltered.length">
                                <ul class="categories whitespace-normal">
                                    <li v-for="(category, index) in categoriesFiltered" :key="index">
                                        <Category :category="category" choose="true" class="mb-3.5"
                                            @category_id="toggleCategorySelection" />
                                    </li>
                                </ul>
                            </template>
                            <template v-else>
                                <div class="text-center">No tag found!</div>
                            </template>
                        </div>

                        <div v-else-if="publish_confirmation"
                            class="create_article__publish__body__confirmation text-center">
                            <div class="font-semibold mb-5">Are you sure?</div>
                            <div class="flex justify-center">
                                <button class="f-13 font-semibold bg-white primary-color border-radius mr-5"
                                    @click="publish_confirmation = 0">No</button>
                                <button class="f-13 font-semibold bg-white primary-color border-radius"
                                    @click="publish">Yes</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <Error />
        </div>

    </div>
</template>