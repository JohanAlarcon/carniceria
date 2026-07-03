import { Head, Link } from '@inertiajs/react';
import { money, qty as fmtQty } from '@/format';
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

export default function OrderShow({ order }) {
    const { t, lang } = useLang();
    const addr = [order.delivery_address_line1, order.delivery_address_line2,
        [order.delivery_city, order.delivery_state, order.delivery_zip].filter(Boolean).join(', ')]
        .filter(Boolean);

    return (
        <ShopLayout>
            <Head title={order.order_number} />

            <div className="mx-auto max-w-3xl">
            <Link href="/mis-pedidos" className="mb-3 inline-flex items-center text-sm text-gray-500">← {t('back')}</Link>

            <div className="rounded-2xl border border-gray-200 bg-white p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-bold">{order.order_number}</h1>
                        <p className="text-sm text-gray-500">
                            {order.placed_at ? new Date(order.placed_at).toLocaleString(lang) : ''}
                        </p>
                    </div>
                    <span className={`rounded-full px-3 py-1 text-sm font-semibold ${BADGE[order.status_color] || BADGE.gray}`}>
                        {order.status_label}
                    </span>
                </div>
            </div>

            <div className="mt-3 rounded-2xl border border-gray-200 bg-white p-4">
                <h2 className="mb-2 font-bold">{t('delivered_to')}</h2>
                {addr.map((line, i) => <p key={i} className="text-sm text-gray-700">{line}</p>)}
                {order.requested_date && (
                    <p className="mt-2 text-sm text-gray-500">
                        {t('requested_date')}: {new Date(order.requested_date).toLocaleDateString(lang)}
                    </p>
                )}
                {order.delivery_notes && <p className="mt-2 text-sm italic text-gray-500">“{order.delivery_notes}”</p>}
            </div>

            <div className="mt-3 rounded-2xl border border-gray-200 bg-white p-4">
                <h2 className="mb-3 font-bold">{t('order_summary')}</h2>
                <div className="space-y-2">
                    {order.items.map((it, i) => (
                        <div key={i} className="flex justify-between text-sm">
                            <span className="pr-2 text-gray-700">
                                {it.product_name}
                                {it.variant_label && <span className="text-gray-400"> · {it.variant_label}</span>}
                                <span className="block text-xs text-gray-400">
                                    {fmtQty(it.quantity)} {it.unit_label} × {money(it.unit_price)}
                                </span>
                            </span>
                            <span className="font-medium">{money(it.amount)}</span>
                        </div>
                    ))}
                </div>
                <div className="mt-3 flex justify-between border-t border-gray-100 pt-3 text-lg font-bold">
                    <span>{t('total')}</span>
                    <span>{money(order.total)}</span>
                </div>
            </div>
            </div>
        </ShopLayout>
    );
}
