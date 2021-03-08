<template>
    <div>
        <v-card>
            <v-card-title>
                <v-container grid-list-xl>
                    <v-layout row>
                        <v-flex xs12>
                            <v-text-field
                                    v-model="searchData.name"
                                    append-icon="mdi-search"
                                    label="Name"
                                    single-line
                                    hide-details
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row>
                        <v-flex xs6>
                            <v-autocomplete
                                    name="service"
                                    v-model="searchData.service"
                                    label="Service"
                                    :items="compileServicesFilterData"
                            ></v-autocomplete>
                        </v-flex>
                        <v-flex xs6>
                            <v-autocomplete
                                    name="system"
                                    v-model="searchData.system"
                                    label="Payment system"
                                    :items="systemsSelect"
                            ></v-autocomplete>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-title>
            <v-data-table :headers="headers" :items="accounts" hide-default-footer class="elevation-1"
                          :search="search"
                          :items-per-page="1000"
                          :custom-filter="customFilter">
                <template slot="item" slot-scope="props">
                    <tr>
                        <td>{{ props.item.id }}</td>
                        <td>{{ props.item.name }}</td>
                        <td>{{ props.item.system }}</td>
                        <td>{{ props.item.service }}</td>
                        <td>{{ props.item.account }}</td>
                        <td>{{ props.item.weight }}</td>
                        <td>{{ props.item.using }}</td>
                        <td>{{ props.item.turnover }}</td>
                        <td>{{ props.item.balance }}</td>
                        <td>
                            <v-icon v-if="isEnabled(props.item.enabled, 'withdraw') && isEnabled(props.item.enabled, 'merchant')">
                                mdi-arrow-up-down
                            </v-icon>
                            <v-icon v-else-if="isEnabled(props.item.enabled, 'withdraw')">mdi-arrow-up</v-icon>
                            <v-icon v-else-if="isEnabled(props.item.enabled, 'merchant')">mdi-arrow-down
                            </v-icon>
                            <v-icon v-if="props.item.isWhite">mdi-piggy-bank</v-icon>
                            <v-icon v-if="props.item.isAssigned">mdi-link-variant</v-icon>
                            <v-icon v-if="!props.item.isActive">mdi-lock</v-icon>
                        </td>
                        <td class="justify-center layout px-0">
                            <v-btn icon class="mx-0" @click="openAccountForm(props.item.id)">
                                <v-icon color="teal">mdi-pencil</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </template>
            </v-data-table>
        </v-card>
        <v-btn color="primary" dark slot="activator" @click="openAccountForm(null)">Add</v-btn>
        <v-dialog v-model="dialog" persistent max-width="500px">
            <v-form ref="form" @submit.prevent="submit">
                <v-card>
                    <v-card-title>
                        <span class="headline">Add Payment Account</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
                                <v-flex xs12>
                                    <v-text-field :error-messages="accountForm.errors.name"
                                                  name="searchName" v-model="accountForm.data.name"
                                                  label="Name"></v-text-field>
                                </v-flex>
                                <v-flex xs12 sm6>
                                    <v-select
                                            :error-messages="accountForm.errors.serviceId"
                                            name="serviceId"
                                            v-model="accountForm.data.serviceId"
                                            label="Service"
                                            :items="compileServicesSelectData"
                                    ></v-select>
                                </v-flex>
                                <v-flex xs12 sm6>
                                    <v-autocomplete
                                            :error-messages="accountForm.errors.enabled"
                                            name="enabled"
                                            v-model="accountForm.data.enabled"
                                            label="Enabled"
                                            multiple
                                            chips
                                            :items="[{'text':'Payment', 'value':'merchant'}, {'text':'Payout', 'value':'withdraw'}]"
                                    ></v-autocomplete>
                                </v-flex>
                                <v-flex xs12>
                                    <v-slider
                                            :error-messages="accountForm.errors.weight"
                                            name="weight"
                                            color="orange"
                                            label="Weight"
                                            min="1"
                                            max="10"
                                            thumb-label
                                            v-model="accountForm.data.weight"
                                    ></v-slider>
                                </v-flex>
                                <v-flex xs12>
                                    <v-text-field :error-messages="accountForm.errors.assignedIds"
                                                  name="assignedIds" v-model="accountForm.data.assignedIds"
                                                  label="Assigned IDs"></v-text-field>
                                </v-flex>
                                <v-flex xs12>
                                    <v-select
                                            :error-messages="accountForm.errors.paymentSystemId"
                                            name="paymentSystemId"
                                            v-model="accountForm.data.paymentSystemId"
                                            @change="setCurrentPaymentSystemConfig"
                                            label="Payment System"
                                            :items="compilePaymentSystemsSelectData"
                                    ></v-select>
                                </v-flex>
                                <v-flex xs12 v-for="(fieldName, index) in currentPaymentSystemConfig" :key="index">
                                    <v-text-field
                                            :error-messages="accountForm.errors[fieldName]"
                                            name="'config_'+fieldName"
                                            v-model="accountForm.data[fieldName]"
                                            :label="fieldName"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs12>
                                    <v-checkbox :error-messages="accountForm.errors.isWhite"
                                                name="isWhite" v-model="accountForm.data.isWhite"
                                                label="White"></v-checkbox>
                                    <v-checkbox :error-messages="accountForm.errors.isActive"
                                                name="isActive" v-model="accountForm.data.isActive"
                                                label="Active"></v-checkbox>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                    <div><span>{{accountForm.errors.form}}<br></span></div>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click.native="dialog = false">Close</v-btn>
                        <v-btn type="submit" name="save" color="blue darken-1" text
                               :disabled="accountForm.submitting">Save
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>
    </div>
</template>

<script>
    export default {
        data() {
            const defaultForm = Object.freeze({
                weight: null,
            });

            return {
                accountForm: {data: {}, errors: {}, submitting: false},
                search: '',
                searchData: {
                    name: this.$route.query.name || '',
                    service: parseInt(this.$route.query.service) || 'all',
                    system: parseInt(this.$route.query.system) || 'all'
                },
                systemsSelect: [{text: 'All', value: 'all'}],
                accounts: [],
                config: config,
                currentPaymentSystemConfig: [],
                dialog: false,
                form: Object.assign({}, defaultForm),
                headers: [
                    {text: 'ID', value: 'id'},
                    {text: 'Name', value: 'name'},
                    {text: 'System', value: 'system'},
                    {text: 'Service', value: 'service'},
                    {text: 'Account', value: 'account'},
                    {text: 'Weight', value: 'weight'},
                    {text: 'Using', value: 'using'},
                    {text: 'Turnover', value: 'turnover'},
                    {text: 'Balance', value: 'balance'},
                    {},
                    {}
                ],
                drawer: null,
                editId: null
            }
        },
        mounted: function () {
            this.showAccounts();
            this.$store.commit('changeTitle', 'PrimeAdmin');
        },
        methods: {
            openAccountForm(editId) {
                this.accountForm.data = {'weight': 1, 'enabled': [], isWhite: false, isActive: true};
                this.currentPaymentSystemConfig = [];
                this.editId = editId;
                if (editId !== null) {
                    let account = this.getItemByProperty('id', editId);
                    this.accountForm.data.name = account.name;
                    this.accountForm.data.weight = account.weight;
                    this.accountForm.data.serviceId = account.serviceId;
                    this.accountForm.data.enabled = account.enabled;
                    this.accountForm.data.assignedIds = account.assignedIds;
                    this.accountForm.data.isWhite = account.isWhite;
                    this.accountForm.data.isActive = account.isActive;
                    this.accountForm.data.paymentSystemId = account.paymentSystemId;
                    let accountConfig = account.config;
                    for (let parameter in accountConfig) {
                        this.accountForm.data[parameter] = accountConfig[parameter];
                    }
                    this.setCurrentPaymentSystemConfig(account.paymentSystemId);
                }
                this.dialog = true;
            },
            submit() {
                this.accountForm.submitting = true;
                let url = '/account';
                if (null !== this.editId) {
                    url = url + '/' + this.editId;
                }
                request(this.$http, 'post', url, this.accountForm, function () {
                    this.dialog = false;
                    this.showAccounts();
                }.bind(this));
            },
            showAccounts() {
                request(this.$http, 'get', '/accounts', [], function (response) {
                    this.accounts = response.body.accounts;
                    this.systemsSelect = this.systemsSelect.concat(response.body.paymentSystems);
                }.bind(this));
            },
            setCurrentPaymentSystemConfig(event) {
                this.currentPaymentSystemConfig = this.config.paymentSystems[event].config;
            },
            getItemByProperty(key, value) {
                for (let item of this.accounts) {
                    if (item[key] === value) {
                        return item;
                    }
                }

                return null;
            },
            isEnabled(array, value) {
                return array.indexOf(value) !== -1;
            },
            customFilter(items, search) {
                let isAllServices = search.service === 'all';
                let isAllSystems = search.system === 'all';
                let isEmptyName = search.name === '';
                return items.filter(function (item) {
                    return (isEmptyName || item.name.indexOf(search.name) !== -1) &&
                        (isAllServices || item.serviceId === search.service) &&
                        (isAllSystems || item.paymentSystemId === search.system);
                }.bind(this));
            },
            changeQueryParam(name, value) {
                let query = Object.assign({}, this.$route.query);
                if (value === '' || value === 'all') {
                    delete query[name];
                    this.$router.replace({query});
                } else {
                    this.$router.push({query: Object.assign({}, query, {[name]: value})});
                }
            }
        },
        computed: {
            compileServicesSelectData() {
                let items = [];
                for (let service in this.config.services) {
                    if ('merchant' === this.config.services[service].appType) {
                        let serviceObj = {};
                        serviceObj.text = this.config.services[service].domain;
                        serviceObj.value = this.config.services[service].id;
                        items.push(serviceObj);
                    }
                }

                return items;
            },
            compileServicesFilterData() {
                let items = [{text: 'All', value: 'all'}];
                items = items.concat(this.compileServicesSelectData);

                return items;
            },
            compilePaymentSystemsSelectData() {
                let items = [];
                for (let paymentSystem in this.config.paymentSystems) {
                    let paymentSystemObj = {};
                    paymentSystemObj.text = this.config.paymentSystems[paymentSystem].name;
                    paymentSystemObj.value = this.config.paymentSystems[paymentSystem].id;
                    items.push(paymentSystemObj);
                }

                return items;
            },
        },
        watch: {
            'searchData.name': function (newVal) {
                this.changeQueryParam('name', newVal);
            },
            'searchData.service': function (newVal) {
                this.changeQueryParam('service', newVal);
            },
            'searchData.system': function (newVal) {
                this.changeQueryParam('system', newVal);
            },
        },
    }
</script>

<style>
</style>
