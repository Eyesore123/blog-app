import React, { useState, useEffect } from 'react';
import SignInForm from '../../components/SignInForm';
import Header from '@/components/Header';
import { Navbar } from '@/components/Navbar';
import { useAlert } from '@/context/AlertContext';
import { usePage } from '@inertiajs/react';

const SignInPage: React.FC = () => {
  const [flow, setFlow] = useState<"signIn" | "signUp">("signIn");
  const { props } = usePage<{ status?: string }>();
  const status = props.status;
  const { showAlert } = useAlert();

  useEffect(() => {
    if (status) {
      showAlert(status, "success");
    }
  }, [status, showAlert]);

  return (
    <>
      <Navbar />
      <Header />
      <div className="min-h-160 flex items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <div className="w-full max-w-md !pb-8 !pr-8 !pl-8">
          <div className="bg-[var(--color-primary)]/10 rounded-lg !p-8">
            <SignInForm flow={flow} setFlow={setFlow} />
          </div>
        </div>
      </div>
    </>
  );
};

export default SignInPage;