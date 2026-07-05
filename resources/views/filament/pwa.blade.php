{{-- Registro del service worker del panel + ajuste de la barra lateral en móvil. --}}
<script>
(function () {
    // 1) Registra el SW del panel (alcance /admin) para que sea instalable.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/admin-sw.js', { scope: '/admin' }).catch(function () {});
        });
    }

    // 2) En móvil, la barra lateral de Filament abre por defecto y tapa el
    //    contenido. La cerramos al cargar y tras navegar (en desktop no se toca).
    function closeSidebarOnMobile() {
        try {
            if (window.innerWidth < 1024 && window.Alpine && window.Alpine.store('sidebar')) {
                window.Alpine.store('sidebar').isOpen = false;
            }
        } catch (e) {}
    }
    document.addEventListener('alpine:initialized', closeSidebarOnMobile);
    document.addEventListener('livewire:navigated', closeSidebarOnMobile);
})();
</script>
