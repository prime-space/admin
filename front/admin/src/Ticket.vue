<template>
    <div>
        <TicketMessage v-for="message in messages" :key="message.id"
                        :text="message.text"
                        :author="message.author"
                        :serviceUserId="message.serviceUserId"
                        :serviceId="message.serviceId"
                        :createdTs="message.createdTs"
                        :isAnswer="message.isAnswer"
        ></TicketMessage>
        <v-container>
            <v-form ref="messageForm" @submit.prevent="submitApiForm">
                <v-flex xs10 offset-xs1>
                    <v-textarea outlined :error-messages="messageForm.errors.message" v-model="messageForm.data.message" label=Message></v-textarea>
                    <v-btn color="primary" @click="submitMessageForm" :disabled="messageForm.submitting">Send</v-btn>
                    <br>
                    <br>
                    <v-btn color="primary" dark slot="activator" @click="openChangeResponsibleForm()">Change responsible</v-btn>
                </v-flex>
            </v-form>
        </v-container>

        <v-layout row justify-left>
            <v-dialog v-model="changeResponsibleForm.dialog" persistent max-width="500px">
                <v-form ref="changeResponsibleForm" @submit.prevent="submitChangeResponsibleForm">
                    <v-card>
                        <v-card-title>
                            <span class="headline">Choose new responsible user</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container grid-list-md>
                                <v-layout wrap>
                                    <v-flex xs12>
                                        <v-select :error-messages="changeResponsibleForm.errors.responsibleUserId" name="responsibleUser" :items="compiledResponsibleUserChoiceList" v-model="changeResponsibleForm.data.responsibleUserId"></v-select>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue darken-1" text @click.native="changeResponsibleForm.dialog = false">close</v-btn>
                            <v-btn type="submit" name="save" color="blue darken-1" text :disabled="changeResponsibleForm.submitting">save</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-dialog>
        </v-layout>
    </div>
</template>

<script>
    import TicketMessage from './components/TicketMessage';
    export default {
        components: {
            TicketMessage
        },
        data() {
            return {
                messages: [],
                messageForm: {data: {message: '', ticketId: null}, errors: {}, submitting: false},
                changeResponsibleForm: {editId: null, data: {responsibleUserId: {}}, errors: {}, submitting: false, dialog: false},
                responsibleUserId: null
            }
        },
        mounted: function () {
            this.showMessages();
        },
        computed: {
            compiledResponsibleUserChoiceList() {
                return config.users
                    .filter(function(user) {
                        return user.id !== this.responsibleUserId;
                    }.bind(this))
                    .map(function(user) {
                        return {text: user.fullName, value: user.id};
                    });
            }
        },
        methods: {
            showMessages() {
                let url = `/ticket/${this.$route.params.id}`;
                request(this.$http, 'get', url, [], function (response) {
                    this.messages = response.body.messages;
                    this.responsibleUserId = response.body.responsibleUserId;
                    this.$store.commit('changeTitle', `Tickets - #${response.body.serviceId}: ${response.body.ticketSubject}`); //@todo fix title
                }.bind(this));
            },
            submitMessageForm() {
                this.messageForm.data.ticketId = this.$route.params.id;
                this.messageForm.submitting = true;
                let url = '/ticket/message';
                request(this.$http, 'post', url, this.messageForm, function () {
                    this.showMessages();
                    this.messageForm.data.message = '';
                }.bind(this));
            },
            openChangeResponsibleForm() {
                this.changeResponsibleForm.errors = {};
                this.changeResponsibleForm.dialog = true;
                this.changeResponsibleForm.data.responsibleUserId = null;
            },
            submitChangeResponsibleForm() {
                this.changeResponsibleForm.submitting = true;
                this.changeResponsibleForm.data.ticketId = this.$route.params.id;
                let url = '/ticket/responsible';
                request(this.$http, 'post', url, this.changeResponsibleForm, function (response) {
                    if (response.status === 200) {
                        this.showMessages();
                        this.changeResponsibleForm.dialog = false;
                    }
                }.bind(this));
            }
        }
    }
</script>

<style>
</style>
