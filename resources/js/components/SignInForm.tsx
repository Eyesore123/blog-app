import React, { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import InputError from "../components/input-error";
import { getCsrfToken } from '../components/auth'; // Adjust path if needed
import Spinner4 from './Spinner4'; // Adjust path if needed
import '../../css/app.css';
import { useTheme } from '../context/ThemeContext';

type SignInFormProps = {
  flow: "signIn" | "signUp";
  setFlow: React.Dispatch<React.SetStateAction<"signIn" | "signUp">>;
};

// Helper function to store token
function storeAuthToken(token: string) {
  localStorage.setItem('auth_token', token);
}

export default function SignInForm({ flow, setFlow }: SignInFormProps) {
  const { errors } = usePage().props as { errors: Record<string, string> };
  const [submitting, setSubmitting] = useState(false);
  const { theme } = useTheme();

  // Handle successful login/signup
  const handleSuccess = (page: any) => {
    const token = (page?.props?.auth_token as string) || null;
    if (token) {
      storeAuthToken(token);
    }
    router.get("/");  // Redirect to home page
  };

  // Anonymous sign-in logic
  async function handleAnonymousSignIn() {
    setSubmitting(true);
    await getCsrfToken();
    router.post("/anonymous-login", {}, {
      onFinish: () => setSubmitting(false),
      onSuccess: (page) => handleSuccess(page),
    });
  }

  // Form submit handler
  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSubmitting(true);
    await getCsrfToken();

    const form = e.target as HTMLFormElement;
    if (!(form instanceof HTMLFormElement)) {
      console.error("handleSubmit: target is not a form");
      setSubmitting(false);
      return;
    }

    const formData = new FormData(form);
    const action = flow === "signIn" ? "/login" : "/register";

    router.post(action, formData, {
      forceFormData: true,
      onFinish: () => setSubmitting(false),
      onSuccess: (page) => handleSuccess(page),
    });
  }

  return (
    <div className="w-full !mt-10 !mb-10">
      {submitting ? (
        <><h3 className="w-full flex justify-center items-center !mb-10 font-bold">Signing in...</h3>
        <div className="flex justify-center !py-10 !mb-20">
          <Spinner4 />
        </div>
        </>)
      : (
        <>
          <h3 className="text-2xl font-bold !mb-8 text-center">
            {flow === "signIn" ? "Sign In to Joni's Blog" : "Sign Up for Joni's Blog"}
          </h3>
          <form className="flex flex-col gap-4 justify-center items-center" onSubmit={handleSubmit}>
            <input
              className="input-field border border-white !p-2 !w-60 md:!w-80"
              style={{ borderColor: theme === 'dark' ? '#fff' : '#000' }}
              type="email"
              name="email"
              placeholder="Email"
              required
              disabled={submitting}
            />
            <InputError message={errors.email} />

            {flow === "signUp" && (
              <div className="input-container">
                <input
                  className="input-field border border-white !p-2 !w-60 md:!w-80"
                  style={{ borderColor: theme === 'dark' ? '#fff' : '#000' }}
                  type="text"
                  name="name"
                  placeholder="Name"
                  required
                  disabled={submitting}
                />
                <InputError message={errors.name} />
              </div>
            )}

            <div className="input-container">
              <input
                className="input-field border border-white !p-2 !w-60 md:!w-80"
                style={{ borderColor: theme === 'dark' ? '#fff' : '#000' }}
                type="password"
                name="password"
                placeholder="Password"
                required
                disabled={submitting}
              />
              <InputError message={errors.password} />
            </div>

            {flow === "signUp" && (
              <div className="input-container">
                <input
                  className="input-field border border-white !p-2 !w-60 md:!w-80"
                  style={{ borderColor: theme === 'dark' ? '#fff' : '#000' }}
                  type="password"
                  name="password_confirmation"
                  placeholder="Confirm Password"
                  required
                  disabled={submitting}
                />
                <InputError message={errors.password_confirmation} />
              </div>
            )}

            <div className="text-sm text-center !mb-4">
              {flow === "signIn" && (
                <a href="/forgot-password" className="text-blue-600 font-semibold hover:underline">
                  Forgot your password?
                </a>
              )}
            </div>

            <button
              className="bg-[#5800FF] hover:bg-[#4600cc] text-white font-semibold !py-2 !px-6 rounded transition disabled:opacity-50"
              type="submit"
              disabled={submitting}
            >
              {flow === "signIn" ? "Sign in" : "Sign up"}
            </button>
          </form>

          <div className="flex items-center justify-center !my-3">
            <hr className="!my-8 grow" />
            <span className="!mx-4 text-slate-400">or</span>
            <hr className="!my-8 grow" />
          </div>

          <div className="button-container flex w-full items-center justify-center">
            <button
              className="bg-[#5800FF] hover:bg-[#4600cc] text-white font-semibold !py-2 !px-6 rounded transition disabled:opacity-50"
              type="button"
              onClick={handleAnonymousSignIn}
              disabled={submitting}
            >
              Continue without account
            </button>
          </div>

          <div className="text-center text-slate-600 !mt-4">
            <span>
              {flow === "signIn"
                ? "Don't have an account? "
                : "Already have an account? "}
            </span>
            <button
              type="button"
              className="text-blue-500 cursor-pointer"
              onClick={() => setFlow(flow === "signIn" ? "signUp" : "signIn")}
              disabled={submitting}
            >
              {flow === "signIn" ? "Sign up instead" : "Sign in instead"}
            </button>
          </div>
        </>
      )}
    </div>
  );
}
