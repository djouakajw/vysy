import { NextResponse } from "next/server";
import { store, normalizeSubdomain } from "@/lib/store";
import type { DeliveryMode, SiteType, Language, PrimaryAction, ShopProduct, ShopDraft } from "@/lib/store";

type CreateShopBody = {
  draft?: ShopDraft | null;
  draftId?: string;
}

export async function GET(req: Request) {
  const { searchParams } = new URL(req.url);
  const userId = searchParams.get("userId");
  if (!userId) {
    return NextResponse.json({ error: "userId requis." }, { status: 400 });
  }
  const shop = store.getShopByUser(userId);
  return NextResponse.json({ shop: shop ?? null });
}

export async function POST(req: Request) {
  try {
    const body: CreateShopBody = await req.json();
    const { draft, draftId } = body;

    let shopData = draft;
    if (draftId && !draft) {
      const existing = store.getDraft(draftId);
      if (!existing) {
        return NextResponse.json({ error: "Brouillon introuvable." }, { status: 404 });
      }
      shopData = existing;
    }

    if (!shopData?.subdomain || !shopData?.siteType || !shopData?.siteName) {
      return NextResponse.json(
        { error: "Données de boutique incomplètes." },
        { status: 400 }
      );
    }

    // Reserve the subdomain atomically before creating the shop.
    const normalized = normalizeSubdomain(shopData.subdomain);
    const availability = store.isSubdomainAvailable(normalized);
    if (!availability.available) {
      return NextResponse.json(
        { error: "Ce sous-domaine est déjà utilisé." },
        { status: 409 }
      );
    }

    const shop = store.createShopFromData({
      orgId: "org-wizard",
      userId: "user-wizard",
      subdomain: normalized,
      siteType: shopData.siteType as SiteType,
      siteName: shopData.siteName,
      language: (shopData.language as Language) || "fr",
      product: shopData.product as ShopProduct,
      deliveryModes: (shopData.deliveryModes || []) as DeliveryMode[],
      primaryAction: shopData.primaryAction as PrimaryAction,
      actionValue: shopData.actionValue || null,
    });

    if (!shop) {
      return NextResponse.json(
        { error: "Impossible de créer la boutique." },
        { status: 409 }
      );
    }

    if (draftId) {
      store.updateDraft(draftId, { step: 5 });
    }

    return NextResponse.json({ shop });
  } catch {
    return NextResponse.json({ error: "Erreur lors de la création." }, { status: 500 });
  }
}
