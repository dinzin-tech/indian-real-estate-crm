<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Http\Request;
use Core\Http\Response;
use App\Models\Lead;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    private WhatsAppService $whatsApp;

    public function __construct()
    {
        parent::__construct();
        $this->whatsApp = new WhatsAppService();
    }

    /**
     * Show form to send WhatsApp message to lead
     * @Route(path="/whatsapp/send/{leadId}", methods="GET", name="whatsapp.send.form")
     */
    public function sendForm(Request $request, int $leadId)
    {
        $lead = Lead::find($leadId);
        if (!$lead) {
            return new Response('Lead not found', 404);
        }
        return $this->render('whatsapp/send.htm.twig', [
            'lead' => $lead,
            'templates' => WhatsAppService::getTemplates(),
        ]);
    }

    /**
     * Send WhatsApp message
     * @Route(path="/whatsapp/send", methods="POST", name="whatsapp.send")
     */
    public function send(Request $request)
    {
        $leadId = (int) $request->post('lead_id');
        $messageTemplate = $request->post('template');
        $customMessage = $request->post('custom_message');

        $lead = Lead::find($leadId);
        if (!$lead) {
            return new Response('Lead not found', 404);
        }

        // Replace placeholders in template
        $message = $customMessage;
        if ($messageTemplate && $messageTemplate !== 'custom') {
            $message = WhatsAppService::getTemplates()[$messageTemplate] ?? $customMessage;
        }
        $message = str_replace('{{name}}', $lead->name, $message);
        $message = str_replace('{{phone}}', $lead->phone, $message);

        $result = $this->whatsApp->sendMessage($lead, $message);

        if ($result['success']) {
            return $this->render('whatsapp/success.htm.twig', ['result' => $result]);
        }
        return new Response('Failed to send message', 500);
    }
}