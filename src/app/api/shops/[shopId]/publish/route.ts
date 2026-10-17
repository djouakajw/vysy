import { NextResponse } from "next/server";
import { store } from "@/lib/store";

// POST /api/shops/[shopId]/publish - publish a shop
export async function POST(
  req: Request,
  { params }: { params: Promise<{ shopId: string }> },
) {
  try {
    const { shopId } = await params;
    const shop = store.publishShop(shopId);
    if (!shop) {
      return NextResponse.json(
        { error: "Boutique introuvable ou sous-domaine plus disponible." },
        { status: 409 }
      );
    }

    return NextResponse.json({ shop, publicUrl: shop.publicUrl });
  } catch {
    return NextResponse.json({ error: "Erreur lors de la publication." }, { status: 500 });
  }
}
