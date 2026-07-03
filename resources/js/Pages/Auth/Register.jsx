import AuthLayout from '@/Layouts/AuthLayout';
import { useLang } from '@/i18n';
import { Head, Link, useForm } from '@inertiajs/react';

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

    const inputClass =
        'w-full rounded-xl border-gray-300 shadow-sm transition focus:border-red-500 focus:ring-red-500';

    const field = (name, label, extra = {}) => (
        <div>
            <label htmlFor={name} className="mb-1 block text-sm font-medium text-gray-700">
                {label}
            </label>
            <input
                id={name}
                name={name}
                value={data[name]}
                className={inputClass}
                onChange={(e) => setData(name, e.target.value)}
                {...extra}
            />
            {errors[name] && <p className="mt-1 text-sm text-red-600">{errors[name]}</p>}
        </div>
    );

    return (
        <AuthLayout>
            <Head title={t('register')} />

            <div className="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-black/5 sm:p-8">
                <h2 className="text-2xl font-extrabold tracking-tight text-gray-900">
                    {t('create_business_account')}
                </h2>
                <p className="mt-1 text-sm text-gray-500">{t('register_subtitle')}</p>

                <form onSubmit={submit} className="mt-6 space-y-4">
                    {field('business_name', t('business_name'), { required: true, autoFocus: true })}

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {field('name', t('contact_name'), { required: true, autoComplete: 'name' })}
                        {field('phone', t('phone'), { autoComplete: 'tel' })}
                    </div>

                    {field('email', t('email'), { type: 'email', required: true, autoComplete: 'username' })}

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {field('password', t('password'), { type: 'password', required: true, autoComplete: 'new-password' })}
                        {field('password_confirmation', t('confirm_password'), { type: 'password', required: true, autoComplete: 'new-password' })}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-xl bg-red-700 py-3.5 font-bold text-white shadow-sm transition hover:bg-red-800 active:scale-[0.99] disabled:opacity-50"
                    >
                        {t('register')}
                    </button>
                </form>

                <p className="mt-6 text-center text-sm text-gray-500">
                    {t('already_registered')}{' '}
                    <Link href={route('login')} className="font-semibold text-red-700 hover:text-red-800">
                        {t('login')}
                    </Link>
                </p>
            </div>
        </AuthLayout>
    );
}
