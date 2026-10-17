"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

interface CreationHomeClientProps {
  initialSubdomain: string;
}

export default function CreationHomeClient({ initialSubdomain }: CreationHomeClientProps) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const handleCreate = () => {
    setLoading(true);
    router.push(`/creer/1?subdomain=${encodeURIComponent(initialSubdomain)}`);
  };

  return (
    <main className="min-h-screen bg-white text-gray-900 px-4 py-6 sm:px-6 lg:px-8">
      <nav className="flex items-center justify-between px-8 py-4 border-b max-w-5xl mx-auto">
        <span className="font-bold text-xl text-green-600">PROCDOR</span>
        <Link href="/" className="text-sm text-gray-600 hover:text-gray-900">← Choisir un autre sous-domaine</Link>
      </nav>

      <section className="max-w-2xl mx-auto py-12 text-center">
        <h1 className="text-3xl font-bold mb-3">Créez votre site maintenant</h1>
        <p className="text-gray-600 mb-8">Votre boutique, votre activité, votre présence en ligne. Tout commence ici.</p>
        {initialSubdomain && (
          <p className="text-sm text-green-600 mb-6 font-medium">Sous-domaine conservé : <span className="font-mono">{initialSubdomain}.procdor.com</span></p>
        )}
        <button
          onClick={handleCreate}
          disabled={loading}
          className="inline-block bg-green-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-green-700 transition shadow-lg shadow-green-200"
        >
          {loading ? "Chargement…" : "Créer mon site maintenant"}
        </button>
      </section>
    </main>
  );
}
