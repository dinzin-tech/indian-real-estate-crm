<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Http\Request;
use Core\Http\Response;
use App\Models\Lead;

class LeadController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all leads / Create new lead
     * @Route(path="/leads", methods="GET,POST", name="leads.index")
     */
    public function index(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            return $this->store($request);
        }
        
        $leads = Lead::findAll();
        return $this->render('leads/index.htm.twig', ['leads' => $leads]);
    }

    /**
     * Show create lead form
     * @Route(path="/leads/create", methods="GET", name="leads.create")
     */
    public function create(Request $request)
    {
        return $this->render('leads/create.htm.twig', [
            'portals' => ['99acres', 'MagicBricks', 'Housing.com', 'NoBroker', 'Direct', 'Other']
        ]);
    }

    /**
     * Handle lead creation (used internally by index)
     */
    private function store(Request $request)
    {
        $data = $request->getPostData();
        
        $lead = new Lead();
        $lead->name = $data['name'] ?? '';
        $lead->phone = $data['phone'] ?? '';
        $lead->email = $data['email'] ?? null;
        $lead->property_preferences = $data['property_preferences'] ?? null;
        $lead->source_portal = $data['source_portal'] ?? null;
        $lead->gstin = $data['gstin'] ?? null;
        $lead->rera_number = $data['rera_number'] ?? null;
        
        if (empty($lead->name) || empty($lead->phone)) {
            return new Response('Name and phone are required', 400);
        }
        
        if (!$lead->isValidRera()) {
            return new Response('Invalid RERA number format', 400);
        }
        
        $lead->save();
        
        // Redirect to leads list
        header('Location: /leads');
        exit;
    }

    /**
     * Show edit lead form
     * @Route(path="/leads/edit/{id}", methods="GET", name="leads.edit")
     */
    public function edit(Request $request, int $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return new Response('Lead not found', 404);
        }
        
        return $this->render('leads/edit.htm.twig', [
            'lead' => $lead,
            'portals' => ['99acres', 'MagicBricks', 'Housing.com', 'NoBroker', 'Direct', 'Other']
        ]);
    }

    /**
     * Update lead
     * @Route(path="/leads/update/{id}", methods="POST", name="leads.update")
     */
    public function update(Request $request, int $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return new Response('Lead not found', 404);
        }
        
        $data = $request->getPostData();
        
        $lead->name = $data['name'] ?? $lead->name;
        $lead->phone = $data['phone'] ?? $lead->phone;
        $lead->email = $data['email'] ?? $lead->email;
        $lead->property_preferences = $data['property_preferences'] ?? $lead->property_preferences;
        $lead->source_portal = $data['source_portal'] ?? $lead->source_portal;
        $lead->gstin = $data['gstin'] ?? $lead->gstin;
        $lead->rera_number = $data['rera_number'] ?? $lead->rera_number;
        
        $lead->save();
        
        header('Location: /leads');
        exit;
    }

    /**
     * Delete lead
     * @Route(path="/leads/delete/{id}", methods="POST", name="leads.delete")
     */
    public function delete(Request $request, int $id)
    {
        $lead = Lead::find($id);
        if ($lead) {
            $lead->delete();
        }
        
        header('Location: /leads');
        exit;
    }

    /**
     * Import leads from external portals
     * @Route(path="/leads/import", methods="GET,POST", name="leads.import")
     */
    public function import(Request $request)
    {
        $portals = ['99acres', 'MagicBricks', 'Housing.com', 'NoBroker'];
        $results = [];
        
        if ($request->getMethod() === 'POST') {
            $selectedPortals = $request->post('portals', []);
            
            foreach ($selectedPortals as $portal) {
                $results[$portal] = $this->importFromPortal($portal);
            }
        }
        
        return $this->render('leads/import.htm.twig', [
            'portals' => $portals,
            'results' => $results
        ]);
    }

    /**
     * Import leads from a specific portal
     */
    private function importFromPortal(string $portal): array
    {
        $service = new \App\Services\PortalImportService();
        return $service->import($portal);
    }

    /**
     * Webhook endpoint for portal lead push integration
     * @Route(path="/webhook/{portal}", methods="POST", name="leads.webhook")
     */
    public function webhook(Request $request, string $portal)
    {
        // Get JSON or POST data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $data = $request->getPostData();
        }
        
        $service = new \App\Services\PortalImportService();
        $result = $service->handleWebhook($portal, $data);
        
        return new Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
    }
}
