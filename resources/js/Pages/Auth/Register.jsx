import AuthLayout from '@/Layouts/AuthLayout';
import { useLang } from '@/i18n';
import { Head, Link, useForm } from '@inertiajs/react';

const inputClass =
    'w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-50';

export default function Register() {
    const { t } = useLang();
    const { data, setData, post, processing, errors, reset } = useForm({
        business_name: '',
        name: '',
        phone: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const field = (name, label, extra = {}) => (
        <div>
            <label htmlFor={name} className="mb-1.5 block text-sm font-medium text-gray-700">
                {label}
            </label>
            <input id={name} name={name} value={data[name]} className={inputClass}
                onChange={(e) => setData(name, e.target.value)} {...extra} />
            {errors[name] && <p className="mt-1 text-sm text-red-600">{errors[name]}</p>}
        </div>
    );

    return (
        <AuthLayout>
            <Head title={t('register')} />

            <h1 className="text-2xl font-extrabold tracking-tight text-gray-900">
                {t('create_business_account')}
            </h1>
            <p className="mt-1.5 text-sm text-gray-500">{t('register_subtitle')}</p>

            <form onSubmit={submit} className="mt-7 space-y-4">
                {field('business_name', t('business_name'), { required: true, autoFocus: true })}
                {field('name', t('contact_name'), { required: true, autoComplete: 'name' })}
                {field('phone', t('phone'), { autoComplete: 'tel' })}
                {field('email', t('email'), { type: 'email', required: true, autoComplete: 'username' })}
                {field('password', t('password'), { type: 'password', required: true, autoComplete: 'new-password' })}
                {field('password_confirmation', t('confirm_password'), { type: 'password', required: true, autoComplete: 'new-password' })}

                <button type="submit" disabled={processing}
                    className="w-full rounded-xl bg-red-700 py-3.5 font-bold text-white transition hover:bg-red-800 active:scale-[0.99] disabled:opacity-50">
                    {t('register')}
                </button>
            </form>

            <p className="mt-6 text-center text-sm text-gray-500">
                {t('already_registered')}{' '}
                <Link href={route('login')} className="font-semibold text-red-700 hover:text-red-800">
                    {t('login')}
                </Link>
            </p>
        </AuthLayout>
    );
}
