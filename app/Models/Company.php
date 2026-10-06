<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alter_phone',
        'address',
        'city',
        'state',
        'pincode',
        'website',
        'logo',
        'description',
    ];

    /**
     * Singleton accessor for global company profile.
     */
    public static function current(): self
    {
        $company = self::first();
        if (!$company) {
            $company = self::create([
                'name' => 'Shibaura Machine India Private Limited',
                'email' => 'customercare@shibaura-machine.co.in',
                'phone' => '+91 44 2681 2000',
                'alter_phone' => '+91 44 2681 2001',
                'address' => 'No. 65 (PO Box 14), Chennai-Bangalore Highway, Chembarambakkam',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'pincode' => '600123',
                'website' => 'https://www.shibaura-machine.co.in',
                'logo' => 'images/shibaura-logo-cropped.webp',
                'description' => 'Precision injection moulding machines, die casting, and industrial robotic solutions engineered with Japanese precision.',
            ]);
        }
        return $company;
    }

    /**
     * Get logo URL with fallback.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }
            if (file_exists(public_path($this->logo))) {
                return asset($this->logo);
            }
            if (file_exists(public_path('storage/' . $this->logo))) {
                return asset('storage/' . $this->logo);
            }
        }
        return asset('images/shibaura-logo-cropped.webp');
    }

    /**
     * Get full formatted address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state . ($this->pincode ? ' - ' . $this->pincode : ''),
        ]);

        return implode(', ', $parts);
    }
}
