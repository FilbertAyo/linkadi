<?php

namespace App\Services;

use App\Models\Profile;
use TCPDF;

class QrCardPdfGenerator
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Generate a business card PDF with QR code.
     *
     * @param Profile $profile
     * @param string $size ('card' or 'postcard')
     * @return string PDF contents
     */
    public function generate(Profile $profile, string $size = 'card'): string
    {
        // Create new PDF document
        $pdf = new TCPDF($size === 'postcard' ? 'L' : 'P', 'mm', $size === 'postcard' ? [152, 102] : [88.9, 50.8], true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Linkadi');
        $pdf->SetAuthor($profile->display_name);
        $pdf->SetTitle($profile->display_name . ' - Digital Business Card');
        $pdf->SetSubject('Digital Business Card with QR Code');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false, 0);

        // Add front page
        $pdf->AddPage();
        $this->renderFront($pdf, $profile, $size);

        // Add back page (instructions)
        $pdf->AddPage();
        $this->renderBack($pdf, $profile, $size);

        return $pdf->Output('', 'S');
    }

    /**
     * Render the front of the card.
     */
    protected function renderFront(TCPDF $pdf, Profile $profile, string $size): void
    {
        $isPostcard = $size === 'postcard';
        
        // Generate QR code
        $qrSize = $isPostcard ? 40 : 30;
        $qrCode = $this->qrCodeService->generate($profile, 'png', 400);
        
        // Save to temp file
        $tempQr = tempnam(sys_get_temp_dir(), 'qr') . '.png';
        file_put_contents($tempQr, $qrCode);

        // Title
        $pdf->SetFont('helvetica', 'B', $isPostcard ? 18 : 14);
        $pdf->SetXY(5, 8);
        $pdf->Cell(0, 8, $profile->display_name, 0, 1, 'L');

        // Subtitle
        if ($profile->subtitle) {
            $pdf->SetFont('helvetica', '', $isPostcard ? 11 : 9);
            $pdf->SetX(5);
            $pdf->Cell(0, 5, $profile->subtitle, 0, 1, 'L');
        }

        // QR Code
        $qrX = $isPostcard ? 105 : 55;
        $qrY = $isPostcard ? 30 : 15;
        $pdf->Image($tempQr, $qrX, $qrY, $qrSize, $qrSize, 'PNG');

        // "Scan to Connect" text
        $pdf->SetFont('helvetica', 'B', $isPostcard ? 10 : 8);
        $pdf->SetXY($qrX, $qrY + $qrSize + 2);
        $pdf->Cell($qrSize, 4, 'Scan to Connect', 0, 0, 'C');

        // Contact info
        $yPos = $isPostcard ? 30 : 20;
        $pdf->SetFont('helvetica', '', $isPostcard ? 9 : 7);
        
        // Get primary contacts
        $primaryPhone = $profile->contacts()->phones()->primary()->first();
        $primaryEmail = $profile->contacts()->emails()->primary()->first();
        
        if ($primaryPhone) {
            $pdf->SetXY(5, $yPos);
            $pdf->Cell(0, 4, '📞 ' . $primaryPhone->value, 0, 1, 'L');
            $yPos += 4;
        }
        
        if ($primaryEmail) {
            $pdf->SetXY(5, $yPos);
            $pdf->Cell(0, 4, '✉️  ' . $primaryEmail->value, 0, 1, 'L');
            $yPos += 4;
        }
        
        if ($profile->website) {
            $pdf->SetXY(5, $yPos);
            $pdf->Cell(0, 4, '🌐 ' . parse_url($profile->website, PHP_URL_HOST), 0, 1, 'L');
            $yPos += 4;
        }
        
        if ($profile->address) {
            $pdf->SetXY(5, $yPos);
            $pdf->MultiCell(0, 4, '📍 ' . $profile->address, 0, 'L');
        }

        // Footer
        $pdf->SetFont('helvetica', 'I', 6);
        $pdf->SetXY(5, $isPostcard ? 95 : 45);
        $pdf->Cell(0, 3, 'Powered by Linkadi - ' . $profile->public_url, 0, 0, 'C');

        // Clean up temp file
        @unlink($tempQr);
    }

    /**
     * Render the back of the card (instructions).
     */
    protected function renderBack(TCPDF $pdf, Profile $profile, string $size): void
    {
        $isPostcard = $size === 'postcard';
        
        $pdf->SetFont('helvetica', 'B', $isPostcard ? 14 : 12);
        $pdf->SetXY(5, $isPostcard ? 15 : 10);
        $pdf->Cell(0, 6, 'How to Save My Contact', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', $isPostcard ? 10 : 8);
        $yPos = $isPostcard ? 25 : 18;
        
        $instructions = [
            '1. Open your camera app',
            '2. Point at the QR code on the front',
            '3. Tap the notification that appears',
            '4. Click "Save to Contacts"',
        ];

        foreach ($instructions as $instruction) {
            $pdf->SetXY(10, $yPos);
            $pdf->Cell(0, $isPostcard ? 6 : 5, $instruction, 0, 1, 'L');
            $yPos += $isPostcard ? 6 : 5;
        }

        // Additional info
        $pdf->SetFont('helvetica', 'I', $isPostcard ? 8 : 7);
        $yPos += 5;
        $pdf->SetXY(5, $yPos);
        $pdf->MultiCell(0, 4, 'This QR code will open my digital profile where you can save my complete contact information, view my portfolio, and connect with me on social media.', 0, 'C');
    }
}

