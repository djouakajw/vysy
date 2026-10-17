import { NextResponse } from "next/server";
import { store, normalizeSubdomain } from "@/lib/store";
import { validateSubdomain } from "@/lib/validations";

const SUFFIX = ".procdor.com";

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { name, shopId } = body as { name?: string; shopId?: string };

    if (!name) {
      return NextResponse.json({ error: "Nom manquant." }, { status: 400 });
    }

    const result = validateSubdomain(name);
    if (!result.valid) {
      return NextResponse.json(
        { available: false, errors: result.errors, suggestion: result.suggestion },
        { status: 409 }
      );
    }

    const normalized = result.normalized;
    const full = `${normalized}${SUFFIX}`;

    // Atomic reservation: claim the subdomain for this shop.
    const claimed = store.reserveSubdomain(normalized, shopId || "draft");
    if (!claimed) {
      return NextResponse.json(
        { available: false, errors: [{ field: "subdomain", message: "Ce nom est déjà utilisé." }] },
        { status: 409 }
      );
    }

    return NextResponse.json({ available: true, normalized, full });
  } catch (err) {
    return NextResponse.json({ error: "Erreur lors de la réservation." }, { status: 500 });
  }
}
