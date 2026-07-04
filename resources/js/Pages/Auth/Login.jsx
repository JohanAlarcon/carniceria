import AuthLayout from '@/Layouts/AuthLayout';
import { useLang } from '@/i18n';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

const inputClass =
    'w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-50';

export default function Login({ status, canResetPassword }) {
    const { t } = useLang();
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), { onFinish: () => reset('password') });
    };

    return (
        <AuthLayout>
            <Head title={t('login')} />

            <h1 className="text-2xl font-extrabold tracking-tight text-gray-900">{t('welcome_back')}</h1>
            <p className="mt-1.5 text-sm text-gray-500">{t('login_subtitle')}</p>

            {status && (
                <div className="mt-4 rounded-xl bg-green-50 px-4 py-2 text-sm font-medium text-green-700">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="mt-7 space-y-4">
                <div>
                    <label htmlFor="email" className="mb-1.5 block text-sm font-medium text-gray-700">
                        {t('email')}
                    </label>
                    <input id="email" type="email" name="email" value={data.email}
                        autoComplete="username" autoFocus className={inputClass}
                        onChange={(e) => setData('email', e.target.value)} />
                    {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                </div>

                <div>
                    <label htmlFor="password" className="mb-1.5 block text-sm font-medium text-gray-700">
                        {t('password')}
                    </label>
                    <div className="relative">
                        <input id="password" type={showPassword ? 'text' : 'password'} name="password"
                            value={data.password} autoComplete="current-password" className={`${inputClass} pr-16`}
                            onChange={(e) => setData('password', e.target.value)} />
                        <button type="button" onClick={() => setShowPassword((v) => !v)}
                            className="absolute inset-y-0 right-0 grid place-items-center px-4 text-xs font-semibold text-gray-500 hover:text-gray-700">
                            {showPassword ? t('hide') : t('show')}
                        </button>
                    </div>
                    {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                </div>

                {canResetPassword && (
                    <div className="flex justify-end pt-1">
                        <Link href={route('password.request')} className="text-sm font-medium text-red-700 hover:text-red-800">
                            {t('forgot_password')}
                        </Link>
                    </div>
                )}

                <button type="submit" disabled={processing}
                    className="w-full rounded-xl bg-red-700 py-3.5 font-bold text-white transition hover:bg-red-800 active:scale-[0.99] disabled:opacity-50">
                    {t('login')}
                </button>
                <p className="text-center text-xs text-gray-400">{t('session_persistent')}</p>
            </form>

            <p className="mt-6 text-center text-sm text-gray-500">
                {t('first_time')}{' '}
                <Link href={route('register')} className="font-semibold text-red-700 hover:text-red-800">
                    {t('create_business_account')}
                </Link>
            </p>
        </AuthLayout>
    );
}
