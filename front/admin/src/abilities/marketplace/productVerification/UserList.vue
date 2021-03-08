<template>
    <div>
        <v-data-table
                :headers="headers"
                :items="items"
                :items-per-page="listing.pagination.itemsPerPage"
                :loading="listing.loading"
                :mobile-breakpoint="100"
                class="marketplaceProductVerificationUserList elevation-1"
                hide-default-footer
        >
            <template slot="item" slot-scope="props">
                <tr class="marketplaceProductVerificationUserList__row">
                    <td>{{ props.item.id }}</td>
                    <td>{{ props.item.email }}</td>
                    <td class="text-center">{{ props.item.productNum }}</td>
                    <td>
                        <v-layout justify-end>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                            v-on="on"
                                            :to="{name: 'marketplaceProductVerificationProductList', params: {serviceId: serviceId, userId: props.item.id}}"
                                            text
                                            icon
                                    >
                                        <v-icon>mdi-eye</v-icon>
                                    </v-btn>
                                </template>
                                <span>Products List</span>
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
                items: [],
                headers: [
                    {text: 'User ID', sortable: false},
                    {text: 'User Email', sortable: false},
                    {text: 'Products Number', sortable: false, align: 'center'},
                    {text: '', sortable: false},
                ],
            }
        },
        created() {
            this.listing = Main.default.initGetForm({}, 1000);
        },
        mounted() {
            this.$store.commit('changeTitle', `Product Verification`); //@todo fix title
            this.serviceId = this.$route.params.serviceId;
            let url = `/ability/${this.serviceId}/product-verification/userList`;
            Main.default.request(this, url, this.listing, function (response) {
                this.items = response.body;
            }.bind(this));
        },
        methods: {},
    }
</script>

<style>
</style>
