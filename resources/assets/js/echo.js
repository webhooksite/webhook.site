import Echo from 'laravel-echo'

let echoConfig = {
    broadcaster: AppConfig.Broadcaster,
    key: AppConfig.PusherToken === '' ? null : AppConfig.PusherToken,
};

if (AppConfig.EchoHostMode === 'port') {
    echoConfig.host = window.location.hostname + ':6001';
} else if (AppConfig.EchoHostMode === 'path') {
    echoConfig.host = { host: '/socket.io' };
}

window.Echo = new Echo(echoConfig);
