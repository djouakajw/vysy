"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

interface AuthClientProps {
  initialSubdomain: string;
}

export default function AuthClient({ initialSubdomain }: AuthClientProps) {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [providerError, setProviderError] = useState("");

  const handleEmail = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const res = await fetch("/api/auth/email", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec");
      router.push(`/creer?subdomain=${encodeURIComponent(initialSubdomain)}`);
    } catch {
      setError("Erreur d&apos;authentification.");
    } finally {
      setLoading(false);
    }
  };

  const handleGoogle = async () => {
    setProviderError("");
    setLoading(true);
    try {
      const res = await fetch("/api/auth/google", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: "utilisateur@exemple.com", name: "Utilisateur Exemple", candidateSubdomain: initialSubdomain }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec Google");
      router.push(`/creer?subdomain=${encodeURIComponent(initialSubdomain)}`);
    } catch {
      setProviderError("Erreur d&apos;authentification Google.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="min-h-screen bg-white text-gray-900 px-4 py-6 sm:px-6 lg:px-8">
      <nav className="flex items-center justify-between px-8 py-4 border-b max-w-5xl mx-auto">
        <span className="font-bold text-xl text-green-600">PROCDOR</span>
        <Link href="/" className="text-sm text-gray-600 hover:text-gray-900">← Choisir un autre sous-domaine</Link>
      </nav>

      <section className="max-w-md mx-auto py-12">
        <div className="text-center mb-10">
          <h2 className="text-2xl font-bold mb-2">Créer votre compte PROCDOR</h2>
          <p className="text-gray-600">Votre adresse sera conservée pendant la création de votre site.</p>
          {initialSubdomain && (
            <p className="text-sm text-green-600 mt-2 font-medium">Sous-domaine conservé : <span className="font-mono">{initialSubdomain}.procdor.com</span></p>
          )}
        </div>

        {error && (
          <div className="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{error}</div>
        )}
        {providerError && (
          <div className="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{providerError}</div>
        )}

        <button
          onClick={handleGoogle}
          disabled={loading}
          className="w-full mb-6 flex items-center justify-center gap-2 px-4 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold shadow-sm hover:bg-gray-50 disabled:opacity-60 transition"
        >
          <svg className="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.8 3.28-8.09"/></svg>
          Continuer avec Google
        </button>

        <p className="text-center text-gray-500 text-sm mb-6">Ou continuer avec votre adresse e-mail</p>

        <form onSubmit={handleEmail} className="space-y-3">
          <div className="relative">
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="vous@exemple.com"
              disabled={loading}
              className="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-gray-50 text-gray-900"
            />
            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@</span>
          </div>
          <button
            type="submit"
            disabled={loading || !email}
            className="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            Continuer avec e-mail
          </button>
        </form>

        <p className="text-center text-xs text-gray-400 mt-8">
          En continuant, vous acceptez les conditions d&apos;utilisation de PROCDOR.
        </p>
      </section>
    </main>
  );
}
