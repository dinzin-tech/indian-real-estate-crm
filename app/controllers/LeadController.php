<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Http\Request;
use Core\Http\Response;
use App\Models\Lead;

class LeadController extends Controller
{
    /**
     * List all leads
     * @Route(path="/leads", methods="GET", name="leads.index")
     */
    public function index(Request $request)
    {
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
     * Store new lead
     * @Route(path="/leads", methods="POST", name="leads.store")
     */
    public function store(Request $request)
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
        
        $lead->save();
        
        // Redirect to leads list
        return new Response('', 302, ['Location' => '/leads']);
    }

    /**
     * Show edit lead form
     * @Route(path="/leads/{id}/edit", methods="GET", name="leads.edit")
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
     * @Route(path="/leads/{id}", methods="POST", name="leads.update")
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
        
        return new Response('', 302, ['Location' => '/leads']);
    }

    /**
     * Delete lead
     * @Route(path="/leads/{id}", methods="DELETE", name="leads.delete")
     */
    public function delete(Request $request, int $id)
    {
        $lead = Lead::find($id);
        if ($lead) {
            $lead->delete();
        }
        return new Response('', 302, ['Location' => '/leads']);
    }

    /**
     * API: Get leads as JSON (for portal integrations later)
     * @Route(path="/api/leads", methods="GET", name="api.leads.index")
     */
    public function apiIndex(Request $request)
    {
        $leads = Lead::findAll();
        return new Response(
            json_encode(array_map(fn($l) => $l->toArray(), $leads)),
            200,
            ['content-type' => 'application/json']
        );
    }
}