import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useCart } from '@/cart';
import { useLang } from '@/i18n';

function Icon({ path, className = 'w-6 h-6' }) {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            strokeWidth={1.7} stroke="currentColor" className={className}>
            <path strokeLinecap="round" strokeLinejoin="round" d={path} />
        </svg>
    );
}

const ICONS = {
    shop: 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.19a3 3 0 0 1-.62 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z',
    orders: 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    account: 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    cart: 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
};

export default function ShopLayout({ children }) {
    const { t, lang, toggle } = useLang();
    const { count } = useCart();
    const { props, url } = usePage();
    const business = props.business?.name || 'Carnicería';
    const flash = props.flash?.success;
    const [toast, setToast] = useState(null);

    useEffect(() => {
        if (flash) {
            setToast(flash);
            const id = setTimeout(() => setToast(null), 4000);
            return () => clearTimeout(id);
        }
    }, [flash]);

    const nav = [
        { href: '/tienda', key: 'shop', icon: ICONS.shop },
        { href: '/mis-pedidos', key: 'my_orders', icon: ICONS.orders },
        { href: '/perfil', key: 'account', icon: ICONS.account },
    ];
    const isActive = (href) => url.startsWith(href);

    return (
        <div className="min-h-dvh bg-gray-50 text-gray-900">
            <header className="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur pt-[env(safe-area-inset-top)]">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-2.5">
                    <Link href="/tienda" className="flex items-center gap-2.5">
                        <img src="/icons/pwa-192.png" alt="" className="h-9 w-9 rounded-xl shadow-sm" />
                        <span className="max-w-[10rem] truncate text-lg font-extrabold tracking-tight text-gray-900 sm:max-w-none">
                            {business}
                        </span>
                    </Link>
                    <div className="flex items-center gap-1">
                        <button onClick={toggle} aria-label="Cambiar idioma"
                            className="grid h-11 min-w-[2.75rem] place-items-center rounded-full border border-gray-300 px-3 text-xs font-bold text-gray-600 transition hover:bg-gray-100 active:scale-95">
                            {lang === 'es' ? 'EN' : 'ES'}
                        </button>
                        <Link href="/carrito" aria-label={t('cart')}
                            className="grid h-11 w-11 place-items-center rounded-full text-gray-700 transition hover:bg-gray-100 active:scale-95">
                            <span className="relative">
                                <Icon path={ICONS.cart} />
                                {count > 0 && (
                                    <span className="absolute -right-2.5 -top-2 grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-red-700 px-1 text-[11px] font-bold text-white ring-2 ring-white">
                                        {count}
                                    </span>
                                )}
                            </span>
                        </Link>
                    </div>
                </div>
            </header>

            {toast && (
                <div className="fixed inset-x-0 top-16 z-40 mx-auto max-w-5xl px-4">
                    <div className="animate-[fadeIn_.2s_ease] rounded-2xl bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-lg">
                        ✓ {toast}
                    </div>
                </div>
            )}

            <main className="mx-auto max-w-5xl px-4 pb-32 pt-4">{children}</main>

            <nav className="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 backdrop-blur pb-[env(safe-area-inset-bottom)]">
                <div className="mx-auto flex max-w-5xl">
                    {nav.map((item) => {
                        const active = isActive(item.href);
                        return (
                            <Link key={item.href} href={item.href}
                                className={`flex flex-1 flex-col items-center gap-0.5 py-2.5 text-xs font-semibold transition ${
                                    active ? 'text-red-700' : 'text-gray-400 hover:text-gray-600'
                                }`}>
                                <span className={`grid place-items-center rounded-full px-4 py-0.5 transition ${active ? 'bg-red-50' : ''}`}>
                                    <Icon path={item.icon} className="h-6 w-6" />
                                </span>
                                {t(item.key)}
                            </Link>
                        );
                    })}
                </div>
            </nav>
        </div>
    );
}
