<template>
    <div>
        <v-data-table
                :headers="tableMethods.headers"
                :items="tableMethods.items"
                :items-per-page="1000"
                class="elevation-1"
                :loading="tableMethods.loading"
                hide-default-footer
        >
            <template slot="headerCell" slot-scope="props">
                <v-tooltip bottom>
                    <span slot="activator">{{ props.header.text }} <span v-if="props.header.tooltip">(?)</span></span>
                    <span v-if="props.header.tooltip">{{ props.header.tooltip }}</span>
                </v-tooltip>
            </template>
            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
            <template slot="item" slot-scope="props">
                <tr>
                    <td>{{ props.item.name }}</td>
                    <td>{{ props.item.waiting }}</td>
                    <td>{{ props.item.available }}</td>
                </tr>
            </template>
        </v-data-table>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                tableMethods: {
                    headers: [
                        {text: 'Name', value: 'name'},
                        {text: 'Waiting', value: 'waiting'},
                        {text: 'Available', value: 'available', tooltip: 'Total amount in active accounts'},
                    ],
                    items: [],
                    loading: false,
                }
            }
        },
        mounted: function () {
            this.$store.commit('changeTitle', 'Payout State');
            this.showMethods();
        },
        computed: {
            pages () {
                return Math.ceil(this.total / this.rowsPerPage);
            },
        },
        methods: {
            showMethods () {
                this.tableMethods.loading = true;
                let url = '/payoutMethods';
                request(this.$http, 'get', url, [], function (response) {
                    this.tableMethods.loading = false;
                    this.tableMethods.items = response.body.items;
                    for (let key in this.tableMethods.items) {
                        this.tableMethods.items[key].waiting = formatMoney(parseFloat(this.tableMethods.items[key].waiting));
                        this.tableMethods.items[key].available = formatMoney(parseFloat(this.tableMethods.items[key].available));
                    }
                }.bind(this));
            },
        }
    }
</script>

<style>
</style>
