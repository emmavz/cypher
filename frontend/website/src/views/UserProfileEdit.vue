<script>
import * as yup from "yup";
export default {
  data() {
    return {
      pfp: "",
      pfp_form_input: "",
      name: "",
      email: "",
      password: "",
      bio: "",
    };
  },
  created() {
    this.sendApiRequest("get_edit_user_profile", {}).then((response) => {
      this.pfp = response.pfp ? response.pfp: '';
      this.name = response.name ? response.name: '';
      this.email = response.email ? response.email: '';
      this.bio = response.bio ? response.bio: '';
    });
  },
  methods: {
    async updateProfile() {
      let validations = {
        name: yup.string().required(),
        bio: yup.string().required(),
        email: yup.string().email().required(),
        password: yup.string(),
        pfp: yup.string().required().label("picture"),
      };

      const schema = yup.object().shape(validations);

      await this.validate(schema, {
        name: this.name,
        bio: this.bio,
        email: this.email,
        password: this.password,
        pfp: this.pfp,
      });

      let formData = new FormData();
      formData.append("name", this.name);
      formData.append("bio", this.bio);
      formData.append("email", this.email);
      formData.append("password", this.password);
      formData.append("pfp", this.pfp_form_input);

      this.sendApiRequest("update_user_profile", formData, true).then(() => {
        this.storeName(this.name);
        this.$router.push({ name: "profile" });
      });
    },
    logout() {
      this.sendApiRequest("logout", {}).then(() => {
        this.removeAuthToken();
        this.removeAuthId();
        this.removeReferrerUrl();
        this.$router.push({ name: "home" });
      });
    },
    onFileChange(e) {
      if (e.target.files.length) {
        const file = e.target.files[0];
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
          this.pfp = reader.result;
          this.pfp_form_input = file;
        };
      } else {
        this.pfp = "";
        this.pfp_form_input = "";
      }
    },
  },
};
</script>

<template>
  <div class="min-h-full">
    <div class="app-wp bg-white" v-if="!isError">
      <div class="text-white primary-bg pt-10 pb-28">
        <div class="flex items-center justify-between container">
          <button @click="$router.push({ name: 'profile' })">
            <img src="@/assets/img/arrow-left-icon.svg" alt="" class="ml-auto" />
          </button>
          <div class="f-15 font-semibold">Settings</div>
          <button>
            <img src="@/assets/img/u_share-alt-icon.svg" alt="" class="ml-auto" />
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="text-black bg-white pb-24">
        <div class="container">
          <div class="profile-edit">
            <div class="profile-edit__header flex items-center flex-col -translate-y-1/2">
              <label for="picture">
                <div>
                  <img :src="getPfpImage(pfp)" alt=""
                    class="cursor-pointer profile-edit__header__profile object-cover" />
                </div>
                <div class="f-12 mt-1 text-center">
                  <span class="cursor-pointer">Change Picture</span>
                </div>
              </label>
              <input type="file" accept="image/*" id="picture" class="input-hide" @change="onFileChange" />
            </div>

            <div class="profile-edit__body">
              <form action="javascript:void(0)" @submit="updateProfile">
                <div class="">
                  <label for="name">Name</label>
                  <input placeholder="" id="name" v-model="name" />
                </div>

                <div class="">
                  <label for="email">Email Address</label>
                  <input placeholder="" id="email" v-model="email" type="email" />
                </div>

                <div class="">
                  <label for="password">Password</label>
                  <input placeholder="" id="password" v-model="password" />
                </div>

                <div class="">
                  <label for="bio">Bio</label>
                  <textarea placeholder="" id="bio" v-model="bio"></textarea>
                </div>

                <div class="mt-9 container">
                  <button type="submit" class="cpe-btn cpe-btn--primary mb-5">
                    Save Changes
                  </button>
                  <button type="button" class="cpe-btn cpe-btn--secondary" @click="logout">
                    Log Out
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Error />
  </div>
</template>
