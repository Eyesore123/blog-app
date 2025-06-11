import React from 'react';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import { Navbar } from '@/components/Navbar';
import Header from '@/components/Header';
import { useTheme } from '../../context/ThemeContext';
import '../../../css/app.css';

interface PageProps {
  [key: string]: any;
  email?: string;
}

const VerifyEmailNotice: React.FC = () => {
  const { theme } = useTheme();
  const { props } = usePage<PageProps>();
  const email = typeof props.email === 'string' ? props.email : 'your email';

  const resendEmail = () => {
    Inertia.post('/email/verification-notification', {}, {
      onSuccess: () => toast.success('Verification email resent!'),
      onError: () => toast.error('Failed to resend email.'),
    });
  };

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <main className="!p-4 md:!p-8 !gap-1 w-full flex justify-center items-center lg:!mt-20">
          <div className="flex flex-col lg:flex-row gap-4 md:gap-6 custom-2xl-gap items-center lg:items-start justify-center">
            <div className="lg:!block !w-full md:!max-w-2/3 xl:!w-2/3 xl:!ml-20 2xl:!ml-30 xl:max-w-120 xl:!mr-10 !mb-8 lg:!mb-0 mx-auto">
              <div className="lg:top-24 !space-y-4 md:!space-y-6 flexcontainer w-full lg:!w-80 xl:!w-120">
                <div className="rounded-lg bg-[#5800FF]/10 !p-4">
                  <h3 className="font-semibold !mb-2">Verify your email</h3>
                  <p className="opacity-80">
                    A verification email has been sent to you. Please check your inbox
                    and click the link to activate your account. If you didn’t receive the email,
                    you can resend it using the button below.
                  </p>
                  <button
                    onClick={resendEmail}
                    className="!mt-6 !px-4 !py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded transition"
                  >
                    Resend verification email
                  </button>
                </div>
              </div>
            </div>
          </div>
        </main>
        <Toaster />
      </div>
    </div>
  );
};

export default VerifyEmailNotice;
