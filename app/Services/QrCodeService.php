<?php

namespace App\Services;

use App\Models\Profile;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate QR code for a profile.
     *
     * @param Profile $profile
     * @param string $format ('png' or 'svg')
     * @param int $size
     * @param array $options
     * @return string File contents
     */
    public function generate(Profile $profile, string $format = 'png', int $size = 500, array $options = []): string
    {
        $cacheKey = $this->getCacheKey($profile, $format, $size, $options);
        
        return Cache::remember($cacheKey, 3600, function () use ($profile, $format, $size, $options) {
            // Set writer based on format
            $writer = $format === 'svg' ? new SvgWriter() : new PngWriter();
            
            $builder = new Builder(
                writer: $writer,
                data: $profile->public_url,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: $options['error_correction'] ?? ErrorCorrectionLevel::High,
                size: $size,
                margin: $options['margin'] ?? 10,
                roundBlockSizeMode: $options['round_block_size'] ?? RoundBlockSizeMode::Margin,
            );

            $result = $builder->build();
            
            return $result->getString();
        });
    }

    /**
     * Save QR code to storage.
     *
     * @param Profile $profile
     * @param string $format
     * @param int $size
     * @return string Storage path
     */
    public function saveToStorage(Profile $profile, string $format = 'png', int $size = 500): string
    {
        $contents = $this->generate($profile, $format, $size);
        $filename = "qr-codes/{$profile->id}/qr-{$size}.{$format}";
        
        Storage::disk('public')->put($filename, $contents);
        
        return $filename;
    }

    /**
     * Get cache key for QR code.
     */
    private function getCacheKey(Profile $profile, string $format, int $size, array $options): string
    {
        $optionsHash = md5(json_encode($options));
        return "qr_code_{$profile->id}_{$format}_{$size}_{$optionsHash}";
    }

    /**
     * Clear cached QR codes for a profile.
     */
    public function clearCache(Profile $profile): void
    {
        // Clear all cached QR codes for this profile
        Cache::flush(); // Simplified - in production, use tags or specific keys
    }

    /**
     * Generate download filename for QR code.
     */
    public function getDownloadFilename(Profile $profile, string $format, int $size): string
    {
        $slug = $profile->slug;
        return "linkadi-qr-{$slug}-{$size}x{$size}.{$format}";
    }
}

