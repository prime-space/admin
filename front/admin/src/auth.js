import VueResource from 'vue-resource'

Vue.use(Vuetify)
Vue.use(VueResource)

new Vue({
    el: '#app',
    data: () => ({
        drawer: null
    }),
    props: {
        source: String,
        info: []
    },
    methods: {
        submit (submitEvent) {
            this.submitted = true;
            this.info = [];
            let data = {};
            for(let element of submitEvent.target.elements) {
                data[element.name] = element.value;
            }
            this.$http.post('/signIn', data, {emulateJSON: true}).then((response) => {
                this.submitted = false;
                window.location.href = '/' + window.location.hash;
            }).catch((errors) => {
                this.submitted = false;
                if (errors.body.errors !== undefined) {
                    this.info = errors.body.errors;
                }
            })
        }
    }
})
