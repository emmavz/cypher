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

            return this.$http.post(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: (data)
            }).then(response => response.data)
                .catch(() => {
                    this.isError = true;
                    return Promise.reject('API ERROR');
                })
                .finally(() => {
                    toggleLoader(false);
                });

        }
    }
}