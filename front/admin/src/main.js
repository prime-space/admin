import './scss/main.scss';
import './scss/vue-snack.css';

import Vue from 'vue';
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'
import VueSnackbar from 'vue-snack';
import vuetify from '@/plugins/vuetify';
import App from './App.vue'
import Dashboard from './Dashboard.vue'
import Tickets from './Tickets'
import Ticket from './Ticket'
import Payouts from './Payouts'
import store from './store'
import Finder from './abilities/Finder'
import Statistic from './abilities/Statistic'
import Listing from './abilities/Listing'
import MerchantUser from './abilities/merchant/MerchantUser'
import MerchantShop from './abilities/merchant/MerchantShop'
import MerchantPayment from './abilities/merchant/MerchantPayment'
import MerchantAccount from './abilities/merchant/MerchantAccount'
import MarketplaceProductVerificationUserList from './abilities/marketplace/productVerification/UserList'
import MarketplaceProductVerificationProductList from './abilities/marketplace/productVerification/ProductList'
import MarketplaceProduct from './abilities/marketplace/Product/Product'
import PayMethods from './abilities/PayMethods'
import Tree from './abilities/Tree'
import Queue from './abilities/Queue'
import ConfirmDialog from './confirmDialog.vue'

Vue.use(VueSnackbar, {position: 'bottom-right', time: 6000});
Vue.use(VueRouter);
Vue.use(VueResource);
Vue.component('confirm-dialog', ConfirmDialog);
Vue.config.productionTip = false;
Vue.config.devtools = true;

const routes = [
    {path: '/', component: Dashboard, meta: {accessRuleId: ACCESS_RULE_ID_DASHBOARD}},
    {path: '/tickets', component: Tickets, meta: {accessRuleId: ACCESS_RULE_ID_TICKETS}},
    {path: '/tickets/:id', component: Ticket, meta: {accessRuleId: ACCESS_RULE_ID_TICKETS}},
    {path: '/payouts', component: Payouts, meta: {accessRuleId: ACCESS_RULE_ID_PAYOUT}},
    {path: '/service/:serviceId/finder', component: Finder, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {path: '/service/:serviceId/statistic', component: Statistic, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {path: '/service/:serviceId/listing', component: Listing, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {path: '/service/:serviceId/tree', component: Tree, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {path: '/service/:serviceId/user/:userId', component: MerchantUser, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {path: '/service/:serviceId/shop/:shopId', component: MerchantShop, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {
        path: '/service/:serviceId/account/:accountId',
        component: MerchantAccount,
        meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}
    },
    {
        path: '/service/:serviceId/payment/:paymentId',
        component: MerchantPayment,
        meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}
    },
    {path: '/service/:serviceId/payMethods', component: PayMethods, meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}},
    {path: '/service/:serviceId/queue', component: Queue},
    {
        path: '/service/:serviceId/product-verification',
        component: MarketplaceProductVerificationUserList,
        meta: {accessRuleId: ACCESS_RULE_ID_SERVICE}
    },
    {
        path: '/service/:serviceId/product-verification/user-products/:userId',
        component: MarketplaceProductVerificationProductList,
        meta: {accessRuleId: ACCESS_RULE_ID_SERVICE},
        name: 'marketplaceProductVerificationProductList',
    },
    {
        path: '/service/:serviceId/product/:productId',
        component: MarketplaceProduct,
        meta: {accessRuleId: ACCESS_RULE_ID_SERVICE},
        name: 'marketplaceProduct',
    },

];

const router = new VueRouter({
    routes
});

router.beforeEach((to, from, next) => {
    let routeAccessRuleId = to.meta.accessRuleId;
    let serviceId = null;
    if (routeAccessRuleId !== undefined) {
        let isList = config.accessRulesIndexedById[routeAccessRuleId].isList;
        if (isList) {
            serviceId = parseInt(to.params.serviceId) || null;
        }
        if (isAccessDenied(routeAccessRuleId, serviceId)) {
            next(false);
        } else {
            next();
        }
    } else {
        next();
    }
});

new Vue({
    render: (h) => h(App),
    router,
    store,
    vuetify,
}).$mount('#app');
