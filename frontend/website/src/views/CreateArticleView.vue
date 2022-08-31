<script>
import CreateArticleBanner from "@/components/CreateArticleBanner.vue";
import Category from "@/components/Category.vue";

import { QuillEditor, Quill } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

// import BlotFormatter from "quill-blot-formatter";
import BlotFormatter from "quill-blot-formatter/dist/BlotFormatter";
import MagicUrl from "quill-magic-url";
import ImageFormats from "@/quill-align-images";

Quill.register("formats/image", ImageFormats);
Quill.register("modules/blotFormatter", BlotFormatter);
Quill.register("modules/magicUrl", MagicUrl);

import * as yup from "yup";

export default {
  data() {
    return {
      article: null,
      article_id: this.$route.params.articleId
      ? Number(this.$route.params.articleId)
      : "",
      article_image: "",
      article_image_form_input: "",
      title: "",
      content: "",
      quill: null,
      description: "",
      publish_description_max: 200,
      price: "0",
      theta: "0",
      liquidation_days: window.default_liquidation_days,
      share_to_read: true,
      publish_step: 1,
      show_publish: 0,
      search_category: "",
      showQuillEditor: 0,
      categories: [],
      categoriesFiltered: [],
      selectedCategories: [],
      options: {
        modules: {
          toolbar: [
            [{ header: [1, 2, 3, 4, 5, 6, false] }],

            ["bold", "italic", "underline", "strike"], // toggled buttons

            [{ list: "ordered" }, { list: "bullet" }],
            [{ indent: "-1" }, { indent: "+1" }], // outdent/indent

            [{ color: [] }, { background: [] }], // dropdown with defaults from theme
            [{ align: [] }],
            ["link", "image"],
            [{ direction: "rtl" }], // text direction,
            ["clean"],
          ],
          blotFormatter: {},
          magicUrl: {},
        },
        placeholder: "",
        theme: "snow",
      },
      publish_text: this.getNextText(),
      theta_info: false,
      share_to_read_info: false,
      custom_tags_count: 0,
    };
  },
  async created() {

    // Update width of inputs for increment/decrement controls
    this.$watch("price", function (value) {
      this.setInputDynamicWidth(this.$refs.price);
    });
    this.$watch("theta", function (value) {
      this.setInputDynamicWidth(this.$refs.theta);
    });
    this.$watch("liquidation_days", function (value) {
      this.setInputDynamicWidth(this.$refs.liquidation_days);
    });

    // Fetch article from api
    if (this.article_id) await this.getArticle();

    // Fetch all tags of auth user
    let obj = {};
    if(this.article_id) {
      obj.article_id = this.article_id;
    }

    this.sendApiRequest("get_article_tags", obj, false, {
      removeLoaderAfterApi: this.article_id ? false : true,
    }).then((categories) => {
      categories.forEach((category) => {
        category.selected = false;
        category.custom = false;

        if (this.article) {
          this.article.tags.forEach((tag) => {
            if (tag.id == category.id) {
              category.selected = true;
              this.selectedCategories.push(category);

              if(tag.user_id) {
                this.custom_tags_count++;
              }
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
      this.sendApiRequest("get_user_draft_article", {
        article_id: this.article_id,
      }).then((article) => {
        this.article = article;
        this.title = this.article.title;
        this.content = this.article.content ? this.article.content : "";
        this.quill.container.firstChild.innerHTML = this.content;
        this.article_image = this.article.image_url;

        if (this.article.description)
          this.description = this.article.description;
        if (this.article.price) this.price = this.article.price;
        if (this.article.theta) this.theta = this.article.theta;
        this.share_to_read = this.article.share_to_read ? true: false;
        this.liquidation_days = this.article.liquidation_days;
      });
    },

    async save(should_publish) {

      // Get selected tags
      let selectedCategories = [];
      this.selectedCategories.forEach((category) => {
        selectedCategories.push({ name: category.name ,id: category.id, custom: category.custom});
      });

      // Validations
      let validations = {
        title: yup.string().required(),
      };

      if (should_publish) {
        validations.content = yup.string().required();
        validations.description = yup.string().required();
        validations.price = yup.number().required().min(0);
        validations.theta = yup.number().required().integer().max(100);
        validations.tags = yup.array().required().min(1);
        validations.liquidation_days = yup.number().integer().min(0);

        if (!this.article_id || (this.article_id && !this.article_image)) {
          validations.article_image_form_input = yup
            .string()
            .required()
            .label("article image");
        }
      }

      const schema = yup.object().shape(validations);

      await this.validate(schema, {
        title: this.title,
        content: this.content,
        article_image_form_input: this.article_image_form_input,
        description: this.description,
        price: this.price,
        theta: this.theta,
        liquidation_days: this.liquidation_days,
        tags: selectedCategories,
      });

      // Prepare data to send via api
      let formData = new FormData();
      if (this.article_id) formData.append("article_id", this.article_id);
      formData.append("image_url", this.article_image_form_input);
      formData.append("title", this.title);
      formData.append("content", this.content);

      formData.append("description", this.description);
      formData.append("price", this.price);
      formData.append("theta", this.theta);
      formData.append("liquidation_days", this.liquidation_days);
      formData.append("share_to_read", this.share_to_read ? 1: 0);
      formData.append("tags", JSON.stringify(selectedCategories));
      formData.append("should_publish", should_publish ? 1 : 0);

      this.sendApiRequest("store_article", formData, true).then((article) => {
        // redirect to draft page
        if (!article.is_published) {
          this.$router.push({ name: "drafts" });
        }
        // redirect to article published page
        else if(article.is_published && !this.$route.query.edit) {
          this.$router.push({
            name: "create_article_published",
            params: { articleId: article.id },
          });
        }
        // redirect to profile page when user try to edit an article
        else {
          this.$router.push({ name: "profile" });
        }

      });
    },
    async publish() {
      this.publish_step = 1;
      this.show_publish = 0;
      this.save(1);
    },
    hidePublish() {
      this.publish_step = 1;
      this.show_publish = 0;
      this.publish_text = this.getNextText();
    },
    editorReady(quill) {
      this.quill = quill;
      if (this.article_id) quill.setText(this.content);
      else quill.focus();
    },
    editorBlur(quill) {},
    editorUpdate() {
      if (this.quill) {
        this.content = this.quill.container.firstChild.innerHTML;
      }
    },
    // Triggers when someone try to select/unselect tag
    toggleCategorySelection(categoryId) {
      this.categoriesFiltered = [];

      // toggle selected attribute to categories array
      let category = null;
      for (let index = 0; index < this.categories.length; index++) {
        let category_i = this.categories[index];
        if (category_i.id == categoryId) {
          this.categories[index].selected = !category_i.selected;
          this.publish_step = 1;
          category = category_i;
          break;
        }
      }

      // push or remove index from selectedCategories array
      if (this.selectedCategories.length) {
        for (let j = 0; j < this.selectedCategories.length; j++) {
          let category_i = this.selectedCategories[j];
          if (!category.selected && category_i.id == categoryId) {
            this.selectedCategories.splice(j, 1);
            this.publish_step = 1;
            break;
          }
          else if(category.selected) {
            this.selectedCategories.push(category);
            this.publish_step = 1;
            break;
          }
        }
      }
      else {
        this.selectedCategories.push(category);
      }

      this.search_category = '';
      this.categoriesFiltered = this.categories;
    },
    // Triggers when someone try to press enter in search tag field
    searchCategory() {

      // Try to match category name with user search query
      this.categoriesFiltered = [];

      this.categories.forEach((category) => {
        if (
          category.name
            .toLowerCase()
            .indexOf(this.search_category.toLowerCase()) >= 0
        ) {
          this.categoriesFiltered.push(category);
        }
      });

      // If no search found
      if (!this.categoriesFiltered.length) {

        let customCategories = this.selectedCategories.filter(category => category.custom == true);
        this.categoriesFiltered = this.categories;

        // If user already selected this.maxArticleTags() then nothing
        if(this.selectedCategories.length == this.maxArticleTags()) {}

        // If user already entered custom_tags and they reached to this.maxArticleTags() then nothing
        else if(this.custom_tags_count == this.maxArticleTags()) {}

        else if(customCategories.length+1 <= this.maxArticleTags()) {

          let lastCategoryId = this.categories[this.categories.length - 1].id;
          let lastCategoryIdInc = lastCategoryId + 1;
          let newCategory = { id: lastCategoryIdInc, name: this.search_category, selected: true, custom: true };
          this.categories.push(newCategory);
          this.selectedCategories.push(newCategory);
          this.custom_tags_count++;
          // this.categoriesFiltered = this.categories;
        }

        this.publish_step = 1;
        this.search_category = '';
      }

    },
    getNextText() {
      return 'Next';
    },
    getPublishText() {
      return this.article && this.article.is_published ? this.getNextText(): 'Publish';
    },
    getPublishTextForPopup() {
      return this.$route.query.edit ? 'Update' : 'Publish';
    },
    getSelectedCategories() {
      let selectedCategories = [];
      this.categories.forEach((category) => {
        if (category.selected) {
          selectedCategories.push(category);
        }
      });
      return selectedCategories;
    },
    checkIfCategoryIsAlreadySelected(categoryId) {
      let categorySelected = false;

      for (let index = 0; index < this.categories.length; index++) {
        const category = this.categories[index];
        if (category.id == categoryId && category.selected) {
          categorySelected = true;
          break;
        }

      }
      return categorySelected;
    },
  },
  components: {
    CreateArticleBanner,
    Category,
    QuillEditor,
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content">
      <CreateArticleBanner :article_id="article_id" :article_image="article_image"
        :article_image_form_input="article_image_form_input" @show-publish="
          publish_text = getPublishText();
          show_publish = 1;
          publish_step = 1;
        " @save-article="save(0)" @article_image="(ar_img) => (article_image = ar_img)" @article_image_form_input="
          (ar_img) => (article_image_form_input = ar_img)
        " :back_url="{ name: 'drafts' }" :publish_text="publish_text" />

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
                v-if="showQuillEditor == 0 && !this.article_id">
                Start Writing
              </div>
              <template v-if="showQuillEditor == 1 || this.article_id">
                <QuillEditor :options="options" @ready="editorReady($event)" @blur="editorBlur"
                  @update:content="editorUpdate" class="editor_height w_template" />
              </template>
            </div>
          </form>
        </div>
      </div>

      <div v-if="show_publish" class="overlay"></div>

      <div :class="[
        'create_article__publish',
  { 'create_article__publish--visible': show_publish },
      ]" v-click-outside="hidePublish">
        <!-- Header -->
        <div class="create_article__publish__header">
          <div class="container">
            <div class="flex justify-between">
              <button>
                <img src="@/assets/img/close-icon--v3.svg" alt=""
                  @click="publish_step == 2 || publish_step == 3 ? (publish_step = 1) : hidePublish()" />
              </button>
              <button class="font-semibold" @click="
                if(publish_step == 3) {
                  publish_step = 1;
                }
                else if(this.$route.query.edit) {
                  publish();
                }
                else {
                  publish_step = 4
                }
                ">
                {{ publish_step == 3 ? 'Back' : getPublishTextForPopup() }}
              </button>
            </div>
          </div>
        </div>

        <!-- Body -->
        <div class="py-5 create_article__publish__body">
          <div class="container">

            <!-- Description -->
            <div v-if="publish_step == 1">
              <div class="pb-4 font-semibold flex justify-between">
                Description
                <button class="relative -top-2" @click="publish_step = 3"><img
                    src="@/assets/img/horizontal-dots-icon.svg" alt="">
                </button>
              </div>
              <textarea v-model="description" :maxlength="publish_description_max"
                class="w-full bg-transparent border-white border f-13"></textarea>
              <div class="flex justify-end f-13">
                {{ description.length }}/{{ publish_description_max }}
              </div>

              <div class="row mt-6">
                <div class="w-6/12">
                  <div class="font-semibold mb-6">Price</div>
                  <div class="flex items-center">
                    <input type="number"
                      class="create_article__publish__body__input f-20 font-bold bg-transparent aquamarine-color opacity-80"
                      min="0" v-model.number="price" @keypress="onlyNumeric" ref="price" />
                    <span class="aquamarine-color opacity-80 f-20 font-bold mr-4">{{ this.currency }}</span>
                    <div>
                      <div>
                        <img src="@/assets/img/polygon-up.svg" alt="" class="cursor-pointer mb-1" @click="price++" />
                      </div>
                      <div>
                        <img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer"
                          @click="price > 0 ? price-- : ''" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="w-6/12">
                  <div class="font-semibold mb-6">Days until liquidation</div>
                  <div class="flex items-center">
                    <input type="number"
                      class="create_article__publish__body__input f-20 font-bold bg-transparent aquamarine-color opacity-80"
                      min="0" v-model.number="liquidation_days" @keypress="onlyNumeric" ref="liquidation_days" />
                    <span class="aquamarine-color opacity-80 f-20 font-bold mr-4">Days</span>
                    <div>
                      <div>
                        <img src="@/assets/img/polygon-up.svg" alt="" class="cursor-pointer mb-1"
                          @click="liquidation_days++" />
                      </div>
                      <div>
                        <img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer"
                          @click="liquidation_days > 0 ? liquidation_days-- : ''" />
                      </div>
                    </div>
                  </div>
                </div>

              </div>

              <div class="mt-8">
                <div class="font-semibold mb-5">Tags</div>
                <ul class="categories whitespace-normal">
                  <li class="categories__plus categories__plus--white mb-4" @click="
                    publish_step = 2
                  ">
                    <a href="javascript:void(0)"><img src="@/assets/img/plus-icon--v2.svg" alt="" /></a>
                  </li>
                  <li v-for="(category, index) in selectedCategories" :key="index">
                    <Category :category="category" class="mb-3.5" class_name="category--bright" />
                  </li>
                </ul>
              </div>
            </div>

            <!-- Tags -->
            <div v-else-if="publish_step == 2">
              <form action="javascript:void(0)" method="GET" :class="['searchbar flex mb-8']" @submit="searchCategory">
                <div class="relative create_article__publish__body__searchbar">
                  <label for="search" class="pos-middle">
                    <img src="@/assets/img/search-icon--dark.svg" alt="" />
                  </label>
                  <input type="text" placeholder="Search for a tag" v-model="search_category" class="text-black" />
                </div>
              </form>

              <template v-if="categoriesFiltered.length">
                <ul class="categories whitespace-normal">
                  <li v-for="(category, index) in categoriesFiltered" :key="index">
                    <Category :category="category" :choose="checkIfCategoryIsAlreadySelected(category.id) || (getSelectedCategories().length != maxArticleTags()) ? true: false"
                      @category_id="toggleCategorySelection" />
                    <!-- <Category :category="category"
                      :choose="checkIfCategoryIsAlreadySelected(category.id) || (getSelectedCategories().length != maxArticleTags()) ? true: false"
                      class="mb-3.5" @category_id="toggleCategorySelection" /> -->
                  </li>
                </ul>
              </template>
              <template v-else>
                <div class="text-center">No tag found!</div>
              </template>
              <p class="mt-4 ml-3">
                <small>You can only select maximum {{ maxArticleTags() }} tags</small>
              </p>
            </div>

            <!-- Advanced Settings -->
            <div v-else-if="publish_step == 3">
              <div class="pb-4 font-semibold flex justify-between">
                Advanced Settings
              </div>

              <div class="row mt-5">
                <div class="w-5/12">
                  <div class="font-semibold mb-3 flex">Theta
                    <button @click="theta_info = !theta_info"><img src="@/assets/img/help-icon.svg"
                        class="relative -top-2 left-1" /></button>
                  </div>
                  <div class="flex items-center">
                    <input type="number"
                      class="create_article__publish__body__input f-20 font-bold bg-transparent aquamarine-color opacity-80"
                      min="0" v-model.number="theta" @keypress="onlyNumeric" ref="theta" />
                    <span class="aquamarine-color opacity-80 f-20 font-bold mr-4">%</span>
                    <div>
                      <div>
                        <img src="@/assets/img/polygon-up.svg" alt="" class="cursor-pointer mb-1" @click="theta++" />
                      </div>
                      <div>
                        <img src="@/assets/img/polygon-down.svg" alt="" class="cursor-pointer"
                          @click="theta > 0 ? theta-- : ''" />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="w-7/12 flex justify-end items-start" v-if="theta_info == 1">
                  <div class="info-box inline-block">
                    This is the % of each pay-to-read transaction that goes to your wallet. Remember, if theta is set
                    too high, it will drive potential readers/investors away!
                  </div>
                </div>
              </div>

              <div class="row mt-24">
                <div class="w-5/12">
                  <div class="font-semibold mb-4 flex">Share-to-read
                    <button @click="share_to_read_info = !share_to_read_info"><img src="@/assets/img/help-icon.svg"
                        class="relative -top-2 left-1" /></button>
                  </div>
                  <div>
                    <div class="can-toggle demo-rebrand-2">
                      <input id="e" type="checkbox" v-model="share_to_read">
                      <label for="e">
                        <div class="can-toggle__switch" data-checked="On" data-unchecked="Off"></div>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="w-7/12 flex justify-end items-start" v-if="share_to_read_info == 1">
                  <div class="info-box inline-block">
                    Leaving this on will encourage your readers to share your work with their network.
                  </div>
                </div>
              </div>

            </div>

            <!-- Publish Confirmation -->
            <div v-else-if="publish_step == 4" class="create_article__publish__body__confirmation text-center">
              <div class="font-semibold mb-5">Are you sure?</div>
              <div class="flex justify-center">
                <button class="f-13 font-semibold bg-white primary-color border-radius mr-5" @click="publish_step = 1">
                  No
                </button>
                <button class="f-13 font-semibold bg-white primary-color border-radius" @click="publish">
                  Yes
                </button>
              </div>
            </div>


          </div>
        </div>
      </div>

      <Error />
    </div>
  </div>
</template>
