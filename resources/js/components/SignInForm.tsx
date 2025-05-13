"use client";
import { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import InputError from "../components/input-error";
import { getCsrfToken } from '../components/auth'; // Adjust the import path as necessary
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

  // Handles common onSuccess logic: optionally extract token and redirect
  const handleSuccess = (page: any) => {
    const token = (page?.props?.auth_token as string) || null;
    if (token) {
      storeAuthToken(token);
    }
    router.get("/");  // redirect to home
  };

  // Anonymous sign-in logic
  async function handleAnonymousSignIn() {
    setSubmitting(true);

    // Fetch CSRF token before posting
    await getCsrfToken();

    router.post("/anonymous-login", {}, {
      onFinish: () => setSubmitting(false),
      onSuccess: (page) => handleSuccess(page),
    });
  }

  // Regular sign-in / sign-up logic
  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSubmitting(true);
  
    await getCsrfToken();
  
    const form = e.target as HTMLFormElement;   // SAFER than currentTarget
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
      <form className="flex flex-col gap-4 justify-center items-center" onSubmit={handleSubmit}>
        <input
          className="input-field border border-white !p-2 !w-60 !md:w-80"
          style={{borderColor: theme === 'dark' ? '#fff' : '#000'}}
          type="email"
          name="email"
          placeholder="Email"
          required
        />
        <InputError message={errors.email} />

        {flow === "signUp" && (
          <div className="input-container">
            <input
              className="input-field border border-white !p-2 !w-60 !md:w-80"
              style={{borderColor: theme === 'dark' ? '#fff' : '#000'}}
              type="text"
              name="name"
              placeholder="Name"
              required
            />
            <InputError message={errors.name} />
          </div>
        )}

        <div className="input-container">
          <input
            className="input-field border border-white !p-2 !w-60 !md:w-80"
            style={{borderColor: theme === 'dark' ? '#fff' : '#000'}}
            type="password"
            name="password"
            placeholder="Password"
            required
          />
          <InputError message={errors.password} />
        </div>

        {flow === "signUp" && (
          <div className="input-container">
            <input
              className="input-field border border-white !p-2 !w-60 !md:!w-60 !md:w-80"
              style={{borderColor: theme === 'dark' ? '#fff' : '#000'}}
              type="password"
              name="password_confirmation"
              placeholder="Confirm Password"
              required
            />
            <InputError message={errors.password_confirmation} />
          </div>
        )}

        <button className="auth-button" type="submit" disabled={submitting}>
          {flow === "signIn" ? "Sign in" : "Sign up"}
        </button>

        <div className="text-center text-slate-600">
          <span>
            {flow === "signIn"
              ? "Don't have an account? "
              : "Already have an account? "}
          </span>
          <button
            type="button"
            className="text-blue-500 cursor-pointer"
            onClick={() => setFlow(flow === "signIn" ? "signUp" : "signIn")}
          >
            {flow === "signIn" ? "Sign up instead" : "Sign in instead"}
          </button>
        </div>
      </form>

      <div className="flex items-center justify-center !my-3">
        <hr className="!my-4 grow" />
        <span className="!mx-4 text-slate-400">or</span>
        <hr className="!my-4 grow" />
      </div>

      <div className="button-container flex w-full items-center justify-center">
        <button className="auth-button" onClick={handleAnonymousSignIn} disabled={submitting}>
          Sign in anonymously
        </button>
      </div>
    </div>
  );
}
