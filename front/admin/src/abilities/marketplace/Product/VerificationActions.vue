<template>
    <div class="productVerificationActions">
        <template v-if="product.data.statusId === 3">
            <v-dialog v-model="rejectDialog" width="500">
                <template v-slot:activator="{ on }">
                    <v-btn v-on="on" color="error" text>Reject</v-btn>
                </template>
                <v-form @submit.prevent="reject" style="display:contents">
                    <v-card>
                        <v-card-title class="headline" primary-title>Reject</v-card-title>
                        <v-card-text>
                            <v-textarea v-model="verificationForm.data.reason"
                                        :error-messages="verificationForm.errors.reason"
                                        label="Reason"
                            />
                            <div class="error--text">
                                        <span v-if="verificationForm.errors.form">
                                            {{ verificationForm.errors.form }}
                                        </span>
                            </div>
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-btn @click="rejectDialog=false" text>Cancel</v-btn>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="verificationForm.loading" type="submit" color="error">
                                Reject
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-dialog>
            <v-btn @click="accept" color="primary" text> Accept</v-btn>
        </template>
        <template v-if="[3,4,5,6,7].includes(product.data.statusId)">
            <v-dialog v-model="blockDialog" width="500">
                <template v-slot:activator="{ on }">
                    <v-btn v-on="on" color="error" text>Block</v-btn>
                </template>
                <v-form @submit.prevent="block" style="display:contents">
                    <v-card>
                        <v-card-title class="headline" primary-title>Block</v-card-title>
                        <v-card-text>
                            <v-textarea v-model="verificationForm.data.reason"
                                        :error-messages="verificationForm.errors.reason"
                                        label="Reason"
                            />
                            <div class="error--text">
                                        <span v-if="verificationForm.errors.form">
                                            {{ verificationForm.errors.form }}
                                        </span>
                            </div>
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-btn @click="blockDialog=false" text>Cancel</v-btn>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="verificationForm.loading" type="submit" color="error">
                                Block
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-dialog>
        </template>
        <template v-else-if="product.data.statusId === 8">
            <v-btn @click="unblock" color="primary" text> Unblock</v-btn>
        </template>
    </div>
</template>


<script>
    export default {
        components: {},
        props: {
            serviceId: Number,
            value: Object,
        },
        data() {
            return {
                verificationForm: null,
                rejectDialog: false,
                blockDialog: false,
                product: null,
            }
        },
        created() {
            this.product = this.value;
            this.verificationForm = Main.default.initForm();
        },
        watch: {
            product: {
                handler: function (newVal) {
                    this.$emit('input', newVal);
                },
                deep: true,
            },
        },
        methods: {
            accept() {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Accept',
                    body: 'Accept product to sale',
                })
                    .then(confirmation => {
                        if (confirmation) {
                            this.verificationForm.data = {};
                            let url = `/ability/${this.serviceId}/product/accept/${this.product.data.id}`;
                            Main.default.request(this, url, this.verificationForm, function () {
                                this.product.data.statusId = 5;
                                this.$router.push({
                                    name: 'marketplaceProductVerificationProductList',
                                    params: {userId: this.product.data.userId}
                                });
                            }.bind(this), {
                                errorFunc: function () {
                                    this.$snack.danger({text: 'Error', button: 'close'});//@TODO
                                }.bind(this)
                            });
                        }
                    })
            },
            reject() {
                let url = `/ability/${this.serviceId}/product/reject/${this.product.data.id}`;
                Main.default.request(this, url, this.verificationForm, function () {
                    this.product.data.statusId = 4;
                    this.rejectDialog = false;
                    this.$router.push({
                        name: 'marketplaceProductVerificationProductList',
                        params: {userId: this.product.data.userId}
                    });
                }.bind(this));
            },
            block() {
                let url = `/ability/${this.serviceId}/product/block/${this.product.data.id}`;
                Main.default.request(this, url, this.verificationForm, function () {
                    this.product.data.statusId = 8;
                    this.blockDialog = false;
                    this.$snack.danger({text: 'Blocked'});
                }.bind(this));
            },
            unblock() {
                this.$store.dispatch('confirmer/ask', {
                    title: 'Unblock',
                    body: 'Unblock product',
                })
                    .then(confirmation => {
                        if (confirmation) {
                            this.verificationForm.data = {};
                            let url = `/ability/${this.serviceId}/product/unblock/${this.product.data.id}`;
                            Main.default.request(this, url, this.verificationForm, function () {
                                this.product.data.statusId = 5;
                                this.$snack.danger({text: 'Unblocked'});
                            }.bind(this), {
                                errorFunc: function () {
                                    this.$snack.danger({text: 'Error', button: 'close'});//@TODO
                                }.bind(this)
                            });
                        }
                    })
            },
        }
    }
</script>
