import React from 'react';
import SignInForm from '../../components/SignInForm';
import Header from '@/components/Header';
import { Navbar } from '@/components/Navbar';

const SignInPage: React.FC = () => {
    return (
        <>
        <Navbar />
        <Header />
        <div className="min-h-screen flex items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
      <div className="w-full max-w-md !p-8">
        <h3 className="text-2xl font-bold !mb-8 text-center">Sign In to Joni's Blog</h3>
        <div className="bg-[var(--color-primary)]/10 rounded-lg !p-8">
          <SignInForm />
        </div>
      </div>
    </div>
        </>
    );
};

export default SignInPage;
