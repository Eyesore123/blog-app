"use client";
import { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import InputError from "../components/input-error";
import '../../css/app.css';

export default function SignInForm() {
  const { errors } = usePage().props as { errors: Record<string, string> };

  const [flow, setFlow] = useState<"signIn" | "signUp">("signIn");
  const [submitting, setSubmitting] = useState(false);

  // Anonymous sign in logic
function handleAnonymousSignIn() {
  setSubmitting(true);
  router.post("/anonymous-login", {}, {
    onFinish: () => setSubmitting(false),
    onSuccess: () => router.get("/"),
  });
}

  return (
    <div className="w-full !mt-10 !mb-10">
      <form
        className="flex flex-col gap-4"
        onSubmit={(e) => {
          e.preventDefault();
          setSubmitting(true);

          const formData = new FormData(e.currentTarget);
          const action = flow === "signIn" ? "/login" : "/register";

          router.post(action, formData, {
            onFinish: () => setSubmitting(false),
            onSuccess: () => router.get("/"),
          });
        }}
      >
        <input
          className="input-field border border-white !p-2 w-80"
          type="email"
          name="email"
          placeholder="Email"
          required
        />
        <InputError message={errors.email} />
        <div className="input-container">
          <input
          className="input-field border border-white !p-2 w-80"
          type="name"
          name="name"
          placeholder="Name"
          required
        />
        </div>
        <InputError message={errors.password} />

        {flow === "signUp" && (
  <>
    <div className="input-container">
      <input
        className="input-field border border-white !p-2 w-80"
        type="password"
        name="password"
        placeholder="Password"
        required
      />
    </div>
    <InputError message={errors.password} />

    <div className="input-container">
      <input
        className="input-field border border-white !p-2 w-80"
        type="password"
        name="password_confirmation"
        placeholder="Confirm Password"
        required
      />
    </div>
    <InputError message={errors.password_confirmation} />
  </>
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
      <button className="auth-button" onClick={handleAnonymousSignIn}>
        Sign in anonymously
      </button>
      </div>
    </div>
  );
}
