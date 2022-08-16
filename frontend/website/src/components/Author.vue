<script>
export default {
  props: {
    author: {
      default: {},
    },
    anotherProfile: {
      default: false,
    },
  },
  data() {
    return {
      isProfileMenuVisible: false,
    }
  },
  methods: {
    async follow(followed_id) {
      this.sendApiRequest(
        "do_follow_toggle",
        {
          followed_id: followed_id,
        },
        true
      ).then(() => {
        this.author.is_followed = this.author.is_followed ? 0 : 1;
        this.author.followers_count = this.author.is_followed
          ? this.author.followers_count + 1
          : this.author.followers_count - 1;
      });
    },
    block_user() {
      this.sendApiRequest(
        "block_user",
        {
          user_id: this.author.id,
        },
        true
      ).then(() => {
        this.closeProfileMenu();
        this.$router.push({name: 'home'});
      });
    },
    openProfileMenu() {
      this.isProfileMenuVisible = true;
    },
    closeProfileMenu() {
      this.isProfileMenuVisible = false;
    }
  },
};
</script>

<template>
  <div class="container">
    <div class="flex justify-end -mt-4 mb-4">
      <slot name="btns" />
    </div>

    <div class="userprofile flex mb-5">
      <div class="userprofile__left">
        <img :src="getPfpImage(author.pfp)" alt="" class="relative" />
      </div>
      <div class="userprofile__right flex justify-between pl-4">
        <div>
          <div class="flex">
            <h2>{{ author.name }}</h2>
            <div v-if="anotherProfile" class="f-16 ml-9 relative top-1">
              <button :class="[author.is_followed ? 'primary-color' : 'text-white']" @click="follow(author.id)">
                <b>{{ author.is_followed ? "followed" : "follow" }}</b>
              </button>
            </div>
          </div>
          <div class="flex mt-1 mb-1">
            <div>
              <span>{{ author.followers_count }}</span> followers
            </div>
            <div class="ml-2.5">
              <span>{{ author.followed_count }}</span> following
            </div>
          </div>
          <div class="u-gap mt-2.5 mb-2.5"></div>
          <p class="f-13 userprofile__right__p">
            {{ author.bio }}
          </p>
        </div>
        <div>
          <button class="relative -top-2" @click.prevent="openProfileMenu">
            <img src="@/assets/img/horizontal-dots-icon.svg" alt="" />

            <div class="menu-modal" v-click-outside="closeProfileMenu" v-if="isProfileMenuVisible">
              <ul v-if="anotherProfile">
                <li>
                  <button @click="block_user">Block</button>
                </li>
              </ul>
              <ul v-else>
                <li>
                  <router-link :to="{ name: 'send-token'}">Send CPHR</router-link>
                </li>
                <li>
                  <router-link :to="{name: 'profile-edit'}">Edit Profile</router-link>
                </li>
              </ul>
            </div>
          </button>
          <div class="default-modal-styles" v-if="isProfileMenuVisible"></div>
        </div>
      </div>
    </div>
  </div>
</template>