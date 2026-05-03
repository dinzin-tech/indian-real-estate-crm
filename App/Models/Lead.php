<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Attributes\Column;

class Lead extends Model
{
    #[Column(type: 'int', primaryKey: true, autoIncrement: true)]
    public int $id;
    
    #[Column(type: 'string', length: 100, nullable: false)]
    public string $name;
    
    #[Column(type: 'string', length: 20, nullable: false)]
    public string $phone;
    
    #[Column(type: 'string', length: 100, nullable: true)]
    public ?string $email;
    
    #[Column(type: 'string', length: 50, nullable: true, name: 'source_portal')]
    public ?string $source_portal;
    
    #[Column(type: 'string', length: 50, nullable: true, name: 'rera_number')]
    public ?string $rera_number;
    
    #[Column(type: 'string', length: 20, nullable: true, name: 'gstin')]
    public ?string $gstin;
    
    #[Column(type: 'text', nullable: true, name: 'property_preferences')]
    public ?string $property_preferences;
    
    #[Column(type: 'datetime', nullable: true, name: 'created_at')]
    public ?\DateTime $created_at;
    
    #[Column(type: 'datetime', nullable: true, name: 'updated_at')]
    public ?\DateTime $updated_at;
    
    /**
     * Validate RERA number format (state-specific)
     * Format: Usually {State Code}{Year}{Project Number}
     * Example: MH2023Project123
     */
    public function isValidRera(): bool
    {
        if (empty($this->rera_number)) {
            return true; // Optional field
        }
        
        // Basic RERA pattern: 2-3 letter state code + numbers/letters
        return preg_match('/^[A-Z]{2,3}[0-9A-Z]{4,20}$/', $this->rera_number) === 1;
    }
    
    /**
     * Get portal display name
     */
    public function getPortalName(): string
    {
        $portals = [
            '99acres' => '99acres',
            'MagicBricks' => 'MagicBricks',
            'Housing.com' => 'Housing.com',
            'NoBroker' => 'NoBroker',
            'Direct' => 'Direct',
            'Other' => 'Other'
        ];
        
        return $portals[$this->source_portal] ?? $this->source_portal ?? 'Unknown';
    }
}
