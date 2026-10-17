"use client";

declare global {
  interface Window {
    __sdCheckTimer?: ReturnType<typeof setTimeout>;
  }
}

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

import { store } from "@/lib/store";
import {
  validateSubdomain,
  validatePhone,
  validateUrl,
  validateRequired,
  validatePrice,
  SITE_TYPE_LABELS,
  LANGUAGES,
  type SiteType,
  type Language,
  type DeliveryMode,
  type PrimaryAction,
  type ShopProduct,
} from "@/lib/validations";

const STEPS = [
  { id: 1, label: "Type d'activité" },
  { id: 2, label: "Identité et langue" },
  { id: 3, label: "Offre" },
  { id: 4, label: "Commandes et activation" },
] as const;

const DELIVERY_MODES_ALL: DeliveryMode[] = [
  "delivery",
  "pickup",
  "onsite",
  "digital-whatsapp",
];

const PRIMARY_ACTIONS_ALL: PrimaryAction[] = [
  "whatsapp",
  "call",
  "external-link",
  "appointment",
];

const SITE_TYPE_ICONS: Record<string, string> = {
  restaurant: "🍽️",
  boutique: "🛍️",
  "boutique-link": "🔗",
  services: "💼",
  association: "🤝",
  cabinet: "🏢",
};

const DELIVERY_LABELS: Record<DeliveryMode, string> = {
  delivery: "Je livre mes clients",
  pickup: "Le client vient récupérer",
  onsite: "Sur place",
  "digital-whatsapp": "Produits numériques par WhatsApp",
};

const ACTION_LABELS: Record<PrimaryAction, string> = {
  whatsapp: "WhatsApp",
  call: "Appeler",
  "external-link": "Lien externe",
  appointment: "Rendez-vous ou devis",
};

type WizardDraft = {
  siteType: SiteType | null;
  siteName: string;
  language: Language;
  subdomain: string;
  product: ShopProduct;
  deliveryModes: DeliveryMode[];
  primaryAction: PrimaryAction | null;
  actionValue: string;
  acceptedTerms: boolean;
};

const INITIAL_DRAFT: WizardDraft = {
  siteType: null,
  siteName: "",
  language: "fr",
  subdomain: "",
  product: { name: "", price: "", description: "", category: "", photoUrl: "" },
  deliveryModes: [],
  primaryAction: null,
  actionValue: "",
  acceptedTerms: false,
};

interface WizardClientProps {
  initialSubdomain: string;
}

export default function WizardClient({ initialSubdomain }: WizardClientProps) {
  const router = useRouter();

  const [step, setStep] = useState(1);
  const [draft, setDraft] = useState<WizardDraft>({
    ...INITIAL_DRAFT,
    subdomain: initialSubdomain,
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [serverMsg, setServerMsg] = useState<{ type: "success" | "error"; message: string } | null>(null);
  const [saving, setSaving] = useState(false);
  const [subdomainStatus, setSubdomainStatus] = useState<"idle" | "checking" | "available" | "taken">("idle");
  const [subdomainMessage, setSubdomainMessage] = useState("");
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [shop, setShop] = useState<{ id: string; publicUrl: string; subdomain: string } | null>(null);

  const checkSubdomain = useCallback(async (name: string) => {
    if (!name.trim()) return;
    setSubdomainStatus("checking");
    setSubdomainMessage("Vérification en cours...");
    try {
      const res = await fetch(`/api/subdomain/check?name=${encodeURIComponent(name)}`);
      const data = await res.json();
      if (data.available) {
        setSubdomainStatus("available");
        setSubdomainMessage("Ce nom est disponible.");
        setSuggestions([]);
      } else {
        setSubdomainStatus("taken");
        setSubdomainMessage(data.errors?.[0]?.message || "Ce nom est déjà utilisé.");
        setSuggestions(data.suggestion ? [data.suggestion] : (data.alternatives || []));
      }
    } catch {
      setSubdomainStatus("idle");
      setSubdomainMessage("Erreur de vérification.");
    }
  }, []);

  // Initialize draft from server on mount
  // eslint-disable-next-line react-hooks/set-state-in-effect
  useEffect(() => {
    // Initial subdomain check if provided
    if (initialSubdomain) {
      // eslint-disable-next-line react-hooks/set-state-in-effect
      checkSubdomain(initialSubdomain);
    }
  }, [initialSubdomain, checkSubdomain]);

  const setField = <K extends keyof WizardDraft>(field: K, value: WizardDraft[K]) => {
    setDraft((prev) => ({ ...prev, [field]: value }));
    // Clear field errors on change
    setErrors((prev) => {
      const next = { ...prev };
      delete next[field];
      return next;
    });
  };

  const handleSubdomainChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    setField("subdomain", val);
    // debounce check
    clearTimeout(window.__sdCheckTimer);
    window.__sdCheckTimer = setTimeout(() => checkSubdomain(val), 400);
  };

  const applySuggestion = (s: string) => {
    setField("subdomain", s);
    checkSubdomain(s);
  };

  // Auto-save draft to server (local state persists; here we just track)
  const autoSave = useCallback(async () => {
    setSaving(true);
    // Simulate server persistence
    await new Promise((r) => setTimeout(r, 300));
    setSaving(false);
  }, []);

  useEffect(() => {
    const t = setTimeout(autoSave, 800);
    return () => clearTimeout(t);
  }, [draft, autoSave]);

  const goTo = (n: number) => setStep(n);

  const isLast = step === 4;

  // ---------- Step 1: Site Type ----------
  const selectSiteType = (t: SiteType) => {
    setField("siteType", t);
    setDraft((prev) => ({ ...prev, product: { ...INITIAL_DRAFT.product } }));
  };

  // ---------- Step 3 conditional required fields ----------
  const requiredProductFields = () => {
    const t = draft.siteType;
    if (t === "restaurant" || t === "boutique" || t === "boutique-link") return ["name", "price"];
    if (t === "services" || t === "cabinet") return ["name"];
    if (t === "association") return ["name"];
    return [];
  };

  // ---------- Step 4 validations ----------
  const validateStep = (n: number): boolean => {
    const next: Record<string, string[]> = {};
    let ok = true;

    if (n === 1) {
      if (!draft.siteType) {
        next.siteType = ["Veuillez sélectionner un type d'activité."];
        ok = false;
      }
    } else if (n === 2) {
      const nameErr = validateRequired(draft.siteName, "siteName", "nom commercial");
      if (nameErr.length) next.siteName = nameErr.map((e) => e.message);
      if (!draft.subdomain.trim()) {
        next.subdomain = ["Veuillez saisir un sous-domaine."];
        ok = false;
      } else if (subdomainStatus !== "available") {
        next.subdomain = ["Le sous-domaine doit être validé comme disponible."];
        ok = false;
      }
      if (nameErr.length) ok = false;
    } else if (n === 3) {
      for (const f of requiredProductFields()) {
        const v = draft.product[f as keyof ShopProduct];
        const errs = validateRequired(String(v ?? ""), f, f);
        if (errs.length) next.product = errs.map((e) => e.message);
      }
      const priceErr = validatePrice(draft.product.price);
      if (priceErr.length) next.product = priceErr.map((e) => e.message);
      if (Object.keys(next).length) ok = false;
    } else if (n === 4) {
      if (draft.deliveryModes.length === 0) {
        next.deliveryModes = ["Veuillez sélectionner au moins un mode de réception."];
        ok = false;
      }
      if (!draft.primaryAction) {
        next.primaryAction = ["Veuillez choisir un bouton d'action principal."];
        ok = false;
      }
      if (draft.primaryAction === "whatsapp" || draft.primaryAction === "call") {
        const pErr = validatePhone(draft.actionValue);
        if (pErr.errors.length) next.actionValue = pErr.errors.map((e) => e.message);
      } else if (draft.primaryAction === "external-link") {
        const uErr = validateUrl(draft.actionValue);
        if (uErr.errors.length) next.actionValue = uErr.errors.map((e) => e.message);
      }
      if (!draft.acceptedTerms) {
        next.acceptedTerms = ["Vous devez accepter les conditions de publication."];
        ok = false;
      }
      if (Object.keys(next).length) ok = false;
    }

    setErrors(next);
    return ok;
  };

  const handleNext = async () => {
    if (!validateStep(step)) return;
    if (step < 4) {
      setStep(step + 1);
    } else {
      await createShopFromDraft();
    }
  };

  const handlePrev = () => {
    if (step > 1) setStep(step - 1);
  };

  const createShopFromDraft = async () => {
    setServerMsg(null);
    try {
      const norm = draft.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, "-").replace(/-{2,}/g, "-").replace(/^-+|-+$/g, "");
      const res = await fetch("/api/shops", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          draft: {
            siteType: draft.siteType,
            siteName: draft.siteName,
            language: draft.language,
            subdomain: norm,
            product: draft.product,
            deliveryModes: draft.deliveryModes,
            primaryAction: draft.primaryAction,
            actionValue: draft.actionValue || null,
          },
        }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec de création");

      setShop({ id: data.shop.id, publicUrl: data.shop.publicUrl, subdomain: norm });
      setServerMsg({ type: "success", message: "Votre boutique est créée. Vous pouvez maintenant l'activer." });
    } catch (err: unknown) {
      setServerMsg({ type: "error", message: (err as { message?: string })?.message || "Une erreur est survenue." });
    }
  };

  const handlePublish = async () => {
    if (!shop) return;
    setServerMsg(null);
    try {
      const res = await fetch(`/api/shops/${shop.id}/publish`, { method: "POST" });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Échec de publication");
      setShop((prev) => prev ? { ...prev, publicUrl: data.shop.publicUrl } : null);
      setServerMsg({ type: "success", message: "Félicitations ! Votre site PROCDOR est en ligne." });
    } catch (err: unknown) {
      setServerMsg({ type: "error", message: (err as { message?: string })?.message || "Échec de publication." });
    }
  };

  const progress = ((step - 1) / 3) * 100;

  return (
    <main className="min-h-screen bg-gray-50 text-gray-900">
      <nav className="border-b bg-white px-6 py-3 flex items-center justify-between">
        <Link href="/" className="font-bold text-green-600 text-lg">PROCDOR</Link>
        <a href="/dashboard" className="text-sm text-gray-600 hover:text-gray-900">Tableau de bord</a>
      </nav>

      {/* Progress bar */}
      <div className="bg-white border-b px-6 py-3">
        <div className="flex items-center gap-2">
          {STEPS.map((s, i) => {
            const active = step === s.id;
            const done = step > s.id;
            return (
              <div key={s.id} className="flex items-center gap-2">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition ${
                  done ? "bg-green-600 text-white" : active ? "bg-green-600 text-white ring-2 ring-green-300" : "bg-gray-200 text-gray-400 border border-gray-300"
                }`}>{s.id}</div>
                {i < STEPS.length - 1 && (
                  <div className={`h-1 px-1 flex-1 ${
                    done ? "bg-green-600" : active ? "bg-green-400" : "bg-gray-200"
                  } rounded`} />
                )}
                <span className={`text-sm font-medium ${active ? "text-green-600" : "text-gray-600"}`}>{s.label}</span>
              </div>
            );
          })}
          <span className="ml-4 text-xs text-gray-500">Étape {step}/4</span>
        </div>
      </div>

      {/* Wizard content */}
      <div className="max-w-4xl mx-auto py-8 px-6">
        {serverMsg && (
          <div className={`mb-6 p-4 rounded-lg ${
            serverMsg.type === "success" ? "bg-green-50 border border-green-200 text-green-800" : "bg-red-50 border border-red-200 text-red-800"
          }`}>{serverMsg.message}</div>
        )}

        {shop && serverMsg?.type === "success" && step === 4 && (
          <div className="text-center py-10">
            <h2 className="text-2xl font-bold mb-3">Votre boutique est prête</h2>
            <p className="text-gray-600 mb-6">Sous-domaine : <span className="font-mono">{shop.subdomain}.procdor.com</span></p>
            <button
              onClick={handlePublish}
              className="bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition shadow-lg shadow-green-200"
            >
              ACTIVER MON SITE
            </button>
          </div>
        )}

        {step === 1 && (
          <section>
            <h2 className="text-xl font-bold mb-2">Quel type de site souhaitez-vous créer ?</h2>
            <p className="text-gray-600 mb-6">Choisissez l&apos;activité qui correspond à votre projet.</p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {Object.entries(SITE_TYPE_LABELS).map(([key, info]) => {
                const t = key as SiteType;
                const selected = draft.siteType === t;
                return (
                  <button
                    key={key}
                    onClick={() => selectSiteType(t)}
                    className={`flex flex-col items-center justify-center p-6 rounded-xl border-2 transition ${
                      selected
                        ? "bg-green-50 border-green-600 text-green-700 shadow"
                        : "bg-white border-gray-200 text-gray-700 hover:border-gray-300 hover:shadow"
                    }`}
                  >
                    <span className="text-3xl mb-2">{SITE_TYPE_ICONS[key]}</span>
                    <span className="font-semibold">{info.title}</span>
                    <span className="text-xs text-gray-500 mt-1 text-center">{info.description}</span>
                  </button>
                );
              })}
            </div>
            {errors.siteType && <p className="mt-3 text-red-600 text-sm">{errors.siteType[0]}</p>}
          </section>
        )}

        {step === 2 && (
          <section>
            <h2 className="text-xl font-bold mb-2">Comment souhaitez-vous nommer votre site ?</h2>
            <p className="text-gray-600 mb-6">Ces informations apparaîtront sur votre boutique.</p>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium mb-1">Nom commercial du site</label>
                <input
                  value={draft.siteName}
                  onChange={(e) => setField("siteName", e.target.value)}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                  placeholder="Le Bon Plat"
                />
                {errors.siteName && <p className="mt-1 text-red-600 text-sm">{errors.siteName[0]}</p>}
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Langue principale</label>
                <select
                  value={draft.language}
                  onChange={(e) => setField("language", e.target.value as Language)}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                >
                  {LANGUAGES.map((l) => (
                    <option key={l.code} value={l.code}>{l.label}</option>
                  ))}
                </select>
              </div>
            </div>
            <div className="mt-6">
              <label className="block text-sm font-medium mb-1">Sous-domaine</label>
              <div className="flex gap-2">
                <input
                  value={draft.subdomain}
                  onChange={handleSubdomainChange}
                  className="flex-1 py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                  placeholder="lebonplat"
                />
                <span className="px-3 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-600 font-mono text-sm">.procdor.com</span>
              </div>
              <p className={`text-xs mt-1 ${
                subdomainStatus === "available" ? "text-green-600" :
                subdomainStatus === "taken" ? "text-red-600" :
                subdomainStatus === "checking" ? "text-orange-600" : "text-gray-500"
              }`}>
                {subdomainStatus === "checking" ? "Vérification en cours..." :
                 subdomainStatus === "available" ? "Ce nom est disponible." :
                 subdomainMessage || "Vérifiez la disponibilité."}
              </p>
              {errors.subdomain && <p className="mt-1 text-red-600 text-sm">{errors.subdomain[0]}</p>}
              {suggestions.length > 0 && (
                <p className="mt-1 text-green-600 text-sm cursor-pointer"                   onClick={() => applySuggestion(suggestions[0])}>
                  Suggestion : {suggestions[0]}
                </p>
              )}
              <p className="text-xs text-gray-500 mt-2">Aperçu : <span className="font-mono">{draft.subdomain ? draft.subdomain.toLowerCase().replace(/[^a-z0-9-]/g,"-") : ""}.procdor.com</span></p>
            </div>
            {errors.siteType && <p className="mt-3 text-red-600 text-sm">{errors.siteType[0]}</p>}
          </section>
        )}

        {step === 3 && (
          <section>
            <h2 className="text-xl font-bold mb-2">Que souhaitez-vous présenter en premier ?</h2>
            <p className="text-gray-600 mb-6">Un produit ou service suffit pour commencer.</p>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium mb-1">Nom du produit / plat / service</label>
                <input
                  value={draft.product.name}
                  onChange={(e) => setField("product", { ...draft.product, name: e.target.value })}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                />
              </div>
              {(draft.siteType === "boutique" || draft.siteType === "restaurant" || draft.siteType === "boutique-link") && (
                <div>
                  <label className="block text-sm font-medium mb-1">Prix</label>
                  <div className="flex items-center gap-2">
                    <input
                      value={draft.product.price}
                      onChange={(e) => setField("product", { ...draft.product, price: e.target.value })}
                      className="w-48 py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                      placeholder="0,00"
                    />
                    <span className="text-gray-500 text-sm">€</span>
                  </div>
                  {errors.product?.includes("Le prix doit être un nombre positif.") && <p className="mt-1 text-red-600 text-sm">{errors.product[0]}</p>}
                </div>
              )}
              <div>
                <label className="block text-sm font-medium mb-1">Description (facultative)</label>
                <textarea
                  value={draft.product.description}
                  onChange={(e) => setField("product", { ...draft.product, description: e.target.value })}
                  rows={3}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1">Catégorie (facultative)</label>
                <input
                  value={draft.product.category}
                  onChange={(e) => setField("product", { ...draft.product, category: e.target.value })}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                />
              </div>
            </div>
            {draft.siteType === "association" && (
              <div className="mt-4">
                <label className="block text-sm font-medium mb-1">Activité ou projet principal</label>
                <input
                  value={draft.product.name}
                  onChange={(e) => setField("product", { ...draft.product, name: e.target.value })}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                />
              </div>
            )}
            {draft.siteType === "boutique-link" && (
              <div className="mt-4">
                <label className="block text-sm font-medium mb-1">URL du produit à importer</label>
                <input
                  value={draft.product.photoUrl}
                  onChange={(e) => setField("product", { ...draft.product, photoUrl: e.target.value })}
                  className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                  placeholder="https://exemple.com/produit"
                />
                <p className="text-xs text-gray-500 mt-1">L&apos;import ne récupère que les informations publiquement accessibles.</p>
              </div>
            )}
            {errors.product && <p className="mt-3 text-red-600 text-sm">{errors.product[0]}</p>}
            <p className="mt-4 text-xs text-gray-500">L&apos;IA peut proposer une description à partir des informations saisies. Résultat modifiable, jamais inventé.</p>
          </section>
        )}

        {step === 4 && (
          <section>
            <h2 className="text-xl font-bold mb-2">Comment vos clients vont-ils commander ou vous contacter ?</h2>
            <p className="text-gray-600 mb-6">Sélectionnez les modes de réception et l&apos;action principale.</p>

            <div className="mb-6">
              <h3 className="text-sm font-bold mb-3">Modes de réception</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {DELIVERY_MODES_ALL.map((mode) => {
                  const selected = draft.deliveryModes.includes(mode);
                  return (
                    <button
                      key={mode}
                      onClick={() => {
                        setDraft((prev) => ({
                          ...prev,
                          deliveryModes: selected
                            ? prev.deliveryModes.filter((m) => m !== mode)
                            : [...prev.deliveryModes, mode],
                        }));
                      }}
                      className={`flex items-center gap-3 p-3 rounded-lg border transition ${
                        selected
                          ? "bg-green-50 border-green-600 text-green-700"
                          : "bg-white border-gray-200 text-gray-700 hover:border-gray-300"
                      }`}
                    >
                      <input type="checkbox" checked={selected} readOnly className="w-4 h-4" />
                      <span className="text-sm">{DELIVERY_LABELS[mode]}</span>
                    </button>
                  );
                })}
              </div>
              {errors.deliveryModes && <p className="mt-2 text-red-600 text-sm">{errors.deliveryModes[0]}</p>}
            </div>

            <div className="mb-6">
              <h3 className="text-sm font-bold mb-3">Bouton d&apos;action principal</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {PRIMARY_ACTIONS_ALL.map((action) => {
                  const selected = draft.primaryAction === action;
                  return (
                    <button
                      key={action}
                      onClick={() => setField("primaryAction", action)}
                      className={`flex items-center gap-3 p-3 rounded-lg border transition ${
                        selected
                          ? "bg-green-50 border-green-600 text-green-700"
                          : "bg-white border-gray-200 text-gray-700 hover:border-gray-300"
                      }`}
                    >
                      <span className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                        selected ? "bg-green-600 border-green-600 text-white" : "border-gray-300 text-gray-400"
                      }`}>{selected ? "✓" : ""}</span>
                      <span className="text-sm">{ACTION_LABELS[action]}</span>
                    </button>
                  );
                })}
              </div>
              {errors.primaryAction && <p className="mt-2 text-red-600 text-sm">{errors.primaryAction[0]}</p>}

              {draft.primaryAction && (
                <div className="mt-4">
                  <label className="block text-sm font-medium mb-1">
                    {draft.primaryAction === "whatsapp" ? "Numéro WhatsApp (international)" :
                     draft.primaryAction === "call" ? "Numéro de téléphone" :
                     draft.primaryAction === "external-link" ? "Adresse web (URL)" :
                     "Information de rendez-vous"}
                  </label>
                  <input
                    value={draft.actionValue}
                    onChange={(e) => setField("actionValue", e.target.value)}
                    className="w-full py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none bg-white"
                    placeholder={draft.primaryAction === "whatsapp" ? "+33612345678" : draft.primaryAction === "external-link" ? "https://exemple.com" : ""}
                  />
                  {errors.actionValue && <p className="mt-1 text-red-600 text-sm">{errors.actionValue[0]}</p>}
                </div>
              )}
            </div>

            <div className="mb-6 flex items-start gap-3">
              <input
                id="terms"
                type="checkbox"
                checked={draft.acceptedTerms}
                onChange={(e) => setField("acceptedTerms", e.target.checked)}
                className="w-4 h-4 mt-0.5"
              />
              <label htmlFor="terms" className="text-sm leading-6">
                J&apos;accepte les conditions nécessaires à la publication de mon site.
              </label>
              {errors.acceptedTerms && <p className="mt-1 text-red-600 text-sm">{errors.acceptedTerms[0]}</p>}
            </div>

            <div className="flex justify-between mt-8">
              <button
                onClick={handlePrev}
                className="px-6 py-3 rounded-lg border border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition"
              >
                Précédent
              </button>
              <button
                onClick={handleNext}
                disabled={saving}
                className="px-8 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 disabled:opacity-60 transition shadow"
              >
                {saving ? "Enregistrement…" : "Vérifier et créer mon site"}
              </button>
            </div>
          </section>
        )}
      </div>
    </main>
  );
}
