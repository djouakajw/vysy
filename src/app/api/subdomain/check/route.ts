import { NextResponse } from "next/server";
import { store, normalizeSubdomain, isValidSubdomain } from "@/lib/store";
import { validateSubdomain } from "@/lib/validations";

const SUFFIX = ".procdor.com";

export async function GET(req: Request) {
  const { searchParams } = new URL(req.url);
  const raw = searchParams.get("name") || "";

  const result = validateSubdomain(raw);
  const full = result.normalized ? `${result.normalized}${SUFFIX}` : null;

  return NextResponse.json({
    available: result.valid,
    normalized: result.normalized,
    full,
    errors: result.errors,
    suggestion: result.suggestion,
    reserved: isReservedName(result.normalized),
  });
}

function isReservedName(name: string): boolean {
  const reserved = new Set([
    "admin", "app", "api", "support", "www", "procdor", "auth", "login",
    "signup", "dashboard", "creer", "publier", "mail", "ftp", "smtp",
    "pop", "imap", "ns1", "ns2", "blog", "help", "contact", "about",
    "shop", "store",
  ]);
  return reserved.has(name);
}
