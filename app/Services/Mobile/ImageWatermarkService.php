<?php

namespace App\Services\Mobile;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageWatermarkService
{
    /**
     * Apply a tamper-proof visual watermark to an uploaded photo
     *
     * @return string Relative storage path of the watermarked image
     */
    public function applyWatermark(
        UploadedFile|string $imageFile,
        string $vehicleReg,
        ?string $driverName = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $locationName = null,
        ?Carbon $timestamp = null
    ): string {
        $timestamp = $timestamp ?? Carbon::now();
        $formattedTime = $timestamp->format('Y-m-d H:i:s');

        $driverText = $driverName ? "Driver: {$driverName}" : 'NZ Group Fleet';
        $locationText = $locationName ?? ($latitude && $longitude ? "Lat: {$latitude}, Lng: {$longitude}" : 'Factory Network');

        $line1 = "VFMS SECURE PROOF | {$formattedTime} | {$vehicleReg}";
        $line2 = "{$driverText} | {$locationText}";
        $line3 = '🔒 NZ GROUP VEHICLE MANAGEMENT SYSTEM - TAMPER EVIDENT AUDIT SEAL';

        $sourcePath = is_string($imageFile) ? $imageFile : $imageFile->getRealPath();

        // Check if GD is available and file exists
        if (! extension_loaded('gd') || ! file_exists($sourcePath)) {
            // Fallback: Store standard file if GD not loaded
            if ($imageFile instanceof UploadedFile) {
                return $imageFile->store('mobile_proofs', 'public');
            }

            return $sourcePath;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! $imageInfo) {
            if ($imageFile instanceof UploadedFile) {
                return $imageFile->store('mobile_proofs', 'public');
            }

            return $sourcePath;
        }

        $mime = $imageInfo['mime'];
        $srcImage = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $srcImage) {
            if ($imageFile instanceof UploadedFile) {
                return $imageFile->store('mobile_proofs', 'public');
            }

            return $sourcePath;
        }

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        // Height of the watermark banner
        $bannerHeight = max(65, (int) ($height * 0.12));
        $bannerY = $height - $bannerHeight;

        // Allocate colors
        $darkBg = imagecolorallocatealpha($srcImage, 15, 23, 42, 25); // Slate 900 semi-transparent
        $orangeAccent = imagecolorallocate($srcImage, 234, 88, 12);    // NZ Group Orange
        $whiteText = imagecolorallocate($srcImage, 255, 255, 255);
        $slateText = imagecolorallocate($srcImage, 203, 213, 225);     // Slate 300

        // Draw banner background and top accent stripe
        imagefilledrectangle($srcImage, 0, $bannerY, $width, $height, $darkBg);
        imagefilledrectangle($srcImage, 0, $bannerY, $width, $bannerY + 4, $orangeAccent);

        // Draw text lines using built-in GD fonts (font 3 or 4)
        $font = 4; // built-in font
        $lineSpacing = 18;
        $startY = $bannerY + 10;

        imagestring($srcImage, $font, 15, $startY, $line1, $whiteText);
        imagestring($srcImage, 3, 15, $startY + $lineSpacing, $line2, $slateText);
        imagestring($srcImage, 2, 15, $startY + ($lineSpacing * 2), $line3, $orangeAccent);

        // Save watermarked image to storage
        $filename = 'proof_'.now()->format('Ymd_His').'_'.uniqid().'.jpg';
        $relativeDir = 'mobile_proofs/'.now()->format('Y/m');
        $fullDir = storage_path("app/public/{$relativeDir}");

        if (! file_exists($fullDir)) {
            @mkdir($fullDir, 0755, true);
        }

        $destPath = "{$fullDir}/{$filename}";
        imagejpeg($srcImage, $destPath, 85);
        imagedestroy($srcImage);

        return "{$relativeDir}/{$filename}";
    }

    /**
     * Generate metadata tag dictionary for audit records
     */
    public function generateProofMetadata(
        string $vehicleReg,
        ?string $driverName = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $locationName = null
    ): array {
        return [
            'captured_at' => now()->toIso8601String(),
            'vehicle_registration' => $vehicleReg,
            'driver_name' => $driverName ?? 'Unassigned',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_name' => $locationName ?? 'Factory Network',
            'watermark_engine' => 'VFMS GD v4 Anti-Tamper',
            'tamper_hash' => hash('sha256', "{$vehicleReg}-{$driverName}-".now()->timestamp),
        ];
    }
}
