<template>
    <div>
        <v-container>
            <v-data-table :headers="queueTableHeaders" :items="queues" hide-default-footer
                          class="elevation-1" :loading="loading" :items-per-page="1000">
                <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                <template slot="item" slot-scope="props">
                    <tr>
                        <td>{{ props.item.queueName }}</td>
                        <td>{{ props.item.messagesNum }}</td>
                        <td>{{ props.item.messagesDlqNum }}</td>
                        <td class="abilityQueue__messageField">{{ props.item.message }}</td>
                        <td>{{ props.item.lagTime }}</td>
                    </tr>
                </template>
            </v-data-table>
        </v-container>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                queues: [],
                loading: false,
                queueTableHeaders: [
                    {text: 'Queue Name', value: 'queueName'},
                    {text: 'Messages', value: 'messagesNum'},
                    {text: 'DLQ', value: 'messagesDlqNum'},
                    {text: 'Next Message', value: 'message'},
                    {text: 'Lag', value: 'lagTime'},
                ],
            }
        },
        mounted: function () {
            this.$store.commit('changeTitle', `PrimeAdmin - Queues`); //@todo fix title
            this.showQueues()
        },
        methods: {
            showQueues() {
                this.loading = true;
                let serviceId = this.$route.params.serviceId;
                let url = `/ability/${serviceId}/queue/queues`;
                request(this.$http, 'get', url, [], function (response) {
                    this.loading = false;
                    this.queues = response.body;
                }.bind(this));
            },
        }
    }
</script>

<style>
    .abilityQueue__messageField {
        max-width: 200px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
