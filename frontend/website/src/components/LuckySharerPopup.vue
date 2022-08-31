<script>
import SuccessPopup from "@/components/SuccessPopup.vue";

export default {
  props: ['showSuccessPopup'],
  data() {
    return {
      successTitle: 'It’s your LUCKY DAY!',
      successMsg: 'Thanks for sharing this article! Your sharing chain has been randomly chosen to read this article for free!',
      successButton: null,
    };
  },
  methods: {
    closeSuccessPopup() {
      this.$emit('showpopup', false);
    }
  },
  components: {
    SuccessPopup
  },
};
</script>

<template>

      <SuccessPopup :showpopup="showSuccessPopup" :successTitle="successTitle" :successMsg="successMsg"
        :successButton="successButton" @showpopup="closeSuccessPopup" />

</template>
