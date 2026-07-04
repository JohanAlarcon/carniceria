@php
    $reverb = config('broadcasting.connections.reverb', []);
    $reverbKey = $reverb['key'] ?? null;
    // El navegador usa el bloque publico (dominio/wss) con fallback a options.*
    $reverbHost = data_get($reverb, 'public.host', data_get($reverb, 'options.host', '127.0.0.1'));
    $reverbPort = data_get($reverb, 'public.port', data_get($reverb, 'options.port', 8080));
    $reverbScheme = data_get($reverb, 'public.scheme', data_get($reverb, 'options.scheme', 'http'));
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

    const log = (...a) => { try { console.log('%c[pedidos-vivo]', 'color:#b91c1c;font-weight:700', ...a); } catch (e) {} };

    // ---------------------------------------------------------------------
    // Indicador visible de conexión (para que el carnicero sepa si está en vivo)
    // ---------------------------------------------------------------------
    const badge = document.createElement('div');
    badge.style.cssText = 'position:fixed;bottom:14px;right:14px;z-index:99998;display:flex;align-items:center;gap:7px;' +
        'background:#1f2937;color:#fff;padding:7px 12px;border-radius:999px;font:600 12px/1 sans-serif;' +
        'box-shadow:0 6px 18px rgba(0,0,0,.25);cursor:pointer;user-select:none;opacity:.92;';
    const dot = document.createElement('span');
    dot.style.cssText = 'width:9px;height:9px;border-radius:50%;background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.25);transition:.2s;';
    const label = document.createElement('span');
    label.textContent = 'Conectando…';
    badge.appendChild(dot); badge.appendChild(label);
    badge.title = 'Estado del aviso de pedidos en tiempo real. Toca para probar el sonido.';
    const mountBadge = () => { if (document.body && !badge.isConnected) document.body.appendChild(badge); };
    if (document.body) mountBadge(); else document.addEventListener('DOMContentLoaded', mountBadge);

    function setState(color, text) {
        dot.style.background = color;
        dot.style.boxShadow = '0 0 0 3px ' + color + '40';
        label.textContent = text;
    }

    // ---------------------------------------------------------------------
    // Audio: un único contexto, desbloqueado en la primera interacción del
    // usuario (los navegadores bloquean el sonido hasta que se toca la página)
    // ---------------------------------------------------------------------
    let ac = null, audioReady = false;
    function ensureAudio() {
        try {
            if (!ac) ac = new (window.AudioContext || window.webkitAudioContext)();
            if (ac.state === 'suspended') ac.resume();
            audioReady = ac.state === 'running';
        } catch (e) {}
        return audioReady;
    }
    function unlock() {
        ensureAudio();
        if (window.Notification && Notification.permission === 'default') {
            try { Notification.requestPermission(); } catch (e) {}
        }
        if (audioReady) log('audio habilitado');
    }
    ['click', 'keydown', 'touchstart', 'pointerdown'].forEach((ev) =>
        window.addEventListener(ev, unlock, { passive: true }));

    function beep() {
        if (!ensureAudio()) { log('sonido bloqueado: toca la pantalla una vez para habilitarlo'); return; }
        try {
            const play = (freq, start, dur) => {
                const o = ac.createOscillator(), g = ac.createGain();
                o.connect(g); g.connect(ac.destination);
                o.type = 'sine'; o.frequency.value = freq;
                g.gain.setValueAtTime(0.0001, ac.currentTime + start);
                g.gain.exponentialRampToValueAtTime(0.25, ac.currentTime + start + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, ac.currentTime + start + dur);
                o.start(ac.currentTime + start);
                o.stop(ac.currentTime + start + dur);
            };
            play(880, 0, 0.18); play(1320, 0.16, 0.22); play(1760, 0.34, 0.26);
        } catch (e) { log('error de sonido', e); }
    }

    function toast(text) {
        const el = document.createElement('div');
        el.textContent = '🔔 ' + text;
        el.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;background:#b91c1c;color:#fff;' +
            'padding:12px 18px;border-radius:12px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.25);' +
            'font-family:sans-serif;font-size:14px;max-width:80vw;';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 8000);
    }

    function refreshTables() {
        try {
            if (window.Livewire && typeof window.Livewire.all === 'function') {
                window.Livewire.all().forEach((c) => { try { c.call('$refresh'); } catch (e) {} });
            }
        } catch (e) {}
    }

    function onOrder(data) {
        log('pedido recibido', data);
        const num = (data && data.order_number) ? data.order_number : '';
        const biz = (data && data.business) ? ' · ' + data.business : '';
        beep();
        toast('Nuevo pedido ' + num + biz);
        if (window.Notification && Notification.permission === 'granted') {
            try { new Notification('Nuevo pedido ' + num, { body: (data && data.business) || '', icon: '/icons/pwa-192.png' }); } catch (e) {}
        }
        refreshTables();
    }

    // ---------------------------------------------------------------------
    // Conexión Reverb (protocolo Pusher)
    // ---------------------------------------------------------------------
    function connect() {
        if (!window.Pusher) { setState('#ef4444', 'Sin librería'); log('Pusher no cargó'); return; }
        let pusher;
        try {
            pusher = new Pusher(KEY, {
                wsHost: HOST, wsPort: PORT, wssPort: PORT,
                forceTLS: TLS, enabledTransports: ['ws', 'wss'],
                disableStats: true, cluster: 'mt1',
                authEndpoint: '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': CSRF } },
            });
        } catch (e) { setState('#ef4444', 'Sin conexión'); log('error creando Pusher', e); return; }

        pusher.connection.bind('state_change', (s) => {
            log('estado:', s.previous, '→', s.current);
            if (s.current === 'connected') setState('#22c55e', 'En vivo');
            else if (s.current === 'connecting') setState('#f59e0b', 'Conectando…');
            else if (s.current === 'unavailable') setState('#ef4444', 'Sin conexión');
            else if (s.current === 'failed') setState('#ef4444', 'WS falló :' + PORT);
            else if (s.current === 'disconnected') setState('#f59e0b', 'Reconectando…');
        });
        pusher.connection.bind('error', (e) => log('error de conexión', e));

        const ch = pusher.subscribe('private-admin-orders');
        ch.bind('pusher:subscription_succeeded', () => { setState('#22c55e', 'En vivo'); log('suscrito a private-admin-orders'); });
        ch.bind('pusher:subscription_error', (e) => { setState('#ef4444', 'Auth falló'); log('error de suscripción (¿login/permiso?)', e); });
        ch.bind('OrderCreated', onOrder);
    }

    function loadPusher() {
        if (window.Pusher) { connect(); return; }
        const tryLoad = (src, next) => {
            const s = document.createElement('script');
            s.src = src; s.onload = connect;
            s.onerror = () => { log('no cargó', src); if (next) next(); else setState('#ef4444', 'Sin librería'); };
            document.head.appendChild(s);
        };
        // Primero local (mismo origen), CDN como respaldo
        tryLoad('/js/pusher.min.js', () => tryLoad('https://js.pusher.com/8.2.0/pusher.min.js', null));
    }

    loadPusher();
})();
</script>
@endif
