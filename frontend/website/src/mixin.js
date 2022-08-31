import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";

export default {
  data() {
    return {
      isError: -1,
      currency: currency,
      profileTabs: ["Articles", "Investments"],
      articleTabs: ["Article", "Statistics"],
      searchTabs: ["Articles", "Authors"],
    };
  },
  created() {
    // isError
    this.$watch("isError", (isError) => {
      this.emitter.emit("isError", isError);
    });

    this.emitter.on("isError", (isError) => {
      this.isError = isError;
    });
  },
  methods: {
    onlyNumeric(event) {
      event = event ? event : window.event;
      var charCode = event.which ? event.which : event.keyCode;
      if (
        charCode > 31 &&
        (charCode < 48 || charCode > 57) &&
        charCode !== 46
      ) {
        event.preventDefault();
      } else {
        return true;
      }
    },

    setInputDynamicWidth(input) {
      if(typeof input !== 'undefined'){
        setTimeout(() => {
        input.style.width = input.value.length + "ch";
      }, 0);
      }
    },

    async sendApiRequest(url, data, errorPopup = false, meta = {}) {
      return this.afterApiCall(
        this.$http.post(url, data, {
          headers: { Authorization: "Bearer " + this.getAuthToken() },
        }),
        errorPopup,
        meta
      );
    },

    async sendAllMultiApiRequests(array, meta = {}) {
      return this.afterApiCall(
        Promise.all(this.prepareMultiApiRequest(array)),
        false,
        meta
      );
    },

    beforeApiCall() {
      toggleLoader(1);
    },

    afterApiCall(api, errorPopup, meta) {
      this.beforeApiCall();

      return api
        .then((apiResponses) => {
          apiResponses = Array.isArray(apiResponses)
            ? apiResponses
            : [apiResponses];

          let responses = [],
            errors = [];
          apiResponses.forEach((response) => {
            let data = response.data;

            responses.push(data);

            // if (Array.isArray(data)) {
            //     responses.push(data);
            // }
            // else {
            //     // errors.push(data.error);
            //     // errors.push(response);
            // }
          });

          if (errors.length) {
            // console.log(errors.join('\n'));
            // return Promise.reject(errors.join('\n'));
          } else {
            this.isError = 0;
            return responses.length == 1 ? responses[0] : responses;
          }
        })
        .catch((response) => {
          if (response.response.status == 401 && response.response.data.error == 'Unauthenticated.') {
            this.storeReferrerUrl(this.$route.fullPath);
            this.$router.replace({ name: "signin" });
            return;
          }

          if (response.response.status == 404) {
            this.$router.push({ name: "home" });
            return;
          }

          let error = response.response.data;
          if (errorPopup) {
            this.swalError(this.errorFormatting(error));
          } else {
            this.isError = true;
          }

          console.log(this.errorFormatting(error));
          return Promise.reject(this.errorFormatting(error));
        })
        .finally(() => {
          if (
            typeof meta.removeLoaderAfterApi !== "undefined" &&
            !meta.removeLoaderAfterApi
          ) {
          } else {
            toggleLoader(false);
          }
        });
    },

    prepareMultiApiRequest(array) {
      let requestData = [];

      array.forEach((element) => {
        requestData.push(
          this.$http.post(element.url, element.data, {
            headers: { Authorization: "Bearer " + this.getAuthToken() },
          })
        );
      });

      return requestData;
    },

    async validate(schema, data) {
      await schema.validate(data, { abortEarly: false }).catch((err) => {
        let errors = [];
        err.inner.forEach((e) => {
          errors.push(e.message);
        });
        this.swalError(errors);
        return Promise.reject(errors);
      });
    },

    errorFormatting(data) {
      let err = "";
      if (typeof data.errors !== "undefined") {
        let errors = [];
        for (let errs in data.errors) {
          data.errors[errs].forEach((error) => {
            errors.push(error);
          });
        }
        err = errors.join("<br>");
      } else if (typeof data.error !== "undefined") {
        err = data.error;
      } else err = data.message;
      return err;
    },

    swalError(errors) {
      errors = Array.isArray(errors) ? errors : [errors];

      let ul = "<ul>";
      errors.forEach((e) => {
        ul += "<li>" + e + "</li>";
      });
      ul += "</ul>";

      Swal.fire({
        title: "Error!",
        html: ul,
        icon: "error",
      });
    },

    getFullUrl(route) {
      return new URL(route, window.location.href).href;
    },

    getUserProfileRoute(user_id, query = null) {
      let r = {
        name: "profile",
        params: { userId: this.getAuthId() == user_id ? "" : user_id },
      };
      if (query) r.query = query;
      return r;
    },

    calculateIntegral($lowerbound, $upperbound)
    {
        return this.toFixedAmount2(
            (2 / 3) * Math.pow($upperbound, 3 / 2) - (2 / 3) * Math.pow($lowerbound, 3 / 2)
        );
    },

    calculateIntegralWithConstant($lowerbound, $upperbound)
    {
        return this.toFixedAmount2(
             0.8 * ((2 / 3) * Math.pow($upperbound, 3 / 2) - (2 / 3) * Math.pow($lowerbound, 3 / 2))
        );
    },

    checkPreviousPage() {
      return this.$router.options.history.state.back ? true : false;
    },

    storeReferralToken(referral_token) {
      if (referral_token && this.getReferralToken() != referral_token) {
        localStorage.setItem("cypher_referral_token", referral_token);
      }
    },

    getReferralToken() {
      return localStorage.getItem("cypher_referral_token");
    },

    storeAuth(auth) {
      localStorage.setItem("cypher_auth_token", auth.token);
      localStorage.setItem("cypher_auth_id", auth.id);
      localStorage.setItem("cypher_auth_name", auth.name);
    },

    storeName(name) {
      localStorage.setItem("cypher_auth_name", name);
    },

    getAuthToken() {
      return localStorage.getItem("cypher_auth_token");
    },

    removeAuthToken() {
      return localStorage.removeItem("cypher_auth_token");
    },

    getAuthId() {
      return localStorage.getItem("cypher_auth_id");
    },

    removeAuthId() {
      return localStorage.removeItem("cypher_auth_id");
    },

    getAuthName() {
      return localStorage.getItem("cypher_auth_name");
    },

    storeReferrerUrl(url) {
      return sessionStorage.setItem("cypher_referrer_url", url);
    },

    getReferrerUrl() {
      return sessionStorage.getItem("cypher_referrer_url");
    },

    removeReferrerUrl() {
      return sessionStorage.removeItem("cypher_referrer_url");
    },

    getPfpImage(pfp) {
      return pfp ? pfp: new URL("./assets/img/faker.png", import.meta.url).href;
    },

    maxArticleTags() {
      return window.max_article_tags;
    },

    toFixedAmount($amount) {
      return Number($amount.toFixed(2));
    },

    toFixedAmount2($amount) {
      return Number($amount.toFixed(4));
    },

    is_logged_in() {
      this.sendApiRequest("is_logged_in", {}).then((response) => {
        if(response != -1) {
          this.$router.replace({ name: "home" });
        }
      });
    },

    // If you edit this function dont forget to edit same function in backend ApiController
    isArticleFree(article) {
      let is_article_free = false;
      if(this.getAuthId() && this.getAuthId() == article.user_id) {
        is_article_free = true;
      }
      else if(article.remaining_liquidation_days == 0) {
        is_article_free = true;
      }
      else if(article.is_paid_by_user_count) {
        is_article_free = true;
      }
      else if(article.is_paid_by_referrals_count) {
        is_article_free = true;
      }
      else if(article.price <= 0) {
        is_article_free = true;
      }
      return is_article_free;
    }
  },
};
