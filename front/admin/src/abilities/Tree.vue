<template>
    <v-card style="padding:16px">
        <v-progress-linear v-if="getForm.loading" style="margin:50px 0" indeterminate></v-progress-linear>
        <v-layout v-else>
            <v-flex style="width:50%;padding-right:10px">
                <v-treeview
                        :items="items"
                        :open="open"
                        @update:open="eventOpen"
                        :active.sync="active"
                        item-key="id"
                        class="editTree__tree editTree__treeSelector"
                        activatable
                        hoverable
                ></v-treeview>
            </v-flex>
            <v-divider vertical></v-divider>
            <v-flex xs6 pa-6>
                <div v-if="selected">
                    <div class="title">{{selected.name}}</div>
                    <v-divider></v-divider>
                    <v-tabs v-model="tab" centered>
                        <v-tabs-slider></v-tabs-slider>
                        <v-tab v-if="selected.id!==1">Edit</v-tab>
                        <v-tab>+Subcategory</v-tab>
                    </v-tabs>

                    <v-tabs-items v-model="tab">
                        <v-tab-item v-if="selected.id!==1">
                            <v-form @submit.prevent="submitEditForm" style="display:contents;">
                                <v-card flat>
                                    <v-card-text class="text-center">
                                        <v-text-field :error-messages="editForm.errors.name" name="name"
                                                      v-model="editForm.data.name"
                                                      label="Name"/>
                                        <v-text-field :error-messages="editForm.errors.code" name="code"
                                                      v-model="editForm.data.code"
                                                      label="Url-code (Updating affects SEO)"/>

                                    </v-card-text>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn type="submit" name="save" color="primary"
                                               :disabled="editForm.loading">
                                            Save
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-form>
                            <v-divider></v-divider>
                            <v-card flat>
                                <v-card-text class="text-center">
                                    <v-form @submit.prevent="submitRemoveForm">
                                        <div class="error--text" v-if="removeForm.errors.form">
                                            {{ removeForm .errors.form }}
                                        </div>
                                        <v-btn type="submit" name="remove" color="error"
                                               :disabled="removeForm.loading">
                                            Remove
                                        </v-btn>
                                    </v-form>
                                </v-card-text>
                            </v-card>
                        </v-tab-item>
                        <v-tab-item>
                            <v-form @submit.prevent="submitCreateForm" style="display:contents;">
                                <v-card flat>
                                    <v-card-text class="text-center">
                                        <v-text-field :error-messages="createForm.errors.name"
                                                      name="name"
                                                      v-model="createForm.data.name"
                                                      label="Name"/>
                                        <v-text-field :error-messages="createForm.errors.code"
                                                      name="code"
                                                      v-model="createForm.data.code"
                                                      label="Url-code"/>

                                    </v-card-text>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn type="submit" name="save" color="primary"
                                               :disabled="createForm.loading">
                                            Create
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-form>
                        </v-tab-item>
                    </v-tabs-items>
                </div>
                <div v-else class="text-center font-weight-light">Item not selected</div>
            </v-flex>
        </v-layout>
    </v-card>
</template>

<script>
    export default {
        data() {
            return {
                config: config,
                items: [],
                itemsFlat: [],
                open: [1],
                active: [],
                tab: null,
                getForm: null,
                editForm: null,
                createForm: null,
                removeForm: null,
                serviceId: null,
            }
        },
        created() {
            this.getForm = Main.default.initGetForm({});
            this.editForm = Main.default.initForm({});
            this.createForm = Main.default.initForm({});
            this.removeForm = Main.default.initForm({});
        },
        mounted: function () {
            this.$store.commit('changeTitle', `PrimeAdmin - Tree`); //@todo fix title
            this.serviceId = this.$route.params.serviceId;
            Main.default.request(this, `/ability/${this.serviceId}/tree/data`, this.getForm, function (response) {
                let items = [];
                let itemsFlat = response.body.treeFlat;
                function hasTreeElementsRecursively(node) {
                    let isNodeFound = false;
                    for (let i in itemsFlat) {
                        let el = itemsFlat[i];
                        if (isNodeFound) {
                            if (el.parentId !== node.id) {
                                break;
                            }
                            if (true === hasTreeElementsRecursively(el)) {
                                return true;
                            }
                        } else if (el.id === node.id) {
                            if (el.elementsNum > 0) {
                                return true;
                            }
                            isNodeFound = true;
                        }
                    }

                    return false;
                }
                let cursor = null;
                for (let i in itemsFlat) {
                    let el = itemsFlat[i];
                    if (el.children === undefined) {
                        el.children = [];
                    }
                    el.haveElements = hasTreeElementsRecursively(el);
                    if (el.id === 1) {
                        el.parent = null;
                        items = [el];
                    } else {
                        while (cursor.id !== el.parentId) {
                            cursor = cursor.parent;
                        }
                        el.parent = cursor;
                        cursor.children.push(el);
                    }
                    cursor = el;
                }
                function sortNodeRecursively(node) {
                    node.children.sort((a, b) => (a.code > b.code) ? 1 : ((b.code > a.code) ? -1 : 0));
                    for (let i in node.children) {
                        sortNodeRecursively(node.children[i]);
                    }
                }
                sortNodeRecursively(items   [0]);

                this.items = items;
                this.itemsFlat = itemsFlat;
            }.bind(this));
        },
        watch: {
            'selected': {
                handler: function (newValue) {
                    if (newValue !== undefined && newValue !== null) {
                        this.editForm.data.name = this.selected.name;
                        this.editForm.data.code = this.selected.code;
                    }
                }
            },
        },
        computed: {
            selected() {
                if (!this.active.length) return undefined;
                const id = this.active[0];

                return this.findInTree(this.items[0], id)
            },
        },
        methods: {
            findInTree(node, id) {
                if (node.id === id) {
                    return node;
                }
                for (let i in node.children) {
                    let result = this.findInTree(node.children[i], id);
                    if (result !== null) {
                        return result;
                    }
                }

                return null;
            },
            submitEditForm() {
                let url = `/ability/${this.serviceId}/tree/edit/${this.selected.id}`;
                Main.default.request(this, url, this.editForm, function () {
                    this.selected.name = this.editForm.data.name;
                    this.selected.code = this.editForm.data.code;
                }.bind(this));
            },
            submitRemoveForm() {
                let url = `/ability/${this.serviceId}/tree/delete/${this.selected.id}`;
                Main.default.request(this, url, this.removeForm, function () {
                    let parent = this.selected.parent;
                    let idx = parent.children.findIndex(x => x.id === this.selected.id);
                    parent.children.splice(idx, 1);
                }.bind(this));
            },
            submitCreateForm() {
                this.createForm.data.parentId = this.selected.id;
                let url = `/ability/${this.serviceId}/tree/create`;
                let el = {
                    id: null,
                    parentId: this.createForm.data.parentId,
                    name: this.createForm.data.name,
                    code: this.createForm.data.code,
                    children: [],
                    parent: this.selected,
                };
                Main.default.request(this, url, this.createForm, function (response) {
                    el.id = response.body.id;
                    el.parent.children.push(el);
                    this.itemsFlat.push(el);
                    this.createForm.data = {};
                    this.open.push(el.parentId);
                }.bind(this));
            },
            eventOpen(open) {
                this.open = open;
            },
        },
    }
</script>

<style>
    .editTree {
        /*max-width: 500px;*/
        padding: 0 5px;
    }

    .editTree__tree .v-treeview-node__root {
        min-height: unset !important;
    }

    .editTree__treeSelector {
        overflow: auto;
        /*height: stretch;*/
    }

    .editTree__treeSelector .v-treeview-node__root {
        cursor: pointer;
    }

    .editTree__tree .v-treeview-node__label {
        white-space: normal !important;
        /*max-width: 450px;*/
    }

    .editTree__current {
        padding: 5px 0 0 50px;
    }

    .editTree__edit {
        position: absolute;
    }

    .editTree__error {
        border: 3px dashed red !important;
    }

    .editTree__dialog {
        height: 90%;
    }
</style>
