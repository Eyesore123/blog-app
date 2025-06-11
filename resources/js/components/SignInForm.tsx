import React, { useState } from "react";
import { usePage, router } from "@inertiajs/react";
import InputError from "../components/input-error";
import { getCsrfToken } from "../components/auth";
import Spinner4 from "./Spinner4";
import "../../css/app.css";
import { useTheme } from "../context/ThemeContext";

type SignInFormProps = {
  flow: "signIn" | "signUp";
  setFlow: React.Dispatch<React.SetStateAction<"signIn" | "signUp">>;
};

function storeAuthToken(token: string) {
  localStorage.setItem("auth_token", token);
}

function removeAuthToken() {
  localStorage.removeItem("auth_token");
}

export default function SignInForm({ flow, setFlow }: SignInFormProps) {
  const { errors } = usePage().props as { errors: Record<string, string> };
  const [submitting, setSubmitting] = useState(false);
  const { theme } = useTheme();

const handleSuccess = (page: any, currentFlow: "signIn" | "signUp") => {
  const token = (page?.props?.auth_token as string) || null;
  if (token) {
    if (currentFlow === "signUp") {
      // Remove auth token from local storage when signing up, not in use in the current flow
      // removeAuthToken();
      // Log out the user
      // router.post("/logout");
    } else {
      storeAuthToken(token);
    }
  }

  if (currentFlow === "signUp" && !page.props.verified) {
    setFlow("signIn"); // Update flow state to "signIn" after sign-up
    router.visit("/verifyemailnotice", {
  data: {
    email: page.props.email,
  },
});
  }
  // signIn and anonymous users: let Laravel middleware handle the redirect
};

  const handleAnonymousSignIn = async () => {
    setSubmitting(true);
    await getCsrfToken();

    router.post("/anonymous-login", {}, {
      onFinish: () => setSubmitting(false),
      onSuccess: (page) => handleSuccess(page, "signIn"), // Anonymous treated as signIn
    });
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitting(true);
    await getCsrfToken();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);
    const action = flow === "signIn" ? "/login" : "/register";

    router.post(action, formData, {
      forceFormData: true,
      onFinish: () => setSubmitting(false),
      onSuccess: (page) => handleSuccess(page, flow),
    });
  };

  return (
  <div className="w-full !mt-10 !mb-10">
    {submitting ? (
      <>
        <h3 className="w-full flex justify-center items-center !mb-10 font-bold">
          {flow === "signIn" ? "Signing in..." : "Signing up..."}
        </h3>
        <div className="flex justify-center !py-10 !mb-20">
            <Spinner4 />
          </div>
        </>
      ) : (
        <>
          <h3 className="text-2xl font-bold !mb-8 text-center">
            {flow === "signIn" ? "Sign In to Joni's Blog" : "Sign Up for Joni's Blog"}
          </h3>

          <form
            onSubmit={handleSubmit}
            className="flex flex-col gap-4 justify-center items-center"
          >
            {flow === "signUp" && (
              <div className="input-container">
                <input
                  className="input-field border border-white !p-2 !w-60 md:!w-80"
                  style={{ borderColor: theme === "dark" ? "#fff" : "#000" }}
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
                style={{ borderColor: theme === "dark" ? "#fff" : "#000" }}
                type="email"
                name="email"
                placeholder="Email"
                required
                disabled={submitting}
              />
              <InputError message={errors.email} />
            </div>

            {flow === "signUp" && (
              <div className="flex flex-col items-center !gap-3">
                <label
                  htmlFor="profile_photo"
                  className="text-sm text-slate-500 italic"
                >
                  Profile photo (optional)
                </label>
                <input
                  id="profile_photo"
                  name="profile_photo"
                  type="file"
                  accept="image/*"
                  disabled={submitting}
                  className="file-input block w-60 md:w-80 border-2 border-dashed rounded-lg !p-2 cursor-pointer bg-transparent hover:bg-slate-50 transition
                    border-gray-400 text-sm text-slate-500 file:!mr-4 file:!py-2 file:!px-4
                    file:rounded-full file:border-0 file:text-sm file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <InputError message={errors.profile_photo} />
              </div>
            )}

            <div className="input-container">
              <input
                className="input-field border border-white !p-2 !w-60 md:!w-80"
                style={{ borderColor: theme === "dark" ? "#fff" : "#000" }}
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
                  style={{ borderColor: theme === "dark" ? "#fff" : "#000" }}
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
                <a
                  href="/forgot-password"
                  className="text-blue-600 font-semibold hover:underline"
                >
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
