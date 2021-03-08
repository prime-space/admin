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
                        <v-data-iterator
                                :items="userProfile"
                                hide-default-footer
                                row
                                wrap
                        >
                            <v-flex
                                    slot="item"
                                    slot-scope="props"
                                    xs12
                                    sm6
                                    md4
                                    lg6
                            >
                                <v-card>
                                    <v-card-title class="card-title"><h4>User</h4></v-card-title>
                                    <v-divider></v-divider>
                                    <v-list dense>
                                        <v-list-item>
                                            <v-list-item-content class="text_selectable">#</v-list-item-content>
                                            <v-list-item-content class="align-end text_selectable">{{ props.item.id }}
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content class="text_selectable">Email</v-list-item-content>
                                            <v-list-item-content class="align-end text_selectable">{{ props.item.email }}
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content class="text_selectable">ApiIps</v-list-item-content>
                                            <div class="text_selectable">
                                                <template v-for="(ip, index) in props.item.apiIps">{{ ip }}<template v-if="index !== (props.item.apiIps.length - 1)">&nbsp;</template>
                                                </template>
                                            </div>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content class="text_selectable">IsApiEnabled
                                            </v-list-item-content>
                                            <v-list-item-content class="align-end text_selectable">
                                                {{props.item.isApiEnabled }}
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content class="text_selectable">Status
                                            </v-list-item-content>
                                            <v-list-item-content class="align-end text_selectable"
                                            :class="{'red--text': props.item.isBlocked}">
                                                {{ props.item.isBlocked ? 'blocked' : 'active' }}
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content class="text_selectable">Timezone</v-list-item-content>
                                            <v-list-item-content class="align-end text_selectable">
                                                {{ props.item.timezone }}
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-list>
                                </v-card>
                            </v-flex>
                        </v-data-iterator>
                    </v-container>
                    <v-container>
                        <v-card>
                            <v-card-title><h4>User Accounts</h4></v-card-title>
                            <v-data-table :headers="userAccountsTableHeaders" :items="userAccounts" hide-default-footer
                                          class="elevation-1" :items-per-page="1000"
                                          :loading="loading">
                                <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                                <template slot="item" slot-scope="props">
                                    <tr @click="$router.push(`/service/${$route.params.serviceId}/account/${props.item.id}`)"
                                        :style="{ cursor: 'pointer'}">
                                        <td>{{ props.item.id }}</td>
                                        <td>{{ props.item.balance }}</td>
                                        <td>{{ props.item.currency }}</td>
                                    </tr>
                                </template>
                            </v-data-table>
                        </v-card>
                    </v-container>
                    <v-container>
                        <v-card>
                            <v-card-title><h4>User Shops</h4></v-card-title>
                            <v-data-table :headers="userShopsTableHeaders" :items="userShops" hide-default-footer
                                          class="elevation-1" :items-per-page="1000"
                                          :loading="loading">
                                <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                                <template slot="item" slot-scope="props">
                                    <tr @click="$router.push(`/service/${$route.params.serviceId}/shop/${props.item.id}`)"
                                        :style="{ cursor: 'pointer'}">
                                        <td>{{ props.item.id }}</td>
                                        <td>{{ props.item.name }}</td>
                                        <td>{{ props.item.url }}</td>
                                        <td>{{ props.item.description }}</td>
                                        <td>{{ props.item.createdTs }}</td>
                                    </tr>
                                </template>
                            </v-data-table>
                        </v-card>
                    </v-container>
                </v-tab-item>
                <v-tab-item>
                    <v-form ref="userStatusForm" @submit.prevent="submitUserStatusForm">
                        <v-card>
                            <v-card-text>
                                <v-container grid-list-md>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <div v-if="userStatusForm.errors.form">
                                                {{ userStatusForm.errors.form }}
                                            </div>
                                            <v-radio-group v-model="userStatusForm.data.isBlocked">
                                                <v-radio label="Blocked" :value="true"></v-radio>
                                                <v-radio label="Active" :value="false"></v-radio>
                                            </v-radio-group>
                                        </v-flex>
                                    </v-layout>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-flex xs2>
                                    <v-btn color="primary" type="submit" name="save"
                                           :disabled="userStatusForm.submitting"
                                    >Save
                                    </v-btn>
                                </v-flex>
                            </v-card-actions>
                        </v-card>
                    </v-form>
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
                                    <td :class="{'text__bold': props.item.hasPersonalFee}">{{ props.item.fee }}</td>
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
                </v-tab-item>
            </v-tabs-items>
        </v-tabs>
        <v-dialog v-model="payoutMethodForm.dialog" persistent max-width="500px">
            <v-form ref="payoutMethodForm" @submit.prevent="submitPayoutMethodForm">
                <v-card>
                    <v-card-title class="headline">
                        Edit
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
                                <v-flex xs12>
                                    <v-text-field :error-messages="payoutMethodForm.errors.fee"
                                                  name="fee"
                                                  v-model="payoutMethodForm.data.fee" label="Fee"
                                                  type="number"
                                                  step="0.01"
                                                  min="0">
                                    </v-text-field>
                                    <v-checkbox :error-messages="payoutMethodForm.errors.isEnabled"
                                                name="isEnabled"
                                                v-model="payoutMethodForm.data.isEnabled"
                                                label="Enabled">
                                    </v-checkbox>
                                    <v-checkbox :error-messages="payoutMethodForm.errors.hasPersonalFee"
                                                name="hasPersonalFee"
                                                v-model="payoutMethodForm.data.hasPersonalFee"
                                                label="Enabled personal fee">
                                    </v-checkbox>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text
                               @click.native="payoutMethodForm.dialog = false">
                            Close
                        </v-btn>
                        <v-btn type="submit" name="save" color="blue darken-1" text
                               :disabled="payoutMethodForm.submitting">Save
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>
        <v-snackbar :timeout="6000" :top="'top'" v-model="snackbar">
            Successfully updated
            <v-btn text color="white" @click.native="snackbar = false">Close</v-btn>
        </v-snackbar>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                snackbar: false,
                userStatusForm: {data: {isBlocked: ''}, errors: {}, submitting: false},
                payoutMethodForm: {data: {hasPersonalFee: false}, errors: {}, submitting: false, dialog: false},
                tab: null,
                tabs: ['Info', 'Status', 'Payout Methods'],
                payoutMethods: [],
                userProfile: [],
                userAccounts: [],
                userShops: [],
                loading: false,
                userAccountsTableHeaders: [
                    {text: '#', value: 'id'},
                    {text: 'Balance', value: 'balance'},
                    {text: 'Currency', value: 'currency'},
                ],
                userShopsTableHeaders: [
                    {text: '#', value: 'id'},
                    {text: 'Name', value: 'name'},
                    {text: 'Url', value: 'url'},
                    {text: 'Description', value: 'description'},
                    {text: 'Created', value: 'createdTs'},
                ],
                payoutMethodsTableHeaders: [
                    {text: '#', value: 'id'},
                    {text: 'Name', value: 'name'},
                    {text: 'Fee', value: 'fee'},
                    {},
                ]
            }
        },
        mounted: function () {
            this.showUserData();
        },
        computed: {},
        methods: {
            showUserData() {
                this.loading = true;
                let serviceId = this.$route.params.serviceId;
                let userId = this.$route.params.userId;
                let url = `/ability/${serviceId}/user/page/${userId}`;
                request(this.$http, 'get', url, [], function (response) {
                    this.loading = false;
                    this.userProfile = [response.body.user];
                    this.userAccounts = response.body.accounts;
                    this.userShops = response.body.shops;
                    this.payoutMethods = sortByIsEnabled(response.body.payoutMethods);
                    this.userStatusForm.data.isBlocked = this.userProfile[0].isBlocked;
                    this.$store.commit('changeTitle', `User - #${userId}: ${this.userProfile[0].email}`); //@todo fix title
                }.bind(this));
            },
            openPayoutMethodForm(payoutMethodId) {
                this.payoutMethodForm.data.payoutMethodId = payoutMethodId;
                let payoutMethod = this.payoutMethods.find(
                    payoutMethod => payoutMethod.id === payoutMethodId
                );
                this.payoutMethodForm.data.fee = payoutMethod.fee;
                this.payoutMethodForm.data.hasPersonalFee = payoutMethod.hasPersonalFee;
                this.payoutMethodForm.data.isEnabled = payoutMethod.isEnabled;
                this.payoutMethodForm.submitting = false;
                this.payoutMethodForm.dialog = true;
            },
            submitPayoutMethodForm() {
                this.payoutMethodForm.submitting = true;
                let userId = this.$route.params.userId;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/user/personalPayoutMethodSettings/${userId}`;
                request(this.$http, 'post', url, this.payoutMethodForm, function () {
                    this.showUserData();
                    this.payoutMethodForm.dialog = false;
                }.bind(this));
            },
            submitUserStatusForm() {
                this.userStatusForm.submitting = true;
                let userId = this.$route.params.userId;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/user/status/${userId}`;
                request(this.$http, 'post', url, this.userStatusForm, function () {
                    this.showUserData();
                    this.snackbar = true;
                }.bind(this));
            }
        }
    }
</script>

<style>
    .text_selectable {
        user-select: text;
    }

    .card-title {
        line-height: normal;
    }
</style>
