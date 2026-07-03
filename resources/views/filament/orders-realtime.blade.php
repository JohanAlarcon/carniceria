@php
    $reverb = config('broadcasting.connections.reverb', []);
    $reverbKey = $reverb['key'] ?? null;
    $reverbHost = data_get($reverb, 'options.host', '127.0.0.1');
    $reverbPort = data_get($reverb, 'options.port', 8080);
    $reverbScheme = data_get($reverb, 'options.scheme', 'http');
@endphp
@if ($reverbKey)
<script>
(function () {
    const KEY = @json($reverbKey);
    const HOST = @json($reverbHost);
    const PORT = @json((int) $reverbPort);
    const TLS = @json($reverbScheme === 'https');
    const CSRF = @json(csrf_token());

    if (window.__ordersRealtime) return;
    window.__ordersRealtime = true;

    if (window.Notification && Notification.permission === 'default') {
        try { Notification.requestPermission(); } catch (e) {}
    }

    function beep() {
        try {
            const ac = new (window.AudioContext || window.webkitAudioContext)();
            const play = (freq, start, dur) => {
                const o = ac.createOscillator();
                const g = ac.createGain();
                o.connect(g); g.connect(ac.destination);
                o.type = 'sine'; o.frequency.value = freq;
                g.gain.setValueAtTime(0.0001, ac.currentTime + start);
                g.gain.exponentialRampToValueAtTime(0.2, ac.currentTime + start + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, ac.currentTime + start + dur);
                o.start(ac.currentTime + start);
                o.stop(ac.currentTime + start + dur);
            };
            play(880, 0, 0.18);
            play(1320, 0.16, 0.22);
            setTimeout(() => ac.close(), 800);
        } catch (e) {}
    }

    function toast(text) {
        let el = document.createElement('div');
        el.textContent = '🔔 ' + text;
        el.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;background:#b91c1c;color:#fff;' +
            'padding:12px 18px;border-radius:12px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.25);' +
            'font-family:sans-serif;font-size:14px;';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 6000);
    }

    function refreshTables() {
        try {
            if (window.Livewire && window.Livewire.all) {
                window.Livewire.all().forEach((c) => { try { c.call('$refresh'); } catch (e) {} });
            }
        } catch (e) {}
    }

    function onOrder(data) {
        const num = (data && data.order_number) ? data.order_number : '';
        const biz = (data && data.business) ? ' · ' + data.business : '';
        beep();
        toast('Nuevo pedido ' + num + biz);
        if (window.Notification && Notification.permission === 'granted') {
            try { new Notification('Nuevo pedido ' + num, { body: (data && data.business) || '', icon: '/icons/pwa-192.png' }); } catch (e) {}
        }
        refreshTables();
    }

    function connect() {
        try {
            const pusher = new Pusher(KEY, {
                wsHost: HOST, wsPort: PORT, wssPort: PORT,
                forceTLS: TLS, enabledTransports: ['ws', 'wss'],
                disableStats: true, cluster: 'mt1',
                authEndpoint: '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': CSRF } },
            });
            const ch = pusher.subscribe('private-admin-orders');
            ch.bind('OrderCreated', onOrder);
        } catch (e) {}
    }

    if (window.Pusher) {
        connect();
    } else {
        const s = document.createElement('script');
        s.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
        s.onload = connect;
        document.head.appendChild(s);
    }
})();
</script>
@endif
