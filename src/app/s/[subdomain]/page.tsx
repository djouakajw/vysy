import { store } from "@/lib/store";

export async function generateStaticParams() {
  return [];
}

export default async function PublicShopPage({
  params,
}: {
  params: Promise<{ subdomain: string[] }>;
}) {
  const { subdomain } = await params;
  const sub = Array.isArray(subdomain) ? subdomain[0] : subdomain;
  const shop = store.getShopBySubdomain(sub);

  if (!shop || !shop.isPublished) {
    return (
      <main className="min-h-screen bg-white text-gray-900 flex items-center justify-center px-4">
        <div className="text-center">
          <h1 className="text-2xl font-bold mb-2">Site introuvable</h1>
          <p className="text-gray-600">Cette boutique PROCDOR n&apos;existe pas ou n&apos;est pas publiée.</p>
        </div>
      </main>
    );
  }

  const actionLabel =
    shop.primaryAction === "whatsapp" ? "Contacter par WhatsApp" :
    shop.primaryAction === "call" ? "Appeler" :
    shop.primaryAction === "external-link" ? "Visiter le lien externe" :
    "Prendre rendez-vous";

  return (
    <main className="min-h-screen bg-white text-gray-900">
      <nav className="border-b bg-white/95 backdrop-blur sticky top-0 z-20 px-6 py-3 flex items-center justify-between">
        <span className="font-bold text-lg text-green-600">{shop.siteName}</span>
        <a href={shop.publicUrl} className="text-sm text-gray-600 hover:text-gray-900">{shop.subdomain}.procdor.com</a>
      </nav>

      <div className="max-w-3xl mx-auto py-10 px-6">
        <header className="text-center mb-10">
          <h1 className="text-3xl font-bold mb-2">{shop.siteName}</h1>
          <p className="text-gray-600">{shop.product.description || "Découvrez notre offre."}</p>
        </header>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <section className="space-y-6">
            <h2 className="text-xl font-bold mb-2">Notre offre</h2>
            <div className="bg-gray-50 rounded-xl p-6 border border-gray-100">
              <h3 className="font-semibold mb-1">{shop.product.name}</h3>
              {shop.product.price && <p className="text-lg font-bold text-green-700">{shop.product.price} €</p>}
              {shop.product.category && <p className="text-sm text-gray-500 mb-2">Catégorie : {shop.product.category}</p>}
              {shop.product.description && <p className="text-gray-700">{shop.product.description}</p>}
            </div>
            <div className="flex gap-3">
              {shop.deliveryModes.includes("delivery") && <span className="px-3 py-1 rounded-full border border-green-200 text-green-700 text-xs font-medium">Livraison</span>}
              {shop.deliveryModes.includes("pickup") && <span className="px-3 py-1 rounded-full border border-green-200 text-green-700 text-xs font-medium">Retrait</span>}
              {shop.deliveryModes.includes("onsite") && <span className="px-3 py-1 rounded-full border border-green-200 text-green-700 text-xs font-medium">Sur place</span>}
              {shop.deliveryModes.includes("digital-whatsapp") && <span className="px-3 py-1 rounded-full border border-green-200 text-green-700 text-xs font-medium">WhatsApp</span>}
            </div>
          </section>

          <section className="bg-green-600 rounded-2xl p-8 text-white flex flex-col justify-center min-h-[300px]">
            <h2 className="text-xl font-bold mb-4">Passez à l&apos;action</h2>
            {shop.primaryAction === "whatsapp" && (
              <a
                href={`https://wa.me/${(shop.actionValue || "").replace(/\D/g, "")}?text=${encodeURIComponent("Bonjour, je découvre votre site " + shop.siteName)}`}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg"
              >
                {actionLabel}
              </a>
            )}
            {shop.primaryAction === "call" && (
              <a
                href={`tel:${shop.actionValue}`}
                className="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg"
              >
                {actionLabel}
              </a>
            )}
            {shop.primaryAction === "external-link" && (
              <a
                href={shop.actionValue || ""}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg"
              >
                {actionLabel}
              </a>
            )}
            {shop.primaryAction === "appointment" && (
              <button className="w-full bg-white text-green-700 px-6 py-4 rounded-xl font-bold text-center hover:bg-green-50 transition text-lg">
                {actionLabel}
              </button>
            )}
          </section>
        </div>

        <footer className="mt-12 text-center text-xs text-gray-500">
          Propulsé par PROCDOR &mdash; {shop.subdomain}.procdor.com
        </footer>
      </div>
    </main>
  );
}
