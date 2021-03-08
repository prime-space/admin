<template>
    <div class="marketplaceProduct">
        <v-card>
            <v-card-text>
                <v-list dense>
                    <v-list-item v-for="(item,key) in product.fields" :key="key">
                        <v-list-item-content>{{ item.title }}</v-list-item-content>
                        <v-list-item-content
                                :class="'entityIteratorType__'+item.type"
                        >
                            <span v-if="item.type === 'image'"><img :src="item.value" style="max-width:500px" alt="Image"></span>
                            <span v-else>{{ item.value }}</span>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
            </v-card-text>
            <v-card-actions>
                <v-spacer/>
                <div>
                    <verification-actions v-model="product" :service-id="serviceId"/>
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
            }
        },
        created() {
            this.productId = this.$route.params.productId - 0;
            this.serviceId = this.$route.params.serviceId - 0;
        },
        mounted() {
            this.$store.commit('changeTitle', `Marketplace - Product ${this.productId}`); //@todo fix title
        },
    }
</script>

<style>
    .list__tile_clickable {
        background: rgba(0, 0, 0, .12)
    }
</style>
