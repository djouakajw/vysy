import { Suspense } from "react";
import Link from "next/link";
import AuthClient from "./_client";

export default async function AuthPage({
  searchParams,
}: {
  searchParams: { subdomain?: string };
}) {
  return (
    <main className="min-h-screen bg-white text-gray-900 px-4 py-6 sm:px-6 lg:px-8">
      <nav className="flex items-center justify-between px-8 py-4 border-b max-w-5xl mx-auto">
        <span className="font-bold text-xl text-green-600">PROCDOR</span>
        <Link href="/" className="text-sm text-gray-600 hover:text-gray-900">← Choisir un autre sous-domaine</Link>
      </nav>

      <Suspense fallback={<div className="max-w-md mx-auto py-12">Chargement…</div>}>
        <AuthClient initialSubdomain={searchParams.subdomain || ""} />
      </Suspense>
    </main>
  );
}
