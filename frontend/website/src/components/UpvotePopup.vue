<script>
export default {
  props: ["showpopup", "bondingCurveTokens"],
  data() {
    return {
      votes: "",
      votesStep: "",
    };
  },
  created() {
    if (this.showpopup != 0) {
      this.votesStep = this.showpopup;
      this.emitter.emit("votes-step", this.showpopup);
    }
  },
  methods: {
    addVotesSuffix() {
      let votes = this.removeVotesSuffix();
      if (votes) this.votes = votes + " Tokens (" + this.calculateIntegral(this.bondingCurveTokens, this.bondingCurveTokens+Number(votes)) + " " + " " +this.currency + ")";
      else this.$emit("votes", "");
    },
    removeVotesSuffix() {
      return this.votes.trim().split(" ")[0];
    },
    upvote() {
      if (this.votes) {
        this.votesStep = "";
        this.$emit("votes", this.removeVotesSuffix());
        this.votes = "";
        this.emitter.emit("votes-step", "");
      }
    },
    closeUpvote() {
      this.$emit("votes", "");
      this.votes = "";
      this.emitter.emit("votes-step", "");
      this.votesStep = "";
    },
  },
};
</script>

<template>
  <div>
    <button
      class="font-bold f-14 mr-7"
      @click.stop="
        votesStep = 1;
        emitter.emit('votes-step', 1);
      "
    >
      Upvote
    </button>

    <div
      v-if="votesStep"
      class="votes-popup flex items-center"
      v-click-outside="closeUpvote"
    >
      <div class="container">
        <template v-if="votesStep == 1">
          <div class="flex justify-between items-center w-full">
            <div>
              <b class="f-14 primary-color">Are you sure?</b>
            </div>
            <div>
              <button
                @click="
                  votesStep = 2;
                  emitter.emit('votes-step', 2);
                "
                class="v-btn"
              >
                Yes
              </button>
              <button
                @click="
                  votesStep = 0;
                  emitter.emit('votes-step', 0);
                "
                class="v-btn ml-4"
              >
                No
              </button>
            </div>
          </div>
        </template>

        <template v-if="votesStep == 2">
          <div class="flex justify-center items-center w-full">
            <div>
              <b class="f-14 primary-color">How much?</b>
            </div>
            <div>
              <form
                action="#"
                method="POST"
                @submit.prevent="upvote"
                class="text-black flex items-center"
              >
                <div>
                  <input
                    type="text"
                    name="votes"
                    v-model="votes"
                    v-on:blur="addVotesSuffix"
                    v-on:focus="votes = removeVotesSuffix()"
                    class="v-input mr-3.5 ml-6"
                  />
                </div>
                <div v-if="votes">
                  <button type="submit" class="relative top-1 tick-icon">
                    <img src="@/assets/img/tick-green-icon.svg" alt="" />
                  </button>
                </div>
              </form>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
