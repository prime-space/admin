<template>
    <div>
        <confirm-dialog/>
        <v-tabs v-model="tab" fixed-tabs>
            <v-tab
                    v-for="(tab, key) in tabs"
                    :key="key"
                    ripple
            >
                {{ tab }}

            </v-tab>
            <v-tabs-items v-model="tab" touchless>
                <v-tab-item>
                    <v-container v-if="payment!==null">
                        <v-card>
                            <PaymentExecuteForm :payment="payment"
                                                v-if="paymentNotExecutedAccessGranted"
                                                @snackbarSuccess="snackbar=true"
                                                @showPayment="showPayment"/>
                            <template v-else>
                                <v-alert v-if="payment.email === ''" :value="true" type="warning">
                                    Refund not possible: Missing email
                                </v-alert>
                                <v-form v-else-if="refundNotExecutedAccessGrantedPaymentExecuted"
                                        ref="refundForm"
                                        @submit.prevent="submitRefundForm">
                                    <v-layout align-start row>
                                        <v-tooltip top>
                                            <template v-slot:activator="{ on }">
                                                <v-btn v-on="on" color="primary" @click="submitRefundForm"
                                                       :disabled="refundForm.submitting"
                                                       :error-messages="refundForm.errors.refundButton"
                                                       name="refundButtonErrors">
                                                    Refund
                                                </v-btn>
                                            </template>
                                            <span>Provide voucher to client for refund money</span>
                                        </v-tooltip>
                                    </v-layout>
                                </v-form>
                                <form target="_blank" :action="payment.redirectFormData.action" :method="payment.redirectFormData.method">
                                    <input
                                           v-for="(fieldValue, fieldName) in payment.redirectFormData.fields"
                                           :key=fieldName
                                           :name="fieldName"
                                           :value="fieldValue"
                                           type="hidden">
                                    <v-tooltip top>
                                        <template v-slot:activator="{ on }">
                                            <v-btn tag="button" type="submit">Success Redirect</v-btn>
                                        </template>
                                        <span>Go to success url redirection</span>
                                    </v-tooltip>
                                </form>
                                <v-alert v-if="refundForm.errors.form" :value="true" type="warning">
                                    {{ refundForm.errors.form }}
                                </v-alert>
                            </template>
                        </v-card>

                        <v-card>
                            <v-card-title class="card-title"><h4>Payment</h4></v-card-title>
                            <v-divider></v-divider>
                            <v-list dense>
                                <v-list-item v-for="row in dataIteratorRows"
                                             :key=row.id
                                             v-on="row.isClickable ? rowClickHandler(row.id, payment) : {}"
                                             :class="row.isClickable ? 'list__tile_clickable' : ''"
                                >
                                    <v-list-item-content class="text_selectable">
                                        {{ row.title}}
                                    </v-list-item-content>
                                    <v-list-item-content class="align-end text_selectable">
                                        {{ payment[row.property] }}
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-card>
                    </v-container>
                </v-tab-item>
                <v-tab-item>
                    <v-container>
                        <v-data-table :headers="headers" :items="notifications" hide-default-footer class="elevation-1"
                                      :loading="loading" :items-per-page="1000">
                            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                            <template slot="item" slot-scope="props">
                                <tr>
                                    <td>{{ props.item.id }}</td>
                                    <td>{{ props.item.paymentId }}</td>
                                    <td>{{ props.item.status }}</td>
                                    <td>{{ props.item.result }}</td>
                                    <td>{{ props.item.httpCode }}</td>
                                    <td>{{ props.item.created }}</td>
                                </tr>
                            </template>
                        </v-data-table>
                    </v-container>
                </v-tab-item>
                <v-tab-item>
                    <v-container>
                        <v-card>
                            <v-form ref="emailChangeForm" @submit.prevent="submitEmailChangeForm">
                                <v-flex xs10 offset-xs1>
                                    <v-text-field  :error-messages="emailChangeForm.errors.email"
                                                   name="email"
                                                   v-model="emailChangeForm.data.email" label="Email">
                                    </v-text-field>
                                </v-flex>
                                <v-flex xs10 offset-xs1>
                                    <v-btn color="primary" type="submit">Change</v-btn>
                                </v-flex>
                            </v-form>
                        </v-card>
                    </v-container>
                </v-tab-item>
            </v-tabs-items>
        </v-tabs>
        <v-snackbar :timeout="6000" :top="'top'" v-model="snackbar">
            Successfully updated
            <v-btn text color="white" @click.native="snackbar = false">Close</v-btn>
        </v-snackbar>
    </div>
</template>

<script>
    import PaymentExecuteForm from './../components/PaymentExecuteForm.vue';
    export default {
        components: {
            PaymentExecuteForm
        },
        data() {
            return {
                snackbar: false,
                emailChangeForm: {data: {email: null}, errors: {}, submitting: false},
                refundForm: {data: {}, errors: {}, submitting: false},
                headers: [
                    {text: '#', value: 'id'},
                    {text: 'Payment Id', value: 'paymentId'},
                    {text: 'Status', value: 'status'},
                    {text: 'Result', value: 'result'},
                    {text: 'HTTP code', value: 'httpCode'},
                    {text: 'Created', value: 'created'},
                ],
                tab: null,
                tabs: ['Info', 'Notifications', 'Email'],
                payment: null,
                notifications: [],
                loading: false,
                dataIteratorRows: [
                    {id: 1, title: '#', property: 'id', isClickable: false},
                    {id: 2, title: 'User payment ID', property: 'userPaymentId', isClickable: false},
                    {id: 3, title: 'Shop', property: 'shop', isClickable: true},
                    {id: 4, title: 'User', property: 'userId', isClickable: true},
                    {id: 5, title: 'Amount', property: 'amount', isClickable: false},
                    {id: 6, title: 'Currency', property: 'currency', isClickable: false},
                    {id: 7, title: 'Status', property: 'status', isClickable: false},
                    {id: 8, title: 'Payment method', property: 'paymentMethod', isClickable: false},
                    {id: 9, title: 'Description', property: 'description', isClickable: false},
                    {id: 10, title: 'Date', property: 'date', isClickable: false},
                    {id: 11, title: 'Email', property: 'email', isClickable: false},
                    {id: 12, title: 'Refund', property: 'refundStatusName', isClickable: false},
                ],
            }
        },
        mounted: function () {
            this.showPayment();
        },
        methods: {
            showPayment() {
                this.loading = true;
                let serviceId = this.$route.params.serviceId;
                let paymentId = this.$route.params.paymentId;
                let url = `/ability/${serviceId}/payment/page/${paymentId}`;
                request(this.$http, 'get', url, [], function (response) {
                    this.loading = false;
                    this.payment = response.body.payment;
                    this.notifications = response.body.notifications;
                    this.$store.commit('changeTitle', `PrimeAdmin - Payment - #${paymentId}`); //@todo fix title
                    this.emailChangeForm.data.email = this.payment.email;
                }.bind(this));
            },
            rowClickHandler(rowId, payment) {
                if (rowId === 3) {
                    return {click: () => this.openEntityPage('user', payment.userId)};
                } else if (rowId === 2) {
                    return {click: () => this.openEntityPage('shop', payment.shopId)};
                } else {
                    return {};
                }
            },
            openEntityPage(entityName, entityId) {
                let serviceId = this.$route.params.serviceId;
                this.$router.push(`/service/${serviceId}/${entityName}/${entityId}`);
            },
            submitRefundForm() {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Confirm',
                    body: 'Refund',
                })
                    .then(confirmation => {
                        if (confirmation) {
                            this.loading = true;
                            this.refundForm.submitting = true;
                            let serviceId = this.$route.params.serviceId;
                            let paymentId = this.$route.params.paymentId;
                            let url = `/ability/${serviceId}/payment/refund/${paymentId}`;
                            request(this.$http, 'post', url, this.refundForm, function () {
                                this.showPayment();
                            }.bind(this));
                        }
                    })
            },
            submitEmailChangeForm() {
                this.emailChangeForm.submitting = true;
                this.emailChangeForm.submitting = true;
                let serviceId = this.$route.params.serviceId;
                let paymentId = this.$route.params.paymentId;
                let url = `/ability/${serviceId}/payment/changeEmail/${paymentId}`;
                request(this.$http, 'post', url, this.emailChangeForm, function () {
                    this.showPayment();
                    this.snackbar = true;
                }.bind(this));
            },
        },
        computed: {
            paymentNotExecutedAccessGranted() {
                return this.payment.statusId === 1 && !isAccessDenied(ACCESS_RULE_ID_EXECUTE_PAYMENT);
            },
            refundNotExecutedAccessGrantedPaymentExecuted() {
                return this.payment.refundStatusId === 1 &&
                    this.payment.statusId === 3 &&
                    !isAccessDenied(ACCESS_RULE_ID_REFUND);
            },
        }
    }
</script>

<style>
    .list__tile_clickable {
        background: rgba(0, 0, 0, .12)
    }
</style>
