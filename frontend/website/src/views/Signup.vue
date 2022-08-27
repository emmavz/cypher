<script>
import * as yup from "yup";
export default {
  data() {
    return {
      name: "",
      email: "",
      password: "",
    };
  },
  async created() {
    await this.is_logged_in();
  },
  methods: {
    async signup() {
      let validations = {
        name: yup.string().required(),
        email: yup.string().email().required(),
        password: yup.string().required(),
      };

      const schema = yup.object().shape(validations);

      await this.validate(schema, {
        name: this.name,
        email: this.email,
        password: this.password,
      });

      this.sendApiRequest(
        "signup",
        {
          name: this.name,
          email: this.email,
          password: this.password,
        },
        true
      ).then((response) => {
        this.storeAuth({
          token: response.token,
          name: response.name,
          id: response.id,
        });

        let referrerUrl = this.getReferrerUrl();
        if (referrerUrl) {
          this.$router.push(referrerUrl);
        }
        else {
          this.$router.push({ name: "profile" });
        }

      });
    },
  },
};
</script>

<template>
  <div class="app-wp">
    <Header />

    <!-- Content -->
    <div class="content i-wrap pt-8">
      <div class="auth-container container">
        <img src="@/assets/img/logo.svg" alt="" class="auth-container__img" />
        <h2>Welcome to CYPHER!</h2>
        <p class="auth-container__p">We can’t wait to get to know you!</p>

        <form action="javascript:void(0)" @submit="signup">
          <div class="">
            <label for="name">Name</label>
            <input
              placeholder="What should we call you?"
              id="name"
              v-model="name"
            />
          </div>

          <div class="">
            <label for="email">Email</label>
            <input
              type="email"
              placeholder="Please enter your email address"
              id="email"
              v-model="email"
            />
          </div>

          <div class="">
            <label for="password">Password</label>
            <input
              placeholder=""
              type="password"
              id="password"
              v-model="password"
            />
          </div>

          <div class="mt-9 auth-container__footer">
            <button type="submit" class="cpe-btn cpe-btn--primary mb-5">
              Sign Up
            </button>
            <div class="flex justify-between">
              <div class="opacity-70">Already have an account?</div>
              <div>
                <router-link :to="{ name: 'signin' }" class="primary-color"
                  >Log in</router-link
                >
              </div>
            </div>
          </div>
        </form>
      </div>

      <Error />
    </div>
  </div>
</template>
