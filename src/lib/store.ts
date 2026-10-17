// Data store abstraction for PROCDOR.
// In-memory implementation for development/demo. Swappable with a real SQL database.
// Provides strict isolation between organizations and shops, unique subdomains, and drafts.

import { randomUUID } from "crypto";

export type SiteType =
  | "restaurant"
  | "boutique"
  | "boutique-link"
  | "services"
  | "association"
  | "cabinet";

export type Language = "fr" | "en" | "es" | "de" | "it";

export type DeliveryMode =
  | "delivery"
  | "pickup"
  | "onsite"
  | "digital-whatsapp";

export type PrimaryAction =
  | "whatsapp"
  | "call"
  | "external-link"
  | "appointment";

export interface ShopDraft {
  id: string;
  orgId: string;
  userId: string;
  candidateSubdomain: string | null;
  step: number; // 1..4
  siteType: SiteType | null;
  siteName: string | null;
  language: Language | null;
  subdomain: string | null;
  product: ShopProduct | null;
  deliveryModes: DeliveryMode[];
  primaryAction: PrimaryAction | null;
  actionValue: string | null;
  acceptedTerms: boolean;
  updatedAt: string;
}

export interface ShopProduct {
  name: string;
  price?: string | number;
  description?: string;
  category?: string;
  photoUrl?: string;
}

export interface Shop {
  id: string;
  orgId: string;
  userId: string;
  subdomain: string; // unique
  siteType: SiteType;
  siteName: string;
  language: Language;
  product: ShopProduct;
  deliveryModes: DeliveryMode[];
  primaryAction: PrimaryAction | null;
  actionValue: string | null;
  isPublished: boolean;
  publishedAt: string | null;
  publicUrl: string;
  createdAt: string;
  updatedAt: string;
}

export interface Organization {
  id: string;
  userId: string;
  name: string;
  createdAt: string;
}

export interface User {
  id: string;
  email: string | null;
  name: string | null;
  image: string | null;
  createdAt: string;
}

const RESERVED_NAMES = new Set([
  "admin",
  "app",
  "api",
  "support",
  "www",
  "procdor",
  "auth",
  "login",
  "signup",
  "dashboard",
  "creer",
  "publier",
  "s",
  "www",
  "mail",
  "ftp",
  "smtp",
  "pop",
  "imap",
  "ns1",
  "ns2",
  "blog",
  "help",
  "contact",
  "about",
  "shop",
  "store",
]);

const SUBDOMAIN_RE = /^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/;

export function normalizeSubdomain(raw: string): string {
  return raw
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9-]/g, "-")
    .replace(/-{2,}/g, "-")
    .replace(/^-+|-+$/g, "");
}

export function isValidSubdomain(name: string): boolean {
  if (!name || name.length < 2 || name.length > 63) return false;
  if (RESERVED_NAMES.has(name)) return false;
  return SUBDOMAIN_RE.test(name);
}

export function isReservedName(name: string): boolean {
  return RESERVED_NAMES.has(name);
}

// In-memory stores
const users = new Map<string, User>();
const organizations = new Map<string, Organization>();
const shops = new Map<string, Shop>();
const drafts = new Map<string, ShopDraft>();
const subdomainIndex = new Map<string, string>(); // normalized subdomain -> shop id
const reservedSubdomains = new Set<string>(); // temporarily reserved during a session

export const store = {
  // Users
  createUser: (email: string | null, name: string | null, image: string | null): User => {
    const id = randomUUID();
    const user: User = { id, email, name, image, createdAt: new Date().toISOString() };
    users.set(id, user);
    return user;
  },
  getUser: (id: string): User | undefined => users.get(id),
  findUserByEmail: (email: string): User | undefined =>
    [...users.values()].find((u) => u.email === email),

  // Organizations
  createOrganization: (userId: string, name: string): Organization => {
    const id = randomUUID();
    const org: Organization = { id, userId, name, createdAt: new Date().toISOString() };
    organizations.set(id, org);
    return org;
  },
  getOrganization: (id: string): Organization | undefined => organizations.get(id),
  getOrganizationsByUser: (userId: string): Organization[] =>
    [...organizations.values()].filter((o) => o.userId === userId),

  // Drafts
  createDraft: (userId: string, candidateSubdomain: string | null): ShopDraft => {
    const orgs = store.getOrganizationsByUser(userId);
    const orgId = orgs[0]?.id || store.createOrganization(userId, "Mon organisation").id;
    const id = randomUUID();
    const draft: ShopDraft = {
      id,
      orgId,
      userId,
      candidateSubdomain,
      step: 1,
      siteType: null,
      siteName: null,
      language: "fr",
      subdomain: candidateSubdomain || null,
      product: null,
      deliveryModes: [],
      primaryAction: null,
      actionValue: null,
      acceptedTerms: false,
      updatedAt: new Date().toISOString(),
    };
    drafts.set(id, draft);
    return draft;
  },
  getDraft: (id: string): ShopDraft | undefined => drafts.get(id),
  getDraftByUser: (userId: string): ShopDraft | undefined =>
    [...drafts.values()].find((d) => d.userId === userId && !store.getShopByUser(userId)?.isPublished),
  updateDraft: (id: string, patch: Partial<ShopDraft>): ShopDraft | undefined => {
    const draft = drafts.get(id);
    if (!draft) return undefined;
    const updated = { ...draft, ...patch, updatedAt: new Date().toISOString() };
    drafts.set(id, updated);
    return updated;
  },
  deleteDraft: (id: string): boolean => drafts.delete(id),

  // Subdomains
  isSubdomainAvailable: (raw: string): { available: boolean; normalized: string; reason?: string } => {
    const normalized = normalizeSubdomain(raw);
    if (!isValidSubdomain(normalized)) {
      return { available: false, normalized, reason: "invalid" };
    }
    if (isReservedName(normalized)) {
      return { available: false, normalized, reason: "reserved" };
    }
    const takenBy = subdomainIndex.get(normalized);
    if (takenBy) {
      return { available: false, normalized, reason: "taken" };
    }
    if (reservedSubdomains.has(normalized)) {
      return { available: false, normalized, reason: "reserved" };
    }
    return { available: true, normalized };
  },
  reserveSubdomain: (normalized: string, shopId: string): boolean => {
    if (subdomainIndex.has(normalized)) return false;
    subdomainIndex.set(normalized, shopId);
    reservedSubdomains.add(normalized);
    return true;
  },
  releaseSubdomain: (normalized: string): void => {
    subdomainIndex.delete(normalized);
    reservedSubdomains.delete(normalized);
  },
  getSubdomainOwner: (normalized: string): string | undefined => subdomainIndex.get(normalized),

  // Shops
  createShop: (draft: ShopDraft): Shop | null => {
    if (!draft.subdomain || !draft.siteType || !draft.siteName) return null;
    const normalized = normalizeSubdomain(draft.subdomain);
    if (!store.isSubdomainAvailable(normalized).available) return null;
    const id = randomUUID();
    const now = new Date().toISOString();
    const publicUrl = `https://${normalized}.procdor.com`;
    const shop: Shop = {
      id,
      orgId: draft.orgId,
      userId: draft.userId,
      subdomain: normalized,
      siteType: draft.siteType,
      siteName: draft.siteName,
      language: draft.language || "fr",
      product: draft.product || { name: "" },
      deliveryModes: draft.deliveryModes,
      primaryAction: draft.primaryAction,
      actionValue: draft.actionValue,
      isPublished: false,
      publishedAt: null,
      publicUrl,
      createdAt: now,
      updatedAt: now,
    };
    shops.set(id, shop);
    store.reserveSubdomain(normalized, id);
    return shop;
  },
  createShopFromData: (data: {
    orgId: string;
    userId: string;
    subdomain: string;
    siteType: SiteType;
    siteName: string;
    language: Language;
    product: ShopProduct;
    deliveryModes: DeliveryMode[];
    primaryAction: PrimaryAction | null;
    actionValue: string | null;
  }): Shop | null => {
    const normalized = normalizeSubdomain(data.subdomain);
    if (!store.isSubdomainAvailable(normalized).available) return null;
    const id = randomUUID();
    const now = new Date().toISOString();
    const publicUrl = `https://${normalized}.procdor.com`;
    const shop: Shop = {
      id,
      orgId: data.orgId,
      userId: data.userId,
      subdomain: normalized,
      siteType: data.siteType,
      siteName: data.siteName,
      language: data.language,
      product: data.product || { name: "" },
      deliveryModes: data.deliveryModes,
      primaryAction: data.primaryAction,
      actionValue: data.actionValue,
      isPublished: false,
      publishedAt: null,
      publicUrl,
      createdAt: now,
      updatedAt: now,
    };
    shops.set(id, shop);
    store.reserveSubdomain(normalized, id);
    return shop;
  },
  getShop: (id: string): Shop | undefined => shops.get(id),
  getShopBySubdomain: (subdomain: string): Shop | undefined => {
    const normalized = normalizeSubdomain(subdomain);
    const shopId = subdomainIndex.get(normalized);
    if (!shopId) return undefined;
    return shops.get(shopId);
  },
  getShopByUser: (userId: string): Shop | undefined =>
    [...shops.values()].find((s) => s.userId === userId),
  updateShop: (id: string, patch: Partial<Shop>): Shop | undefined => {
    const shop = shops.get(id);
    if (!shop) return undefined;
    const updated = { ...shop, ...patch, updatedAt: new Date().toISOString() };
    shops.set(id, updated);
    return updated;
  },
  publishShop: (id: string): Shop | null => {
    const shop = shops.get(id);
    if (!shop) return null;
    const updated: Shop = {
      ...shop,
      isPublished: true,
      publishedAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    };
    shops.set(id, updated);
    return updated;
  },
  listShops: (): Shop[] => [...shops.values()],

  // Suggest alternatives
  suggestAlternatives: (base: string, limit = 5): string[] => {
    const normalized = normalizeSubdomain(base);
    const suffixes = ["boutique", "shop", "store", "pro", "2024", "2025", "fr", "online"];
    const results: string[] = [];
    for (const s of suffixes) {
      const candidate = `${normalized}-${s}`;
      if (store.isSubdomainAvailable(candidate).available) {
        results.push(candidate);
        if (results.length >= limit) break;
      }
    }
    return results;
  },
};

export { users, organizations, shops, drafts, subdomainIndex };
