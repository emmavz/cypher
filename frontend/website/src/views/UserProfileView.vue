<script>
import UserProfileTemplate from '@/views/UserProfileTemplate.vue';
import OtherUserProfileTemplate from '@/views/OtherUserProfileTemplate.vue';

export default ({
  data() {
    return {
    }
  },
  computed: {
      dynamicComponent() {
          if (this.$route.params.userId) {
              return OtherUserProfileTemplate;
          }
          else {
              return UserProfileTemplate;
          }
      }
  },
})
</script>

<template>

    <component :is="dynamicComponent" />

</template>