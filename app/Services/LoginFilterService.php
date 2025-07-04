<?php

namespace App\Services;

use Illuminate\Http\Request;
use Carbon\Carbon;

class LoginFilterService
{
    /**
     * Predefined list of valid systems.
     */
    protected array $validSystems = [
        'SCM', 'Odoo', 'D365 Live', 'Fit Express', 'FIT ERP',
        'Fit Express UAT', 'FITerp UAT', 'OPS', 'OPS UAT',
    ];

    /**
     * Extracts and normalizes date range and system filters from the request.
     */
    public function extract(Request $request): array
    {
        $filter = $request->input('filter');

        // 🧠 Resolve date range from filter or manual input
        [$startDate, $endDate, $normalizedFilter] = $this->resolveDateRange($filter, $request);

        // ✅ Fallback to 'SCM' if input is not in predefined system list
        $requestedSystem = trim($request->input('system', ''));
        $system = in_array($requestedSystem, $this->validSystems, true) ? $requestedSystem : 'SCM';

        return compact('startDate', 'endDate', 'system', 'filter');
    }

    /**
     * Public accessor for valid systems list (optional use in controller/view).
     */
    public function getValidSystems(): array
    {
        return $this->validSystems;
    }

    /**
     * Resolves the date range based on the predefined filter or manual range.
     */
    private function resolveDateRange(?string $filter, Request $request): array
    {
        switch ($filter) {
            case 'this_month':
                return [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfDay(),
                    $filter
                ];
            case 'previous_month':
                return [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth(),
                    $filter
                ];
            case 'last_3_months':
                return [
                    Carbon::now()->subMonths(3)->startOfMonth(),
                    Carbon::now()->endOfDay(),
                    $filter
                ];
            default:
                // Manual date selection fallback
                $startDate = $request->input('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : Carbon::now()->subDays(30)->startOfDay();

                $endDate = $request->input('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : Carbon::now()->endOfDay();

                return [$startDate, $endDate, 'custom'];
        }
    }
}
