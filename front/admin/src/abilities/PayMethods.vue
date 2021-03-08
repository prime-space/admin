<template>
    <div>
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
                    <v-container>
                        <v-data-table :headers="paymentMethodsTableHeaders" :items="paymentMethods" hide-default-footer
                                      class="elevation-1" :loading="loading" :items-per-page="1000">
                            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                            <template slot="item" slot-scope="props">
                                <tr :class="{'row-text__disabled': !props.item.isEnabled}">
                                    <td>{{ props.item.id }}</td>
                                    <td>{{ props.item.name }}</td>
                                    <td>{{ props.item.paymentSystem }}</td>
                                    <td>{{ props.item.currency }}</td>
                                    <td>{{ props.item.fee }}</td>
                                    <td class="justify-center layout px-0">
                                        <v-tooltip bottom>
                                            <v-btn icon class="mx-0" @click="openPaymentMethodForm(props.item.id)"
                                                   slot="activator">
                                                <v-icon color="teal">mdi-pencil</v-icon>
                                            </v-btn>
                                            Edit
                                        </v-tooltip>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>
                    </v-container>
                    <v-dialog v-model="paymentMethodSettingsForm.dialog" persistent max-width="500px">
                        <v-form ref="paymentMethodSettingsForm" @submit.prevent="submitPaymentMethodSettingsForm">
                            <v-card>
                                <v-card-title class="headline">
                                    Edit
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                            <v-flex xs12>
                                                <v-text-field :error-messages="paymentMethodSettingsForm.errors.fee"
                                                              name="fee"
                                                              v-model="paymentMethodSettingsForm.data.fee" label="Fee"
                                                              type="number"
                                                              step="0.01"
                                                              min="0"></v-text-field>
                                                <v-checkbox :error-messages="paymentMethodSettingsForm.errors.isEnabled"
                                                            name="isEnabled"
                                                            v-model="paymentMethodSettingsForm.data.isEnabled"
                                                            label="Enabled"></v-checkbox>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" text
                                           @click.native="paymentMethodSettingsForm.dialog = false">
                                        Close
                                    </v-btn>
                                    <v-btn type="submit" name="save" color="blue darken-1" text
                                           :disabled="paymentMethodSettingsForm.submitting">Save
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                    </v-dialog>
                </v-tab-item>
                <v-tab-item>
                    <v-container>
                        <v-data-table :headers="payoutMethodsTableHeaders" :items="payoutMethods" hide-default-footer
                                      class="elevation-1" :loading="loading" :items-per-page="1000">
                            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                            <template slot="item" slot-scope="props">
                                <tr :class="{'row-text__disabled': !props.item.isEnabled}">
                                    <td>{{ props.item.id }}</td>
                                    <td>{{ props.item.name }}</td>
                                    <td>{{ props.item.paymentSystem }}</td>
                                    <td>{{ props.item.currency }}</td>
                                    <td>{{ props.item.fee }}</td>
                                    <td>{{ props.item.code }}</td>
                                    <td class="justify-center layout px-0">
                                        <v-tooltip bottom>
                                            <v-btn icon class="mx-0" @click="openPayoutMethodForm(props.item.id)"
                                                   slot="activator">
                                                <v-icon color="teal">mdi-pencil</v-icon>
                                            </v-btn>
                                            Edit
                                        </v-tooltip>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>
                    </v-container>
                    <v-dialog v-model="payoutMethodSettingsForm.dialog" persistent max-width="500px">
                        <v-form ref="payoutMethodSettingsForm" @submit.prevent="submitPayoutMethodSettingsForm">
                            <v-card>
                                <v-card-title class="headline">
                                    Edit
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                            <v-flex xs12>
                                                <v-text-field :error-messages="payoutMethodSettingsForm.errors.fee"
                                                              name="fee"
                                                              v-model="payoutMethodSettingsForm.data.fee" label="Fee"
                                                              type="number"
                                                              step="0.01"
                                                              min="0"></v-text-field>
                                                <v-checkbox :error-messages="payoutMethodSettingsForm.errors.isEnabled"
                                                            name="isEnabled"
                                                            v-model="payoutMethodSettingsForm.data.isEnabled"
                                                            label="Enabled"></v-checkbox>
                                                <v-checkbox :error-messages="payoutMethodSettingsForm.errors.isDefaultExcluded"
                                                            name="isDefaultExcluded"
                                                            v-model="payoutMethodSettingsForm.data.isDefaultExcluded"
                                                            label="isDefaultExcluded"></v-checkbox>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" text
                                           @click.native="payoutMethodSettingsForm.dialog = false">
                                        Close
                                    </v-btn>
                                    <v-btn type="submit" name="save" color="blue darken-1" text
                                           :disabled="payoutMethodSettingsForm.submitting">Save
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                    </v-dialog>
                </v-tab-item>
            </v-tabs-items>
        </v-tabs>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                paymentMethodsTableHeaders: [],
                payoutMethodsTableHeaders: [],
                paymentMethods: [],
                payoutMethods: [],
                tab: null,
                tabs: ['Payment', 'Payout'],
                payment: [],
                paymentMethodSettingsForm: {data: {isEnabled: false}, errors: {}, submitting: false, dialog: false},
                payoutMethodSettingsForm: {data: {isEnabled: false, isDefaultExcluded:false}, errors: {}, submitting: false, dialog: false},
                notifications: [],
                loading: false,
            }
        },
        mounted: function () {
            this.$store.commit('changeTitle', `PrimeAdmin - Pay Methods`); //@todo fix title
            this.paymentMethodsTableHeaders = [
                {text: '#', value: 'id'},
                {text: 'Name', value: 'name'},
                {text: 'Payment system', value: 'paymentSystem'},
                {text: 'Currency', value: 'currency'},
                {text: 'Fee', value: 'fee'},
                {},
            ];
            this.payoutMethodsTableHeaders = [
                {text: '#', value: 'id'},
                {text: 'Name', value: 'name'},
                {text: 'Payment system', value: 'payoutMethod'},
                {text: 'Currency', value: 'currency'},
                {text: 'Fee', value: 'fee'},
                {text: 'Code', value: 'code'},
                {},
            ];
            this.showPayoutMethods();
            this.showPaymentMethods();
        },
        methods: {
            openPayoutMethodForm(payoutMethodId) {
                this.payoutMethodSettingsForm.data.payoutMethodId = payoutMethodId;
                let payoutMethod = this.payoutMethods.find(
                    payoutMethod => payoutMethod.id === payoutMethodId
                );
                this.payoutMethodSettingsForm.data.fee = payoutMethod.fee;
                this.payoutMethodSettingsForm.data.isEnabled = payoutMethod.isEnabled;
                this.payoutMethodSettingsForm.data.isDefaultExcluded = payoutMethod.isDefaultExcluded;
                this.payoutMethodSettingsForm.submitting = false;
                this.payoutMethodSettingsForm.dialog = true;
            },
            showPayoutMethods() {
                this.loading = true;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/payMethods/payoutMethods`;
                request(this.$http, 'get', url, [], function (response) {
                    this.loading = false;
                    this.payoutMethods = sortByIsEnabled(response.body);
                }.bind(this));
            },
            submitPayoutMethodSettingsForm() {
                this.payoutMethodSettingsForm.submitting = true;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/payMethods/payoutMethodSettings`;
                request(this.$http, 'post', url, this.payoutMethodSettingsForm, function () {
                    this.payoutMethodSettingsForm.dialog = false;
                    this.showPayoutMethods();
                }.bind(this));
            },
            openPaymentMethodForm(paymentMethodId) {
                this.paymentMethodSettingsForm.data.paymentMethodId = paymentMethodId;
                this.paymentMethodSettingsForm.data.fee = this.paymentMethods.find(
                    paymentMethod => paymentMethod.id === paymentMethodId
                ).fee;
                this.paymentMethodSettingsForm.data.isEnabled = this.paymentMethods.find(
                    paymentMethod => paymentMethod.id === paymentMethodId
                ).isEnabled;
                this.paymentMethodSettingsForm.submitting = false;
                this.paymentMethodSettingsForm.dialog = true;
            },
            showPaymentMethods() {
                this.loading = true;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/payMethods/paymentMethods`;
                request(this.$http, 'get', url, [], function (response) {
                    this.loading = false;
                    this.paymentMethods = sortByIsEnabled(response.body);
                }.bind(this));
            },
            submitPaymentMethodSettingsForm() {
                this.paymentMethodSettingsForm.submitting = true;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/payMethods/paymentMethodSettings`;
                request(this.$http, 'post', url, this.paymentMethodSettingsForm, function () {
                    this.paymentMethodSettingsForm.dialog = false;
                    this.showPaymentMethods();
                }.bind(this));
            }
        }
    }
</script>

<style>
</style>
