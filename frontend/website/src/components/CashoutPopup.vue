<script>
export default {
  props: ["showpopup"],
  data() {
    return {
      cashout: "",
      cashoutStep: "",
    };
  },
  created() {
    if (this.showpopup != 0) {
      this.cashoutStep = this.showpopup;
      this.emitter.emit("cashout-step", this.showpopup);
    }
  },
  methods: {
    addCashoutSuffix() {
      let cashout = this.removeCashoutSuffix();
      if (cashout) this.cashout = cashout + " " + this.currency;
      else this.$emit("cashout", "");
    },
    removeCashoutSuffix() {
      return this.cashout.replace(/[^0-9\.]+/g, "");
    },
    cashoutFunc() {
      if (this.cashout) {
        this.cashoutStep = "";
        this.$emit("cashout", this.removeCashoutSuffix());
        this.cashout = "";
        this.emitter.emit("cashout-step", "");
      }
    },
    closeCashout() {
      this.$emit("cashout", "");
      this.cashout = "";
      this.emitter.emit("cashout-step", "");
      this.cashoutStep = "";
    },
  },
};
</script>

<template>
  <div>
    <button
      class="font-bold f-14"
      @click.stop="
        cashoutStep = 1;
        emitter.emit('cashout-step', 1);
      "
    >
      Cash Out
    </button>

    <div
      v-if="cashoutStep"
      class="votes-popup flex items-center"
      v-click-outside="closeCashout"
    >
      <div class="container">
        <template v-if="cashoutStep == 1">
          <div class="flex justify-between items-center w-full">
            <div>
              <b class="f-14 primary-color">Are you sure?</b>
            </div>
            <div>
              <button
                @click="
                  cashoutStep = 2;
                  emitter.emit('cashout-step', 2);
                "
                class="v-btn"
              >
                Yes
              </button>
              <button
                @click="
                  cashoutStep = 0;
                  emitter.emit('cashout-step', 0);
                "
                class="v-btn ml-4"
              >
                No
              </button>
            </div>
          </div>
        </template>

        <template v-if="cashoutStep == 2">
          <div class="flex justify-center items-center w-full">
            <div>
              <b class="f-14 primary-color">How much?</b>
            </div>
            <div>
              <form
                action="#"
                method="POST"
                @submit.prevent="cashoutFunc"
                class="text-black flex items-center"
              >
                <div>
                  <input
                    type="text"
                    name="cashout"
                    v-model="cashout"
                    v-on:blur="addCashoutSuffix"
                    v-on:focus="cashout = removeCashoutSuffix()"
                    class="v-input mr-3.5 ml-6"
                  />
                </div>
                <div v-if="cashout">
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
