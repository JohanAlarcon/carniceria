import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useCart } from '@/cart';
import { money } from '@/format';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';

function VariantSheet({ product, approved, categoryColor, onClose }) {
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
        <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/50 backdrop-blur-sm sm:items-center sm:p-4" onClick={onClose}>
            <div className="w-full max-w-2xl rounded-t-3xl bg-white p-5 pb-8 shadow-2xl sm:max-w-md sm:rounded-3xl"
                onClick={(e) => e.stopPropagation()}>
                <div className="mx-auto mb-4 h-1.5 w-12 rounded-full bg-gray-300 sm:hidden" />
                <div className="mb-4 flex items-center gap-3">
                    <div className="grid h-16 w-16 shrink-0 place-items-center rounded-2xl"
                        style={{ background: `linear-gradient(135deg, ${categoryColor}26, ${categoryColor}0d)` }}>
                        {(product.image || product.icon) && (
                            <img src={product.image || product.icon} alt="" className="h-12 w-12 object-contain" />
                        )}
                    </div>
                    <div>
                        <h3 className="text-lg font-bold leading-tight">{name(product)}</h3>
                        {product.english_cut && <p className="text-xs text-gray-500">{product.english_cut}</p>}
                    </div>
                </div>

                <p className="mb-2 text-sm font-semibold text-gray-600">{t('select_option')}</p>
                <div className="mb-4 max-h-56 space-y-2 overflow-y-auto pr-1">
                    {product.variants.map((v) => (
                        <button key={v.id} onClick={() => setVariantId(v.id)}
                            className={`flex w-full items-center justify-between rounded-xl border p-3 text-left transition ${
                                v.id === variantId ? 'border-red-600 bg-red-50 ring-1 ring-red-200' : 'border-gray-200 bg-white hover:border-gray-300'
                            }`}>
                            <span className="pr-2">
                                <span className="text-sm font-medium text-gray-900">{name(v, 'label')}</span>
                                {v.is_frozen && (
                                    <span className="ml-2 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">❄</span>
                                )}
                            </span>
                            {approved && (
                                <span className="whitespace-nowrap text-sm font-bold text-red-700">
                                    {money(v.price)}
                                    <span className="text-xs font-normal text-gray-400">/{name(v, 'unit_label')}</span>
                                </span>
                            )}
                        </button>
                    ))}
                </div>

                <div className="mb-4 flex items-center justify-between">
                    <span className="text-sm font-semibold text-gray-600">{t('quantity')}</span>
                    <div className="flex items-center gap-3">
                        <button onClick={() => step(-1)} className="grid h-10 w-10 place-items-center rounded-full bg-gray-100 text-xl font-bold text-gray-700 hover:bg-gray-200">−</button>
                        <input type="number" min="0.5" step="0.5" value={quantity}
                            onChange={(e) => setQuantity(e.target.value)}
                            className="w-20 rounded-lg border-gray-300 text-center font-semibold" />
                        <button onClick={() => step(1)} className="grid h-10 w-10 place-items-center rounded-full bg-gray-100 text-xl font-bold text-gray-700 hover:bg-gray-200">+</button>
                    </div>
                </div>

                <button onClick={submit} disabled={!approved || !variant}
                    className="w-full rounded-xl bg-red-700 py-3.5 font-bold text-white shadow-sm transition hover:bg-red-800 disabled:opacity-40">
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

    const colorOf = (id) => categories.find((c) => c.id === id)?.color || '#b91c1c';

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
                <div className="mb-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <span className="text-2xl">⏳</span>
                    <div>
                        <h2 className="font-bold text-amber-900">{t('pending_title')}</h2>
                        <p className="mt-1 text-sm text-amber-800">{t('pending_text')}</p>
                    </div>
                </div>
            )}

            <div className="relative mb-3">
                <svg className="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                    fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder={t('search')}
                    className="w-full rounded-xl border-gray-300 bg-white py-2.5 pl-10 shadow-sm focus:border-red-500 focus:ring-red-500" />
            </div>

            <div className="-mx-4 mb-5 flex snap-x gap-2 overflow-x-auto px-4 pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <button onClick={() => setCat(null)}
                    className={`snap-start whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition ${
                        cat === null ? 'bg-red-700 text-white shadow-sm' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                    }`}>
                    {t('all')}
                </button>
                {categories.map((c) => (
                    <button key={c.id} onClick={() => setCat(c.id)}
                        style={cat === c.id ? { backgroundColor: c.color || '#b91c1c' } : {}}
                        className={`flex snap-start items-center gap-1.5 whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-semibold transition ${
                            cat === c.id ? 'text-white shadow-sm' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                        }`}>
                        {c.icon && <img src={c.icon} alt="" className="h-6 w-6 object-contain" />}
                        {name(c)}
                    </button>
                ))}
            </div>

            {filtered.length === 0 ? (
                <p className="py-16 text-center text-gray-400">{t('no_products')}</p>
            ) : (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                    {filtered.map((p) => {
                        const low = lowest(p);
                        const color = colorOf(p.category_id);
                        return (
                            <button key={p.id} onClick={() => setPicked(p)}
                                className="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-[0.98]">
                                <div className="relative aspect-square w-full overflow-hidden"
                                    style={{ background: `linear-gradient(135deg, ${color}24, ${color}08)` }}>
                                    {(p.image || p.icon) ? (
                                        <img src={p.image || p.icon} alt=""
                                            className="absolute inset-0 m-auto h-[72%] w-[72%] object-contain drop-shadow-sm transition group-hover:scale-105" />
                                    ) : (
                                        <span className="absolute inset-0 grid place-items-center text-4xl">🥩</span>
                                    )}
                                    {approved && low != null && (
                                        <span className="absolute bottom-2 right-2 rounded-full bg-white/95 px-2 py-0.5 text-xs font-bold text-red-700 shadow-sm">
                                            {t('from')} {money(low)}
                                        </span>
                                    )}
                                </div>
                                <div className="p-2.5">
                                    <p className="line-clamp-2 text-sm font-semibold leading-tight text-gray-900">{name(p)}</p>
                                    <p className="mt-0.5 text-xs text-gray-400">{p.variants.length} {t('options')}</p>
                                </div>
                            </button>
                        );
                    })}
                </div>
            )}

            {picked && (
                <VariantSheet product={picked} approved={approved} categoryColor={colorOf(picked.category_id)} onClose={() => setPicked(null)} />
            )}
        </ShopLayout>
    );
}
