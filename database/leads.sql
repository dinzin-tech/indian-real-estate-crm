-- Leads table for Indian real estate contact/lead management
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL COMMENT 'Indian phone number with +91',
    email VARCHAR(255) NULL,
    property_preferences TEXT NULL COMMENT 'JSON or text: location, budget, type',
    source_portal VARCHAR(50) NULL COMMENT '99acres, MagicBricks, Housing.com, NoBroker, Direct',
    gstin VARCHAR(15) NULL COMMENT 'Optional GSTIN for B2B leads',
    rera_number VARCHAR(50) NULL COMMENT 'RERA registration number (state-specific)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_source (source_portal),
    INDEX idx_rera (rera_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;