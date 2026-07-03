import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
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

    return (
        <GuestLayout>
            <Head title={t('register')} />

            <p className="mb-4 text-sm text-gray-500">{t('registering')}</p>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="business_name" value={t('business_name')} />
                    <TextInput id="business_name" name="business_name" value={data.business_name}
                        className="mt-1 block w-full" isFocused={true} required
                        onChange={(e) => setData('business_name', e.target.value)} />
                    <InputError message={errors.business_name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="name" value={t('contact_name')} />
                    <TextInput id="name" name="name" value={data.name}
                        className="mt-1 block w-full" autoComplete="name" required
                        onChange={(e) => setData('name', e.target.value)} />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="phone" value={t('phone')} />
                    <TextInput id="phone" name="phone" value={data.phone}
                        className="mt-1 block w-full" autoComplete="tel"
                        onChange={(e) => setData('phone', e.target.value)} />
                    <InputError message={errors.phone} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value={t('email')} />
                    <TextInput id="email" type="email" name="email" value={data.email}
                        className="mt-1 block w-full" autoComplete="username" required
                        onChange={(e) => setData('email', e.target.value)} />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value={t('password')} />
                    <TextInput id="password" type="password" name="password" value={data.password}
                        className="mt-1 block w-full" autoComplete="new-password" required
                        onChange={(e) => setData('password', e.target.value)} />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value={t('confirm_password')} />
                    <TextInput id="password_confirmation" type="password" name="password_confirmation"
                        value={data.password_confirmation} className="mt-1 block w-full"
                        autoComplete="new-password" required
                        onChange={(e) => setData('password_confirmation', e.target.value)} />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <Link href={route('login')} className="rounded-md text-sm text-gray-600 underline hover:text-gray-900">
                        {t('already_registered')}
                    </Link>
                    <PrimaryButton className="ms-4" disabled={processing}>
                        {t('register')}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
