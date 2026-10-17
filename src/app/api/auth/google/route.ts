import { NextResponse } from "next/server";
import { store } from "@/lib/store";

// Google auth placeholder (OAuth flow to be wired to a real provider per documentation).
// Returns the user and a redirect to the creation home with the candidate subdomain preserved.
export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { email, name, image, candidateSubdomain } = body as {
      email?: string;
      name?: string;
      image?: string;
      candidateSubdomain?: string;
    };

    if (!email) {
      return NextResponse.json({ error: "E-mail manquant." }, { status: 400 });
    }

    let user = store.findUserByEmail(email);
    if (!user) {
      user = store.createUser(email, name || email.split("@")[0], image || null);
    }

    const orgs = store.getOrganizationsByUser(user.id);
    if (orgs.length === 0) {
      store.createOrganization(user.id, "Mon organisation");
    }

    // Preserve candidate subdomain for the session (store in draft if provided)
    let draft = store.getDraftByUser(user.id);
    if (!draft && candidateSubdomain) {
      draft = store.createDraft(user.id, candidateSubdomain);
    }

    return NextResponse.json({ user, draftId: draft?.id ?? null });
  } catch {
    return NextResponse.json({ error: "Erreur d'authentification Google." }, { status: 500 });
  }
}
