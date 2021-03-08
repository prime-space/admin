import { Bar, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins;

const DATASET_AMOUNT_INDEX = 0;
const DATASET_POINT_AMOUNT_INDEX = 1;
export default {
    extends: Bar,
    mixins: [reactiveProp],
    props: [],
    data() {
        return {
            options: {
                tooltips: {
                    mode: 'index',
                    callbacks: {
                        label: function (tooltipItems, data) {
                            let label = data.datasets[tooltipItems.datasetIndex].label;
                            let value = tooltipItems.yLabel;
                            if (tooltipItems.datasetIndex === DATASET_AMOUNT_INDEX) {
                                value = formatMoney(value);

                                return `${label}: ${value} rub`;
                            } else if (tooltipItems.datasetIndex === DATASET_POINT_AMOUNT_INDEX) {
                                value = formatMoney(value);

                                return `${label}: ${value} rub`;
                            } else {
                                return `${label}: ${value}`;
                            }
                        }.bind(this)
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: false,
                        position: "left",
                        id: "y-axis-1",
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {
                                value = formatMoney(value);
                                return `${value} rub`;
                            }.bind(this)
                        }
                    },{
                        stacked: false,
                        position: "left",
                        id: "y-axis-1",
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {
                                value = formatMoney(value);
                                return `${value} rub`;
                            }.bind(this)
                        }
                    }]
                }
            }
        }
    },
    mounted() {
        this.renderChart(this.chartData, this.options);
    }
}
