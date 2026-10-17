// Validation helpers for PROCDOR site creation.

import {
  isValidSubdomain,
  isReservedName,
  normalizeSubdomain,
  store,
  type SiteType,
  type DeliveryMode,
  type PrimaryAction,
  type ShopProduct,
} from "./store";

export interface FieldError {
  field: string;
  message: string;
}

export type Language = "fr" | "en" | "es" | "de" | "it";

export {
  type SiteType,
  type DeliveryMode,
  type PrimaryAction,
  type ShopProduct,
};

export function validateSubdomain(raw: string): {
  valid: boolean;
  normalized: string;
  errors: FieldError[];
  suggestion?: string;
} {
  const normalized = normalizeSubdomain(raw);
  const errors: FieldError[] = [];

  if (!raw || !raw.trim()) {
    return { valid: false, normalized: "", errors: [{ field: "subdomain", message: "Veuillez saisir un nom de sous-domaine." }] };
  }

  if (!isValidSubdomain(normalized)) {
    errors.push({ field: "subdomain", message: "Ce nom contient des caractères interdits ou est trop long." });
  }

  if (isReservedName(normalized)) {
    errors.push({ field: "subdomain", message: "Ce nom est réservé et ne peut pas être utilisé." });
  }

  const availability = store.isSubdomainAvailable(normalized);
  if (!availability.available && availability.reason === "taken") {
    errors.push({ field: "subdomain", message: "Ce nom est déjà utilisé." });
  }

  return {
    valid: errors.length === 0,
    normalized,
    errors,
    suggestion: errors.length > 0 ? store.suggestAlternatives(normalized)[0] : undefined,
  };
}

const INTERNATIONAL_PHONE_RE = /^\+[1-9]\d{1,14}$/;

export function validatePhone(raw: string): { valid: boolean; errors: FieldError[] } {
  const errors: FieldError[] = [];
  if (!raw || !raw.trim()) {
    errors.push({ field: "phone", message: "Veuillez saisir un numéro de téléphone." });
    return { valid: false, errors };
  }
  if (!INTERNATIONAL_PHONE_RE.test(raw.trim())) {
    errors.push({ field: "phone", message: "Le numéro doit être au format international (ex: +33612345678)." });
  }
  return { valid: errors.length === 0, errors };
}

export function validateUrl(raw: string): { valid: boolean; errors: FieldError[] } {
  const errors: FieldError[] = [];
  if (!raw || !raw.trim()) {
    errors.push({ field: "url", message: "Veuillez saisir une adresse web." });
    return { valid: false, errors };
  }
  let url: URL;
  try {
    url = new URL(raw.trim());
  } catch {
    errors.push({ field: "url", message: "L'adresse web n'est pas valide." });
    return { valid: false, errors };
  }
  if (!["http:", "https:"].includes(url.protocol)) {
    errors.push({ field: "url", message: "L'adresse doit commencer par http:// ou https://." });
  }
  return { valid: errors.length === 0, errors };
}

export function validateRequired(value: string | undefined, field: string, label: string): FieldError[] {
  if (!value || !value.trim()) {
    return [{ field, message: `Le champ ${label} est requis.` }];
  }
  return [];
}

export function validatePrice(value: string | number | undefined): FieldError[] {
  if (!value || (typeof value === "string" && !value.trim())) return [];
  const n = Number(value);
  if (!Number.isFinite(n) || n < 0) {
    return [{ field: "price", message: "Le prix doit être un nombre positif." }];
  }
  return [];
}

export const SITE_TYPE_LABELS: Record<string, { title: string; description: string }> = {
  restaurant: { title: "Restaurant", description: "Menu, plats, réservations et commandes." },
  boutique: { title: "Boutique", description: "Produits, prix, catalogue et commandes." },
  "boutique-link": { title: "Boutique à partir d'un lien", description: "Importer les informations d'un produit à partir d'une URL accessible." },
  services: { title: "Services", description: "Prestations, devis, rendez-vous et contact." },
  association: { title: "Association", description: "Mission, activités, événements et dons." },
  cabinet: { title: "Cabinet", description: "Présentation professionnelle, services et prise de rendez-vous." },
};

export const LANGUAGES: { code: Language; label: string }[] = [
  { code: "fr", label: "Français" },
  { code: "en", label: "Anglais" },
  { code: "es", label: "Espagnol" },
  { code: "de", label: "Allemand" },
  { code: "it", label: "Italien" },
];
