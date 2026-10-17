import { NextResponse } from "next/server";
import { store } from "@/lib/store";

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { email } = body as { email?: string };

    if (!email || !email.includes("@")) {
      return NextResponse.json({ error: "Adresse e-mail invalide." }, { status: 400 });
    }

    let user = store.findUserByEmail(email);
    if (!user) {
      user = store.createUser(email, email.split("@")[0], null);
    }

    // Ensure organization exists
    const orgs = store.getOrganizationsByUser(user.id);
    if (orgs.length === 0) {
      store.createOrganization(user.id, "Mon organisation");
    }

    return NextResponse.json({ user, requiresPassword: false });
  } catch {
    return NextResponse.json({ error: "Erreur d'authentification." }, { status: 500 });
  }
}
