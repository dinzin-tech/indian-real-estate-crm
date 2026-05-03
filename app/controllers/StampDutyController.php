<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Http\Request;
use Core\Http\Response;

class StampDutyController extends Controller
{
    // State-wise stamp duty rates (as of 2026, simplified)
    private const RATES = [
        'Maharashtra' => ['residential' => 5.0, 'commercial' => 6.0, 'women_rebate' => 1.0],
        'Karnataka' => ['residential' => 5.0, 'commercial' => 6.0, 'threshold' => 3500000], // 5% above 35L
        'Tamil Nadu' => ['residential' => 7.0, 'commercial' => 9.0],
        'Delhi' => ['residential' => 6.0, 'commercial' => 6.0],
        'Gujarat' => ['residential' => 4.9, 'commercial' => 5.9],
        'Telangana' => ['residential' => 5.0, 'commercial' => 6.0],
        'Uttar Pradesh' => ['residential' => 7.0, 'commercial' => 7.0],
    ];

    /**
     * Show stamp duty calculator form
     * @Route(path="/stamp-duty", methods="GET", name="stampduty.index")
     */
    public function index(Request $request)
    {
        return $this->render('stampduty/index.htm.twig', [
            'states' => array_keys(self::RATES),
            'property_types' => ['residential', 'commercial'],
        ]);
    }

    /**
     * Calculate stamp duty
     * @Route(path="/stamp-duty/calculate", methods="POST", name="stampduty.calculate")
     */
    public function calculate(Request $request)
    {
        $state = $request->post('state');
        $propertyType = $request->post('property_type');
        $propertyValue = (float) $request->post('property_value', 0);
        $ownerGender = $request->post('owner_gender', 'male'); // male/female (some states rebate for women)

        if (!isset(self::RATES[$state])) {
            return new Response('Invalid state', 400);
        }

        $rate = self::RATES[$state][$propertyType] ?? 0;
        $stampDuty = ($propertyValue * $rate) / 100;

        // Karnataka: 5% only if property value > 35L
        if ($state === 'Karnataka' && $propertyValue <= self::RATES[$state]['threshold']) {
            $stampDuty = 0;
            $rate = 0;
        }

        // Women rebate example (Maharashtra: 1% less)
        $rebate = 0;
        if ($ownerGender === 'female' && isset(self::RATES[$state]['women_rebate'])) {
            $rebate = self::RATES[$state]['women_rebate'];
            $stampDuty = ($propertyValue * ($rate - $rebate)) / 100;
        }

        $registrationCharge = $propertyValue * 0.01; // Typically 1%
        $total = $stampDuty + $registrationCharge;

        return $this->render('stampduty/result.htm.twig', [
            'state' => $state,
            'property_type' => $propertyType,
            'property_value' => $propertyValue,
            'owner_gender' => $ownerGender,
            'stamp_duty_rate' => $rate,
            'stamp_duty' => $stampDuty,
            'rebate' => $rebate,
            'registration_charge' => $registrationCharge,
            'total' => $total,
        ]);
    }
}