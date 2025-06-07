import React from "react";
import { Navbar } from "@/components/Navbar";
import Header from "@/components/Header";
import { useTheme } from "../context/ThemeContext";

export default function PrivacyPolicy() {
  const { theme } = useTheme();

  return (
    <div className={`min-h-160 ${theme}`}>
      <div className="min-h-160 bg-[var(--bg-primary)] text-[var(--text-primary)]">
        <Navbar />
        <Header />
        <main className="!p-4 md:!p-8 flex flex-col items-center">
          <div className="w-full max-w-2xl bg-[#5800FF]/10 rounded-lg shadow !p-6 md:!p-10 !mt-8">
            <h1 className="text-2xl md:text-3xl font-bold !mb-16 text-[#5800FF]">
              Privacy Policy
            </h1>
            <p className="!mb-4 text-base md:text-lg opacity-90">
              This page describes how we collect, use, and protect your personal data. Please read this policy carefully.
            </p>
            <h2 className="text-lg md:text-xl font-semibold !mt-6 !mb-2">What data do we collect?</h2>
            <ul className="list-disc !ml-6 !mb-4 text-base">
              <li>Your name and email address (when registering)</li>
              <li>Comments and posts you submit</li>
              <li>Technical data (IP address, browser, etc.)</li>
            </ul>
            <h2 className="text-lg md:text-xl font-semibold !mt-6 !mb-2">How is your data used?</h2>
            <ul className="list-disc !ml-6 !mb-4 text-base">
              <li>To provide and improve the blog service</li>
              <li>To send notifications or newsletters (if you make an account and subscribe)</li>
              <li>To ensure security and prevent abuse</li>
              <li>Our site takes backups of your data every day. Previous backups get deleted and replaced with new ones.</li>
              <li>We use SSL encryption to protect your data from unauthorized access. Hashing is used to protect your password. We never see your password or any other sensitive data!</li>
            </ul>
            <h2 className="text-lg md:text-xl font-semibold !mt-6 !mb-2">Your rights</h2>
            <ul className="list-disc !ml-6 !mb-4 text-base">
              <li>You can request to see, correct, or delete your data at any time</li>
              <li>You can unsubscribe from emails at any time</li>
              <li>We keep your email address private and delete it automatically when the account gets deleted.</li>
            </ul>
            <h2 className="text-lg md:text-xl font-semibold !mt-6 !mb-2">We take your privacy seriously</h2>
                <ul className="list-disc !ml-6 !mb-4 text-base">
              <li>You can always sign in anonymously in case you don't want to share your email address.</li>
            </ul>
            <p className="!mt-8 text-sm opacity-70">
              For more information, contact the site administrator. Email: joni.putkinen@protonmail.com
            </p>
          </div>
        </main>
      </div>
    </div>
  );
}