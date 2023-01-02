window._ = require('lodash');
window.Popper = require('popper.js').default;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    let to = localStorage.getItem('token');
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
    //window.axios.defaults.headers.common = {'Authorization': `Bearer ${to}`}

} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}


/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.io = require('pusher-js');

//const client = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'f14fa5935a785a9e6ea0',
    cluster: 'mt1',
    logToConsole: true,
    //client: client
    //host: `https://${process.env.MIX_ECHO_HOST}:${process.env.MIX_ECHO_PORT}`,
    //auth: {
    //    headers: {
    //        Authorization: 'Bearer ' + window.localStorage.getItem("token")
    //    }
    //}
})
