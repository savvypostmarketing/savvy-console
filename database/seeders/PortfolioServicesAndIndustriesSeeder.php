<?php

namespace Database\Seeders;

use App\Models\PortfolioIndustry;
use App\Models\PortfolioService;
use Illuminate\Database\Seeder;

class PortfolioServicesAndIndustriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Services - matching frontend services
        $services = [
            [
                'slug' => 'website',
                'name' => 'Website',
                'name_es' => 'Sitio Web',
                'color' => '#3B82F6', // blue
                'icon' => 'PaletteRound',
                'sort_order' => 1,
            ],
            [
                'slug' => 'seo',
                'name' => 'SEO',
                'name_es' => 'SEO',
                'color' => '#10B981', // green
                'icon' => 'MagniferBug',
                'sort_order' => 2,
            ],
            [
                'slug' => 'e-commerce',
                'name' => 'E-Commerce',
                'name_es' => 'Comercio Electrónico',
                'color' => '#F59E0B', // amber
                'icon' => 'Cart3',
                'sort_order' => 3,
            ],
            [
                'slug' => 'branding',
                'name' => 'Branding',
                'name_es' => 'Branding',
                'color' => '#EC4899', // pink
                'icon' => 'StickerSmileCircle2',
                'sort_order' => 4,
            ],
            [
                'slug' => 'app-development',
                'name' => 'App Development',
                'name_es' => 'Desarrollo de Apps',
                'color' => '#8B5CF6', // violet
                'icon' => 'Smartphone',
                'sort_order' => 5,
            ],
            [
                'slug' => 'digital-marketing',
                'name' => 'Digital Marketing',
                'name_es' => 'Marketing Digital',
                'color' => '#EF4444', // red
                'icon' => 'GraphUp',
                'sort_order' => 6,
            ],
        ];

        foreach ($services as $service) {
            PortfolioService::updateOrCreate(
                ['slug' => $service['slug']],
                $service
            );
        }

        // Industries
        $industries = [
            [
                'slug' => 'sign-company',
                'name' => 'Sign Companies',
                'name_es' => 'Empresas de Letreros',
                'icon' => 'Signpost',
                'sort_order' => 1,
            ],
            [
                'slug' => 'ecommerce',
                'name' => 'E-commerce',
                'name_es' => 'Comercio Electrónico',
                'icon' => 'Cart3',
                'sort_order' => 2,
            ],
            [
                'slug' => 'healthcare',
                'name' => 'Healthcare',
                'name_es' => 'Salud',
                'icon' => 'Health',
                'sort_order' => 3,
            ],
            [
                'slug' => 'real-estate',
                'name' => 'Real Estate',
                'name_es' => 'Bienes Raíces',
                'icon' => 'Buildings',
                'sort_order' => 4,
            ],
            [
                'slug' => 'technology',
                'name' => 'Technology',
                'name_es' => 'Tecnología',
                'icon' => 'Laptop',
                'sort_order' => 5,
            ],
            [
                'slug' => 'finance',
                'name' => 'Finance',
                'name_es' => 'Finanzas',
                'icon' => 'Dollar',
                'sort_order' => 6,
            ],
            [
                'slug' => 'education',
                'name' => 'Education',
                'name_es' => 'Educación',
                'icon' => 'BookOpenText',
                'sort_order' => 7,
            ],
            [
                'slug' => 'hospitality',
                'name' => 'Hospitality',
                'name_es' => 'Hospitalidad',
                'icon' => 'Hotel',
                'sort_order' => 8,
            ],
            [
                'slug' => 'restaurant',
                'name' => 'Restaurant & Food',
                'name_es' => 'Restaurantes y Comida',
                'icon' => 'ForkSpoon',
                'sort_order' => 9,
            ],
            [
                'slug' => 'professional-services',
                'name' => 'Professional Services',
                'name_es' => 'Servicios Profesionales',
                'icon' => 'BriefcaseBold',
                'sort_order' => 10,
            ],
            [
                'slug' => 'manufacturing',
                'name' => 'Manufacturing',
                'name_es' => 'Manufactura',
                'icon' => 'Factory',
                'sort_order' => 11,
            ],
            [
                'slug' => 'automotive',
                'name' => 'Automotive',
                'name_es' => 'Automotriz',
                'icon' => 'Car',
                'sort_order' => 12,
            ],
            [
                'slug' => 'beauty-wellness',
                'name' => 'Beauty & Wellness',
                'name_es' => 'Belleza y Bienestar',
                'icon' => 'Heart',
                'sort_order' => 13,
            ],
            [
                'slug' => 'fitness',
                'name' => 'Fitness & Sports',
                'name_es' => 'Fitness y Deportes',
                'icon' => 'Running',
                'sort_order' => 14,
            ],
            [
                'slug' => 'entertainment',
                'name' => 'Entertainment',
                'name_es' => 'Entretenimiento',
                'icon' => 'Music',
                'sort_order' => 15,
            ],
        ];

        foreach ($industries as $industry) {
            PortfolioIndustry::updateOrCreate(
                ['slug' => $industry['slug']],
                $industry
            );
        }
    }
}
