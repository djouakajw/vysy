<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', QrCode::class);

        $tenant = $request->user()->primaryTenant ?? $request->user()->tenants()->first();
        $qrcodes = $tenant->qrCodes()->orderBy('created_at', 'desc')->get();

        return view('tenant.qrcodes', compact('tenant', 'qrcodes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', QrCode::class);

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'target_url' => 'required|url',
            'file_type' => 'nullable|in:png,svg',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = Tenant::where('id', $request->tenant_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $fileType = $request->file_type ?? 'png';

        // Generate QR code
        $result = Builder::create()
            ->data($request->target_url)
            ->encoding(new Encoding('UTF-8'))
            ->size(400)
            ->margin(10)
            ->build();

        $fileName = 'qr_' . Str::uuid()->toString() . '.' . $fileType;
        $directory = 'uploads/qrcodes/' . $tenant->id;
        
        if ($fileType === 'svg') {
            $content = (new SvgWriter())->write($result)->getString();
            Storage::disk('public')->put($directory . '/' . $fileName, $content);
        } else {
            $content = (new PngWriter())->write($result)->getString();
            Storage::disk('public')->put($directory . '/' . $fileName, $content);
        }

        $qrCode = $tenant->qrCodes()->create([
            'name' => $request->name,
            'target_url' => $request->target_url,
            'file_path' => $directory . '/' . $fileName,
            'file_type' => $fileType,
            'scans_count' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'qr_code' => $qrCode,
            'download_url' => route('tenant.qrcodes.download', $qrCode->id),
        ]);
    }

    public function download(QrCode $qrCode)
    {
        $this->authorize('view', $qrCode);

        $path = $qrCode->file_path;
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Fichier QR code introuvable.');
        }

        return Storage::disk('public')->download($path, $qrCode->name . '.' . $qrCode->file_type);
    }
}
