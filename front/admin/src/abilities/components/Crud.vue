<template>
    <div>
        <v-container>
            <v-btn v-if="addForm.config" color="primary" class="crud__createButton" @click="showAddForm()">Add</v-btn>
            <v-data-table :headers="headers" :items="items" hide-default-footer
                          class="elevation-1" :items-per-page="1000"
                          :loading="loading"
            >
                <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                <template slot="headerCell" slot-scope="props">
                    <v-tooltip bottom :disabled="!props.header.tooltip">
                        <template v-slot:activator="{ on }">
                            <span v-on="on">
                                {{ props.header.text }}
                            </span>
                        </template>
                        <span>{{ props.header.tooltip }}</span>
                    </v-tooltip>
                </template>
                <template slot="item" slot-scope="props">
                    <tr>
                        <td v-for="(header, key) in headers" :key="key">
                            <span v-if="header.type === 'text'">{{ props.item[header.value] }}</span>
                            <span v-else-if="header.type === 'url'">
                                <a :href="props.item[header.value]" target="_blank">{{ props.item[header.value] }}</a>
                            </span>
                            <span v-else-if="header.type === 'actions'">
                                <span v-for="(action, i) in props.item[header.value]" :key="i">
                                    <v-btn
                                            v-if="action.type === 'entity'"
                                            class="mx-0"
                                            @click="openEntityPage(action.entity, action.entityId)"
                                            icon
                                    >
                                        <v-icon color="teal">mdi-{{ action.icon }}</v-icon>
                                    </v-btn>
                                </span>
                            </span>
                        </td>
                    </tr>
                </template>
            </v-data-table>
        </v-container>
        <div class="text-xs-center pt-2">
            <v-pagination v-model="pagination.page" :length="pages" @input="showListing"></v-pagination>
        </div>
        <v-dialog v-if="addForm.config" v-model="addForm.dialog" persistent max-width="500px">
            <v-form ref="addForm" @submit.prevent="submitAddForm">
                <v-card>
                    <v-card-title>
                        <div class="headline">Add</div>
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <v-layout wrap>
                                <v-flex xs12 v-for="(field, key) in addForm.config.fields" :key="key">
                                    <v-text-field v-if="field.type === 'number'"
                                                  :error-messages="addForm.errors[field.name]"
                                                  :name="field.name"
                                                  type="number"
                                                  step="1"
                                                  v-model="addForm.data[field.name]"
                                                  :label="field.name"></v-text-field>
                                    <v-text-field v-else-if="field.type === 'hidden'"
                                                  :error-messages="addForm.errors[field.name]"
                                                  :name="field.name"
                                                  v-model="addForm.data[field.name]"
                                                  :label="field.name"
                                                  style="display: none;"></v-text-field>
                                    <v-text-field v-else :error-messages="addForm.errors[field.name]"
                                                  :name="field.name"
                                                  v-model="addForm.data[field.name]"
                                                  :label="field.name"></v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-container>
                        <div class="error--text" v-if="addForm.errors.form">
                            {{ addForm.errors.form }}
                        </div>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click.native="addForm.dialog = false">Close</v-btn>
                        <v-btn type="submit" name="save" color="blue darken-1" text
                               :disabled="addForm.submitting">
                            Add
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>
    </div>
</template>

<script>
    export default {
        props: {
            serviceId: {},
            listing: {},
            params: {
                default: function () {
                    return [];
                }
            },
        },
        computed: {
            pages() {
                return Math.ceil(this.total / this.rowsPerPage);
            }
        },
        data() {
            return {
                headers: [],
                items: [],
                loading: false,
                pagination: {},
                rowsPerPage: 13,
                total: 0,
                addForm: {config: {}, dialog: false, errors: {}, data: {}, submitting: false},
            }
        },
        watch: {
            listing: function () {
                this.showListing(1);
            },
        },
        mounted: function () {
            this.showListing(1);
        },
        methods: {
            showListing(pageId) {
                let form = {data: this.params};
                this.loading = true;
                let url = `/ability/${this.serviceId}/listing/getData?listing=${this.listing}&rowsPerPage=${this.rowsPerPage}&pageId=${pageId}`;
                request(this.$http, 'post', url, form, function (response) {
                    this.headers = response.body.headers;
                    this.items = response.body.items;
                    this.total = response.body.total;
                    this.addForm.config = response.body.add;
                    this.loading = false;
                }.bind(this));
            },
            openEntityPage(entityName, entityId) {
                let serviceId = this.$route.params.serviceId;
                window.open(`/#/service/${serviceId}/${entityName}/${entityId}`);
            },
            showAddForm() {
                this.addForm.errors = {};
                this.addForm.config.fields.forEach(function (field) {
                    this.addForm.data[field.name] = field.default;
                }.bind(this));
                this.addForm.dialog = true;
            },
            submitAddForm() {
                this.addForm.submitting = true;
                let url = `/ability/${this.serviceId}/listing/add?listing=${this.listing}`;
                request(this.$http, 'post', url, this.addForm, function () {
                    this.showListing(1);
                    this.addForm.dialog = false;
                }.bind(this));
            },
        },
    }
</script>

<style>
    .crud__createButton {
        margin: 0 0 10px;
    }
</style>
