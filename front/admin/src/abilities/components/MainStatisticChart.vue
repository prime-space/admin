<template>
    <div>
        <v-radio-group hide-details row v-model="switchModel" @click.native="fillData()">
            <v-radio label="Months" :value="true" class="noMarginBottomVRadio"></v-radio>
            <v-radio label="Days" :value="false"></v-radio>
        </v-radio-group>
        <LineChart v-if="dataCollection !== null" :chart-data="dataCollection"></LineChart>
    </div>
</template>

<script>
    import LineChart from './MainStatisticChart.js';
    export default {
        components: {
            LineChart
        },
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
                    datasets: [
                        {
                            type: 'line',
                            label: 'Point Amount',
                            yAxisID: "y-axis-1",
                            backgroundColor: "#88b7fc",
                            data: this.dataForRender.pointAmount
                        },
                        {
                            type: 'line',
                            label: 'Amount',
                            yAxisID: "y-axis-1",
                            backgroundColor: "#b3d4fc",
                            data: this.dataForRender.amount
                        }
                    ]
                };
            },
            getChartData() {
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/statistic/chartData`;
                request(this.$http, 'get', url, [], function (response) {
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
