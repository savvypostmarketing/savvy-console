<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPositionController extends Controller
{
    /**
     * Get all active job positions for the public frontend.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'en');

        $positions = JobPosition::active()
            ->ordered()
            ->get()
            ->map(function ($position) use ($locale) {
                return [
                    'id' => $position->id,
                    'title' => $position->getLocalizedTitle($locale),
                    'department' => $position->department,
                    'employment_type' => $position->employment_type,
                    'employment_type_label' => $this->getLocalizedEmploymentType($position->employment_type, $locale),
                    'location_type' => $position->location_type,
                    'location_type_label' => $this->getLocalizedLocationType($position->location_type, $locale),
                    'location' => $position->location,
                    'salary_range' => $position->salary_range,
                    'description' => $position->getLocalizedDescription($locale),
                    'apply_url' => $position->apply_link,
                    'is_featured' => $position->is_featured,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $positions,
        ]);
    }

    /**
     * Get localized employment type label.
     */
    private function getLocalizedEmploymentType(string $type, string $locale): string
    {
        $labels = [
            'en' => [
                'full-time' => 'Full Time',
                'part-time' => 'Part Time',
                'contract' => 'Contract',
                'internship' => 'Internship',
            ],
            'es' => [
                'full-time' => 'Tiempo Completo',
                'part-time' => 'Medio Tiempo',
                'contract' => 'Contrato',
                'internship' => 'Pasantía',
            ],
        ];

        return $labels[$locale][$type] ?? $labels['en'][$type] ?? $type;
    }

    /**
     * Get localized location type label.
     */
    private function getLocalizedLocationType(string $type, string $locale): string
    {
        $labels = [
            'en' => [
                'remote' => 'Remote',
                'hybrid' => 'Hybrid',
                'on-site' => 'On-site',
            ],
            'es' => [
                'remote' => 'Remoto',
                'hybrid' => 'Híbrido',
                'on-site' => 'Presencial',
            ],
        ];

        return $labels[$locale][$type] ?? $labels['en'][$type] ?? $type;
    }
}
