import React, { useState, useEffect } from 'react';
import SignInForm from '../../components/SignInForm';
import Header from '@/components/Header';
import { Navbar } from '@/components/Navbar';
import { usePage } from '@inertiajs/react';

interface Props {
  successMessage?: string | null;
}

const SignInPage: React.FC<Props> = ({ successMessage }) => {
  const [flow, setFlow] = useState<"signIn" | "signUp">("signIn");
  const [message, setMessage] = useState<string | null>(successMessage || null);

  // If you want to keep flash as backup:
  const { flash } = usePage<{ flash: { message?: string | null } }>().props;

  useEffect(() => {
    if (flash.message) {
      setMessage(flash.message);
    }
  }, [flash.message]);

  return (
    <>
      <Navbar />
      <Header />
      <div className="min-h-160 flex items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <div className="w-full max-w-md !pb-8 !pr-8 !pl-8">
          {message && (
            <div
              style={{
                backgroundColor: '#d4edda',
                color: '#155724',
                padding: '12px 20px',
                marginBottom: '16px',
                borderRadius: '4px',
                border: '1px solid #c3e6cb',
                textAlign: 'center',
                fontWeight: '600',
                marginTop: '50px',
              }}
            >
              {message}
            </div>
          )}

          <div className="bg-[var(--color-primary)]/10 rounded-lg !p-8">
            <SignInForm flow={flow} setFlow={setFlow} />
          </div>
        </div>
      </div>
    </>
  );
};

export default SignInPage;
