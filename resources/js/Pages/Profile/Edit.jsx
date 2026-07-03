import { Head, Link, usePage } from '@inertiajs/react';
import { useLang } from '@/i18n';
import ShopLayout from '@/Layouts/ShopLayout';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({ mustVerifyEmail, status }) {
    const { t } = useLang();
    const user = usePage().props.auth.user;
    const initial = (user?.name || '?').trim().charAt(0).toUpperCase();

    return (
        <ShopLayout>
            <Head title={t('account')} />

            <div className="mx-auto max-w-xl space-y-4">
                <h1 className="text-2xl font-extrabold tracking-tight">{t('account')}</h1>

                {/* Identidad */}
                <div className="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-red-700 text-xl font-bold text-white">
                        {initial}
                    </div>
                    <div className="min-w-0">
                        <p className="truncate font-bold text-gray-900">{user?.name}</p>
                        <p className="truncate text-sm text-gray-500">{user?.email}</p>
                    </div>
                </div>

                <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                </div>

                <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <UpdatePasswordForm />
                </div>

                <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <DeleteUserForm />
                </div>

                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    className="flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white py-3.5 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-[0.99]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.8} stroke="currentColor" className="h-5 w-5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    {t('logout')}
                </Link>
            </div>
        </ShopLayout>
    );
}
