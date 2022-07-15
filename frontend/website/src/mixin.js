import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss';

export default {
    data() {
        return {
            isError: -1,
            currency: currency,
            profileTabs: ['Investments', 'Articles'],
            articleTabs: ['Article', 'Statistics'],
            searchTabs: ['Articles', 'Authors'],
        }
    },
    created() {

        let user_id = window.user_id;
        if (sessionStorage.getItem('cypher_user_id')) {
            user_id = sessionStorage.getItem('cypher_user_id');
        }
        else {
            let userIds = [1, 2, 3, 4, 5];
            user_id = userIds[Math.floor(Math.random() * userIds.length)];
            sessionStorage.setItem('cypher_user_id', user_id);
        }

        window.user_id = user_id;

        // isError
        this.$watch('isError', (isError) => {
            this.emitter.emit('isError', isError);
        });

        this.emitter.on("isError", isError => {
            this.isError = isError;
        });
    },
    methods: {

        onlyNumeric(event) {
            event = (event) ? event : window.event;
            var charCode = (event.which) ? event.which : event.keyCode;
            if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                event.preventDefault();
            } else {
                return true;
            }
        },

        setInputDynamicWidth(input) {
            setTimeout(() => {
                input.style.width = input.value.length + 'ch';
            }, 0);
        },

        async sendApiRequest(url, data, errorPopup = false, meta = {}) {

            return this.afterApiCall(this.$http.post(url, data), errorPopup, meta);

        },

        async sendAllMultiApiRequests(array, meta = {}) {

            return this.afterApiCall(Promise.all(this.prepareMultiApiRequest(array)), false, meta);

        },

        beforeApiCall() {
            toggleLoader(1);
        },

        afterApiCall(api, errorPopup, meta) {

            this.beforeApiCall();

            return api.then((apiResponses) => {

                apiResponses = Array.isArray(apiResponses) ? apiResponses : [apiResponses];

                let responses = [],
                    errors = [];
                apiResponses.forEach(response => {
                    let data = response.data;

                    responses.push(data);

                    // if (Array.isArray(data)) {
                    //     responses.push(data);
                    // }
                    // else {
                    //     // errors.push(data.error);
                    //     // errors.push(response);
                    // }
                });

                if (errors.length) {
                    // console.log(errors.join('\n'));
                    // return Promise.reject(errors.join('\n'));
                }
                else {
                    this.isError = 0;
                    return responses.length == 1 ? responses[0] : responses;
                }

            }).catch((error) => {
                error = error.response.data;
                if (errorPopup) {
                    this.swalError(this.errorFormatting(error));
                }
                else {
                    this.isError = true;
                }

                console.log(this.errorFormatting(error));
                return Promise.reject(this.errorFormatting(error));
            }).finally(() => {
                if (typeof meta.removeLoaderAfterApi !== 'undefined' && !meta.removeLoaderAfterApi) {

                }
                else {
                    toggleLoader(false);
                }
            });
        },

        prepareMultiApiRequest(array) {
            let requestData = [];

            array.forEach(element => {
                requestData.push(
                    this.$http.post(element.url, element.data)
                );
            });

            return requestData;
        },

        async validate(schema, data) {
            await schema
                .validate(data, { abortEarly: false })
                .catch((err) => {
                    let errors = [];
                    err.inner.forEach(e => {
                        errors.push(e.message);
                    });
                    this.swalError(errors);
                    return Promise.reject(errors);
                });
        },

        errorFormatting(data) {
            let err = '';
            if (typeof data.errors !== 'undefined') {
                let errors = [];
                for (let errs in data.errors) {
                    data.errors[errs].forEach(error => {
                        errors.push(error);
                    });
                }
                err = errors.join('<br>');
            }
            else err = data.message;
            return err;
        },

        swalError(errors) {

            errors = Array.isArray(errors) ? errors : [errors];

            let ul = '<ul>';
            errors.forEach(e => {
                ul += '<li>' + e + '</li>';
            });
            ul += '</ul>';

            Swal.fire({
                title: 'Error!',
                html: ul,
                icon: 'error',
            });
        },

        getFullUrl(route) {
            return new URL(route, window.location.href).href;
        },

        getUserProfileRoute(user_id, query = null) {
            let r = { name: 'profile', params: { userId: window.user_id == user_id ? '' : user_id } };
            if (query) r.query = query;
            return r;
        },

        checkPreviousPage() {
            return this.$router.options.history.state.back ? true : false;
        },

        storeReferralToken(referral_token) {
            if (referral_token && this.getReferralToken() != referral_token) {
                localStorage.setItem('cypher_referral_token', referral_token);

                // this.sendApiRequest('set_referred_id', {
                //     "referral_token": referral_token,
                //     "auth_id": window.user_id
                // });

            }
        },

        getReferralToken() {
            return localStorage.getItem('cypher_referral_token');
        },

        getLiquidationDays(current_time, date_posted) {
            current_time = this.moment(current_time);
            let posted_time_after_days = this.moment(date_posted).add(window.liquidation_days_limit, 'days');
            let liquidation_days = Math.ceil((posted_time_after_days.valueOf() - current_time.valueOf()) / (1000 * 3600 * 24));
            return liquidation_days < 0 ? 0 : liquidation_days;
        }
    }
}