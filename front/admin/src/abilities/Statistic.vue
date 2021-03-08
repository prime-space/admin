<template>
    <div>
        <v-layout>
            <v-flex class="mt-1">
                <v-card class="pa-4">
                    <MainStatisticChart></MainStatisticChart>
                </v-card>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
    import MainStatisticChart from './components/MainStatisticChart.vue';
    export default {
        data() {
            return {
            }
        },
        mounted: function () {
            this.$store.commit('changeTitle', `PrimeAdmin - Statistic`); //@todo fix title
        },
        methods: {

        },
        components: {
            MainStatisticChart
        },
    }
</script>

<style>
</style>
