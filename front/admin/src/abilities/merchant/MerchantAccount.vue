<template>
    <div>
        <crud :serviceId="this.serviceId" listing="account" :params="params"></crud>
    </div>
</template>

<script>
    import Crud from './../components/Crud.vue';
    export default {
        data() {
            return {
                serviceId: this.$route.params.serviceId,
                params: {accountId: this.$route.params.accountId},
            }
        },
        components: {
            Crud
        },
        mounted: function () {
            this.$store.commit('changeTitle', `Service #${this.serviceId} Account #${this.params.accountId}`); //@todo fix title
        },
    }
</script>

<style>
</style>
