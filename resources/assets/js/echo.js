import Echo from 'laravel-echo'

window.io = require('socket.io-client');

window.Echo = new Echo({
    broadcaster: AppConfig.Broadcaster,
    key: AppConfig.PusherToken === '' ? null : AppConfig.PusherToken,
    host: { path: '/socket.io' }
});
