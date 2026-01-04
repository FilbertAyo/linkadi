<?php

namespace App\Services;

use App\Models\Profile;

class VCardGeneratorService
{
    /**
     * Generate vCard 3.0 format for a profile.
     *
     * @param Profile $profile
     * @return string vCard content
     */
    public function generate(Profile $profile): string
    {
        $vcard = [];
        
        // vCard header
        $vcard[] = "BEGIN:VCARD";
        $vcard[] = "VERSION:3.0";
        
        // Name fields
        if ($profile->isBusinessProfile()) {
            // For business: Organization name is primary, contact person is secondary
            $vcard[] = $this->formatLine("FN", $profile->business_name ?: $profile->user->name);
            $vcard[] = $this->formatLine("ORG", $profile->business_name ?: $profile->user->name);
            
            // Add contact person name
            $names = $this->parseName($profile->user->name);
            $vcard[] = "N:" . $names['last'] . ";" . $names['first'] . ";;;";
            
            if ($profile->title) {
                $vcard[] = $this->formatLine("TITLE", $profile->title);
            }
        } else {
            // For individual: Personal name is primary
            $vcard[] = $this->formatLine("FN", $profile->user->name);
            
            $names = $this->parseName($profile->user->name);
            $vcard[] = "N:" . $names['last'] . ";" . $names['first'] . ";;;";
            
            if ($profile->title) {
                $vcard[] = $this->formatLine("TITLE", $profile->title);
            }
            
            if ($profile->company) {
                $vcard[] = $this->formatLine("ORG", $profile->company);
            }
        }
        
        // Phone numbers (from profile_contacts)
        $phones = $profile->contacts()->phones()->public()->get();
        foreach ($phones as $phone) {
            $vcard[] = "TEL;TYPE=" . $phone->vcard_type . ":" . $phone->value;
        }
        
        // Email addresses (from profile_contacts)
        $emails = $profile->contacts()->emails()->public()->get();
        foreach ($emails as $email) {
            $vcard[] = "EMAIL;TYPE=" . $email->vcard_type . ":" . $email->value;
        }
        
        // Website
        if ($profile->website) {
            $vcard[] = $this->formatLine("URL", $profile->website);
        }
        
        // Profile URL (Linkadi profile link)
        $vcard[] = $this->formatLine("URL", $profile->public_url);
        
        // Address
        if ($profile->address) {
            // vCard ADR format: PO Box;Extended Address;Street;City;State;Postal Code;Country
            $vcard[] = "ADR;TYPE=WORK:;;" . $this->escape($profile->address) . ";;;;";
        }
        
        // Profile image (base64 encoded)
        if ($profile->profile_image) {
            $imagePath = storage_path('app/public/' . $profile->profile_image);
            if (file_exists($imagePath)) {
                $imageData = base64_encode(file_get_contents($imagePath));
                $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
                $mimeType = $imageType === 'png' ? 'PNG' : 'JPEG';
                
                $vcard[] = "PHOTO;ENCODING=b;TYPE=" . $mimeType . ":" . $imageData;
            }
        }
        
        // Bio as NOTE
        if ($profile->bio) {
            $vcard[] = $this->formatLine("NOTE", $profile->bio);
        }
        
        // Tax ID for business profiles
        if ($profile->isBusinessProfile() && $profile->tax_id) {
            $vcard[] = $this->formatLine("X-TAX-ID", $profile->tax_id);
        }
        
        // Revision timestamp
        $vcard[] = "REV:" . now()->format('Y-m-d\TH:i:s\Z');
        
        // vCard footer
        $vcard[] = "END:VCARD";
        
        return implode("\r\n", $vcard);
    }
    
    /**
     * Parse full name into first and last name.
     */
    private function parseName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);
        
        return [
            'first' => $parts[0] ?? '',
            'last' => $parts[1] ?? '',
        ];
    }
    
    /**
     * Format a vCard line with proper escaping.
     */
    private function formatLine(string $property, string $value): string
    {
        return $property . ":" . $this->escape($value);
    }
    
    /**
     * Escape special characters for vCard format.
     */
    private function escape(string $value): string
    {
        // Escape backslashes first
        $value = str_replace('\\', '\\\\', $value);
        
        // Escape commas, semicolons, and newlines
        $value = str_replace(',', '\\,', $value);
        $value = str_replace(';', '\\;', $value);
        $value = str_replace("\n", '\\n', $value);
        $value = str_replace("\r", '', $value);
        
        return $value;
    }
    
    /**
     * Get the filename for the vCard download.
     */
    public function getFilename(Profile $profile): string
    {
        $name = $profile->isBusinessProfile() && $profile->business_name 
            ? $profile->business_name 
            : $profile->user->name;
        
        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $name);
        
        return $filename . '.vcf';
    }
}

