import { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import InputError from "@/components/input-error";
import { Navbar } from "@/components/Navbar";
import Header from "@/components/Header";
import { getCsrfToken } from "@/components/auth";

export default function ForgotPasswordPage() {
  const { errors, status } = usePage().props as {
    errors: Record<string, string>;
    status?: string;
  };
  const [email, setEmail] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSubmitting(true);

    await getCsrfToken();

    router.post("/forgot-password", { email }, {
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
            Reset Your Password
          </h3>
          <div className="bg-[var(--color-primary)]/10 rounded-lg !p-8">
            {status && (
              <div className="text-green-500 text-sm mb-4 text-center">
                {status}
              </div>
            )}
            <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
              <input
                className="input-field border border-white !p-2 !w-full"
                type="email"
                name="email"
                placeholder="Your email address"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
              <InputError message={errors.email} />
              <button
                type="submit"
                className="auth-button"
                disabled={submitting}
              >
                Email Password Reset Link
              </button>
            </form>
          </div>
        </div>
      </div>
    </>
  );
}
