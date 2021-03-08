<template>
    <div>
        <v-tabs v-model="tab" fixed-tabs @change="changeTab">
            <v-tab
                    v-for="(tab, key) in tabs"
                    :key="key"
                    ripple
            >
                {{ tab }}
            </v-tab>
        </v-tabs>
        <Crud :serviceId="serviceId" :listing="listing"></Crud>
    </div>
</template>

<script>
    import Crud from './components/Crud.vue';
    export default {
        data() {
            return {
                tab: null,
                tabs: ['shops', 'payouts'],
                serviceId: this.$route.params.serviceId,
                listing: 'shops',
            }
        },
        mounted: function () {
            this.$store.commit('changeTitle', `Listing`); //@todo fix title
        },
        methods: {
            changeTab (tabKey) {
                this.listing = this.tabs[tabKey];
            },
        },
        components: {
            Crud
        },
    }
</script>

<style>
</style>
