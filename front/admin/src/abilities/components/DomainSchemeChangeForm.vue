<template>
    <v-form ref="form" @submit.prevent="submitForm">
        <v-card>
            <v-card-text>
                <v-container>
                    <v-layout row>
                        <v-flex md1 offset-xs1>
                            <v-select
                                    :error-messages="form.errors.scheme"
                                    name="schema"
                                    v-model="form.data.scheme"
                                    label="Schema"
                                    :items="['http', 'https']"
                            ></v-select>
                        </v-flex>
                        <v-flex md6 offset-xs1>
                            <v-text-field :error-messages="form.errors.domain" name="domain"
                                          v-model="form.data.domain"
                                          label="Domain">
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-flex xs2>
                    <v-btn color="primary" type="submit" name="save"
                           :disabled="form.submitting"
                    >Save
                    </v-btn>
                </v-flex>
            </v-card-actions>
        </v-card>
    </v-form>
</template>

<script>
    export default {
        name: "DomainSchemeChangeForm",
        props: ['shop'],
        data() {
            return {
                form: {data: {scheme: '', domain: ''}, errors: {}, submitting: false}
            }
        },
        mounted: function () {
            this.showForm();
        },
        methods: {
            showForm() {
                this.form.data.scheme = this.shop[0].scheme;
                this.form.data.domain = this.shop[0].domain;
            },

            submitForm() {
                this.form.submitting = true;
                let shopId = this.$route.params.shopId;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/shop/domainSchemeChange/${shopId}`;
                request(this.$http, 'post', url, this.form, function () {
                    this.$emit('showShop');
                    this.$emit('snackbarSuccess');
                }.bind(this));
            },
        }
    }
</script>

<style scoped>

</style>
