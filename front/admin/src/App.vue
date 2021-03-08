<template>
    <v-app id="inspire">
        <v-navigation-drawer
                fixed
                v-model="drawer"
                app
        >
            <v-list dense>
                <v-list-item @click="signOut">
                    <v-list-item-action>
                        <v-icon>mdi-exit-run</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Sign Out</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-divider></v-divider>
                <div v-for="(section, index) in grantedSections" :key="index">
                    <v-list-item :to="section.route">
                        <v-list-item-action>
                            <v-icon>mdi-{{section.icon}}</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>{{section.title}}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </div>
                <v-divider></v-divider>
                <v-list-group
                        v-for="service in sidebarServices"
                        v-model="service.active"
                        :key="service.id"
                        no-action
                >
                    <v-list-item slot="activator">
                        <v-list-item-content>
                            <v-list-item-title>{{service.domain}}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item
                            v-for="ability in service.abilities"
                            :key="ability"
                            @click="$router.push(`/service/${service.id}/${ability}`)"
                    >
                        <v-list-item-content>
                            <v-list-item-title>{{ability}}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list-group>
            </v-list>
        </v-navigation-drawer>
        <v-app-bar color="indigo" dark fixed app>
            <v-btn @click.stop="drawer = !drawer" icon>
                <v-icon>mdi-menu</v-icon>
            </v-btn>
            <v-toolbar-title>{{title}}</v-toolbar-title>
        </v-app-bar>
        <v-content>
            <v-container fluid>
                <router-view></router-view>
            </v-container>
        </v-content>
        <v-footer color="indigo" app>
            <span class="white--text">PRIMEAREA SYSTEM OÜ &copy; 2018</span>
        </v-footer>
    </v-app>
</template>

<script>
    export default {
        data() {
            return {
                drawer: null,
                sections: [
                    {id: 1, icon: 'home', title: 'Dashboard', route: '/', accessRuleId: ACCESS_RULE_ID_DASHBOARD},
                    {
                        id: 2,
                        icon: 'help',
                        title: 'Tickets',
                        route: '/tickets',
                        accessRuleId: ACCESS_RULE_ID_TICKETS
                    },
                    {
                        id: 3,
                        icon: 'currency-usd',
                        title: 'Payout State',
                        route: '/payouts',
                        accessRuleId: ACCESS_RULE_ID_PAYOUT
                    },
                ],
            }
        },
        computed: {
            title() {
                return this.$store.state.pageTitle
            },
            sidebarServices() {
                let services = [];
                for (let key in config.services) {
                    let service = config.services[key];
                    if (service.isGranted && null !== service.appType) {
                        service['active'] = true;
                        services.push(service);
                    }
                }

                return services;
            },
            grantedSections() {
                let sections = [];
                for (let i in this.sections) {
                    if (!isAccessDenied(this.sections[i].accessRuleId)) {
                        sections.push(this.sections[i]);
                    }
                }

                return sections;
            },
        },
        methods: {
            signOut() {
                window.location.href = '/signOut'
            },
            isAccessDenied(accessRuleId) {
                return isAccessDenied(accessRuleId);
            }
        },
    }
</script>

<style>
    header.v-sheet {
        max-height: 64px;
    }

    .text_selectable {
        user-select: text;
    }

    .text__bold {
        font-weight: bolder !important;
    }

    .row-text__disabled {
        color: rgba(0, 0, 0, .54);
    }
</style>
