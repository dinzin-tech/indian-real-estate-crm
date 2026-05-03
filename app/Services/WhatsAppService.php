<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;

class WhatsAppService
{
    // Simulate sending WhatsApp message (replace with real API integration later)
    public function sendMessage(Lead $lead, string $message): array
    {
        // In production, integrate with Interakt, Gupshup, or WhatsApp Business API
        // Example API call:
        // $response = wp_remote_post('https://api.interakt.ai/v1/public/message/', [
        //     'body' => json_encode(['phone' => $lead->phone, 'message' => $message]),
        //     'headers' => ['Authorization' => 'Bearer ' . env('WHATSAPP_API_KEY')]
        // ]);
        
        // Simulate successful send
        return [
            'success' => true,
            'message' => "WhatsApp message sent to {$lead->phone} (simulated)",
            'lead_name' => $lead->name,
        ];
    }
    
    // Pre-defined Indian real estate message templates
    public static function getTemplates(): array
    {
        return [
            'welcome' => "Hello {{name}}, thank you for your interest in our property. We'll contact you soon.",
            'site_visit' => "Hi {{name}}, your site visit is scheduled for tomorrow at 11 AM. Address: {{address}}.",
            'payment_reminder' => "Dear {{name}}, your payment of ₹{{amount}} is due on {{date}}. Please pay via UPI: {{upi_id}}.",
            'rera_info' => "Hi {{name}}, the RERA registered project you enquired about: {{rera_number}}. Check details at https://rera.state.gov.in",
        ];
    }
}