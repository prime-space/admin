<template>
    <v-layout v-if="confirmer.active" row justify-center>
        <v-dialog :value="confirmer.active" persistent max-width="290">
            <v-card>
                <v-card-title class="headline">{{confirmer.title}}</v-card-title>
                <v-card-text v-if="confirmer.body">{{confirmer.body}}</v-card-text>
                <v-card-actions>
                    <v-btn color="darken-1" text @click.native="cancel">Отмена</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click.native="confirm" text>Подтвердить</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>

<script>
    module.exports = {
        data () {
            return {}
        },
        computed: {
            confirmer() {
                return this.$store.state.confirmer
            }
        },
        methods: {
            confirm() {
                this.confirmer.resolve(true)
                this.$store.commit('confirmer/DEACTIVATE')
            },
            cancel() {
                this.confirmer.resolve(false)
                this.$store.commit('confirmer/DEACTIVATE')
            }
        }
    }
</script>

<style>

</style>
