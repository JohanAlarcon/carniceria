import { usePage } from '@inertiajs/react';
import { useLang } from '@/i18n';

function Steps() {
    const { t } = useLang();
    const steps = [t('step1_title'), t('step2_title'), t('step3_title')];
    return (
        <div className="grid grid-cols-3 gap-2 text-center">
            {steps.map((title, i) => (
                <div key={i} className="flex flex-col items-center gap-1.5">
                    <span className="grid h-7 w-7 place-items-center rounded-full bg-red-50 text-xs font-bold text-red-700">
                        {i + 1}
                    </span>
                    <span className="text-xs font-medium text-gray-500">{title}</span>
                </div>
            ))}
        </div>
    );
}

export default function AuthLayout({ children }) {
    const { t, lang, toggle } = useLang();
    const business = usePage().props.business?.name || 'Mi Carnicería';

    return (
        <div className="min-h-dvh bg-white text-gray-900">
            <div className="mx-auto flex min-h-dvh max-w-sm flex-col px-6 pb-8 pt-[calc(env(safe-area-inset-top)+1.5rem)]">
                <header className="flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                        <img src="/icons/pwa-192.png" alt="" className="h-9 w-9 rounded-xl shadow-sm ring-1 ring-black/5" />
                        <span className="text-sm font-bold tracking-tight text-gray-700">{business}</span>
                    </div>
                    <button onClick={toggle} aria-label="ES / EN"
                        className="grid h-9 min-w-[2.25rem] place-items-center rounded-full border border-gray-200 px-2.5 text-xs font-bold text-gray-500 transition hover:bg-gray-50 active:scale-95">
                        {lang === 'es' ? 'EN' : 'ES'}
                    </button>
                </header>

                <main className="flex flex-1 flex-col justify-center py-10">{children}</main>

                <footer className="border-t border-gray-100 pt-6">
                    <Steps />
                    <p className="mt-5 flex items-center justify-center gap-1.5 text-xs text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" strokeWidth={1.8} stroke="currentColor" className="h-4 w-4">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                        {t('trust_verified')}
                    </p>
                </footer>
            </div>
        </div>
    );
}
