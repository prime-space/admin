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
                                :items="shop"
                                hide-default-footer
                                row
                                wrap
                        >
                            <v-flex
                                    slot="item"
                                    slot-scope="props"
                            >
                                <v-card>
                                    <v-card-title><h4>Shop</h4>
                                    </v-card-title>
                                    <v-divider></v-divider>
                                    <v-list dense>
                                        <v-list-item v-for="row in dataIteratorRows"
                                                     :key=row.id
                                                     v-on="row.isClickable ? rowClickHandler(row.id, props.item[row.property]) : {}"
                                                     :class="row.isClickable ? 'list__tile_clickable' : ''"
                                        >
                                            <v-list-item-content class="text_selectable">
                                                {{ row.title}}
                                            </v-list-item-content>
                                            <v-list-item-content class="align-end text_selectable">
                                                {{ props.item[row.property] }}
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-list>
                                </v-card>
                            </v-flex>
                        </v-data-iterator>
                    </v-container>
                </v-tab-item>
                <v-tab-item>
                    <v-form ref="considerRequestForm" @submit.prevent="submitConsiderRequestForm">
                        <v-card>
                            <v-card-text>
                                <v-container grid-list-md>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <div v-if="considerRequestForm.errors.form">
                                                {{ considerRequestForm.errors.form }}
                                            </div>
                                            <v-radio-group v-model="considerRequestForm.data.status">
                                                <v-radio label="Working" :value="shopOkStatus"></v-radio>
                                                <v-radio label="Declined" :value="shopDeclinedStatus"></v-radio>
                                                <v-radio label="On verification"
                                                         :value="shopOnVerificationStatus"></v-radio>
                                            </v-radio-group>
                                        </v-flex>
                                    </v-layout>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-flex xs2>
                                    <v-btn color="primary" type="submit" name="save"
                                           :disabled="considerRequestForm.submitting"
                                    >Save
                                    </v-btn>
                                </v-flex>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-tab-item>
                <v-tab-item>
                    <v-container>
                        <v-data-table :headers="paymentMethodsTableHeaders" :items="paymentMethods" hide-default-footer
                                      class="elevation-1" :loading="loading" :items-per-page="1000">
                            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                            <template slot="item" slot-scope="props">
                                <tr :class="{'row-text__disabled': !props.item.isEnabled}">
                                    <td>{{ props.item.id }}</td>
                                    <td>{{ props.item.name }}</td>
                                    <td :class="{'text__bold': props.item.hasPersonalFee}">{{ props.item.fee }}</td>
                                    <td>
                                        <v-icon v-if="isDisabledByUser(props.item)">mdi-fa-times</v-icon>
                                    </td>
                                    <td>{{ props.item.dayStat }}</td>
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
                </v-tab-item>
                <v-tab-item>
                    <v-form ref="dailyLimitForm" @submit.prevent="submitDailyLimitForm">
                        <v-card>
                            <v-card-text>
                                <v-container grid-list-md>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-text-field :error-messages="dailyLimitForm.errors.dailyLimit"
                                                          name="dailyLimit"
                                                          v-model="dailyLimitForm.data.dailyLimit" label="Daily limit"
                                                          type="number"
                                                          step="0.01"
                                                          min="0"></v-text-field>
                                        </v-flex>
                                    </v-layout>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-flex xs2>
                                    <v-btn color="primary" type="submit" name="save"
                                           :disabled="considerRequestForm.submitting"
                                    >Save
                                    </v-btn>
                                </v-flex>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-tab-item>
                <v-tab-item>
                    <v-card>
                        <PaymentStatisticChart :url="'/ability/'+this.$route.params.serviceId+'/shop/statistics/'+this.$route.params.shopId" v-if="shop.length > 0"></PaymentStatisticChart>
                    </v-card>
                </v-tab-item>
                <v-tab-item>
                    <DomainSchemeChangeForm :shop="shop" @showShop="showShop" @snackbarSuccess="snackbar=true"></DomainSchemeChangeForm>
                </v-tab-item>
                <v-tab-item>
                    <shop-payments :shop="shop"/>
                </v-tab-item>
            </v-tabs-items>
        </v-tabs>
        <v-dialog v-model="paymentMethodForm.dialog" persistent max-width="500px">
            <v-form ref="paymentMethodForm" @submit.prevent="submitPaymentMethodForm">
                <v-card>
                    <v-card-title class="headline">
                        Edit
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
                                <v-flex xs12>
                                    <v-text-field :error-messages="paymentMethodForm.errors.fee"
                                                  name="fee"
                                                  v-model="paymentMethodForm.data.fee" label="Fee"
                                                  type="number"
                                                  step="0.01"
                                                  min="0">
                                    </v-text-field>
                                    <v-checkbox :error-messages="paymentMethodForm.errors.isEnabled"
                                                name="isEnabled"
                                                v-model="paymentMethodForm.data.isEnabled"
                                                persistent-hint
                                                label="Enabled">
                                    </v-checkbox>
                                    <v-checkbox :error-messages="paymentMethodForm.errors.hasPersonalFee"
                                                name="hasPersonalFee"
                                                v-model="paymentMethodForm.data.hasPersonalFee"
                                                label="Enabled personal fee">
                                    </v-checkbox>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text
                               @click.native="paymentMethodForm.dialog = false">
                            Close
                        </v-btn>
                        <v-btn type="submit" name="save" color="blue darken-1" text
                               :disabled="paymentMethodForm.submitting">Save
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
    import PaymentStatisticChart from './../components/PaymentStatisticChart.vue';
    import DomainSchemeChangeForm from './../components/DomainSchemeChangeForm'
    import ShopPayments from './components/ShopPayments'
    export default {
        components: {
            PaymentStatisticChart,
            DomainSchemeChangeForm,
            ShopPayments
        },
        data() {
            return {
                isPaymentMethodDisabledByUser: false,
                snackbar: false,
                loading: false,
                tab: null,
                tabs: ['Info', 'Status', 'Payment Methods', 'Daily Limit', 'Statistics', 'Domain', 'Payments'],
                shopDeclinedStatus: config.shopDeclinedStatus,
                shopOkStatus: config.shopOkStatus,
                shopOnVerificationStatus: config.shopOnVerificationStatus,
                shop: [],
                paymentMethods: [],
                considerRequestForm: {data: {status: ''}, errors: {}, submitting: false},
                paymentMethodForm: {
                    data: {isEnabled: false, hasPersonalFee: false},
                    errors: {},
                    submitting: false,
                    dialog: false
                },
                dailyLimitForm: {data: {dailyLimit: null}, errors: {}, submitting: false},
                dataIteratorRows: [
                    {id: 1, title: '#', property: 'id', isClickable: false},
                    {id: 2, title: 'User id', property: 'userId', isClickable: true},
                    {id: 3, title: 'Name', property: 'name', isClickable: false},
                    {id: 4, title: 'Url', property: 'url', isClickable: true},
                    {id: 5, title: 'Description', property: 'description', isClickable: false},
                    {id: 6, title: 'Success URL', property: 'successUrl', isClickable: false},
                    {id: 7, title: 'Fail URL', property: 'failUrl', isClickable: false},
                    {id: 8, title: 'Result URL', property: 'resultUrl', isClickable: false},
                    {id: 9, title: 'Is test mode', property: 'isTestMode', isClickable: false},
                    {id: 10, title: 'Is fee by client', property: 'isFeeByClient', isClickable: false},
                    {id: 11, title: 'Is allowed to redefine URL', property: 'isAllowedToRedefineUrl', isClickable: false},
                    {id: 12, title: 'Status', property: 'statusName', isClickable: false},
                    {id: 13, title: 'Created', property: 'createdTs', isClickable: false},
                    {id: 14, title: 'Daily limit', property: 'paymentDayStatistic', isClickable: false},
                ],
                paymentMethodsTableHeaders: []
            }
        },
        mounted: function () {
            this.paymentMethodsTableHeaders = [
                {text: '#', value: 'id'},
                {text: 'Name', value: 'name'},
                {text: 'Fee', value: 'fee'},
                {text: 'Disabled by user', value: 'isDisabledByUser'},
                {text: 'Day stat', value: 'dayStat'},
                {},
            ];
            this.showShop();
        },
        methods: {
            showShop() {
                this.loading = true;
                let serviceId = this.$route.params.serviceId;
                let shopId = this.$route.params.shopId;
                let url = `/ability/${serviceId}/shop/page/${shopId}`;
                request(this.$http, 'get', url, [], function (response) {
                    this.shop = [response.body.shop];
                    let dailyAmountFormatted = formatMoney(parseFloat(this.shop[0].paymentDayAmount));
                    let dailyLimitFormatted = formatMoney(parseFloat(this.shop[0].paymentDayLimit));
                    let currencyLabel = this.shop[0].paymentDayStatisticCurrency;
                    this.shop[0].paymentDayStatistic = `${dailyAmountFormatted} ${currencyLabel} / ${dailyLimitFormatted} ${currencyLabel}`;
                    this.paymentMethods = sortByIsEnabled(response.body.paymentMethods);
                    this.showConsiderRequestForm();
                    this.$store.commit('changeTitle', `PrimeAdmin - Shop - #${shopId}: ${this.shop[0].name}`); //@todo fix title
                    this.loading = false;
                    this.dailyLimitForm.data.dailyLimit = this.shop[0].paymentDayLimit;
                }.bind(this));
            },
            showConsiderRequestForm() {
                this.considerRequestForm.data.status = this.shop[0].statusId;
                this.considerRequestForm.errors = {};
            },
            submitPaymentMethodForm() {
                this.paymentMethodForm.submitting = true;
                let shopId = this.$route.params.shopId;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/shop/personalPaymentMethodSettings/${shopId}`;
                request(this.$http, 'post', url, this.paymentMethodForm, function () {
                    this.showShop();
                    this.paymentMethodForm.dialog = false;
                }.bind(this));
            },
            submitConsiderRequestForm() {
                this.considerRequestForm.submitting = true;
                let shopId = this.$route.params.shopId;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/shop/considerRequest/${shopId}`;
                request(this.$http, 'post', url, this.considerRequestForm, function () {
                    this.showShop();
                    this.snackbar = true;
                }.bind(this));
            },
            submitDailyLimitForm() {
                this.dailyLimitForm.submitting = true;
                let shopId = this.$route.params.shopId;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/shop/dailyLimit/${shopId}`;
                request(this.$http, 'post', url, this.dailyLimitForm, function () {
                    this.showShop();
                    this.snackbar = true;
                }.bind(this));
            },
            toUserPage(userId) {
                let serviceId = this.$route.params.serviceId;
                this.$router.push(`/service/${serviceId}/user/${userId}`);
            },
            openShopUrlNewTab(url) {
                window.open(url);
            },
            rowClickHandler(rowId, payLoad) {
                if (rowId === 2) {
                    return {click: () => this.toUserPage(payLoad)};
                } else if (rowId === 4) {
                    return {click: () => this.openShopUrlNewTab(payLoad)};
                } else {
                    return {};
                }
            },
            openPaymentMethodForm(paymentMethodId) {
                this.paymentMethodForm.data.paymentMethodId = paymentMethodId;
                let paymentMethod = this.paymentMethods.find(
                    paymentMethod => paymentMethod.id === paymentMethodId
                );
                this.paymentMethodForm.data.fee = paymentMethod.fee;
                this.paymentMethodForm.data.isEnabled = paymentMethod.isEnabled;
                this.paymentMethodForm.data.hasPersonalFee = paymentMethod.hasPersonalFee;
                this.paymentMethodForm.submitting = false;
                this.paymentMethodForm.dialog = true;
            },
            isDisabledByUser(method){
                return !method.isEnabledByUser;
            }
        }
    }
</script>

<style>
    .list__tile_clickable {
        background: rgba(0, 0, 0, .12)
    }
</style>
