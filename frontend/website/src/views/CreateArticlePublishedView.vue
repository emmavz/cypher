<script>
export default {
  data() {
    return {
      article_id: "0", // 0 is required otherwise it will throw an error in $router.resolve
      title: "",
      image_url: "",
      user: {},
      share_link: "",
    };
  },
  async created() {
    this.sendApiRequest("get_article_homepage", {
      article_id: Number(this.$route.params.articleId),
      article_published: 1,
    }).then((response) => {
      if (response[0]) {
        this.article_id = response[0].id;
        this.title = response[0].title;
        this.image_url = response[0].image_url;
        this.user = response[1];

        this.share_link = this.getFullUrl(
          this.$router.resolve({
            name: "article_homepage",
            params: {
              articleId: this.article_id,
              referralToken: this.user.referral_token,
            },
          }).fullPath
        );
      }
    });
  },
  methods: {
    popupwindow() {
      let url =
        "https://www.facebook.com/sharer.php?u=" +
        window.apiRoot +
        this.article_id +
        "/" +
        this.getAuthId() +
        "/" +
        new Date().getTime() +
        Math.floor(Math.random() * 10000) +
        "/" +
        "facebookshare";
      url = url + "?internal_request=1";
      var w = 550;
      var h = 450;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      return window.open(
        url,
        "",
        "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=" +
          w +
          ", height=" +
          h +
          ", top=" +
          top +
          ", left=" +
          left
      );
    },
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content text-center">
      <div v-if="!isError">
        <div class="font-semibold f-18 br-b py-5 banner_img">
          <RouterLink :to="{ name: 'drafts' }">
            <button class="close-icon">
              <img src="/src/assets/img/close-icon--v2.svg" alt="" width="34" />
            </button>
          </RouterLink>
          Congratulations!
        </div>

        <div class="pt-8 pb-12">
          <div class="container">
            <h2 class="f-18 mb-7">
              You just published<br />
              ‘<span>{{ title }}</span
              >’
            </h2>
          </div>

          <div>
            <img :src="image_url" alt="" class="w-full mb-11 article-banner-img" />
          </div>

          <div class="br-t">
            <div class="container">
              <div class="f-18 font-semibold mt-9 mb-5">
                Share your story now
              </div>
              <div class="flex justify-center">
                <div>
                  <button @click="popupwindow()">
                    <img src="@/assets/img/facebook-icon.svg" alt="" />
                  </button>
                </div>
                <div>
                  <button class="ml-6" @click="$copyText(share_link)">
                    <img src="@/assets/img/website-icon.svg" alt="" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Error />
    </div>
  </div>
</template>
