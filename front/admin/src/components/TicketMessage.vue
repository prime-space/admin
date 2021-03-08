<template>
    <div class="ticket-message" :class="{'ticket-message__answer': isAnswer}">
        <div>
            <div class="ticket-message__text">{{ text }}</div>
        </div>
        <div class="ticket-message__info">
            <div class="ticket-message__author" @click="!isAnswer ? openUserPage(serviceId, serviceUserId) : null"
                 :class="{'ticket-message__author_linked': !isAnswer}">{{ author }}</div>
            <div class="ticket-message__date">{{ createdTs }}</div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['text', 'author', 'serviceUserId', 'serviceId', 'createdTs', 'isAnswer'],
        methods: {
            openUserPage(serviceId, serviceUserId) {
                this.$router.push(`/service/${serviceId}/user/${serviceUserId}`);
            }
        }
    }
</script>

<style>
    .ticket-message {
        overflow-wrap: break-word;
        display: table;
        padding: 5px 10px 2px;
        margin-bottom: 10px;
        border-radius: 10px;
        background-color: #c3e7fc;
        white-space: pre-wrap;
    }

    .ticket-message__answer {
        background-color: #ececec;
    }

    .ticket-message > div {
        display: table-row;
    }

    .ticket-message > div > div {
        display: inline-block;
    }

    .ticket-message__info {
        color: #bbb;
        font-size: 90%;
    }

    .ticket-message__date {
        padding-left: 10px;
        float: right;
    }

    .ticket-message__author_linked {
        display: inline-block !important;
        cursor:pointer;
        color:blue;
        text-decoration:underline;
    }
    .ticket-message__text {
        word-break: normal;
    }
</style>
