import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

const SUFFIX = ".procdor.com";

export function middleware(req: NextRequest) {
  const host = req.headers.get("host") || "";
  const hostname = host.split(":")[0];

  // Only handle custom subdomain hosts (e.g. lebonplat.procdor.com),
  // leaving the root app (procdor.com, localhost) untouched.
  if (hostname.endsWith(SUFFIX) && hostname !== SUFFIX) {
    const subdomain = hostname.slice(0, -SUFFIX.length);
    return NextResponse.rewrite(new URL(`/s/${subdomain}`, req.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except static files and API routes.
     */
    "/((?!_next/static|_next/image|favicon.ico|api|s/.*).*)",
  ],
};
