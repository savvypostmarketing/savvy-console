export type LeadStatus = 'new' | 'contacted' | 'qualified' | 'converted' | 'lost';

export type LeadSourceSite = 'savvypostmarketing' | 'savvytechinnovation';

export const LEAD_SITES: Record<LeadSourceSite, string> = {
    savvypostmarketing: 'Savvy Post Marketing',
    savvytechinnovation: 'Savvy Tech Innovation',
};

export interface Lead {
    id: number;
    uuid: string;
    name: string | null;
    email: string | null;
    company: string | null;
    has_website: string | null;
    website_url: string | null;
    industry: string | null;
    other_industry: string | null;
    services: string[] | null;
    message: string | null;
    discovery_answers: Record<string, unknown> | null;
    terms_accepted: boolean;
    status: LeadStatus;
    spam_score: number;
    is_spam: boolean;
    locale: string | null;
    source_site: LeadSourceSite | null;
    site_display: string | null;
    ip_address: string | null;
    country: string | null;
    country_name: string | null;
    city: string | null;
    user_agent: string | null;
    referrer: string | null;
    landing_page: string | null;
    utm_source: string | null;
    utm_medium: string | null;
    utm_campaign: string | null;
    utm_term: string | null;
    utm_content: string | null;
    notes: string | null;
    started_at: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    steps?: LeadStep[];
    attempts?: LeadAttempt[];
}

export interface LeadStep {
    id: number;
    step_id: string | null; // The actual step name ('name', 'email', 'services', etc.)
    step_type: string | null; // The component type ('input', 'choice', 'discovery', etc.)
    step_number: number;
    data: Record<string, unknown>;
    time_spent: number | null;
    created_at: string;
}

export interface LeadAttempt {
    id: number;
    action: string;
    ip_address: string;
    is_suspicious: boolean;
    failure_reason: string | null;
    created_at: string;
}

export interface LeadFilters {
    status?: string;
    source_site?: string;
    from?: string;
    to?: string;
    search?: string;
    [key: string]: string | undefined;
}

export interface LeadStats {
    total: number;
    new: number;
    contacted: number;
    qualified: number;
    converted: number;
    spam: number;
    by_site?: {
        savvypostmarketing: number;
        savvytechinnovation: number;
    };
}

// Visitor Session types for Lead view
export interface LeadVisitorSession {
    id: number;
    uuid: string;
    intent_score: number;
    intent_level: 'cold' | 'warm' | 'hot' | 'qualified';
    status: 'active' | 'idle' | 'ended';
    device_type: string | null;
    browser: string | null;
    os: string | null;
    country: string | null;
    city: string | null;
    referrer_type: string | null;
    landing_page: string | null;
    page_views_count: number;
    events_count: number;
    total_time_seconds: number;
    scroll_depth_max: number;
    visited_pricing: boolean;
    visited_services: boolean;
    visited_portfolio: boolean;
    visited_contact: boolean;
    started_form: boolean;
    completed_form: boolean;
    clicked_cta: boolean;
    watched_video: boolean;
    is_returning: boolean;
    started_at: string | null;
    last_activity_at: string | null;
    page_views: LeadPageView[];
    events: LeadEvent[];
}

export interface LeadPageView {
    id: number;
    path: string;
    page_type: string | null;
    time_on_page: number;
    scroll_depth: number;
    entered_at: string | null;
}

export interface LeadEvent {
    id: number;
    type: string;
    category: string | null;
    label: string | null;
    element_text: string | null;
    intent_points: number;
    occurred_at: string | null;
}

export interface LeadIntentBreakdown {
    total: number;
    level: 'cold' | 'warm' | 'hot' | 'qualified';
    components: {
        page_views: number;
        time_on_site: number;
        engagement: number;
        form_interaction: number;
        conversion_signals: number;
        returning_visitor: number;
    };
}
