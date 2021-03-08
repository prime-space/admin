<template>
    <div>
        <v-data-table
                :headers="headers"
                :items="items"
                :items-per-page="listing.pagination.itemsPerPage"
                :loading="listing.loading"
                :mobile-breakpoint="100"
                class="marketplaceProductVerificationProductList elevation-1"
                hide-default-footer
        >
            <template slot="item" slot-scope="props">
                <tr class="marketplaceProductVerificationProductList__row">
                    <td>{{ props.item.id }}</td>
                    <td>{{ props.item.name }}</td>
                    <td>{{ props.item.createdDate }}</td>
                    <td>
                        <v-layout justify-end>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                            v-on="on"
                                            :to="{name: 'marketplaceProduct', params: {serviceId: serviceId, productId: props.item.id}}"
                                            text
                                            icon
                                    >
                                        <v-icon>mdi-eye</v-icon>
                                    </v-btn>
                                </template>
                                <span>Verification</span>
                            </v-tooltip>
                        </v-layout>
                    </td>
                </tr>
            </template>
        </v-data-table>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                listing: null,
                serviceId: null,
                userId: null,
                items: [],
                headers: [
                    {text: 'ID', sortable: false},
                    {text: 'Name', sortable: false},
                    {text: 'Created Date', sortable: false},
                    {text: '', sortable: false},
                ],
            }
        },
        created() {
            this.listing = Main.default.initGetForm({}, 1000);
        },
        mounted() {
            this.serviceId = this.$route.params.serviceId;
            this.userId = this.$route.params.userId;
            this.$store.commit('changeTitle', `Product Verification - User ${this.userId}`); //@todo fix title
            let url = `/ability/${this.serviceId}/product-verification/productList/${this.userId}`;
            Main.default.request(this, url, this.listing, function (response) {
                this.items = response.body;
            }.bind(this));
        },
        methods: {},
    }
</script>

<style>
</style>
