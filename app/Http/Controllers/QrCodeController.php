<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\QrCardPdfGenerator;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QrCodeController extends Controller
{
    protected QrCodeService $qrCodeService;
    protected QrCardPdfGenerator $pdfGenerator;

    public function __construct(QrCodeService $qrCodeService, QrCardPdfGenerator $pdfGenerator)
    {
        $this->qrCodeService = $qrCodeService;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Download QR code in specified format.
     */
    public function download(Request $request, string $slug)
    {
        $profile = Profile::where('slug', $slug)->firstOrFail();

        // Verify ownership
        if ($profile->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'format' => ['required', 'in:png,svg'],
            'size' => ['required', 'integer', 'in:300,500,1000,2000'],
        ]);

        $format = $validated['format'];
        $size = (int) $validated['size'];

        $contents = $this->qrCodeService->generate($profile, $format, $size);
        $filename = $this->qrCodeService->getDownloadFilename($profile, $format, $size);

        $mimeType = $format === 'svg' ? 'image/svg+xml' : 'image/png';

        return response($contents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate and download smart business card PDF.
     */
    public function generateCard(Request $request, string $slug)
    {
        $profile = Profile::where('slug', $slug)->firstOrFail();

        // Verify ownership
        if ($profile->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'card_size' => ['required', 'in:card,postcard'],
        ]);

        $cardSize = $validated['card_size'];
        $pdfContents = $this->pdfGenerator->generate($profile, $cardSize);
        $filename = "linkadi-card-{$profile->slug}.pdf";

        return response($pdfContents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
