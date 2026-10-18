@component('mail::message')
# Nouveau contact depuis {{ $tenant->name }}

Bonjour,

Vous avez reçu un nouveau message depuis votre boutique {{ $tenant->name }} ({{ $publicUrl }}).

## Détails du contact

- **Nom :** {{ $contact->name ?? 'Non renseigné' }}
- **E-mail :** {{ $contact->email ?? 'Non renseigné' }}
- **Téléphone :** {{ $contact->phone ?? 'Non renseigné' }}
- **Source :** {{ $contact->source ?? 'Site web' }}
- **Date :** {{ $contact->created_at->format('d/m/Y H:i') }}

## Message

{{ $contact->message }}

---

Répondez directement à cet e-mail pour contacter le client, ou utilisez les boutons d'action sur votre boutique.

Cordialement,
L'équipe PROCDOR
@endcomponent
