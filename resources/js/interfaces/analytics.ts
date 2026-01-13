// Visitor Analytics Types
import type { LeadSourceSite } from './lead';

export interface AnalyticsStats {
    total_sessions: number;
    unique_visitors: number;
    total_page_views: number;
    avg_session_duration: number;
    avg_pages_per_session: number;
    bounce_rate: number;
    conversion_rate: number;
    hot_leads: number;
    qualified_leads: number;
    avg_intent_score: number;
    returning_rate: number;
}

export interface VisitorSession {
    id: number;
    uuid: string;
    visitor_id: string;
    intent_score: number;
    intent_level: IntentLevel;
    intent_color: string;
    status: 'active' | 'idle' | 'ended';
    page_views: number;
    events: number;
    duration: number;
    duration_formatted: string;
    device: string | null;
    browser: string | null;
    country: string | null;
    country_name: string | null;
    city: string | null;
    referrer_type: string | null;
    landing_page: string | null;
    is_returning: boolean;
    has_lead: boolean;
    started_form: boolean;
    completed_form: boolean;
    started_at: string | null;
    last_activity: string | null;
    source_site: LeadSourceSite | null;
    site_display: string | null;
}

export interface VisitorSessionDetailed extends VisitorSession {
    ip_address: string | null;
    user_agent: string | null;
    utm_source: string | null;
    utm_medium: string | null;
    utm_campaign: string | null;
    referrer_url: string | null;
    scroll_depth_max: number;
    visited_pricing: boolean;
    visited_services: boolean;
    visited_portfolio: boolean;
    visited_contact: boolean;
    clicked_cta: boolean;
    watched_video: boolean;
    intent_signals: Record<string, unknown> | null;
    lead_id: string | null;
}

export interface PageViewRecord {
    id: number;
    path: string;
    page_type: string | null;
    page_title: string | null;
    time_on_page: number;
    scroll_depth: number;
    entered_at: string;
    exited_at: string | null;
    interacted: boolean;
    bounced: boolean;
}

export interface EventRecord {
    id: number;
    type: string;
    category: string | null;
    action: string | null;
    label: string | null;
    element_text: string | null;
    intent_points: number;
    occurred_at: string;
}

export interface TrafficSource {
    referrer_type: string | null;
    count: number;
}

export interface IntentDistribution {
    intent_level: IntentLevel;
    count: number;
}

export interface TopPage {
    path: string;
    page_type: string | null;
    views: number;
    avg_time: number;
}

export interface DailyVisitor {
    date: string;
    sessions: number;
    visitors: number;
    avg_intent: number;
}

export interface IntentBreakdown {
    total: number;
    level: IntentLevel;
    components: {
        page_views: number;
        time_on_site: number;
        engagement: number;
        form_interaction: number;
        conversion_signals: number;
        returning_visitor: number;
    };
}

export type IntentLevel = 'cold' | 'warm' | 'hot' | 'qualified';

export interface AnalyticsPeriod {
    value: string;
    label: string;
}

export interface AnalyticsFilters {
    period: string;
    source_site?: string;
}

export interface AnalyticsStatsBySite {
    savvypostmarketing: number;
    savvytechinnovation: number;
}
