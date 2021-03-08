<template>
    <div>
        <v-layout row>
            <v-flex>
                <v-container>
                    <v-card>
                        <v-form ref="searchForm" @submit.prevent="submitSearchForm">
                            <v-layout align-start row>
                                <v-flex xs7 offset-xs1>
                                    <v-text-field :error-messages="searchForm.errors.query"
                                                  name="query" v-model="searchForm.data.query"
                                                  label=Search></v-text-field>
                                </v-flex>
                                <v-flex xs1 offset-xs1>
                                    <v-btn color="primary" @click="submitSearchForm" :disabled="searchForm.submitting">
                                        Search
                                    </v-btn>
                                </v-flex>
                            </v-layout>
                        </v-form>
                    </v-card>
                </v-container>
                <v-card>
                    <v-list v-if="entityTypesLengthIsGreaterThanZero()" two-line class="pa-0">
                        <div v-for="(entityType, entityTypeName, idx1) in entityTypes" :key="idx1">
                            <v-subheader><h4 class="subheader__text">{{ entityTypeName }}</h4></v-subheader>
                            <v-divider></v-divider>
                            <div v-for="(entity, idx2) in entityType.views" :key="idx2">
                                <v-list-item :class="{'list__tile--link': entityType.implemented}" @click="entityType.implemented ? showEntityPage(entityTypeName, entity.id) : ''">
                                    <v-list-item-content class="text--primary"
                                                         :class="{'text--disabled': !entityType.implemented}"
                                                         >
                                        <v-list-item-subtitle class="text--primary"
                                                               :class="{'text--disabled': !entityType.implemented}">
                                            #{{ entity.id }}
                                        </v-list-item-subtitle>
                                        <div>
                                            {{ entity.info }}
                                            <div v-if="entity.status" class="entity-data-inline">
                                                Status -
                                                <div :class="{'red--text': entity.isBlocked}">
                                                {{ entity.status }}
                                                </div>
                                            </div>
                                            <div v-if="entity.userPaymentId" class="entity-data-inline">
                                                User payment ID - {{ entity.userPaymentId }}
                                            </div>
                                        </div>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider v-if="!isLastEntityType(idx1 + 1)"></v-divider>
                            </div>
                        </div>
                    </v-list>
                    <v-card-text v-else :hidden="emptyListHidden" class="text-xs-center">
                        No data available
                    </v-card-text>
                </v-card>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                searchForm: {data: {query: ''}, errors: {}, submitting: false},
                entityTypes: {},
                emptyListHidden: true
            }
        },
        mounted: function () {
            this.$store.commit('changeTitle', `PrimeAdmin - Finder`); //@todo fix title
        },
        methods: {
            submitSearchForm() {
                let serviceId = this.$route.params.serviceId;
                this.searchForm.submitting = true;
                let url = `/ability/${serviceId}/finder/find`;
                request(this.$http, 'post', url, this.searchForm, function (response) {
                    this.entityTypes = response.body.entityTypes;
                    this.implementedAbilities = response.body.implementedAbilities;
                    if (response.body.entityTypes.length === 0) {
                        this.emptyListHidden = false;
                    }
                }.bind(this));
            },
            showEntityPage(entityTypeName, entityId) {
                let entityTypeNameLowerCased = entityTypeName.toLowerCase();
                let serviceId = this.$route.params.serviceId;
                this.$router.push(`/service/${serviceId}/${entityTypeNameLowerCased}/${entityId}`);
            },
            entityTypesLengthIsGreaterThanZero() {
                return Object.keys(this.entityTypes).length > 0;
            },
            isLastEntityType(entityTypeIndex) {
                return entityTypeIndex === Object.keys(this.entityTypes).length;
            }
        }
    }
</script>

<style>
    .subheader__text {
        color: rgba(0, 0, 0, .87);
    }

    .entity-data-inline {
        display: inline;
    }

    .entity-data-inline > div {
        display: inherit;
    }
</style>
