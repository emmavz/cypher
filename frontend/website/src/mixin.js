export default {
    data() {
        return {
            isError: false,
            currency: currency
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

        async sendApiRequest(url, data) {

            toggleLoader();

            return this.$http.post(url, data, {
                headers: {
                    'Content-Type': 'application/json'
                },
            }).then(response => {
                let data = response.data;
                if (Array.isArray(data)) {
                    return data;
                }
                else {
                    console.log(data.error);
                    return Promise.reject(data.error);
                }
            }).catch((error) => {
                this.isError = true;
                return Promise.reject(error);
            }).finally(() => {
                toggleLoader(false);
            });

        }
    }
}