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
            <h1 className="mb-4 text-xl font-bold">{t('cart')}</h1>

            {items.length === 0 ? (
                <div className="rounded-2xl border border-gray-200 bg-white p-8 text-center">
                    <p className="text-gray-500">{t('empty_cart')}</p>
                    <Link href="/tienda" className="mt-4 inline-block rounded-xl bg-red-700 px-5 py-2.5 font-semibold text-white">
                        {t('go_shop')}
                    </Link>
                </div>
            ) : (
                <>
                    <div className="space-y-3">
                        {items.map((it) => (
                            <div key={it.variant_id} className="flex gap-3 rounded-2xl border border-gray-200 bg-white p-3">
                                {it.icon && <img src={it.icon} alt="" className="h-14 w-14 shrink-0 object-contain" />}
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <p className="truncate font-semibold text-gray-900">{name(it)}</p>
                                            <p className="truncate text-xs text-gray-500">{name(it, 'variant_label')}</p>
                                        </div>
                                        <button onClick={() => remove(it.variant_id)} className="text-xs text-gray-400 hover:text-red-600">
                                            {t('remove')}
                                        </button>
                                    </div>
                                    <div className="mt-2 flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <button onClick={() => setQty(it.variant_id, Math.max(0.5, it.quantity - 1))}
                                                className="grid h-8 w-8 place-items-center rounded-full bg-gray-100 font-bold">−</button>
                                            <input type="number" min="0.5" step="0.5" value={it.quantity}
                                                onChange={(e) => setQty(it.variant_id, e.target.value)}
                                                className="w-16 rounded-lg border-gray-300 py-1 text-center text-sm font-semibold" />
                                            <button onClick={() => setQty(it.variant_id, it.quantity + 1)}
                                                className="grid h-8 w-8 place-items-center rounded-full bg-gray-100 font-bold">+</button>
                                            <span className="text-xs text-gray-500">{name(it, 'unit_label')}</span>
                                        </div>
                                        <span className="font-bold text-gray-900">{money(it.quantity * it.unit_price)}</span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <button onClick={clear} className="mt-3 text-sm text-gray-400 hover:text-red-600">{t('clear_cart')}</button>

                    <div className="mt-4 rounded-2xl border border-gray-200 bg-white p-4">
                        <div className="flex items-center justify-between text-lg font-bold">
                            <span>{t('subtotal')}</span>
                            <span>{money(subtotal)}</span>
                        </div>
                        <Link href="/checkout" className="mt-4 block rounded-xl bg-red-700 py-3.5 text-center font-bold text-white">
                            {t('checkout')}
                        </Link>
                    </div>
                </>
            )}
        </ShopLayout>
    );
}
