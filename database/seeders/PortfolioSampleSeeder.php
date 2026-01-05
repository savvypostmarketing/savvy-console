<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\PortfolioIndustry;
use App\Models\PortfolioService;
use Illuminate\Database\Seeder;

class PortfolioSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get industries
        $signIndustry = PortfolioIndustry::where('slug', 'sign-company')->first();
        $restaurantIndustry = PortfolioIndustry::where('slug', 'restaurant')->first();

        // Get services
        $websiteService = PortfolioService::where('slug', 'website')->first();
        $seoService = PortfolioService::where('slug', 'seo')->first();
        $ecommerceService = PortfolioService::where('slug', 'e-commerce')->first();
        $brandingService = PortfolioService::where('slug', 'branding')->first();
        $digitalMarketingService = PortfolioService::where('slug', 'digital-marketing')->first();

        if (!$signIndustry || !$restaurantIndustry) {
            $this->command->warn('Required industries not found. Run PortfolioServicesAndIndustriesSeeder first.');
            return;
        }

        // Portfolio 1: The Super Sign Guy
        $superSignGuy = Portfolio::updateOrCreate(
            ['slug' => 'the-super-sign-guy'],
            [
                'title' => 'The Super Sign Guy',
                'title_es' => 'The Super Sign Guy',
                'industry_id' => $signIndustry->id,
                'description' => 'The Super Sign Guy needed a complete digital transformation to compete in the modern signage industry. We developed a comprehensive solution that combines a stunning website with powerful e-commerce capabilities and search engine optimization.',
                'description_es' => 'The Super Sign Guy necesitaba una transformación digital completa para competir en la industria moderna de señalización. Desarrollamos una solución integral que combina un sitio web impresionante con poderosas capacidades de comercio electrónico y optimización de motores de búsqueda.',
                'challenge' => 'The Super Sign Guy was struggling to compete online despite having excellent products and services. Their outdated website was not mobile-friendly, had poor search rankings, and lacked the ability to process online orders. They were losing potential customers to competitors with stronger digital presence.',
                'challenge_es' => 'The Super Sign Guy estaba luchando por competir en línea a pesar de tener excelentes productos y servicios. Su sitio web obsoleto no era compatible con dispositivos móviles, tenía un bajo posicionamiento en buscadores y carecía de la capacidad de procesar pedidos en línea.',
                'solution' => 'We implemented a full digital strategy starting with a modern, mobile-first website design that showcases their work beautifully. We integrated a custom e-commerce solution allowing customers to request quotes and place orders online. Our SEO strategy focused on local search optimization and industry-specific keywords to drive targeted traffic.',
                'solution_es' => 'Implementamos una estrategia digital completa comenzando con un diseño de sitio web moderno y orientado a móviles que muestra su trabajo de manera hermosa. Integramos una solución de comercio electrónico personalizada que permite a los clientes solicitar cotizaciones y realizar pedidos en línea.',
                'website_url' => 'https://thesupersignguy.com',
                'testimonial_quote' => 'Savvy Post Marketing transformed our business. We went from barely getting any online inquiries to having more leads than we can handle. Their team understood our industry and delivered exactly what we needed.',
                'testimonial_quote_es' => 'Savvy Post Marketing transformó nuestro negocio. Pasamos de apenas recibir consultas en línea a tener más prospectos de los que podemos manejar. Su equipo entendió nuestra industria y entregó exactamente lo que necesitábamos.',
                'testimonial_author' => 'John Smith',
                'testimonial_role' => 'Owner, The Super Sign Guy',
                'testimonial_role_es' => 'Propietario, The Super Sign Guy',
                'is_published' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ]
        );

        // Attach services
        $superSignGuy->services()->sync([
            $websiteService->id,
            $seoService->id,
            $ecommerceService->id,
        ]);

        // Add stats
        $superSignGuy->stats()->delete();
        $superSignGuy->stats()->createMany([
            ['label' => 'Traffic Increase', 'label_es' => 'Aumento de Tráfico', 'value' => '+245%', 'sort_order' => 0],
            ['label' => 'Lead Generation', 'label_es' => 'Generación de Leads', 'value' => '+180%', 'sort_order' => 1],
            ['label' => 'Conversion Rate', 'label_es' => 'Tasa de Conversión', 'value' => '+320%', 'sort_order' => 2],
            ['label' => 'Revenue Growth', 'label_es' => 'Crecimiento de Ingresos', 'value' => '+85%', 'sort_order' => 3],
        ]);

        // Add features
        $superSignGuy->features()->delete();
        $superSignGuy->features()->createMany([
            [
                'number' => '01',
                'title' => 'Custom E-Commerce Platform',
                'title_es' => 'Plataforma de Comercio Electrónico Personalizada',
                'description' => 'Built a custom quote and ordering system that streamlines the sales process.',
                'description_es' => 'Construimos un sistema personalizado de cotizaciones y pedidos que agiliza el proceso de ventas.',
                'icon' => 'Cart',
                'sort_order' => 0,
            ],
            [
                'number' => '02',
                'title' => 'Mobile-First Design',
                'title_es' => 'Diseño Mobile-First',
                'description' => 'Responsive design that looks great on all devices and drives mobile conversions.',
                'description_es' => 'Diseño responsivo que se ve genial en todos los dispositivos y genera conversiones móviles.',
                'icon' => 'Phone',
                'sort_order' => 1,
            ],
            [
                'number' => '03',
                'title' => 'Local SEO Strategy',
                'title_es' => 'Estrategia de SEO Local',
                'description' => 'Optimized for local searches to capture nearby customers looking for sign services.',
                'description_es' => 'Optimizado para búsquedas locales para captar clientes cercanos que buscan servicios de señalización.',
                'icon' => 'Location',
                'sort_order' => 2,
            ],
        ]);

        // Add results
        $superSignGuy->results()->delete();
        $superSignGuy->results()->createMany([
            ['result' => 'Increased organic traffic by 245% within 6 months', 'result_es' => 'Aumento del tráfico orgánico en un 245% en 6 meses', 'sort_order' => 0],
            ['result' => 'Online quote requests grew by 180%', 'result_es' => 'Las solicitudes de cotización en línea crecieron un 180%', 'sort_order' => 1],
            ['result' => 'First page Google rankings for 15 target keywords', 'result_es' => 'Primera página de Google para 15 palabras clave objetivo', 'sort_order' => 2],
            ['result' => 'Mobile traffic conversion rate improved by 320%', 'result_es' => 'La tasa de conversión del tráfico móvil mejoró en un 320%', 'sort_order' => 3],
            ['result' => 'Average order value increased by 45% through strategic upselling', 'result_es' => 'El valor promedio de pedido aumentó un 45% mediante ventas adicionales estratégicas', 'sort_order' => 4],
        ]);

        $this->command->info('Created portfolio: The Super Sign Guy');

        // Portfolio 2: Mini Batch Bakery
        $miniBatch = Portfolio::updateOrCreate(
            ['slug' => 'mini-batch-bakery'],
            [
                'title' => 'Mini Batch Bakery',
                'title_es' => 'Mini Batch Bakery',
                'industry_id' => $restaurantIndustry->id,
                'description' => 'Mini Batch Bakery specializes in custom small-batch baked goods for boutique cafes and restaurants. They needed a brand identity and website that reflected their artisanal approach to baking.',
                'description_es' => 'Mini Batch Bakery se especializa en productos horneados personalizados de pequeños lotes para cafeterías y restaurantes boutique. Necesitaban una identidad de marca y un sitio web que reflejara su enfoque artesanal en la panadería.',
                'challenge' => 'As a newer company in a competitive market, Mini Batch Bakery struggled to differentiate themselves from larger bakeries. They needed to communicate their unique value proposition of handcrafted, small-batch quality.',
                'challenge_es' => 'Como empresa nueva en un mercado competitivo, Mini Batch Bakery luchaba por diferenciarse de las panaderías más grandes. Necesitaban comunicar su propuesta de valor única de calidad artesanal y de pequeños lotes.',
                'solution' => 'We developed a complete brand identity that emphasizes craftsmanship and attention to detail. The website showcases their portfolio with high-quality photography and tells the story of their artisanal process.',
                'solution_es' => 'Desarrollamos una identidad de marca completa que enfatiza la artesanía y la atención al detalle. El sitio web muestra su portafolio con fotografía de alta calidad y cuenta la historia de su proceso artesanal.',
                'website_url' => 'https://minibatchbakery.com',
                'testimonial_quote' => 'Savvy Post Marketing helped us stand out in a crowded market. Our new brand and website perfectly capture what makes Mini Batch Bakery special.',
                'testimonial_quote_es' => 'Savvy Post Marketing nos ayudó a destacar en un mercado saturado. Nuestra nueva marca y sitio web capturan perfectamente lo que hace especial a Mini Batch Bakery.',
                'testimonial_author' => 'Sarah Johnson',
                'testimonial_role' => 'Founder, Mini Batch Bakery',
                'testimonial_role_es' => 'Fundadora, Mini Batch Bakery',
                'is_published' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ]
        );

        $miniBatch->services()->sync([
            $websiteService->id,
            $brandingService->id,
        ]);

        $miniBatch->stats()->delete();
        $miniBatch->stats()->createMany([
            ['label' => 'Brand Recognition', 'label_es' => 'Reconocimiento de Marca', 'value' => '+400%', 'sort_order' => 0],
            ['label' => 'Website Traffic', 'label_es' => 'Tráfico Web', 'value' => '+175%', 'sort_order' => 1],
            ['label' => 'Lead Quality', 'label_es' => 'Calidad de Leads', 'value' => '+90%', 'sort_order' => 2],
            ['label' => 'Project Value', 'label_es' => 'Valor de Proyectos', 'value' => '+65%', 'sort_order' => 3],
        ]);

        $miniBatch->features()->delete();
        $miniBatch->features()->createMany([
            [
                'number' => '01',
                'title' => 'Brand Identity Design',
                'title_es' => 'Diseño de Identidad de Marca',
                'description' => 'Complete visual identity including logo, color palette, and typography.',
                'description_es' => 'Identidad visual completa incluyendo logo, paleta de colores y tipografía.',
                'icon' => 'PaintBrush',
                'sort_order' => 0,
            ],
            [
                'number' => '02',
                'title' => 'Portfolio Showcase',
                'title_es' => 'Vitrina de Portafolio',
                'description' => 'Beautiful gallery showcasing their handcrafted work with detailed case studies.',
                'description_es' => 'Hermosa galería que muestra su trabajo artesanal con estudios de caso detallados.',
                'icon' => 'Image',
                'sort_order' => 1,
            ],
            [
                'number' => '03',
                'title' => 'Story-Driven Content',
                'title_es' => 'Contenido Basado en Historia',
                'description' => 'Compelling narrative that communicates their artisanal approach.',
                'description_es' => 'Narrativa convincente que comunica su enfoque artesanal.',
                'icon' => 'DocumentText',
                'sort_order' => 2,
            ],
        ]);

        $miniBatch->results()->delete();
        $miniBatch->results()->createMany([
            ['result' => 'Established strong brand recognition in the boutique bakery market', 'result_es' => 'Estableció un fuerte reconocimiento de marca en el mercado de panadería boutique', 'sort_order' => 0],
            ['result' => '175% increase in website traffic within 3 months', 'result_es' => 'Aumento del 175% en el tráfico web en 3 meses', 'sort_order' => 1],
            ['result' => 'Attracted higher-value clients seeking premium quality', 'result_es' => 'Atrajo clientes de mayor valor que buscan calidad premium', 'sort_order' => 2],
            ['result' => 'Featured in 3 industry publications', 'result_es' => 'Destacado en 3 publicaciones de la industria', 'sort_order' => 3],
        ]);

        $this->command->info('Created portfolio: Mini Batch Bakery');

        // Portfolio 3: She Sells Signs
        $sheSellsSigns = Portfolio::updateOrCreate(
            ['slug' => 'she-sells-signs'],
            [
                'title' => 'She Sells Signs',
                'title_es' => 'She Sells Signs',
                'industry_id' => $signIndustry->id,
                'description' => 'She Sells Signs is a woman-owned sign company focused on empowering female entrepreneurs with professional signage. They needed a complete digital marketing strategy to reach their target audience.',
                'description_es' => 'She Sells Signs es una empresa de señalización propiedad de una mujer, enfocada en empoderar a mujeres emprendedoras con señalización profesional. Necesitaban una estrategia de marketing digital completa para llegar a su público objetivo.',
                'challenge' => 'Despite having a unique niche and compelling story, She Sells Signs was not reaching their target market effectively. Their social media presence was minimal and their website was not converting visitors into customers.',
                'challenge_es' => 'A pesar de tener un nicho único y una historia convincente, She Sells Signs no estaba llegando a su mercado objetivo de manera efectiva. Su presencia en redes sociales era mínima y su sitio web no convertía visitantes en clientes.',
                'solution' => 'We created a comprehensive digital marketing strategy including social media management, content marketing, and email campaigns. The website was redesigned to tell their story and connect with female business owners.',
                'solution_es' => 'Creamos una estrategia de marketing digital integral que incluye gestión de redes sociales, marketing de contenidos y campañas de email. El sitio web fue rediseñado para contar su historia y conectar con mujeres empresarias.',
                'website_url' => 'https://shesellssigns.com',
                'testimonial_quote' => 'Working with Savvy Post Marketing was a game-changer. They understood our mission and helped us connect with the women entrepreneurs we want to serve.',
                'testimonial_quote_es' => 'Trabajar con Savvy Post Marketing fue un cambio de juego. Entendieron nuestra misión y nos ayudaron a conectar con las mujeres emprendedoras a las que queremos servir.',
                'testimonial_author' => 'Michelle Rodriguez',
                'testimonial_role' => 'CEO, She Sells Signs',
                'testimonial_role_es' => 'CEO, She Sells Signs',
                'is_published' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ]
        );

        $sheSellsSigns->services()->sync([
            $websiteService->id,
            $digitalMarketingService->id,
            $seoService->id,
        ]);

        $sheSellsSigns->stats()->delete();
        $sheSellsSigns->stats()->createMany([
            ['label' => 'Social Following', 'label_es' => 'Seguidores Sociales', 'value' => '+500%', 'sort_order' => 0],
            ['label' => 'Email Subscribers', 'label_es' => 'Suscriptores Email', 'value' => '+350%', 'sort_order' => 1],
            ['label' => 'Website Conversions', 'label_es' => 'Conversiones Web', 'value' => '+200%', 'sort_order' => 2],
            ['label' => 'Monthly Revenue', 'label_es' => 'Ingresos Mensuales', 'value' => '+120%', 'sort_order' => 3],
        ]);

        $sheSellsSigns->features()->delete();
        $sheSellsSigns->features()->createMany([
            [
                'number' => '01',
                'title' => 'Social Media Strategy',
                'title_es' => 'Estrategia de Redes Sociales',
                'description' => 'Engaging content strategy focused on Instagram and Pinterest.',
                'description_es' => 'Estrategia de contenido atractiva enfocada en Instagram y Pinterest.',
                'icon' => 'Share',
                'sort_order' => 0,
            ],
            [
                'number' => '02',
                'title' => 'Email Marketing Automation',
                'title_es' => 'Automatización de Email Marketing',
                'description' => 'Nurture sequences that guide prospects through the customer journey.',
                'description_es' => 'Secuencias de nutrición que guían a los prospectos a través del viaje del cliente.',
                'icon' => 'Mail',
                'sort_order' => 1,
            ],
            [
                'number' => '03',
                'title' => 'Community Building',
                'title_es' => 'Construcción de Comunidad',
                'description' => 'Built a supportive community of women entrepreneurs.',
                'description_es' => 'Construimos una comunidad de apoyo de mujeres emprendedoras.',
                'icon' => 'People',
                'sort_order' => 2,
            ],
        ]);

        $sheSellsSigns->results()->delete();
        $sheSellsSigns->results()->createMany([
            ['result' => 'Grew Instagram following from 500 to 15,000 in 6 months', 'result_es' => 'Crecimiento de seguidores en Instagram de 500 a 15,000 en 6 meses', 'sort_order' => 0],
            ['result' => 'Email list grew to 5,000+ subscribers', 'result_es' => 'La lista de email creció a más de 5,000 suscriptores', 'sort_order' => 1],
            ['result' => 'Featured in Women in Business magazine', 'result_es' => 'Destacado en la revista Women in Business', 'sort_order' => 2],
            ['result' => '120% increase in monthly revenue', 'result_es' => 'Aumento del 120% en ingresos mensuales', 'sort_order' => 3],
        ]);

        $this->command->info('Created portfolio: She Sells Signs');

        $this->command->info('Portfolio samples seeded successfully!');
    }
}
