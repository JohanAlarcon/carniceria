import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCart } from '@/cart';
import { money } from '@/format';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';

export default function Checkout() {
    const { t, name } = useLang();
    const { items, subtotal, clear } = useCart();
    const customer = usePage().props.auth?.customer || {};

    const [form, setForm] = useState({
        delivery_address_line1: customer.address_line1 || '',
        delivery_address_line2: customer.address_line2 || '',
        delivery_city: customer.city || '',
        delivery_state: customer.state || 'NY',
        delivery_zip: customer.zip || '',
        delivery_phone: customer.phone || '',
        requested_date: '',
        delivery_notes: '',
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});

    const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

    const submit = (e) => {
        e.preventDefault();
        setProcessing(true);
        router.post('/pedidos', {
            ...form,
            items: items.map((i) => ({ variant_id: i.variant_id, quantity: i.quantity })),
        }, {
            onSuccess: () => clear(),
            onError: (err) => setErrors(err),
            onFinish: () => setProcessing(false),
        });
    };

    if (items.length === 0) {
        return (
            <ShopLayout>
                <Head title={t('checkout')} />
                <div className="rounded-2xl border border-gray-200 bg-white p-8 text-center">
                    <p className="text-gray-500">{t('empty_cart')}</p>
                    <Link href="/tienda" className="mt-4 inline-block rounded-xl bg-red-700 px-5 py-2.5 font-semibold text-white">
                        {t('go_shop')}
                    </Link>
                </div>
            </ShopLayout>
        );
    }

    const field = (k, label, extra = {}) => (
        <div>
            <label className="text-sm font-medium text-gray-700">{label}</label>
            <input value={form[k]} onChange={set(k)} {...extra}
                className="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
            {errors[k] && <p className="mt-1 text-xs text-red-600">{errors[k]}</p>}
        </div>
    );

    return (
        <ShopLayout>
            <Head title={t('checkout')} />
            <h1 className="mb-4 text-xl font-bold">{t('checkout')}</h1>

            <form onSubmit={submit} className="space-y-4">
                <div className="rounded-2xl border border-gray-200 bg-white p-4">
                    <h2 className="mb-3 font-bold">{t('delivery_address')}</h2>
                    <div className="space-y-3">
                        {field('delivery_address_line1', t('address'), { required: true })}
                        {field('delivery_address_line2', t('address2'))}
                        <div className="grid grid-cols-2 gap-3">
                            {field('delivery_city', t('city'), { required: true })}
                            {field('delivery_state', t('state'))}
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            {field('delivery_zip', t('zip'))}
                            {field('delivery_phone', t('phone'))}
                        </div>
                        {field('requested_date', t('requested_date'), { type: 'date' })}
                        <div>
                            <label className="text-sm font-medium text-gray-700">{t('notes')}</label>
                            <textarea value={form.delivery_notes} onChange={set('delivery_notes')} rows={2}
                                className="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-gray-200 bg-white p-4">
                    <h2 className="mb-3 font-bold">{t('order_summary')}</h2>
                    <div className="space-y-2">
                        {items.map((it) => (
                            <div key={it.variant_id} className="flex justify-between text-sm">
                                <span className="pr-2 text-gray-700">
                                    {name(it)} · <span className="text-gray-400">{it.quantity} {name(it, 'unit_label')}</span>
                                </span>
                                <span className="font-medium">{money(it.quantity * it.unit_price)}</span>
                            </div>
                        ))}
                    </div>
                    <div className="mt-3 flex justify-between border-t border-gray-100 pt-3 text-lg font-bold">
                        <span>{t('total')}</span>
                        <span>{money(subtotal)}</span>
                    </div>
                </div>

                <button type="submit" disabled={processing}
                    className="w-full rounded-xl bg-red-700 py-3.5 font-bold text-white disabled:opacity-50">
                    {t('place_order')}
                </button>
            </form>
        </ShopLayout>
    );
}
