import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCart } from '@/cart';
import { money } from '@/format';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';

const pad = (n) => String(n).padStart(2, '0');

function minDeliveryDate(leadDays) {
    const d = new Date();
    d.setDate(d.getDate() + (leadDays || 0));
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function timeSlots(start, end) {
    const [sh, sm] = start.split(':').map(Number);
    const [eh, em] = end.split(':').map(Number);
    const out = [];
    for (let m = sh * 60 + sm; m <= eh * 60 + em; m += 30) {
        out.push(`${pad(Math.floor(m / 60))}:${pad(m % 60)}`);
    }
    return out;
}

export default function Checkout() {
    const { t, name, lang } = useLang();
    const { items, subtotal, clear } = useCart();
    const page = usePage().props;
    const customer = page.auth?.customer || {};
    const credit = page.credit || { enabled: false, available: 0, terms_days: 30 };
    const delivery = page.delivery || { start_time: '08:00', end_time: '18:00', min_lead_days: 1 };

    const [form, setForm] = useState({
        delivery_address_line1: customer.address_line1 || '',
        delivery_address_line2: customer.address_line2 || '',
        delivery_city: customer.city || '',
        delivery_state: customer.state || '',
        delivery_zip: customer.zip || '',
        delivery_phone: customer.phone || '',
        requested_date: '',
        requested_time: '',
        delivery_notes: '',
        payment_method: 'contraentrega',
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});

    const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

    const isCredit = form.payment_method === 'credito';
    const overLimit = isCredit && subtotal > credit.available;

    const dueDate = () => {
        if (!form.requested_date) return null;
        const d = new Date(form.requested_date);
        d.setDate(d.getDate() + credit.terms_days);
        return d.toLocaleDateString(lang);
    };

    const submit = (e) => {
        e.preventDefault();
        if (!form.requested_date || !form.requested_time) {
            setErrors({ requested_at: t('delivery_required') });
            return;
        }
        setProcessing(true);
        router.post('/pedidos', {
            delivery_address_line1: form.delivery_address_line1,
            delivery_address_line2: form.delivery_address_line2,
            delivery_city: form.delivery_city,
            delivery_state: form.delivery_state,
            delivery_zip: form.delivery_zip,
            delivery_phone: form.delivery_phone,
            delivery_notes: form.delivery_notes,
            requested_at: `${form.requested_date} ${form.requested_time}`,
            payment_method: form.payment_method,
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
                <div className="rounded-2xl border border-gray-200 bg-white p-10 text-center">
                    <div className="mb-3 text-5xl">🛒</div>
                    <p className="text-gray-500">{t('empty_cart')}</p>
                    <Link href="/tienda" className="mt-4 inline-block rounded-xl bg-red-700 px-5 py-2.5 font-semibold text-white shadow-sm hover:bg-red-800">
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

    const payOption = (value, title, desc, extra = null) => {
        const active = form.payment_method === value;
        return (
            <button type="button" onClick={() => setForm((f) => ({ ...f, payment_method: value }))}
                className={`w-full rounded-xl border p-3 text-left transition ${active ? 'border-red-500 bg-red-50 ring-1 ring-red-500' : 'border-gray-300 hover:bg-gray-50'}`}>
                <div className="flex items-center justify-between">
                    <span className="font-semibold text-gray-900">{title}</span>
                    <span className={`grid h-5 w-5 place-items-center rounded-full border ${active ? 'border-red-600 bg-red-600' : 'border-gray-300'}`}>
                        {active && <span className="h-2 w-2 rounded-full bg-white" />}
                    </span>
                </div>
                <p className="mt-0.5 text-sm text-gray-500">{desc}</p>
                {extra}
            </button>
        );
    };

    return (
        <ShopLayout>
            <Head title={t('checkout')} />
            <h1 className="mb-4 text-2xl font-extrabold tracking-tight">{t('checkout')}</h1>

            <form onSubmit={submit} className="lg:grid lg:grid-cols-3 lg:items-start lg:gap-6">
                <div className="space-y-4 lg:col-span-2">
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
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
                            <div>
                                <label className="text-sm font-medium text-gray-700">{t('notes')}</label>
                                <textarea value={form.delivery_notes} onChange={set('delivery_notes')} rows={2}
                                    className="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 className="font-bold">{t('delivery_datetime')}</h2>
                        <p className="mb-3 text-xs text-gray-400">
                            {t('delivery_window')}: {delivery.start_time}–{delivery.end_time}
                        </p>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-sm font-medium text-gray-700">{t('date')}</label>
                                <input type="date" value={form.requested_date} onChange={set('requested_date')}
                                    min={minDeliveryDate(delivery.min_lead_days)} required
                                    className="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-700">{t('time')}</label>
                                <select value={form.requested_time} onChange={set('requested_time')} required
                                    className="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">{t('select_time')}</option>
                                    {timeSlots(delivery.start_time, delivery.end_time).map((s) => (
                                        <option key={s} value={s}>{s}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        {errors.requested_at && <p className="mt-2 text-xs text-red-600">{errors.requested_at}</p>}
                    </div>

                    <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 className="mb-3 font-bold">{t('payment_method')}</h2>
                        <div className="space-y-3">
                            {payOption('contraentrega', t('pay_on_delivery'), t('pay_on_delivery_desc'))}
                            {credit.enabled
                                ? payOption('credito', t('pay_credit'), t('pay_credit_desc'),
                                    <div className="mt-2 border-t border-red-100 pt-2 text-sm">
                                        <div className="flex justify-between text-gray-600">
                                            <span>{t('credit_available')}</span>
                                            <span className="font-semibold">{money(credit.available)}</span>
                                        </div>
                                        {isCredit && form.requested_date && (
                                            <div className="mt-1 flex justify-between text-gray-600">
                                                <span>{t('credit_due')}</span>
                                                <span className="font-semibold">{dueDate()}</span>
                                            </div>
                                        )}
                                    </div>)
                                : <p className="text-xs text-gray-400">{t('credit_not_enabled')}</p>}
                        </div>
                        {overLimit && <p className="mt-2 text-xs font-medium text-red-600">{t('credit_over_limit')}</p>}
                        {errors.payment_method && <p className="mt-2 text-xs text-red-600">{errors.payment_method}</p>}
                    </div>
                </div>

                <div className="mt-4 lg:sticky lg:top-24 lg:mt-0">
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 className="mb-3 font-bold">{t('order_summary')}</h2>
                        <div className="max-h-64 space-y-2 overflow-y-auto pr-1">
                            {items.map((it) => (
                                <div key={it.variant_id} className="flex justify-between text-sm">
                                    <span className="pr-2 text-gray-700">
                                        {name(it)} <span className="text-gray-400">· {it.quantity} {name(it, 'unit_label')}</span>
                                    </span>
                                    <span className="font-medium">{money(it.quantity * it.unit_price)}</span>
                                </div>
                            ))}
                        </div>
                        <div className="mt-3 flex justify-between border-t border-gray-100 pt-3 text-lg font-bold">
                            <span>{t('total')}</span>
                            <span>{money(subtotal)}</span>
                        </div>
                        <button type="submit" disabled={processing || overLimit}
                            className="mt-4 w-full rounded-xl bg-red-700 py-3.5 font-bold text-white shadow-sm transition hover:bg-red-800 disabled:opacity-50">
                            {t('place_order')}
                        </button>
                    </div>
                </div>
            </form>
        </ShopLayout>
    );
}
