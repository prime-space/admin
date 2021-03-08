<template>
    <v-form class="form" v-if="payment.statusId===1" ref="form"
            @submit.prevent="submitForm">
        <v-layout align-start row>
            <v-flex xs6 class="ml-3">
            <v-select :error-messages="form.errors.paymentShotId" name="paymentShotId"
                      :items="paymentShots" v-model="form.data.paymentShotId">

            </v-select>
            </v-flex>
            <v-tooltip top>
                <template v-slot:activator="{ on }">
                    <v-btn color="primary" v-on="on" @click="submitForm" :disabled="form.submitting" class="form__button">
                        Execute
                    </v-btn>
                </template>
                <span>Set payment status as "Paid". After success message, please, wait few seconds and refresh page</span>
            </v-tooltip>
        </v-layout>
    </v-form>
</template>

<script>
    export default {
        name: "PaymentExecuteForm",
        props: ['payment'],
        data() {
            return {
                form: {data: {paymentShotId: null}, errors: {}, submitting: false},
                paymentShots: [],
            }
        },
        mounted: function () {
            this.showForm();
        },
        methods: {
            showForm() {
                let serviceId = this.$route.params.serviceId;
                let paymentId = this.$route.params.paymentId;
                let url = `/ability/${serviceId}/payment/paymentShots/${paymentId}`;
                request(this.$http, 'get', url, [], function (response) {
                    this.paymentShots = response.body;
                }.bind(this));
            },

            submitForm() {
                this.form.submitting = true;
                let serviceId = this.$route.params.serviceId;
                let paymentId = this.$route.params.paymentId;
                let url = `/ability/${serviceId}/payment/execute/${paymentId}`;
                request(this.$http, 'post', url, this.form, function () {
                    this.$emit('showPayment');
                    this.$emit('snackbarSuccess');
                }.bind(this));
            },
        }
    }
</script>

<style scoped>
.form__button {
    margin-top: 14px !important;
}
</style>
