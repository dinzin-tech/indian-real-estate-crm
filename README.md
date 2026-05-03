# Indian Real Estate CRM

A PHP MVC application tailored for Indian real estate businesses with core features like lead management, RERA compliance, WhatsApp integration, and stamp duty calculation.

## Features

### 1. Contact/Lead Management
- Store lead details with Indian-specific fields
- Integration ready for local portals: 99acres, MagicBricks, Housing.com, NoBroker
- Fields: Name, Phone (+91 format), Email, Property Preferences, Source Portal, GSTIN (B2B), RERA Number
- Full CRUD operations via web interface

### 2. RERA Compliance
- RERA registration number field for properties/leads
- State-specific RERA number validation
- Helps maintain compliance with Real Estate Regulation Act

### 3. WhatsApp Integration
- Send WhatsApp messages to leads (currently simulated)
- Pre-defined message templates: Welcome, Site Visit, Payment Reminder, RERA Info
- Ready for integration with Interakt, Gupshup, or WhatsApp Business API

### 4. Stamp Duty Calculator
- Calculate stamp duty and registration charges across Indian states
- Supports: Maharashtra, Karnataka, Tamil Nadu, Delhi, Gujarat, Telangana, Uttar Pradesh
- Accounts for women rebate (Maharashtra: 1% less)
- Karnataka threshold: 5% only for properties above ₹35 lakhs

## Requirements

- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js (optional, for asset compilation)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/dinzin-tech/indian-real-estate-crm.git
cd indian-real-estate-crm
```

2. Install PHP dependencies:
```bash
composer install
```

3. Set up environment:
```bash
cp .env.example .env
# Edit .env with your database credentials
```

4. Create MySQL database and import schema:
```bash
mysql -u root -p -e "CREATE DATABASE indian_crm;"
mysql -u root -p indian_crm < database/leads.sql
```

5. Start the development server:
```bash
php -S localhost:8000 -t public dev-router.php
```

6. Visit: http://localhost:8000

## Configuration

Edit `.env` file:
```env
DEFAULT_DB_HOST=127.0.0.1
DEFAULT_DB_DATABASE=indian_crm
DEFAULT_DB_USER=root
DEFAULT_DB_PASSWORD=your_password
APP_ENV=dev
DEBUG_MODE=true
BASE_URL=http://localhost:8000
```

## Database Schema

### Leads Table
```sql
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    property_preferences TEXT,
    source_portal VARCHAR(50),
    gstin VARCHAR(15),
    rera_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Usage

### Lead Management
- List leads: `http://localhost:8000/leads`
- Add lead: `http://localhost:8000/leads/create`
- Edit lead: `http://localhost:8000/leads/{id}/edit`

### Stamp Duty Calculator
- Access: `http://localhost:8000/stamp-duty`
- Enter state, property type, value, and owner gender
- Get instant calculation of stamp duty + registration charges

### WhatsApp Integration
- From any lead's edit page, click "Send WhatsApp"
- Choose template or write custom message
- Currently simulated (replace with real API in production)

## Project Structure

```
app/
├── controllers/
│   ├── LeadController.php       # Lead CRUD
│   ├── StampDutyController.php # Stamp duty calculator
│   └── WhatsAppController.php  # WhatsApp messaging
├── models/
│   └── Lead.php                # Lead model with RERA validation
├── Services/
│   └── WhatsAppService.php     # WhatsApp API stub
└── views/
    ├── leads/                   # Lead CRUD views
    ├── stampduty/               # Calculator views
    └── whatsapp/                # WhatsApp views
database/
└── leads.sql                    # Database schema
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/leads` | List all leads |
| POST | `/leads` | Create new lead |
| GET | `/leads/create` | Lead creation form |
| GET | `/leads/{id}/edit` | Edit lead form |
| POST | `/leads/{id}` | Update lead |
| DELETE | `/leads/{id}` | Delete lead |
| GET | `/stamp-duty` | Stamp duty calculator form |
| POST | `/stamp-duty/calculate` | Calculate stamp duty |
| GET | `/whatsapp/send/{leadId}` | WhatsApp send form |
| POST | `/whatsapp/send` | Send WhatsApp message |

## Indian-Specific Features

### Phone Number Format
- All phone numbers stored with +91 prefix
- Validation: `+91[0-9]{10}`

### RERA Numbers
- State-specific format validation
- Example: `P-BPL-17-1234` (Madhya Pradesh)

### GSTIN Validation
- Format: `22AAAAA0000A1Z5`
- 15-character alphanumeric code

### Stamp Duty Rates (2026)
| State | Residential | Commercial | Notes |
|-------|-------------|-------------|-------|
| Maharashtra | 5% | 6% | 1% rebate for women |
| Karnataka | 5% | 6% | Only above ₹35L |
| Tamil Nadu | 7% | 9% | - |
| Delhi | 6% | 6% | - |
| Gujarat | 4.9% | 5.9% | - |
| Telangana | 5% | 6% | - |
| Uttar Pradesh | 7% | 7% | - |

## Future Enhancements

- [ ] Real portal API integrations (99acres, MagicBricks)
- [ ] WhatsApp Business API integration (Interakt/Gupshup)
- [ ] User authentication and roles
- [ ] Property management module
- [ ] Site visit scheduling
- [ ] Payment tracking
- [ ] Email notifications
- [ ] Reports and analytics
- [ ] Mobile responsive improvements
- [ ] Docker setup for easy deployment

## Framework

Built on [Simple MVC](https://github.com/dinzin-tech/simple-mvc) PHP framework with:
- Custom routing with annotations
- Twig templating
- PDO database layer
- Dependency injection container

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

MIT License - feel free to use this project for your Indian real estate business.

## Support

For issues and feature requests, please use the GitHub issue tracker.

---

**Built for Indian real estate professionals, by Indian developers.** 🙏
