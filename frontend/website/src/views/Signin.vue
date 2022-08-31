<script>
import * as yup from "yup";
export default {
  data() {
    return {
      email: "",
      password: "",
    };
  },
  async created() {
    await this.is_logged_in();
  },
  methods: {
    async signin() {
      let validations = {
        email: yup.string().email().required(),
        password: yup.string().required(),
      };

      const schema = yup.object().shape(validations);

      await this.validate(schema, {
        email: this.email,
        password: this.password,
      });

      this.sendApiRequest(
        "signin",
        {
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
        <h2>
          <div v-if="getAuthName()">Welcome back, {{ getAuthName() }}!</div>
          <div v-else>Welcome!</div>
        </h2>
        <p class="auth-container__p">The world awaits.</p>

        <form action="javascript:void(0)" @submit="signin">
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
              Sign in
            </button>
            <div class="flex justify-between">
              <div class="opacity-70">Don’t have an account?</div>
              <div>
                <router-link :to="{ name: 'signup' }" class="primary-color"
                  >Sign up</router-link
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
