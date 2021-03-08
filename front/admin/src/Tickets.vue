<template>
    <div>
        <v-data-table :headers="headers" :items-per-page="1000" :items="tickets" hide-default-footer class="elevation-1" :loading="loading">
            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
            <template slot="item" slot-scope="props">
                <tr @click="$router.push(`/tickets/${props.item.id}`)" :style="{ cursor: 'pointer'}">
                    <td>{{ props.item.id }}</td>
                    <td>{{ props.item.subject }}</td>
                    <td>{{ props.item.serviceName }}</td>
                    <td>{{ props.item.responsible }}</td>
                    <td>{{ props.item.lastMessageTs }}</td>
                    <td class="justify-center layout px-0">
                        <div v-if="props.item.isReplied">
                            <v-chip> New Message </v-chip>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>
        <div class="text-xs-center pt-2">
            <v-pagination v-model="pagination.page" :length="pages" @input="showTickets"></v-pagination>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                tickets: [],
                loading: false,
                pagination: {},
                total: 0,
                rowsPerPage: 13,
                headers: [
                {text: '#', value: 'id'},
                {text: 'Subject', value: 'subject'},
                {text: 'Service', value: 'service'},
                {text: 'Responsible', value: 'responsible'},
                {text: 'Last message date', value: 'date'},
                {},
            ],
            }
        },
        mounted: function () {
            this.showTickets(1);
            this.$store.commit('changeTitle', 'Tickets');
        },
        computed: {
            pages () {
                return Math.ceil(this.total / this.rowsPerPage);
            }
        },
        methods: {
            showTickets (pageId) {
                this.loading = true;
                let url = '/tickets/' + this.rowsPerPage + '/' + pageId;
                request(this.$http, 'get', url, [], function (response) {
                    this.loading = false;
                    this.tickets = response.body.tickets;
                    this.total = response.body.total;
                }.bind(this));
            },
        }
    }
</script>

<style>
</style>
