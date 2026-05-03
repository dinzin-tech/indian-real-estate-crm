<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Lead extends Model
{
    public function __construct()
    {
        $this->table = 'leads';
        parent::__construct();
    }

    // Public properties for the model (will be saved to DB)
    public ?int $id = null;
    public string $name = '';
    public string $phone = '';
    public ?string $email = null;
    public ?string $property_preferences = null;
    public ?string $source_portal = null;
    public ?string $gstin = null;
    public ?string $rera_number = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    // Validation rules (could be used in controller)
    public static function validationRules(): array
    {
        return [
            'name' => 'required|min:2|max:255',
            'phone' => 'required|pattern:/^\+91[0-9]{10}$/',
            'email' => 'email|max:255',
            'source_portal' => 'in:99acres,MagicBricks,Housing.com,NoBroker,Direct,Other',
            'gstin' => 'pattern:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'rera_number' => 'max:50',
        ];
    }

    // Indian-specific: format phone number
    public function formatPhone(): string
    {
        if (strpos($this->phone, '+91') !== 0) {
            return '+91' . $this->phone;
        }
        return $this->phone;
    }

    // Indian-specific: validate RERA number format (state-specific)
    public function isValidRera(): bool
    {
        if (empty($this->rera_number)) {
            return true; // optional
        }
        // Basic pattern: state code (2 letters) + numbers + optional suffix
        return (bool) preg_match('/^[A-Z]{2}[0-9]{2,10}[A-Z0-9]*$/', $this->rera_number);
    }
}