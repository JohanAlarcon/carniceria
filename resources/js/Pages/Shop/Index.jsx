import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useCart } from '@/cart';
import { money, qty as fmtQty } from '@/format';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';

function VariantSheet({ product, approved, onClose }) {
    const { t, name } = useLang();
    const { add } = useCart();
    const [variantId, setVariantId] = useState(product.variants[0]?.id ?? null);
    const [quantity, setQuantity] = useState(1);

    const variant = product.variants.find((v) => v.id === variantId);

    const step = (d) => setQuantity((q) => Math.max(0.5, +(q + d).toFixed(2)));

    const submit = () => {
        if (!variant) return;
        add({
            variant_id: variant.id,
            product_id: product.id,
            name_es: product.name_es,
            name_en: product.name_en,
            icon: product.image || product.icon,
            variant_label_es: variant.label_es,
            variant_label_en: variant.label_en,
            unit_label_es: variant.unit_label_es,
            unit_label_en: variant.unit_label_en,
            unit_price: variant.price,
            is_frozen: variant.is_frozen,
            quantity: +Number(quantity).toFixed(2),
        });
        onClose();
    };

    return (
        <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40" onClick={onClose}>
            <div className="w-full max-w-2xl rounded-t-3xl bg-white p-5 pb-8" onClick={(e) => e.stopPropagation()}>
                <div className="mx-auto mb-4 h-1.5 w-12 rounded-full bg-gray-300" />
                <div className="mb-4 flex items-center gap-3">
                    {(product.image || product.icon) && (
                        <img src={product.image || product.icon} alt="" className="h-14 w-14 object-contain" />
                    )}
                    <div>
                        <h3 className="text-lg font-bold leading-tight">{name(product)}</h3>
                        {product.english_cut && <p className="text-xs text-gray-500">{product.english_cut}</p>}
                    </div>
                </div>

                <p className="mb-2 text-sm font-semibold text-gray-600">{t('select_option')}</p>
                <div className="mb-4 max-h-56 space-y-2 overflow-y-auto">
                    {product.variants.map((v) => (
                        <button key={v.id} onClick={() => setVariantId(v.id)}
                            className={`flex w-full items-center justify-between rounded-xl border p-3 text-left ${
                                v.id === variantId ? 'border-red-600 bg-red-50' : 'border-gray-200 bg-white'
                            }`}>
                            <span className="pr-2">
                                <span className="text-sm font-medium text-gray-900">{name(v, 'label')}</span>
                                {v.is_frozen && (
                                    <span className="ml-2 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">
                                        ❄ {t('frozen')}
                                    </span>
                                )}
                            </span>
                            {approved && (
                                <span className="whitespace-nowrap text-sm font-bold text-red-700">
                                    {money(v.price)}
                                    <span className="text-xs font-normal text-gray-500">/{name(v, 'unit_label')}</span>
                                </span>
                            )}
                        </button>
                    ))}
                </div>

                <div className="mb-4 flex items-center justify-between">
                    <span className="text-sm font-semibold text-gray-600">{t('quantity')}</span>
                    <div className="flex items-center gap-3">
                        <button onClick={() => step(-1)} className="grid h-9 w-9 place-items-center rounded-full bg-gray-100 text-xl font-bold">−</button>
                        <input type="number" min="0.5" step="0.5" value={quantity}
                            onChange={(e) => setQuantity(e.target.value)}
                            className="w-20 rounded-lg border-gray-300 text-center font-semibold" />
                        <button onClick={() => step(1)} className="grid h-9 w-9 place-items-center rounded-full bg-gray-100 text-xl font-bold">+</button>
                    </div>
                </div>

                <button onClick={submit} disabled={!approved || !variant}
                    className="w-full rounded-xl bg-red-700 py-3.5 font-bold text-white disabled:opacity-40">
                    {approved && variant
                        ? `${t('add_to_cart')} · ${money((variant.price || 0) * quantity)}`
                        : t('add_to_cart')}
                </button>
            </div>
        </div>
    );
}

export default function ShopIndex({ categories, products, approved }) {
    const { t, name } = useLang();
    const [cat, setCat] = useState(null);
    const [search, setSearch] = useState('');
    const [picked, setPicked] = useState(null);

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        return products.filter((p) => {
            if (cat && p.category_id !== cat) return false;
            if (!q) return true;
            return (
                p.name_es.toLowerCase().includes(q) ||
                p.name_en.toLowerCase().includes(q) ||
                (p.english_cut || '').toLowerCase().includes(q)
            );
        });
    }, [products, cat, search]);

    const lowest = (p) => {
        const prices = p.variants.map((v) => v.price).filter((x) => x != null);
        return prices.length ? Math.min(...prices) : null;
    };

    return (
        <ShopLayout>
            <Head title={t('shop')} />

            {!approved && (
                <div className="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <h2 className="font-bold text-amber-900">{t('pending_title')}</h2>
                    <p className="mt-1 text-sm text-amber-800">{t('pending_text')}</p>
                </div>
            )}

            <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder={t('search')}
                className="mb-3 w-full rounded-xl border-gray-300 bg-white shadow-sm focus:border-red-500 focus:ring-red-500" />

            <div className="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1">
                <button onClick={() => setCat(null)}
                    className={`whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold ${
                        cat === null ? 'bg-red-700 text-white' : 'border border-gray-300 bg-white text-gray-700'
                    }`}>
                    {t('all')}
                </button>
                {categories.map((c) => (
                    <button key={c.id} onClick={() => setCat(c.id)}
                        style={cat === c.id ? { backgroundColor: c.color || '#b91c1c' } : {}}
                        className={`flex items-center gap-1.5 whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold ${
                            cat === c.id ? 'text-white' : 'border border-gray-300 bg-white text-gray-700'
                        }`}>
                        {c.icon && <img src={c.icon} alt="" className="h-5 w-5 object-contain" />}
                        {name(c)}
                    </button>
                ))}
            </div>

            {filtered.length === 0 ? (
                <p className="py-16 text-center text-gray-400">{t('no_products')}</p>
            ) : (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    {filtered.map((p) => {
                        const low = lowest(p);
                        const c = categories.find((x) => x.id === p.category_id);
                        return (
                            <button key={p.id} onClick={() => setPicked(p)}
                                className="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-3 text-left transition active:scale-[0.98]">
                                <div className="mb-2 grid h-24 place-items-center rounded-xl"
                                    style={{ backgroundColor: (c?.color || '#b91c1c') + '14' }}>
                                    {(p.image || p.icon) ? (
                                        <img src={p.image || p.icon} alt="" className="h-16 w-16 object-contain" />
                                    ) : (
                                        <span className="text-3xl">🥩</span>
                                    )}
                                </div>
                                <span className="line-clamp-2 text-sm font-semibold leading-tight text-gray-900">{name(p)}</span>
                                <span className="mt-1 text-xs text-gray-500">
                                    {p.variants.length} {t('options')}
                                </span>
                                {approved && low != null && (
                                    <span className="mt-1 text-sm font-bold text-red-700">
                                        {t('from')} {money(low)}
                                    </span>
                                )}
                            </button>
                        );
                    })}
                </div>
            )}

            {picked && <VariantSheet product={picked} approved={approved} onClose={() => setPicked(null)} />}
        </ShopLayout>
    );
}
