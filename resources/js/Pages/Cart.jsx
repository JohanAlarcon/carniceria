import { Head, Link } from '@inertiajs/react';
import { useCart } from '@/cart';
import { money } from '@/format';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';

export default function Cart() {
    const { t, name } = useLang();
    const { items, setQty, remove, clear, subtotal } = useCart();

    return (
        <ShopLayout>
            <Head title={t('cart')} />
            <h1 className="mb-4 text-2xl font-extrabold tracking-tight">{t('cart')}</h1>

            {items.length === 0 ? (
                <div className="rounded-2xl border border-gray-200 bg-white p-10 text-center">
                    <div className="mb-3 text-5xl">🛒</div>
                    <p className="text-gray-500">{t('empty_cart')}</p>
                    <Link href="/tienda" className="mt-4 inline-block rounded-xl bg-red-700 px-5 py-2.5 font-semibold text-white shadow-sm hover:bg-red-800">
                        {t('go_shop')}
                    </Link>
                </div>
            ) : (
                <div className="lg:grid lg:grid-cols-3 lg:items-start lg:gap-6">
                    <div className="space-y-3 lg:col-span-2">
                        {items.map((it) => (
                            <div key={it.variant_id} className="flex gap-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                {it.icon && (
                                    <div className="grid h-16 w-16 shrink-0 place-items-center rounded-xl bg-gray-50">
                                        <img src={it.icon} alt="" className="h-12 w-12 object-contain" />
                                    </div>
                                )}
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <p className="truncate font-semibold text-gray-900">{name(it)}</p>
                                            <p className="truncate text-xs text-gray-500">{name(it, 'variant_label')}</p>
                                            <p className="text-xs text-gray-400">{money(it.unit_price)}/{name(it, 'unit_label')}</p>
                                        </div>
                                        <button onClick={() => remove(it.variant_id)} className="text-xs text-gray-400 hover:text-red-600">✕</button>
                                    </div>
                                    <div className="mt-2 flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <button onClick={() => setQty(it.variant_id, Math.max(0.5, it.quantity - 1))}
                                                className="grid h-8 w-8 place-items-center rounded-full bg-gray-100 font-bold hover:bg-gray-200">−</button>
                                            <input type="number" min="0.5" step="0.5" value={it.quantity}
                                                onChange={(e) => setQty(it.variant_id, e.target.value)}
                                                className="w-16 rounded-lg border-gray-300 py-1 text-center text-sm font-semibold" />
                                            <button onClick={() => setQty(it.variant_id, it.quantity + 1)}
                                                className="grid h-8 w-8 place-items-center rounded-full bg-gray-100 font-bold hover:bg-gray-200">+</button>
                                        </div>
                                        <span className="font-bold text-gray-900">{money(it.quantity * it.unit_price)}</span>
                                    </div>
                                </div>
                            </div>
                        ))}
                        <button onClick={clear} className="text-sm text-gray-400 hover:text-red-600">{t('clear_cart')}</button>
                    </div>

                    <div className="mt-4 lg:sticky lg:top-24 lg:mt-0">
                        <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div className="flex items-center justify-between text-lg font-bold">
                                <span>{t('subtotal')}</span>
                                <span>{money(subtotal)}</span>
                            </div>
                            <p className="mt-1 text-xs text-gray-400">{items.length} {t('items')}</p>
                            <Link href="/checkout" className="mt-4 block rounded-xl bg-red-700 py-3.5 text-center font-bold text-white shadow-sm hover:bg-red-800">
                                {t('checkout')}
                            </Link>
                        </div>
                    </div>
                </div>
            )}
        </ShopLayout>
    );
}
