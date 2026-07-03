import { Head, Link } from '@inertiajs/react';
import { money } from '@/format';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';

const BADGE = {
    warning: 'bg-amber-100 text-amber-800',
    info: 'bg-sky-100 text-sky-800',
    primary: 'bg-red-100 text-red-800',
    success: 'bg-green-100 text-green-800',
    danger: 'bg-red-100 text-red-800',
    gray: 'bg-gray-100 text-gray-700',
};

export default function OrdersIndex({ orders }) {
    const { t, lang } = useLang();

    return (
        <ShopLayout>
            <Head title={t('my_orders')} />
            <h1 className="mb-4 text-xl font-bold">{t('my_orders')}</h1>

            {orders.length === 0 ? (
                <div className="rounded-2xl border border-gray-200 bg-white p-8 text-center">
                    <p className="text-gray-500">{t('no_orders')}</p>
                    <Link href="/tienda" className="mt-4 inline-block rounded-xl bg-red-700 px-5 py-2.5 font-semibold text-white">
                        {t('go_shop')}
                    </Link>
                </div>
            ) : (
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {orders.map((o) => (
                        <Link key={o.id} href={`/mis-pedidos/${o.id}`}
                            className="block rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                            <div className="flex items-center justify-between">
                                <span className="font-bold text-gray-900">{o.order_number}</span>
                                <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${BADGE[o.status_color] || BADGE.gray}`}>
                                    {o.status_label}
                                </span>
                            </div>
                            <div className="mt-2 flex items-center justify-between text-sm text-gray-500">
                                <span>
                                    {o.placed_at ? new Date(o.placed_at).toLocaleDateString(lang) : ''} · {o.items_count} {t('items')}
                                </span>
                                <span className="font-bold text-gray-900">{money(o.total)}</span>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </ShopLayout>
    );
}
