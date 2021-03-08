<template>
    <div>
        <crud :serviceId="$route.params.serviceId" listing="payment" :params="{shopId:this.shop[0].id}"></crud>
    </div>
</template>

<script>
    import Crud from './../../components/Crud';
    export default {
        components: {Crud},
        props: ['shop'],
    }
</script>
