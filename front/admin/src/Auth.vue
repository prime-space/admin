<template>
    <div>
        <form name="form" method="post" action="/signIn" @submit.prevent="submit">
            <div>
                <v-form @submit.prevent="submit">
                <div>
                    <label for="form_login" class="required">Login</label>
                    <input type="text" id="form_login" name="form[login]" >
                </div>
                <div>
                    <label for="form_password" class="required">Password</label>
                    <input type="password" id="form_password" name="form[password]">
                </div>
                <div><span v-for="item in info">{{item}}<br></span></div>
                <div>
                    <button type="submit" id="form_save" name="form[save]" :disabled="submitted">Sign in</button>
                </div>
                </v-form>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        data () {
            return {
                submitted: false,
                info: []
            }
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
                    this.$router.push('/');
                }).catch((errors) => {
                    this.submitted = false;
                    if (errors.body.errors !== undefined) {
                        this.info = errors.body.errors;
                    }
                })
            }
        }
    }
</script>
