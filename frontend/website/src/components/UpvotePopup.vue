<script>
export default {
    props: [''],
    data() {
        return {
            votesStep: '',
            votes: '',
        }
    },
    methods: {
        addVotesSuffix() {
            let votes = this.votes.replace(/[^0-9\.]+/g,"");
            if(votes) this.votes = votes + ' ' + this.currency;
            else this.votes = '';
        },
        removeVotesSuffix() {
            this.votes = this.votes.replace(/[^0-9\.]+/g,"");
        },
        upvote() {
            if(this.votes) {
                this.votesStep = '';
                this.votes = '';
                this.emitter.emit('votes-step', '');
            }
        }
    }
}
</script>

<template>

<div>
    <button class="currency-tag currency-tag--opacity-70" @click="votesStep = 1;emitter.emit('votes-step', 1)">Upvote</button>

    <div v-if="votesStep" class="votes-popup flex items-center">
        <div class="container">

            <template v-if="votesStep == 1">
                <div class="flex justify-between items-center w-full">
                    <div>
                        <b class="f-14 primary-color">Are you sure?</b>
                    </div>
                    <div>
                        <button @click="votesStep = 2;emitter.emit('votes-step', 2)" class="v-btn">Yes</button>
                        <button @click="votesStep = 0;emitter.emit('votes-step', 0)" class="v-btn ml-4">No</button>
                    </div>
                </div>
            </template>

            <template v-if="votesStep == 2">
                <div class="flex justify-center items-center w-full">
                    <div>
                        <b class="f-14 primary-color">How much?</b>
                    </div>
                    <div>
                        <form action="#" method="POST" @submit.prevent="upvote" class="text-black flex items-center">
                            <div>
                                <input type="text" name="votes" v-model="votes" v-on:blur="addVotesSuffix" v-on:focus="removeVotesSuffix" class="v-input mr-3.5 ml-6">
                            </div>
                            <div v-if="votes">
                                <button type="submit" class="relative top-1"><img src="@/assets/img/tick-green-icon.svg" alt=""></button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
</template>