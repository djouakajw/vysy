<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Tenant;
use App\Mail\NewContactNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request, Tenant $tenant)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string',
            'source' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = $tenant->contacts()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
            'source' => $request->source ?? 'website',
            'status' => Contact::STATUS_NEW,
        ]);

        // Envoyer une notification email au propriétaire de la boutique
        try {
            $tenantOwner = $tenant->user;
            if ($tenantOwner && $tenantOwner->email) {
                Mail::to($tenantOwner->email)->send(new NewContactNotification($contact));
            }
        } catch (\Exception $e) {
            // Ne pas bloquer la création du contact si l'email échoue
            \Log::warning('Échec de l\'email de notification de contact: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre message a été envoyé.',
            'contact' => $contact,
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est le propriétaire du tenant
        if ($contact->tenant->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:new,contacted,in_discussion,converted,closed',
            'notes' => 'nullable|string',
            'converted' => 'sometimes|boolean',
        ]);

        $contact->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'converted' => $request->boolean('converted') ?? false,
        ]);

        return redirect()->route('tenant.contacts.index')->with('success', 'Contact mis à jour.');
    }
}
