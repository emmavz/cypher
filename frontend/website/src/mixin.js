export default {
    data() {
        return {
            isError: false,
            currency: currency,
            profileTabs: ['Investments', 'Articles'],
            articleTabs: ['Article', 'Statistics'],
            searchTabs: ['Articles', 'Authors'],
        }
    },
    created() {
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

        async sendApiRequest(url, data) {

            return this.afterApiCall(this.$http.post(url, data));

        },

        async sendAllMultiApiRequests(array) {

            return this.afterApiCall(Promise.all(this.prepareMultiApiRequest(array)));

        },

        beforeApiCall() {
            toggleLoader();
        },

        afterApiCall(api) {

            this.beforeApiCall();

            return api.then((apiResponses) => {

                apiResponses = Array.isArray(apiResponses) ? apiResponses : [apiResponses];

                let responses = [],
                    errors = [];
                apiResponses.forEach(response => {
                    let data = response.data;

                    if (Array.isArray(data)) {
                        responses.push(data);
                    }
                    else {
                        errors.push(data.error);
                    }
                });

                if (errors.length) {
                    console.log(errors.join('\n'));
                    return Promise.reject(errors.join('\n'));
                }
                else {
                    return responses.length == 1 ? responses[0] : responses;
                }

            }).catch((error) => {
                this.isError = true;
                return Promise.reject(error);
            }).finally(() => {
                toggleLoader(false);
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
        }
    }
}