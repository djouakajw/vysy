import { Suspense } from "react";
import Link from "next/link";
import WizardClient from "./_client";

export default async function StepPage({
  searchParams,
}: {
  searchParams: { subdomain?: string };
}) {
  return (
    <main className="min-h-screen bg-gray-50 text-gray-900">
      <nav className="border-b bg-white px-6 py-3 flex items-center justify-between">
        <Link href="/" className="font-bold text-green-600 text-lg">PROCDOR</Link>
        <a href="/dashboard" className="text-sm text-gray-600 hover:text-gray-900">Tableau de bord</a>
      </nav>

      <Suspense fallback={<div className="max-w-4xl mx-auto py-8 px-6">Chargement…</div>}>
        <WizardClient initialSubdomain={searchParams.subdomain || ""} />
      </Suspense>
    </main>
  );
}
