import { usePage } from '@inertiajs/react';
import { useLang } from '@/i18n';

function Steps({ dark = false }) {
    const { t } = useLang();
    const steps = [
        { n: '1', title: t('step1_title'), text: t('step1_text') },
        { n: '2', title: t('step2_title'), text: t('step2_text') },
        { n: '3', title: t('step3_title'), text: t('step3_text') },
    ];
    const badge = dark ? 'bg-white/15 text-white' : 'bg-red-100 text-red-700';
    const title = dark ? 'text-white' : 'text-gray-900';
    const text = dark ? 'text-white/75' : 'text-gray-500';
    const label = dark ? 'text-white/60' : 'text-red-700/70';

    return (
        <div>
            <p className={`mb-3 text-xs font-bold uppercase tracking-wider ${label}`}>
                {t('how_it_works')}
            </p>
            <ol className="space-y-3.5">
                {steps.map((s) => (
                    <li key={s.n} className="flex items-start gap-3">
                        <span className={`grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-bold ${badge}`}>
                            {s.n}
                        </span>
                        <div>
                            <p className={`font-semibold leading-tight ${title}`}>{s.title}</p>
                            <p className={`text-sm ${text}`}>{s.text}</p>
                        </div>
                    </li>
                ))}
            </ol>
        </div>
    );
}

function Trust({ dark = false }) {
    const { t } = useLang();
    return (
        <div className={`flex items-center gap-2 text-sm ${dark ? 'text-white/70' : 'text-gray-500'}`}>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" strokeWidth={1.8} stroke="currentColor" className="h-5 w-5">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
            </svg>
            {t('trust_verified')}
        </div>
    );
}

export default function AuthLayout({ children }) {
    const { t, lang, toggle } = useLang();
    const business = usePage().props.business?.name || 'Mi Carnicería';

    return (
        <div className="min-h-dvh bg-gray-50 lg:grid lg:grid-cols-2">
            <aside className="relative overflow-hidden bg-gradient-to-br from-red-800 via-red-700 to-red-900 px-6 pb-14 pt-[calc(env(safe-area-inset-top)+1.75rem)] text-white rounded-b-[2.5rem] lg:flex lg:flex-col lg:justify-center lg:rounded-none lg:px-12 lg:py-12">
                <img src="/images/icons/beef.webp" alt="" className="pointer-events-none absolute -right-8 -top-8 h-44 w-44 rotate-12 opacity-10" />
                <img src="/images/icons/chicken-leg.webp" alt="" className="pointer-events-none absolute -bottom-10 -left-8 h-40 w-40 -rotate-12 opacity-10 lg:opacity-[0.08]" />

                <div className="relative mx-auto w-full max-w-md lg:mx-0">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <img src="/icons/pwa-192.png" alt="" className="h-12 w-12 rounded-2xl shadow-md ring-1 ring-white/20" />
                            <span className="text-xl font-extrabold tracking-tight">{business}</span>
                        </div>
                        <button onClick={toggle} aria-label="ES / EN"
                            className="grid h-10 min-w-[2.5rem] place-items-center rounded-full border border-white/30 px-3 text-xs font-bold text-white/90 transition hover:bg-white/10 active:scale-95">
                            {lang === 'es' ? 'EN' : 'ES'}
                        </button>
                    </div>

                    <h1 className="mt-8 text-3xl font-extrabold leading-tight tracking-tight lg:mt-10 lg:text-4xl">
                        {t('brand_tagline')}
                    </h1>
                    <p className="mt-3 max-w-md text-white/80 lg:text-lg">{t('brand_intro')}</p>

                    <div className="mt-9 hidden lg:block">
                        <Steps dark />
                    </div>
                    <div className="mt-8 hidden lg:block">
                        <Trust dark />
                    </div>
                </div>
            </aside>

            <main className="flex flex-col justify-center px-6 py-8 lg:px-12">
                <div className="mx-auto -mt-14 w-full max-w-md lg:mt-0">
                    {children}

                    <div className="mt-8 lg:hidden">
                        <Steps />
                    </div>
                    <div className="mt-6 flex justify-center lg:hidden">
                        <Trust />
                    </div>
                </div>
            </main>
        </div>
    );
}
