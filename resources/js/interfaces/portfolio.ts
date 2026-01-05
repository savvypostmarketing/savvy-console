export interface PortfolioIndustry {
    id: number;
    name: string;
    name_es: string;
    slug?: string;
    icon?: string;
}

export interface PortfolioService {
    id: number;
    name: string;
    name_es: string;
    slug?: string;
    color: string;
    icon?: string;
}

export interface PortfolioStat {
    id?: number;
    label: string;
    label_es?: string;
    value: string;
}

export interface PortfolioGalleryItem {
    id: number;
    image_path: string;
    alt_text?: string;
    alt_text_es?: string;
    caption?: string;
    caption_es?: string;
}

export interface PortfolioFeature {
    id?: number;
    number: string;
    title: string;
    title_es?: string;
    description?: string;
    description_es?: string;
    icon?: string;
}

export interface PortfolioResult {
    id?: number;
    result: string;
    result_es?: string;
}

export interface PortfolioVideoFeature {
    id?: number;
    title: string;
    title_es?: string;
    description?: string;
    description_es?: string;
}

export interface PortfolioListItem {
    id: number;
    title: string;
    title_es?: string;
    slug: string;
    featured_image?: string;
    industry: PortfolioIndustry | null;
    services: PortfolioService[];
    is_published: boolean;
    is_featured: boolean;
    created_at: string;
}

export interface Portfolio {
    id: number;
    title: string;
    title_es?: string;
    slug: string;
    industry_id: number;
    industry: PortfolioIndustry | null;
    services: PortfolioService[];
    service_ids: number[];
    description?: string;
    description_es?: string;
    challenge?: string;
    challenge_es?: string;
    solution?: string;
    solution_es?: string;
    featured_image?: string;
    website_url?: string;
    testimonial_quote?: string;
    testimonial_quote_es?: string;
    testimonial_author?: string;
    testimonial_role?: string;
    testimonial_role_es?: string;
    testimonial_avatar?: string;
    video_url?: string;
    video_thumbnail?: string;
    is_published: boolean;
    is_featured: boolean;
    sort_order: number;
    stats: PortfolioStat[];
    gallery: PortfolioGalleryItem[];
    features: PortfolioFeature[];
    results: PortfolioResult[];
    video_features: PortfolioVideoFeature[];
    created_at: string;
    updated_at: string;
}

export interface PortfolioStats {
    total: number;
    published: number;
    draft: number;
    featured: number;
}

export interface PortfolioFilters {
    industry?: string;
    service?: string;
    status?: string;
    search?: string;
}

export interface PortfolioFormData {
    title: string;
    title_es: string;
    slug: string;
    industry_id: number | string;
    services: number[];
    description: string;
    description_es: string;
    challenge: string;
    challenge_es: string;
    solution: string;
    solution_es: string;
    featured_image: File | null;
    website_url: string;
    testimonial_quote: string;
    testimonial_quote_es: string;
    testimonial_author: string;
    testimonial_role: string;
    testimonial_role_es: string;
    testimonial_avatar: File | null;
    video_url: string;
    video_thumbnail: File | null;
    is_published: boolean;
    is_featured: boolean;
    sort_order: number;
    stats: PortfolioStat[];
    features: PortfolioFeature[];
    results: PortfolioResult[];
    video_features: PortfolioVideoFeature[];
}
