<template>
    <div class="marketplaceProductObjects">
        <v-alert border="left" elevation="1">
            <div>The list excludes sold objects.</div>
        </v-alert>
        <v-data-table
                :headers="headers"
                :items="items"
                :items-per-page="1000"
                :loading="getForm.loading"
                class="elevation-1"
                hide-default-footer
        >
            <template slot="item" slot-scope="props">
                <tr>
                    <td>{{ props.item.id }}</td>
                    <td class="marketplaceProductObjects__fieldData">{{ props.item.data }}</td>
                </tr>
            </template>
        </v-data-table>
        <v-card>
            <v-card-actions>
                <v-spacer/>
                <div>
                    <verification-actions :v-model="product" :service-id="serviceId"/>
                </div>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script>
    import VerificationActions from './VerificationActions';

    export default {
        components: {VerificationActions},
        props: {
            product: Object,
        },
        data() {
            return {
                serviceId: null,
                productId: null,
                getForm: null,
                headers: [
                    {text: 'Id'},
                    {text: 'Data'},
                ],
                items: [],
            }
        },
        created() {
            this.getForm = Main.default.initGetForm();
        },
        mounted() {
            this.productId = this.$route.params.productId - 0;
            this.serviceId = this.$route.params.serviceId - 0;
            this.$store.commit('changeTitle', `Marketplace - Product Objects ${this.productId}`); //@todo fix title
            this.showItems();
        },
        methods: {
            showItems() {
                let url = `/ability/${this.serviceId}/product/objects/${this.productId}`;
                Main.default.request(this, url, this.getForm, function (response) {
                    this.items = response.body;
                }.bind(this));
            },
        },
    }
</script>

<style>
    .marketplaceProductObjects__fieldData {
        white-space: pre;
    }
</style>
