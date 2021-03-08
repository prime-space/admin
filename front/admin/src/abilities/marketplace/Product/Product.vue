<template>
    <div class="marketplaceProduct">
        <confirm-dialog/>
        <v-progress-linear v-if="!item" style="margin:50px 0" indeterminate/>
        <div v-else>
            <v-tabs v-model="tab" style="margin-bottom:5px" fixed-tabs>
                <v-tab>Summary</v-tab>
                <v-tab>Objects</v-tab>
            </v-tabs>
            <v-tabs-items v-model="tab" touchless>
                <v-tab-item>
                    <Summary :product="item"/>
                </v-tab-item>
                <v-tab-item>
                    <Objects :product="item"/>
                </v-tab-item>
            </v-tabs-items>
        </div>
    </div>
</template>


<script>
    import Summary from './Summary';
    import Objects from './Objects';

    export default {
        components: {Summary, Objects},
        data() {
            return {
                getForm: null,
                tab: null,
                item: null,
            }
        },
        created() {
            this.getForm = Main.default.initGetForm();
        },
        mounted() {
            let url = `/ability/${this.$route.params.serviceId}/product/page/${this.$route.params.productId}`;
            Main.default.request(this, url, this.getForm, function (response) {
                this.item = response.body;
            }.bind(this));
        }
    }
</script>
