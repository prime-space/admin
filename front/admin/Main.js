export default {
    request: function (vue, url, form, func, options = {}) {
        options.errorFunc = undefined === options.errorFunc ? function(){} : options.errorFunc;
        options.twofaFunc = undefined === options.twofaFunc ? function(){} : options.twofaFunc;
        form.loading = true;
        if (form.method === 'get') {
            let params = {};
            for (let i in form.filters) {
                params['f_' + i] = form.filters[i];
            }
            params['page'] = form.pagination.page;
            params['itemsPerPage'] = form.pagination.itemsPerPage;
            for (let i in form.sort) {
                params['s_' + form.sort[i]] = 1;
            }
            const buildURLQuery = obj =>
                Object.entries(obj)
                    .map(pair => pair.map(encodeURIComponent).join('='))
                    .join('&');
            url = url + '?' + buildURLQuery(params);
            vue.$http.get(url).then((response) => {
                form.loading = false;
                form.pagination.total = response.body.total;
                form.pagination.show = form.pagination.total > 0 ? 1 : 0;
                form.pagination.pageCount = Math.ceil(form.pagination.total / form.pagination.itemsPerPage);
                func(response);
            }).catch((response) => {
                form.loading = false;
                vue.$snack.danger({text: 'Error ' + response.status, button: 'close'});
                options.errorFunc();
            });
        } else {
            form.errors = {};
            let post = {'_token': config.token};
            for (let item in form.data) {
                let name = 'form[' + item + ']';
                post[name] = form.data[item];
            }
            vue.$http[form.method](url, post, {emulateJSON: true}).then((response) => {
                form.loading = false;
                func(response);
            }).catch((response) => {
                let errors = {};
                if (response.status === 412) {
                    options.twofaFunc(response);
                    errors = response.body.errors;
                    form.errors = errors;
                } else if (response.status !== 400) {
                    vue.$snack.danger({text: 'Error ' + response.status, button: 'close'});
                } else {
                    errors = response.body.errors;
                    form.errors = errors;
                }
                form.loading = false;
                options.errorFunc(response);
            });
        }
    },
    formatMoney: function (value) {
        return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    initForm: function (data = {}, method = 'post') {
        let form = {
            method: method,
            data: data,
            errors: {},
            loading: false,
            dialog: false,
        };
        form.reset = function () {
            this.data = {};
            this.errors = {};
        }.bind(form);
        return form;
    },
    initGetForm: function (filters = {}, itemsPerPage = 15) {
        let page = 1;
        return {
            method: 'get',
            filters: filters,
            sort: [],
            loading: false,
            pagination: {show: 0, total: 0, page: page, itemsPerPage: itemsPerPage, pageCount: 0, componentKey: 0},
            reset() {
                this.pagination.page = page;
                this.pagination.show = 0;
                this.pagination.itemsPerPage = itemsPerPage;
                this.pagination.componentKey += 1;
            },
        };
    },
};


