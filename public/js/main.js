const ACCESS_RULE_ID_DASHBOARD = 1;
const ACCESS_RULE_ID_TICKETS = 2;
const ACCESS_RULE_ID_SERVICE = 3;
const ACCESS_RULE_ID_PAYOUT = 4;
const ACCESS_RULE_ID_EXECUTE_PAYMENT = 5;
const ACCESS_RULE_ID_REFUND = 6;

function request(http, method, url, form, func) {
    if (method === 'get') {
        http.get(url).then((response) => {
            func(response);
        }).catch(e => {});
    } else {
        form.errors = {};
        let post = {'_token': config.token};
        for (item in form.data) {
            let name = 'form[' + item + ']';
            post[name] = form.data[item];
        }
        http.post(url, post, {emulateJSON: true}).then((response) => {
            form.submitting = false;
            func(response);
        }).catch((errors) => {
            form.submitting = false;
            if (typeof errors.body.errors !== 'undefined') {
                form.errors = errors.body.errors;
            }
        });
    }

}

function sortByIsEnabled(methods) {
    return methods.sort((first, second) => {
        if (first.isEnabled === second.isEnabled) {
            if (first.id < second.id) {
                return -1;
            } else {
                return 1;
            }
        } else {
            if (first.isEnabled) {
                return -1;
            } else {
                return 1;
            }
        }
    });
}

function isAccessDenied(accessRuleId, idInList = null) {
    let userAccessRight = config.userAccessRights.find(userAccessRight => userAccessRight.id === accessRuleId);
    if (idInList !== null && userAccessRight !== undefined) {
        return !userAccessRight.list.includes(idInList);
    }
    return userAccessRight === undefined;
}

function formatMoney(value) {
    return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
