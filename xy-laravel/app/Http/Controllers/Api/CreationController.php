<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreationController extends Controller
{
    public function getDraft(Request $request)
    {
        $user = $request->user();
        $draft = Tenant::where('user_id', $user->id)
            ->where('status', 'draft')
            ->orderBy('updated_at', 'desc')
            ->first();

        return response()->json([
            'draft' => $draft ? [
                'id' => $draft->id,
                'name' => $draft->name,
                'slug' => $draft->slug,
                'language' => $draft->language,
                'description' => $draft->description,
                'city' => $draft->city,
                'country' => $draft->country,
                'primary_color' => $draft->primary_color,
                'settings' => $draft->settings,
            ] : null,
        ]);
    }

    public function saveDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:63|unique:tenants,slug',
            'language' => 'nullable|string|max:5',
            'description' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'primary_color' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $slug = $request->slug ?: Str::slug($request->name);

        // Verify availability again
        if (Tenant::where('slug', $slug)->exists()) {
            return response()->json([
                'error' => 'slug_taken',
                'message' => 'Ce sous-domaine est déjà utilisé.',
            ], 409);
        }

        $draft = Tenant::updateOrCreate(
            ['id' => $request->input('draft_id')],
            [
                'user_id' => $user->id,
                'name' => $request->name,
                'slug' => $slug,
                'language' => $request->language ?? 'fr',
                'description' => $request->description,
                'city' => $request->city,
                'country' => $request->country ?? 'CM',
                'primary_color' => $request->primary_color ?? '#00a86b',
                'status' => 'draft',
                'settings' => array_merge(
                    $request->input('settings', []),
                    ['updated_via' => 'wizard']
                ),
            ]
        );

        return response()->json([
            'draft' => [
                'id' => $draft->id,
                'slug' => $draft->slug,
                'public_url' => $draft->getPublicUrlAttribute(),
            ],
        ]);
    }

    public function saveStep(Request $request, $step)
    {
        $user = $request->user();
        $draftId = $request->input('draft_id');

        $draft = Tenant::where('id', $draftId)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $settings = $draft->settings ?? [];

        switch ($step) {
            case 1: // Activity type
                $validator = Validator::make($request->all(), [
                    'activity_type' => 'required|string|in:restaurant,boutique,services,association,cabinet',
                ]);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                $settings['activity_type'] = $request->activity_type;
                break;

            case 3: // Content & contact
                $validator = Validator::make($request->all(), [
                    'products' => 'nullable|array',
                    'delivery_modes' => 'nullable|array',
                    'primary_action' => 'nullable|string|in:whatsapp,call,external_link,appointment',
                    'action_value' => 'nullable|string',
                    'contact_phone' => 'nullable|string',
                    'contact_whatsapp' => 'nullable|string',
                ]);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                $settings['products'] = $request->products ?? [];
                $settings['delivery_modes'] = $request->delivery_modes ?? [];
                $settings['primary_action'] = $request->primary_action;
                $settings['action_value'] = $request->action_value;
                $settings['contact_phone'] = $request->contact_phone;
                $settings['contact_whatsapp'] = $request->contact_whatsapp;
                break;

            case 4: // Preview & activate
                $validator = Validator::make($request->all(), [
                    'accepted_terms' => 'required|boolean',
                ]);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                $settings['accepted_terms'] = true;
                break;

            default:
                return response()->json(['error' => 'invalid_step'], 400);
        }

        $draft->update(['settings' => $settings]);

        return response()->json([
            'success' => true,
            'step' => $step,
            'settings' => $settings,
        ]);
    }

    public function preview(Request $request)
    {
        $user = $request->user();
        $draftId = $request->input('draft_id');

        $draft = Tenant::where('id', $draftId)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $products = Product::where('tenant_id', $draft->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'tenant' => $draft,
            'products' => $products,
            'preview_url' => route('tenant.site', ['tenant' => $draft->slug]),
            'settings' => $draft->settings,
        ]);
    }
}
