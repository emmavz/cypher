<script>
import * as yup from "yup";
import SuccessPopup from "@/components/SuccessPopup.vue";
export default {
    data() {
        return {
            pfp: "",
            email: "",
            cphr: "",
            receiver: "",
            showSuccessPopup: false,
            successTitle: 'Success!',
            successMsg: '',
        };
    },
    created() {
        this.sendApiRequest("get_edit_user_profile", {}).then((response) => {
            this.pfp = response.pfp ? response.pfp : '';
            this.email = response.email ? response.email : '';
        });
    },
    methods: {
        async sendToken() {

            let validations = {
                cphr: yup.string().required(),
                receiver: yup.string().email().required(),
            };

            const schema = yup.object().shape(validations);

            await this.validate(schema, {
                cphr: this.cphr,
                receiver: this.receiver,
            });

            this.sendApiRequest("send_token", {
                cphr: this.cphr,
                receiver: this.receiver
            }, true).then(() => {
                this.showSuccessPopup = true;
                this.successMsg = 'You\’ve sent '+this.cphr+' CPHR to<br> <b>'+this.receiver+'</b>.<br>We\’ll let them know you sent it.';
            });
        },
        closeSuccessPopup() {
            this.showSuccessPopup = false;
        }
    },
    components: {
        SuccessPopup
    }
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
                    <div class="f-15 font-semibold">Send CPHR</div>
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
                            <div>
                                <img :src="getPfpImage(pfp)" alt=""
                                    class="cursor-pointer profile-edit__header__profile object-cover" />
                            </div>
                            <div class="f-12 mt-1 text-center">
                                <b>YOUR USER ID</b>
                                <div><span>{{ email }}</span></div>
                            </div>
                        </div>

                        <div class="profile-edit__body">
                            <form action="javascript:void(0)" @submit="sendToken">
                                <div class="">
                                    <label for="receiver">Who are you sending CPHR to?</label>
                                    <input type="email" placeholder="Must be a valid User ID" id="receiver"
                                        v-model="receiver" class="italic-placeholder" />
                                </div>

                                <div class="">
                                    <label for="cphr">How much CPHR are you sending?</label>
                                    <input placeholder="0" id="cphr" v-model="cphr" />
                                </div>

                                <div class="mt-32 container">
                                    <p class="mb-6 f-13 text-center">
                                        <em>Looking for CPHR somebody sent you? Receive CPHR through your notifications
                                            page!</em>
                                    </p>
                                    <button type="submit" class="cpe-btn cpe-btn--primary mb-5">
                                        Send CPHR
                                    </button>
                                    <router-link :to="{name: 'profile'}" class="cpe-btn cpe-btn--secondary w-full block text-center">Cancel</router-link>
                                </div>
                            </form>

                            <SuccessPopup :showpopup="showSuccessPopup" :successTitle="successTitle"
                                :successMsg="successMsg"
                                @showpopup="closeSuccessPopup" />

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Error />
    </div>
</template>
