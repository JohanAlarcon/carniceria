import { useEffect, useState } from 'react';
import { useLang } from '@/i18n';

// Pon false si quieres bloquear por completo el uso sin instalar (sin escape).
const ALLOW_BROWSER = true;

function isStandalone() {
    return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
        || window.navigator.standalone === true
        || document.referrer.startsWith('android-app://');
}

const isIOS = () => /iphone|ipad|ipod/i.test(window.navigator.userAgent);

const ShareIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" className="h-5 w-5" stroke="currentColor" strokeWidth={1.8}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M6 12v6a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-6" />
    </svg>
);
const PlusIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" className="h-5 w-5" stroke="currentColor" strokeWidth={1.8}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 5v14M5 12h14" />
    </svg>
);

export default function InstallGate({ businessName = 'Mi Carnicería', children }) {
    const { t } = useLang();
    const [standalone] = useState(isStandalone);
    const [bypass, setBypass] = useState(() => sessionStorage.getItem('install_bypass') === '1');
    const [prompt, setPrompt] = useState(() => window.__installPrompt || null);
    const [installed, setInstalled] = useState(false);

    useEffect(() => {
        if (standalone) return;
        const onPrompt = (e) => { e.preventDefault(); window.__installPrompt = e; setPrompt(e); };
        const onInstalled = () => { setInstalled(true); window.__installPrompt = null; };
        window.addEventListener('beforeinstallprompt', onPrompt);
        window.addEventListener('appinstalled', onInstalled);
        return () => {
            window.removeEventListener('beforeinstallprompt', onPrompt);
            window.removeEventListener('appinstalled', onInstalled);
        };
    }, [standalone]);

    if (standalone || bypass) return children;

    const doInstall = async () => {
        if (!prompt) return;
        prompt.prompt();
        await prompt.userChoice;
        window.__installPrompt = null;
        setPrompt(null);
    };

    const skip = () => { sessionStorage.setItem('install_bypass', '1'); setBypass(true); };

    const benefits = [t('install_benefit1'), t('install_benefit2'), t('install_benefit3')];

    return (
        <div className="min-h-dvh bg-gradient-to-b from-red-700 to-red-900 text-white">
            <div className="mx-auto flex min-h-dvh max-w-sm flex-col justify-center px-6 py-10 text-center">
                <img src="/icons/pwa-192.png" alt="" className="mx-auto h-20 w-20 rounded-3xl shadow-lg ring-1 ring-white/20" />
                <p className="mt-3 text-lg font-bold tracking-tight">{businessName}</p>

                {installed ? (
                    <div className="mt-8 rounded-3xl bg-white/10 p-6 backdrop-blur">
                        <h1 className="text-2xl font-extrabold">{t('install_done_title')}</h1>
                        <p className="mt-2 text-white/85">{t('install_done_text')}</p>
                    </div>
                ) : (
                    <>
                        <h1 className="mt-6 text-2xl font-extrabold">{t('install_title')}</h1>
                        <p className="mt-2 text-white/85">{t('install_subtitle')}</p>

                        <div className="mt-6 grid grid-cols-3 gap-2">
                            {benefits.map((b, i) => (
                                <div key={i} className="rounded-2xl bg-white/10 px-2 py-3 text-xs font-medium">{b}</div>
                            ))}
                        </div>

                        <div className="mt-7">
                            {prompt ? (
                                <button onClick={doInstall}
                                    className="w-full rounded-2xl bg-white py-4 text-lg font-bold text-red-700 shadow-lg transition hover:bg-red-50 active:scale-[0.99]">
                                    {t('install_btn')}
                                </button>
                            ) : isIOS() ? (
                                <div className="rounded-2xl bg-white/10 p-4 text-left text-sm backdrop-blur">
                                    <p className="mb-3 text-center font-bold">{t('install_ios_title')}</p>
                                    <ol className="space-y-2.5">
                                        <li className="flex items-center gap-3"><span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/20"><ShareIcon /></span>{t('install_ios_1')}</li>
                                        <li className="flex items-center gap-3"><span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/20"><PlusIcon /></span>{t('install_ios_2')}</li>
                                        <li className="flex items-center gap-3"><span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/20 text-sm font-bold">3</span>{t('install_ios_3')}</li>
                                    </ol>
                                </div>
                            ) : (
                                <p className="rounded-2xl bg-white/10 p-4 text-sm text-white/85 backdrop-blur">{t('install_other')}</p>
                            )}
                        </div>
                    </>
                )}

                {ALLOW_BROWSER && !installed && (
                    <button onClick={skip} className="mt-6 text-sm text-white/60 underline underline-offset-4 hover:text-white/90">
                        {t('install_continue_browser')}
                    </button>
                )}
            </div>
        </div>
    );
}
