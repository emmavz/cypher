<script>
export default {
  props: {
    tabList: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      activeTab: 1,
      votesStep: "",
      cashoutsStep: "",
    };
  },
  created() {
    this.emitter.on("votes-step", (votesStep) => {
      this.votesStep = votesStep;
    });
    this.emitter.on("cashout-step", (cashoutsStep) => {
      this.cashoutsStep = cashoutsStep;
    });
  },
};
</script>

<template>
  <div>
    <div
      :class="[
        'tabs-wrap',
        votesStep >= 1 || cashoutsStep >= 1 ? 'tabs-wrap--hide' : '',
      ]"
    >
      <div class="container">
        <div class="flex justify-between items-end pb-2">
          <ul class="tabs flex">
            <li
              v-for="(tab, index) in tabList"
              :key="index"
              :class="{ active: index + 1 == activeTab }"
            >
              <label :for="`${index}`" v-text="tab" class="cursor-pointer" />
              <input
                :id="`${index}`"
                type="radio"
                :value="index + 1"
                v-model="activeTab"
                class="input-hide"
              />
            </li>
          </ul>
          <div>
            <slot name="currency" />

            <ul class="flex">
              <slot name="btns" />
            </ul>
          </div>
        </div>
      </div>
    </div>

    <template v-for="(tab, index) in tabList">
      <div
        :key="index"
        v-if="index + 1 === activeTab"
        class="flex-col blog-post-wrap flex flex-wrap mt-8"
      >
        <slot :name="`tabPanel-${index + 1}`" />
      </div>
    </template>
  </div>
</template>
