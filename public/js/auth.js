!function(t){var e={};function n(o){if(e[o])return e[o].exports;var r=e[o]={i:o,l:!1,exports:{}};return t[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=t,n.c=e,n.d=function(t,e,o){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)n.d(o,r,function(e){return t[e]}.bind(null,r));return o},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="/js",n(n.s=38)}({15:function(t,e){},2:function(t,e,n){"use strict";function o(t){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}
        /*!
         * vue-resource v1.5.1
         * https://github.com/pagekit/vue-resource
         * Released under the MIT License.
         */function r(t){this.state=2,this.value=void 0,this.deferred=[];var e=this;try{t(function(t){e.resolve(t)},function(t){e.reject(t)})}catch(t){e.reject(t)}}r.reject=function(t){return new r(function(e,n){n(t)})},r.resolve=function(t){return new r(function(e,n){e(t)})},r.all=function(t){return new r(function(e,n){var o=0,i=[];function u(n){return function(r){i[n]=r,(o+=1)===t.length&&e(i)}}0===t.length&&e(i);for(var s=0;s<t.length;s+=1)r.resolve(t[s]).then(u(s),n)})},r.race=function(t){return new r(function(e,n){for(var o=0;o<t.length;o+=1)r.resolve(t[o]).then(e,n)})};var i=r.prototype;function u(t,e){t instanceof Promise?this.promise=t:this.promise=new Promise(t.bind(e)),this.context=e}i.resolve=function(t){var e=this;if(2===e.state){if(t===e)throw new TypeError("Promise settled with itself.");var n=!1;try{var r=t&&t.then;if(null!==t&&"object"===o(t)&&"function"==typeof r)return void r.call(t,function(t){n||e.resolve(t),n=!0},function(t){n||e.reject(t),n=!0})}catch(t){return void(n||e.reject(t))}e.state=0,e.value=t,e.notify()}},i.reject=function(t){var e=this;if(2===e.state){if(t===e)throw new TypeError("Promise settled with itself.");e.state=1,e.value=t,e.notify()}},i.notify=function(){var t=this;a(function(){if(2!==t.state)for(;t.deferred.length;){var e=t.deferred.shift(),n=e[0],o=e[1],r=e[2],i=e[3];try{0===t.state?r("function"==typeof n?n.call(void 0,t.value):t.value):1===t.state&&("function"==typeof o?r(o.call(void 0,t.value)):i(t.value))}catch(e){i(e)}}},void 0)},i.then=function(t,e){var n=this;return new r(function(o,r){n.deferred.push([t,e,o,r]),n.notify()})},i.catch=function(t){return this.then(void 0,t)},"undefined"==typeof Promise&&(window.Promise=r),u.all=function(t,e){return new u(Promise.all(t),e)},u.resolve=function(t,e){return new u(Promise.resolve(t),e)},u.reject=function(t,e){return new u(Promise.reject(t),e)},u.race=function(t,e){return new u(Promise.race(t),e)};var s=u.prototype;s.bind=function(t){return this.context=t,this},s.then=function(t,e){return t&&t.bind&&this.context&&(t=t.bind(this.context)),e&&e.bind&&this.context&&(e=e.bind(this.context)),new u(this.promise.then(t,e),this.context)},s.catch=function(t){return t&&t.bind&&this.context&&(t=t.bind(this.context)),new u(this.promise.catch(t),this.context)},s.finally=function(t){return this.then(function(e){return t.call(this),e},function(e){return t.call(this),Promise.reject(e)})};var a,c={}.hasOwnProperty,f=[].slice,p=!1,l="undefined"!=typeof window;function h(t){"undefined"!=typeof console&&p&&console.warn("[VueResource warn]: "+t)}function d(t){return t?t.replace(/^\s*|\s*$/g,""):""}function m(t){return t?t.toLowerCase():""}var y=Array.isArray;function v(t){return"string"==typeof t}function b(t){return"function"==typeof t}function g(t){return null!==t&&"object"===o(t)}function w(t){return g(t)&&Object.getPrototypeOf(t)==Object.prototype}function j(t,e,n){var o=u.resolve(t);return arguments.length<2?o:o.then(e,n)}function T(t,e,n){return b(n=n||{})&&(n=n.call(e)),O(t.bind({$vm:e,$options:n}),t,{$options:n})}function x(t,e){var n,o;if(y(t))for(n=0;n<t.length;n++)e.call(t[n],t[n],n);else if(g(t))for(o in t)c.call(t,o)&&e.call(t[o],t[o],o);return t}var E=Object.assign||function(t){return f.call(arguments,1).forEach(function(e){P(t,e)}),t};function O(t){return f.call(arguments,1).forEach(function(e){P(t,e,!0)}),t}function P(t,e,n){for(var o in e)n&&(w(e[o])||y(e[o]))?(w(e[o])&&!w(t[o])&&(t[o]={}),y(e[o])&&!y(t[o])&&(t[o]=[]),P(t[o],e[o],n)):void 0!==e[o]&&(t[o]=e[o])}function S(t){return null!=t}function C(t){return";"===t||"&"===t||"?"===t}function $(t,e,n){return e="+"===t||"#"===t?U(e):encodeURIComponent(e),n?encodeURIComponent(n)+"="+e:e}function U(t){return t.split(/(%[0-9A-Fa-f]{2})/g).map(function(t){return/%[0-9A-Fa-f]/.test(t)||(t=encodeURI(t)),t}).join("")}function A(t,e){var n,o=this||{},r=t;return v(t)&&(r={url:t,params:e}),r=O({},A.options,o.$options,r),A.transforms.forEach(function(t){v(t)&&(t=A.transform[t]),b(t)&&(n=function(t,e,n){return function(o){return t.call(n,o,e)}}(t,n,o.$vm))}),n(r)}function R(t){return new u(function(e){var n=new XDomainRequest,o=function(o){var r=o.type,i=0;"load"===r?i=200:"error"===r&&(i=500),e(t.respondWith(n.responseText,{status:i}))};t.abort=function(){return n.abort()},n.open(t.method,t.getUrl()),t.timeout&&(n.timeout=t.timeout),n.onload=o,n.onabort=o,n.onerror=o,n.ontimeout=o,n.onprogress=function(){},n.send(t.getBody())})}A.options={url:"",root:null,params:{}},A.transform={template:function(t){var e=[],n=function(t,e,n){var o=function(t){var e=["+","#",".","/",";","?","&"],n=[];return{vars:n,expand:function(o){return t.replace(/\{([^{}]+)\}|([^{}]+)/g,function(t,r,i){if(r){var u=null,s=[];if(-1!==e.indexOf(r.charAt(0))&&(u=r.charAt(0),r=r.substr(1)),r.split(/,/g).forEach(function(t){var e=/([^:*]*)(?::(\d+)|(\*))?/.exec(t);s.push.apply(s,function(t,e,n,o){var r=t[n],i=[];if(S(r)&&""!==r)if("string"==typeof r||"number"==typeof r||"boolean"==typeof r)r=r.toString(),o&&"*"!==o&&(r=r.substring(0,parseInt(o,10))),i.push($(e,r,C(e)?n:null));else if("*"===o)Array.isArray(r)?r.filter(S).forEach(function(t){i.push($(e,t,C(e)?n:null))}):Object.keys(r).forEach(function(t){S(r[t])&&i.push($(e,r[t],t))});else{var u=[];Array.isArray(r)?r.filter(S).forEach(function(t){u.push($(e,t))}):Object.keys(r).forEach(function(t){S(r[t])&&(u.push(encodeURIComponent(t)),u.push($(e,r[t].toString())))}),C(e)?i.push(encodeURIComponent(n)+"="+u.join(",")):0!==u.length&&i.push(u.join(","))}else";"===e?i.push(encodeURIComponent(n)):""!==r||"&"!==e&&"?"!==e?""===r&&i.push(""):i.push(encodeURIComponent(n)+"=");return i}(o,u,e[1],e[2]||e[3])),n.push(e[1])}),u&&"+"!==u){var a=",";return"?"===u?a="&":"#"!==u&&(a=u),(0!==s.length?u:"")+s.join(a)}return s.join(",")}return U(i)})}}}(t),r=o.expand(e);return n&&n.push.apply(n,o.vars),r}(t.url,t.params,e);return e.forEach(function(e){delete t.params[e]}),n},query:function(t,e){var n=Object.keys(A.options.params),o={},r=e(t);return x(t.params,function(t,e){-1===n.indexOf(e)&&(o[e]=t)}),(o=A.params(o))&&(r+=(-1==r.indexOf("?")?"?":"&")+o),r},root:function(t,e){var n=e(t);return v(t.root)&&!/^(https?:)?\//.test(n)&&(n=function(t,e){return t?t.replace(new RegExp("[/]+$"),""):t}(t.root)+"/"+n),n}},A.transforms=["template","query","root"],A.params=function(t){var e=[],n=encodeURIComponent;return e.add=function(t,e){b(e)&&(e=e()),null===e&&(e=""),this.push(n(t)+"="+n(e))},function t(e,n,o){var r,i=y(n),u=w(n);x(n,function(n,s){r=g(n)||y(n),o&&(s=o+"["+(u||r?s:"")+"]"),!o&&i?e.add(n.name,n.value):r?t(e,n,s):e.add(s,n)})}(e,t),e.join("&").replace(/%20/g,"+")},A.parse=function(t){var e=document.createElement("a");return document.documentMode&&(e.href=t,t=e.href),e.href=t,{href:e.href,protocol:e.protocol?e.protocol.replace(/:$/,""):"",port:e.port,host:e.host,hostname:e.hostname,pathname:"/"===e.pathname.charAt(0)?e.pathname:"/"+e.pathname,search:e.search?e.search.replace(/^\?/,""):"",hash:e.hash?e.hash.replace(/^#/,""):""}};var k=l&&"withCredentials"in new XMLHttpRequest;function I(t){return new u(function(e){var n,o,r=t.jsonp||"callback",i=t.jsonpCallback||"_jsonp"+Math.random().toString(36).substr(2),u=null;n=function(n){var r=n.type,s=0;"load"===r&&null!==u?s=200:"error"===r&&(s=500),s&&window[i]&&(delete window[i],document.body.removeChild(o)),e(t.respondWith(u,{status:s}))},window[i]=function(t){u=JSON.stringify(t)},t.abort=function(){n({type:"abort"})},t.params[r]=i,t.timeout&&setTimeout(t.abort,t.timeout),(o=document.createElement("script")).src=t.getUrl(),o.type="text/javascript",o.async=!0,o.onload=n,o.onerror=n,document.body.appendChild(o)})}function L(t){return(t.client||(l?function(t){return new u(function(e){var n=new XMLHttpRequest,o=function(o){var r=t.respondWith("response"in n?n.response:n.responseText,{status:1223===n.status?204:n.status,statusText:1223===n.status?"No Content":d(n.statusText)});x(d(n.getAllResponseHeaders()).split("\n"),function(t){r.headers.append(t.slice(0,t.indexOf(":")),t.slice(t.indexOf(":")+1))}),e(r)};t.abort=function(){return n.abort()},n.open(t.method,t.getUrl(),!0),t.timeout&&(n.timeout=t.timeout),t.responseType&&"responseType"in n&&(n.responseType=t.responseType),(t.withCredentials||t.credentials)&&(n.withCredentials=!0),t.crossOrigin||t.headers.set("X-Requested-With","XMLHttpRequest"),b(t.progress)&&"GET"===t.method&&n.addEventListener("progress",t.progress),b(t.downloadProgress)&&n.addEventListener("progress",t.downloadProgress),b(t.progress)&&/^(POST|PUT)$/i.test(t.method)&&n.upload.addEventListener("progress",t.progress),b(t.uploadProgress)&&n.upload&&n.upload.addEventListener("progress",t.uploadProgress),t.headers.forEach(function(t,e){n.setRequestHeader(e,t)}),n.onload=o,n.onabort=o,n.onerror=o,n.ontimeout=o,n.send(t.getBody())})}:function(t){var e=n(15);return new u(function(n){var o,r=t.getUrl(),i=t.getBody(),u=t.method,s={};t.headers.forEach(function(t,e){s[e]=t}),e(r,{body:i,method:u,headers:s}).then(o=function(e){var o=t.respondWith(e.body,{status:e.statusCode,statusText:d(e.statusMessage)});x(e.headers,function(t,e){o.headers.set(e,t)}),n(o)},function(t){return o(t.response)})})}))(t)}var M=function(t){var e=this;this.map={},x(t,function(t,n){return e.append(n,t)})};function H(t,e){return Object.keys(t).reduce(function(t,n){return m(e)===m(n)?n:t},null)}M.prototype.has=function(t){return null!==H(this.map,t)},M.prototype.get=function(t){var e=this.map[H(this.map,t)];return e?e.join():null},M.prototype.getAll=function(t){return this.map[H(this.map,t)]||[]},M.prototype.set=function(t,e){this.map[function(t){if(/[^a-z0-9\-#$%&'*+.^_`|~]/i.test(t))throw new TypeError("Invalid character in header field name");return d(t)}(H(this.map,t)||t)]=[d(e)]},M.prototype.append=function(t,e){var n=this.map[H(this.map,t)];n?n.push(d(e)):this.set(t,e)},M.prototype.delete=function(t){delete this.map[H(this.map,t)]},M.prototype.deleteAll=function(){this.map={}},M.prototype.forEach=function(t,e){var n=this;x(this.map,function(o,r){x(o,function(o){return t.call(e,o,r,n)})})};var q=function(t,e){var n=e.url,o=e.headers,r=e.status,i=e.statusText;this.url=n,this.ok=200<=r&&r<300,this.status=r||0,this.statusText=i||"",this.headers=new M(o),v(this.body=t)?this.bodyText=t:function(t){return"undefined"!=typeof Blob&&t instanceof Blob}(t)&&function(t){return 0===t.type.indexOf("text")||-1!==t.type.indexOf("json")}(this.bodyBlob=t)&&(this.bodyText=function(t){return new u(function(e){var n=new FileReader;n.readAsText(t),n.onload=function(){e(n.result)}})}(t))};q.prototype.blob=function(){return j(this.bodyBlob)},q.prototype.text=function(){return j(this.bodyText)},q.prototype.json=function(){return j(this.text(),function(t){return JSON.parse(t)})},Object.defineProperty(q.prototype,"data",{get:function(){return this.body},set:function(t){this.body=t}});var B=function(t){this.body=null,this.params={},E(this,t,{method:function(t){return t?t.toUpperCase():""}(t.method||"GET")}),this.headers instanceof M||(this.headers=new M(this.headers))};B.prototype.getUrl=function(){return A(this)},B.prototype.getBody=function(){return this.body},B.prototype.respondWith=function(t,e){return new q(t,E(e||{},{url:this.getUrl()}))};var N={"Content-Type":"application/json;charset=utf-8"};function _(t){var e=this||{},n=function(t){var e=[L],n=[];function r(r){for(;e.length;){var i=e.pop();if(b(i)){var s=void 0,a=void 0;if(g(s=i.call(t,r,function(t){return a=t})||a))return new u(function(e,o){n.forEach(function(e){s=j(s,function(n){return e.call(t,n)||n},o)}),j(s,e,o)},t);b(s)&&n.unshift(s)}else h("Invalid interceptor of type "+o(i)+", must be a function")}}return g(t)||(t=null),r.use=function(t){e.push(t)},r}(e.$vm);return function(t){f.call(arguments,1).forEach(function(e){for(var n in e)void 0===t[n]&&(t[n]=e[n])})}(t||{},e.$options,_.options),_.interceptors.forEach(function(t){v(t)&&(t=_.interceptor[t]),b(t)&&n.use(t)}),n(new B(t)).then(function(t){return t.ok?t:u.reject(t)},function(t){return t instanceof Error&&function(t){"undefined"!=typeof console&&console.error(t)}(t),u.reject(t)})}function J(t,e,n,o){var r=this||{},i={};return x(n=E({},J.actions,n),function(n,u){n=O({url:t,params:E({},e)},o,n),i[u]=function(){return(r.$http||_)(function(t,e){var n,o=E({},t),r={};switch(e.length){case 2:r=e[0],n=e[1];break;case 1:/^(POST|PUT|PATCH)$/i.test(o.method)?n=e[0]:r=e[0];break;case 0:break;default:throw"Expected up to 2 arguments [params, body], got "+e.length+" arguments"}return o.body=n,o.params=E({},o.params,r),o}(n,arguments))}}),i}function V(t){V.installed||(function(t){var e=t.config,n=t.nextTick;a=n,p=e.debug||!e.silent}(t),t.url=A,t.http=_,t.resource=J,t.Promise=u,Object.defineProperties(t.prototype,{$url:{get:function(){return T(t.url,this,this.$options.url)}},$http:{get:function(){return T(t.http,this,this.$options.http)}},$resource:{get:function(){return t.resource.bind(this)}},$promise:{get:function(){var e=this;return function(n){return new t.Promise(n,e)}}}}))}_.options={},_.headers={put:N,post:N,patch:N,delete:N,common:{Accept:"application/json, text/plain, */*"},custom:{}},_.interceptor={before:function(t){b(t.before)&&t.before.call(this,t)},method:function(t){t.emulateHTTP&&/^(PUT|PATCH|DELETE)$/i.test(t.method)&&(t.headers.set("X-HTTP-Method-Override",t.method),t.method="POST")},jsonp:function(t){"JSONP"==t.method&&(t.client=I)},json:function(t){var e=t.headers.get("Content-Type")||"";return g(t.body)&&0===e.indexOf("application/json")&&(t.body=JSON.stringify(t.body)),function(t){return t.bodyText?j(t.text(),function(e){if(0===(t.headers.get("Content-Type")||"").indexOf("application/json")||function(t){var e=t.match(/^\s*(\[|\{)/);return e&&{"[":/]\s*$/,"{":/}\s*$/}[e[1]].test(t)}(e))try{t.body=JSON.parse(e)}catch(e){t.body=null}else t.body=e;return t}):t}},form:function(t){!function(t){return"undefined"!=typeof FormData&&t instanceof FormData}(t.body)?g(t.body)&&t.emulateJSON&&(t.body=A.params(t.body),t.headers.set("Content-Type","application/x-www-form-urlencoded")):t.headers.delete("Content-Type")},header:function(t){x(E({},_.headers.common,t.crossOrigin?{}:_.headers.custom,_.headers[m(t.method)]),function(e,n){t.headers.has(n)||t.headers.set(n,e)})},cors:function(t){if(l){var e=A.parse(location.href),n=A.parse(t.getUrl());n.protocol===e.protocol&&n.host===e.host||(t.crossOrigin=!0,t.emulateHTTP=!1,k||(t.client=R))}}},_.interceptors=["before","method","jsonp","json","form","header","cors"],["get","delete","head","jsonp"].forEach(function(t){_[t]=function(e,n){return this(E(n||{},{url:e,method:t}))}}),["post","put","patch"].forEach(function(t){_[t]=function(e,n,o){return this(E(o||{},{url:e,method:t,body:n}))}}),J.actions={get:{method:"GET"},save:{method:"POST"},query:{method:"GET"},update:{method:"PUT"},remove:{method:"DELETE"},delete:{method:"DELETE"}},"undefined"!=typeof window&&window.Vue&&window.Vue.use(V),e.a=V},38:function(t,e,n){"use strict";n.r(e);var o=n(2);Vue.use(Vuetify),Vue.use(o.a),new Vue({el:"#app",data:function(){return{drawer:null}},props:{source:String,info:[]},methods:{submit:function(t){var e=this,n={},o=this.submitted=!0,r=!(this.info=[]),i=void 0;try{for(var u,s=t.target.elements[Symbol.iterator]();!(o=(u=s.next()).done);o=!0){var a=u.value;n[a.name]=a.value}}catch(t){r=!0,i=t}finally{try{o||null==s.return||s.return()}finally{if(r)throw i}}this.$http.post("/signIn",n,{emulateJSON:!0}).then(function(t){e.submitted=!1,window.location.href="/"+window.location.hash}).catch(function(t){e.submitted=!1,void 0!==t.body.errors&&(e.info=t.body.errors)})}}})}});
//# sourceMappingURL=auth.js.map