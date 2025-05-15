import { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import InputError from "@/components/input-error";
import { Navbar } from "@/components/Navbar";
import Header from "@/components/Header";
import { getCsrfToken } from "@/components/auth";

export default function ResetPasswordPage({ token, email }: { token: string; email: string }) {
  const { errors } = usePage().props as { errors: Record<string, string> };
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSubmitting(true);

    await getCsrfToken();

    router.post("/reset-password", {
      token,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }, {
      onFinish: () => setSubmitting(false),
    });
  }

  return (
    <>
      <Navbar />
      <Header />
      <div className="min-h-screen flex items-center justify-center bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <div className="w-full max-w-md !p-8">
          <h3 className="text-2xl font-bold !mb-8 text-center">
            Choose a New Password
          </h3>
          <div className="bg-[var(--color-primary)]/10 rounded-lg !p-8">
            <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
              <input
                className="input-field border border-white !p-2 !w-full"
                type="email"
                name="email"
                value={email}
                readOnly
              />
              <input
                className="input-field border border-white !p-2 !w-full"
                type="password"
                name="password"
                placeholder="New password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
              <InputError message={errors.password} />
              <input
                className="input-field border border-white !p-2 !w-full"
                type="password"
                name="password_confirmation"
                placeholder="Confirm password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
                required
              />
              <InputError message={errors.password_confirmation} />
              <button
                type="submit"
                className="auth-button"
                disabled={submitting}
              >
                Reset Password
              </button>
            </form>
          </div>
        </div>
      </div>
    </>
  );
}
