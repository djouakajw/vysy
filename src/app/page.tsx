"use client";

import { useState, useEffect, useCallback } from "react";
import Link from "next/link";

const SUFFIX = ".procdor.com";

const RESERVED = ["admin", "app", "api", "support", "www", "procdor", "auth"];

export default function SubdomainEntry() {
  const [value, setValue] = useState("");
  const [status, setStatus] = useState<"idle" | "checking" | "available" | "taken" | "error">("idle");
  const [message, setMessage] = useState("");
  const [suggestion, setSuggestion] = useState<string | null>(null);
  const [normalized, setNormalized] = useState("");

  const check = useCallback(async () => {
    const raw = value.trim();
    if (!raw) {
      setStatus("idle");
      setMessage("");
      setSuggestion(null);
      return;
    }
    setStatus("checking");
    setMessage("Vérification en cours...");
    try {
      const res = await fetch(`/api/subdomain/check?name=${encodeURIComponent(raw)}`);
      const data = await res.json();
      setNormalized(data.normalized || "");
      if (data.available) {
        setStatus("available");
        setMessage("Ce nom est disponible.");
        setSuggestion(null);
      } else {
        setStatus("taken");
        setMessage(data.errors?.[0]?.message || "Ce nom est déjà utilisé.");
        setSuggestion(data.suggestion || null);
      }
    } catch {
      setStatus("error");
      setMessage("Erreur réseau, réessayez.");
    }
  }, [value]);

  useEffect(() => {
    const t = setTimeout(check, 400);
    return () => clearTimeout(t);
  }, [check]);

  const normalizedFull = normalized ? `${normalized}${SUFFIX}` : "";

  return (
    <main className="min-h-screen bg-white text-gray-900 px-4 py-6 sm:px-6 lg:px-8">
      <nav className="flex items-center justify-between px-8 py-4 border-b max-w-5xl mx-auto">
        <span className="font-bold text-xl text-green-600">PROCDOR</span>
        <a href="/dashboard" className="text-sm text-gray-600 hover:text-gray-900">Connexion</a>
      </nav>

      <section className="max-w-2xl mx-auto py-12">
        <div className="text-center mb-10">
          <h1 className="text-3xl font-bold mb-3">Votre activité mérite son propre site.</h1>
          <p className="text-gray-600">Choisissez votre adresse PROCDOR et créez votre présence en ligne.</p>
        </div>

        <form onSubmit={(e) => e.preventDefault()} className="space-y-4">
          <div className="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <div className="flex-1 relative group">
              <input
                id="subdomain"
                type="text"
                value={value}
                onChange={(e) => setValue(e.target.value)}
                placeholder="jodyfashion"
                className="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-gray-50 text-gray-900"
                disabled={status === "checking"}
                autoComplete="off"
              />
              <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">.procdor.com</span>
              {status === "checking" && (
                <span className="absolute right-3 top-1/2 -translate-y-1/2 text-green-500 animate-pulse">●</span>
              )}
            </div>
            <button
              type="button"
              onClick={check}
              disabled={!value.trim() || status === "checking"}
              className="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition whitespace-nowrap"
            >
              {status === "checking" ? "Vérification…" : "Vérifier la disponibilité"}
            </button>
          </div>

          {normalizedFull && (
            <p className="text-center text-xs text-gray-500 mt-2">Exemple : {normalizedFull}</p>
          )}

          {status !== "idle" && (
            <div className={`p-4 rounded-lg border text-sm flex items-center gap-3 max-w-md mx-auto ${
              status === "available"
                ? "bg-green-50 border-green-200 text-green-800"
                : status === "error"
                ? "bg-red-50 border-red-200 text-red-800"
                : "bg-orange-50 border-orange-200 text-orange-800"
            }`}>
              {status === "available" ? (
                <span className="text-green-600 font-medium">✓</span>
              ) : status === "error" ? (
                <span className="text-red-600 font-medium">!</span>
              ) : (
                <span className="text-orange-600 font-medium">!</span>
              )}
              {message}
              {status === "taken" && suggestion && (
                <>
                  <br />
                  <span className="mt-1 block text-green-600 text-sm underline underline-offset-4 hover:decoration-solid cursor-pointer" onClick={() => setValue(suggestion)}>
                    Suggestion : {suggestion}
                  </span>
                </>
              )}
            </div>
          )}

          {status === "available" && (
            <div className="max-w-md mx-auto mt-8">
              <Link
                href={`/auth?subdomain=${encodeURIComponent(normalized)}`}
                className="w-full bg-green-600 text-white px-6 py-4 rounded-xl font-semibold text-center hover:bg-green-700 transition shadow-lg shadow-green-200"
              >
                Continuer avec ce sous-domaine
              </Link>
            </div>
          )}
        </form>
      </section>
    </main>
  );
}
