"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

interface PublierClientProps {
  initialShopId: string;
}

export default function PublierClient({ initialShopId }: PublierClientProps) {
  const router = useRouter();
  const [status, setStatus] = useState<"idle" | "checking" | "ready" | "published" | "error">("checking");
  const [shop, setShop] = useState<{ id: string; subdomain: string; publicUrl: string; siteName: string } | null>(null);
  const [error, setError] = useState("");
  const [published, setPublished] = useState(false);

  useEffect(() => {
    fetch(`/api/shops?userId=wizard-user`)
      .then((r) => r.json())
      .then((data: { shop?: { id: string; subdomain: string; publicUrl: string; siteName: string } }) => {
        if (data.shop) {
          setShop(data.shop);
          setStatus("ready");
        } else {
          setStatus("error");
          setError("Boutique introuvable.");
        }
      })
      .catch(() => {
        setStatus("error");
        setError("Erreur de chargement.");
      });
  }, []);

  const handleActivate = async () => {
    if (!shop) return;
    setStatus("idle");
    try {
      const res = await fetch(`/api/shops/${shop.id}/publish`, { method: "POST" });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec");
      setPublished(true);
      setShop((prev) => prev ? { ...prev, publicUrl: data.shop.publicUrl } : null);
    } catch (err: unknown) {
      setError((err as { message?: string })?.message || "Échec de l&apos;activation. Vos données sont sauvegardées.");
    }
  };

  const handleRetry = () => {
    setError("");
    setStatus("checking");
    window.location.reload();
  };

  return (
    <main className="min-h-screen bg-white text-gray-900 px-4 py-6 sm:px-6 lg:px-8">
      <nav className="flex items-center justify-between px-8 py-4 border-b max-w-5xl mx-auto">
        <span className="font-bold text-xl text-green-600">PROCDOR</span>
        <Link href="/" className="text-sm text-gray-600 hover:text-gray-900">← Accueil</Link>
      </nav>

      <section className="max-w-2xl mx-auto py-12">
        <h1 className="text-2xl font-bold mb-2">Vérification de l&apos;activité et activation</h1>
        <p className="text-gray-600 mb-8">Avant la publication, nous contrôlons la cohérence de votre dossier.</p>

        {status === "checking" && (
          <div className="flex items-center gap-3 text-green-600 animate-pulse">
            <span className="w-3 h-3 rounded-full bg-green-600"></span>
            <span>Vérification du sous-domaine et des informations essentielles…</span>
          </div>
        )}

        {status === "error" && (
          <div className="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            {error}
            <button onClick={handleRetry} className="mt-3 text-sm underline text-red-700">Réessayer</button>
          </div>
        )}

        {status === "ready" && shop && (
          <>
            <div className="mb-8 p-4 rounded-lg border border-gray-200 bg-gray-50">
              <p className="text-sm font-medium mb-2">Votre site</p>
              <p className="text-gray-900 font-semibold">{shop.siteName}</p>
              <p className="text-gray-600 text-sm">Adresse : <span className="font-mono">{shop.subdomain}.procdor.com</span></p>
              <p className="text-green-600 text-sm font-medium mt-2">✓ Sous-domaine disponible et réservable</p>
            </div>

            <button
              onClick={handleActivate}
              disabled={published}
              className="w-full bg-green-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed transition shadow-lg shadow-green-200"
            >
              {published ? "Activation en cours…" : "ACTIVER MON SITE"}
            </button>
          </>
        )}

        {published && shop && (
          <>
            <div className="text-center py-12">
              <div className="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4 text-green-600 font-bold">✓</div>
              <h2 className="text-2xl font-bold mb-3">Félicitations ! Votre site PROCDOR est en ligne.</h2>
              <p className="text-gray-600 mb-6">Votre boutique est publiée à l&apos;adresse suivante :</p>
              <p className="font-mono text-lg text-gray-900 mb-6">{shop.publicUrl}</p>
              <div className="flex flex-col sm:flex-row gap-3 justify-center">
                <a
                  href={shop.publicUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-6 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition"
                >
                  Voir mon site
                </a>
                <button
                  onClick={() => {
                    navigator.clipboard.writeText(shop.publicUrl);
                  }}
                  className="px-6 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition"
                >
                  Copier mon lien
                </button>
                <a
                  href={`https://wa.me/?text=${encodeURIComponent("Découvrez mon site : " + shop.publicUrl)}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-6 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition"
                >
                  Partager sur WhatsApp
                </a>
              </div>
            </div>
          </>
        )}
      </section>
    </main>
  );
}
