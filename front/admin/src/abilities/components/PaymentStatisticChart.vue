<template>
    <div>
        <v-radio-group hide-details row v-model="switchModel" @click.native="fillData()">
            <v-radio label="Months" :value="true" class="noMarginBottomVRadio"></v-radio>
            <v-radio label="Days" :value="false"></v-radio>
        </v-radio-group>
        <PaymentStatisticChart v-if="dataCollection !== null" :chart-data="dataCollection"></PaymentStatisticChart>
    </div>
</template>

<script>
    import PaymentStatisticChart from './PaymentStatisticChart.js';
    export default {
        components: {
            PaymentStatisticChart
        },
        props: ['url'],
        data() {
            return {
                dataCollection: null,
                switchModel: false,
                apiChartData: null
            }
        },
        mounted() {
            this.getChartData();
        },
        methods: {
            fillData() {
                this.dataCollection = {
                    labels: this.dataForRender.intervals,
                    datasets: [{
                        type: 'bar',
                        label: 'Total',
                        yAxisID: "y-axis-0",
                        backgroundColor: "rgba(128, 128, 128, 0.5)",
                        data: this.dataForRender.total
                    }, {
                        type: 'bar',
                        label: 'Success',
                        yAxisID: "y-axis-0",
                        backgroundColor: "#2d7aff",
                        data: this.dataForRender.success
                    },
                        {
                            type: 'line',
                            label: 'Amount',
                            yAxisID: "y-axis-1",
                            backgroundColor: "#b3d4fc",
                            data: this.dataForRender.amount
                        }]
                };
            },
            getChartData() {
                request(this.$http, 'get', this.url, [], function (response) {
                    this.apiChartData = response.body;
                    this.fillData();
                }.bind(this));
            }
        },
        computed: {
            dataForRender() {
                if (this.switchModel) {
                    return this.apiChartData.byMonths;
                } else {
                    return this.apiChartData.byDays;
                }
            }
        }
    }
</script>

<style>
    .noMarginBottomVRadio {
        margin-bottom: 0!important;
    }
</style>
