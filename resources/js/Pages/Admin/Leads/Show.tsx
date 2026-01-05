import { useCallback, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Badge,
    Button,
    Select,
    Textarea,
    makeStyles,
    shorthands,
    tokens,
    Table,
    TableHeader,
    TableRow,
    TableHeaderCell,
    TableBody,
    TableCell,
    ProgressBar,
    Divider,
    Accordion,
    AccordionItem,
    AccordionHeader,
    AccordionPanel,
} from '@fluentui/react-components';
import {
    ArrowLeft24Regular,
    Delete24Regular,
    Warning24Regular,
    Lightbulb24Regular,
    Timer24Regular,
    Document24Regular,
    Cursor24Regular,
    Desktop24Regular,
    Globe24Regular,
    Location24Regular,
    ArrowTrending24Regular,
    Video24Regular,
    Form24Regular,
    Person24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type {
    Lead,
    LeadStep,
    LeadStatus,
    LeadVisitorSession,
    LeadIntentBreakdown,
} from '@/interfaces';

const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: '24px',
        flexWrap: 'wrap',
        ...shorthands.gap('16px'),
    },
    headerLeft: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
    },
    grid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
        ...shorthands.gap('20px'),
        marginBottom: '20px',
    },
    card: {
        ...shorthands.padding('20px'),
    },
    cardTitle: {
        marginBottom: '16px',
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('8px'),
    },
    field: {
        marginBottom: '16px',
    },
    fieldLabel: {
        color: tokens.colorNeutralForeground3,
        fontSize: '12px',
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
        marginBottom: '4px',
    },
    fieldValue: {
        fontSize: '14px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        flexWrap: 'wrap',
    },
    dangerButton: {
        color: tokens.colorPaletteRedForeground1,
    },
    intentMeter: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
        marginBottom: '16px',
    },
    intentScore: {
        fontSize: '42px',
        fontWeight: '700',
    },
    breakdownItem: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
        marginBottom: '8px',
    },
    breakdownLabel: {
        minWidth: '130px',
        fontSize: '13px',
    },
    flag: {
        display: 'inline-flex',
        alignItems: 'center',
        ...shorthands.gap('4px'),
        ...shorthands.padding('4px', '8px'),
        ...shorthands.borderRadius('4px'),
        backgroundColor: tokens.colorNeutralBackground3,
        fontSize: '12px',
        marginRight: '8px',
        marginBottom: '8px',
    },
    flagActive: {
        backgroundColor: tokens.colorStatusSuccessBackground1,
        color: tokens.colorStatusSuccessForeground1,
    },
    infoRow: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
        ...shorthands.padding('10px', '0'),
        borderBottom: `1px solid ${tokens.colorNeutralStroke2}`,
        '&:last-child': {
            borderBottom: 'none',
        },
    },
    infoIcon: {
        color: tokens.colorNeutralForeground3,
        flexShrink: 0,
    },
    infoLabel: {
        color: tokens.colorNeutralForeground3,
        minWidth: '100px',
        fontSize: '13px',
    },
    timeline: {
        position: 'relative',
    },
    timelineItem: {
        position: 'relative',
        ...shorthands.padding('12px', '0', '12px', '24px'),
        borderLeft: `2px solid ${tokens.colorNeutralStroke2}`,
    },
    timelineDot: {
        position: 'absolute',
        left: '-6px',
        top: '16px',
        width: '10px',
        height: '10px',
        ...shorthands.borderRadius('50%'),
        backgroundColor: tokens.colorBrandBackground,
    },
    sessionCard: {
        ...shorthands.padding('16px'),
        marginBottom: '16px',
        backgroundColor: tokens.colorNeutralBackground3,
        ...shorthands.borderRadius('8px'),
    },
});

// Types
interface LeadShowProps {
    lead: Lead;
    visitorSessions: LeadVisitorSession[];
    intentBreakdown: LeadIntentBreakdown | null;
}

// Constants
const STATUS_OPTIONS: Array<{ value: LeadStatus; label: string }> = [
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'converted', label: 'Converted' },
    { value: 'lost', label: 'Lost' },
];

// Helper functions
function getIntentColor(level: string): string {
    switch (level) {
        case 'hot':
            return '#ef4444';
        case 'qualified':
            return '#f59e0b';
        case 'warm':
            return '#3b82f6';
        default:
            return '#6b7280';
    }
}

function formatDuration(seconds: number): string {
    if (seconds < 60) {
        return `${seconds}s`;
    }
    if (seconds < 3600) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}m ${secs}s`;
    }
    const hours = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    return `${hours}h ${mins}m`;
}

function getEventTypeColor(
    type: string
): 'brand' | 'danger' | 'success' | 'warning' | 'informative' {
    switch (type) {
        case 'cta_click':
            return 'danger';
        case 'form_start':
        case 'form_submit':
            return 'success';
        case 'scroll':
            return 'informative';
        case 'video_play':
        case 'video_complete':
            return 'warning';
        default:
            return 'brand';
    }
}

export default function LeadShow({ lead, visitorSessions, intentBreakdown }: LeadShowProps) {
    const styles = useStyles();
    const [status, setStatus] = useState<LeadStatus>(lead.status);
    const [notes, setNotes] = useState<string>(lead.notes ?? '');
    const [isSaving, setIsSaving] = useState<boolean>(false);

    // Handlers
    const handleBack = useCallback(() => {
        router.get('/admin/leads');
    }, []);

    const handleStatusChange = useCallback(
        (_: unknown, data: { value: string }) => {
            const newStatus = data.value as LeadStatus;
            setStatus(newStatus);
            router.patch(
                `/admin/leads/${lead.id}/status`,
                { status: newStatus },
                { preserveState: true }
            );
        },
        [lead.id]
    );

    const handleNotesChange = useCallback((e: React.ChangeEvent<HTMLTextAreaElement>) => {
        setNotes(e.target.value);
    }, []);

    const handleSaveNotes = useCallback(() => {
        setIsSaving(true);
        router.patch(
            `/admin/leads/${lead.id}/notes`,
            { notes },
            {
                preserveState: true,
                onFinish: () => setIsSaving(false),
            }
        );
    }, [lead.id, notes]);

    const handleToggleSpam = useCallback(() => {
        router.patch(`/admin/leads/${lead.id}/spam`, {}, { preserveState: true });
    }, [lead.id]);

    const handleDelete = useCallback(() => {
        if (confirm('Are you sure you want to delete this lead?')) {
            router.delete(`/admin/leads/${lead.id}`);
        }
    }, [lead.id]);

    // Helper to format step data nicely
    const formatStepData = (data: Record<string, unknown> | null): React.ReactNode => {
        if (!data) {
            return '-';
        }

        // Known field mappings for better labels
        const fieldLabels: Record<string, string> = {
            value: 'Value',
            values: 'Selected',
            name: 'Name',
            email: 'Email',
            company: 'Company',
            website_url: 'Website URL',
            other_value: 'Other',
            industry: 'Industry',
            services: 'Services',
            message: 'Message',
            terms_accepted: 'Terms Accepted',
            privacy_accepted: 'Privacy Accepted',
        };

        const entries = Object.entries(data);
        if (entries.length === 0) {
            return '-';
        }

        // If there's only a "value" key, show it directly
        if (entries.length === 1 && entries[0][0] === 'value') {
            const val = entries[0][1];
            return (
                <Text size={200} style={{ fontWeight: 500 }}>
                    {String(val)}
                </Text>
            );
        }

        // If there's only a boolean like terms_accepted
        if (entries.length === 1 && typeof entries[0][1] === 'boolean') {
            return (
                <Badge color={entries[0][1] ? 'success' : 'warning'} appearance="filled">
                    {entries[0][1] ? 'Yes' : 'No'}
                </Badge>
            );
        }

        // Multiple fields - show as formatted list
        return (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                {entries.map(([key, value]) => {
                    const label = fieldLabels[key] || key;
                    let displayValue: React.ReactNode = String(value);

                    if (typeof value === 'boolean') {
                        displayValue = (
                            <Badge color={value ? 'success' : 'warning'} size="small">
                                {value ? 'Yes' : 'No'}
                            </Badge>
                        );
                    } else if (Array.isArray(value)) {
                        displayValue = value.join(', ');
                    }

                    return (
                        <div
                            key={key}
                            style={{ display: 'flex', gap: '8px', alignItems: 'center' }}
                        >
                            <Text
                                size={200}
                                style={{ color: tokens.colorNeutralForeground3, minWidth: '80px' }}
                            >
                                {label}:
                            </Text>
                            <Text size={200} style={{ fontWeight: 500 }}>
                                {displayValue}
                            </Text>
                        </div>
                    );
                })}
            </div>
        );
    };

    // Step type to friendly name mapping
    const getStepTypeName = (stepType: string | null, stepNumber: number): string => {
        const typeLabels: Record<string, string> = {
            welcome: 'Terms & Conditions',
            terms: 'Terms & Conditions',
            name: 'Name',
            email: 'Email',
            company: 'Company',
            hasWebsite: 'Website',
            website: 'Website',
            industry: 'Industry',
            services: 'Services',
            summary: 'Summary',
            transition: 'Transition',
            discovery: 'Discovery Form',
            'thank-you': 'Thank You',
            'edit-answers': 'Edit Answers',
        };

        if (stepType && typeLabels[stepType]) {
            return typeLabels[stepType];
        }

        // Fallback to guessing by step number (based on actual form order)
        const stepNames = [
            'Terms',
            'Name',
            'Email',
            'Company',
            'Website',
            'Industry',
            'Services',
            'Summary',
            'Transition',
            'Discovery',
        ];
        return stepNames[stepNumber] || `Step ${stepNumber}`;
    };

    // Render helpers
    const renderStepRow = useCallback((step: LeadStep) => {
        // Use step_id for the name (e.g., 'name', 'email', 'services')
        // Fall back to step_type if step_id is not available
        const stepName = getStepTypeName(step.step_id || step.step_type, step.step_number);

        return (
            <TableRow key={step.id}>
                <TableCell>
                    <Badge appearance="outline">{step.step_number}</Badge>
                </TableCell>
                <TableCell>
                    <Text weight="semibold">{stepName}</Text>
                </TableCell>
                <TableCell>{formatStepData(step.data)}</TableCell>
                <TableCell>{step.time_spent ? `${step.time_spent}s` : '-'}</TableCell>
                <TableCell>{step.created_at}</TableCell>
            </TableRow>
        );
    }, []);

    // Computed values
    const pageTitle = `Lead - ${lead.name ?? lead.email ?? 'Unknown'}`;
    const displayName = lead.name ?? 'Unknown';
    const hasSteps = lead.steps && lead.steps.length > 0;
    const spamBadgeColor =
        lead.spam_score > 50 ? 'danger' : lead.spam_score > 25 ? 'warning' : 'success';
    const hasVisitorSessions = visitorSessions && visitorSessions.length > 0;
    const latestSession = hasVisitorSessions ? visitorSessions[0] : null;

    return (
        <AdminLayout title="Lead Details">
            <Head title={pageTitle} />

            <div className={styles.header}>
                <div className={styles.headerLeft}>
                    <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                        Back
                    </Button>
                    <div>
                        <Text size={600} weight="semibold">
                            {displayName}
                            {lead.is_spam && (
                                <Badge color="danger" style={{ marginLeft: '12px' }}>
                                    Spam
                                </Badge>
                            )}
                        </Text>
                        <Text
                            size={300}
                            style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                        >
                            {lead.email}
                        </Text>
                    </div>
                </div>
                <div className={styles.actions}>
                    <Button
                        appearance="subtle"
                        icon={<Warning24Regular />}
                        onClick={handleToggleSpam}
                    >
                        {lead.is_spam ? 'Unmark Spam' : 'Mark as Spam'}
                    </Button>
                    <Button
                        appearance="subtle"
                        icon={<Delete24Regular />}
                        className={styles.dangerButton}
                        onClick={handleDelete}
                    >
                        Delete
                    </Button>
                </div>
            </div>

            {/* Intent Score Banner - Show if visitor session exists */}
            {latestSession && intentBreakdown && (
                <Card className={styles.card} style={{ marginBottom: '20px' }}>
                    <div
                        style={{
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'flex-start',
                            flexWrap: 'wrap',
                            gap: '24px',
                        }}
                    >
                        <div>
                            <Text className={styles.cardTitle} size={500} weight="semibold">
                                <Lightbulb24Regular
                                    style={{ color: getIntentColor(intentBreakdown.level) }}
                                />
                                Visitor Intent Score
                            </Text>
                            <div className={styles.intentMeter}>
                                <Text
                                    className={styles.intentScore}
                                    style={{ color: getIntentColor(intentBreakdown.level) }}
                                >
                                    {intentBreakdown.total}
                                </Text>
                                <div>
                                    <Badge
                                        style={{
                                            backgroundColor: getIntentColor(intentBreakdown.level),
                                            color: 'white',
                                            textTransform: 'uppercase',
                                            padding: '6px 12px',
                                        }}
                                    >
                                        {intentBreakdown.level}
                                    </Badge>
                                    <Text
                                        size={200}
                                        style={{
                                            display: 'block',
                                            marginTop: '4px',
                                            color: tokens.colorNeutralForeground3,
                                        }}
                                    >
                                        out of 100
                                    </Text>
                                </div>
                            </div>
                        </div>
                        <div style={{ minWidth: '300px' }}>
                            <div className={styles.breakdownItem}>
                                <Text className={styles.breakdownLabel}>Page Views</Text>
                                <ProgressBar
                                    value={intentBreakdown.components.page_views / 15}
                                    style={{ width: '80px' }}
                                />
                                <Text size={200}>{intentBreakdown.components.page_views}/15</Text>
                            </div>
                            <div className={styles.breakdownItem}>
                                <Text className={styles.breakdownLabel}>Time on Site</Text>
                                <ProgressBar
                                    value={intentBreakdown.components.time_on_site / 15}
                                    style={{ width: '80px' }}
                                />
                                <Text size={200}>{intentBreakdown.components.time_on_site}/15</Text>
                            </div>
                            <div className={styles.breakdownItem}>
                                <Text className={styles.breakdownLabel}>Engagement</Text>
                                <ProgressBar
                                    value={intentBreakdown.components.engagement / 20}
                                    style={{ width: '80px' }}
                                />
                                <Text size={200}>{intentBreakdown.components.engagement}/20</Text>
                            </div>
                            <div className={styles.breakdownItem}>
                                <Text className={styles.breakdownLabel}>Form Interaction</Text>
                                <ProgressBar
                                    value={intentBreakdown.components.form_interaction / 25}
                                    style={{ width: '80px' }}
                                />
                                <Text size={200}>
                                    {intentBreakdown.components.form_interaction}/25
                                </Text>
                            </div>
                            <div className={styles.breakdownItem}>
                                <Text className={styles.breakdownLabel}>Conversion</Text>
                                <ProgressBar
                                    value={intentBreakdown.components.conversion_signals / 15}
                                    style={{ width: '80px' }}
                                />
                                <Text size={200}>
                                    {intentBreakdown.components.conversion_signals}/15
                                </Text>
                            </div>
                            <div className={styles.breakdownItem}>
                                <Text className={styles.breakdownLabel}>Returning</Text>
                                <ProgressBar
                                    value={intentBreakdown.components.returning_visitor / 10}
                                    style={{ width: '80px' }}
                                />
                                <Text size={200}>
                                    {intentBreakdown.components.returning_visitor}/10
                                </Text>
                            </div>
                        </div>
                    </div>
                </Card>
            )}

            <div className={styles.grid}>
                {/* Contact Information */}
                <Card className={styles.card}>
                    <Text className={styles.cardTitle} size={500} weight="semibold">
                        Contact Information
                    </Text>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Name</Text>
                        <Text className={styles.fieldValue}>{lead.name || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Email</Text>
                        <Text className={styles.fieldValue}>{lead.email || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Company</Text>
                        <Text className={styles.fieldValue}>{lead.company || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Website</Text>
                        <Text className={styles.fieldValue}>
                            {lead.has_website === 'yes' || lead.has_website === 'Yes' ? (
                                lead.website_url ? (
                                    <a
                                        href={lead.website_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        style={{ color: tokens.colorBrandForeground1 }}
                                    >
                                        {lead.website_url}
                                    </a>
                                ) : (
                                    'Yes (no URL provided)'
                                )
                            ) : lead.has_website === 'no' || lead.has_website === 'No' ? (
                                'No'
                            ) : (
                                '-'
                            )}
                        </Text>
                    </div>
                </Card>

                {/* Project Details */}
                <Card className={styles.card}>
                    <Text className={styles.cardTitle} size={500} weight="semibold">
                        Project Details
                    </Text>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Industry</Text>
                        <Text className={styles.fieldValue}>
                            {lead.industry === 'Other' && lead.other_industry
                                ? `Other: ${lead.other_industry}`
                                : lead.industry || '-'}
                        </Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Services</Text>
                        <div
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                gap: '6px',
                                marginTop: '4px',
                            }}
                        >
                            {lead.services && lead.services.length > 0 ? (
                                lead.services.map((service, idx) => (
                                    <Badge key={idx} appearance="outline">
                                        {service}
                                    </Badge>
                                ))
                            ) : (
                                <Text className={styles.fieldValue}>-</Text>
                            )}
                        </div>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Message</Text>
                        <Text className={styles.fieldValue}>{lead.message || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Terms Accepted</Text>
                        <Badge color={lead.terms_accepted ? 'success' : 'warning'}>
                            {lead.terms_accepted ? 'Yes' : 'No'}
                        </Badge>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Locale</Text>
                        <Text className={styles.fieldValue}>
                            {lead.locale === 'es' ? 'Spanish' : 'English'}
                        </Text>
                    </div>
                </Card>

                {/* Status & Notes */}
                <Card className={styles.card}>
                    <Text className={styles.cardTitle} size={500} weight="semibold">
                        Status & Notes
                    </Text>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Status</Text>
                        <Select
                            value={status}
                            onChange={handleStatusChange}
                            style={{ marginTop: '4px' }}
                        >
                            {STATUS_OPTIONS.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </Select>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Notes</Text>
                        <Textarea
                            value={notes}
                            onChange={handleNotesChange}
                            placeholder="Add notes about this lead..."
                            style={{ marginTop: '4px', minHeight: '100px' }}
                        />
                        <Button
                            appearance="primary"
                            size="small"
                            style={{ marginTop: '8px' }}
                            onClick={handleSaveNotes}
                            disabled={isSaving}
                        >
                            {isSaving ? 'Saving...' : 'Save Notes'}
                        </Button>
                    </div>
                </Card>

                {/* Tracking Information */}
                <Card className={styles.card}>
                    <Text className={styles.cardTitle} size={500} weight="semibold">
                        Tracking Information
                    </Text>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>UTM Source</Text>
                        <Text className={styles.fieldValue}>{lead.utm_source || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>UTM Medium</Text>
                        <Text className={styles.fieldValue}>{lead.utm_medium || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>UTM Campaign</Text>
                        <Text className={styles.fieldValue}>{lead.utm_campaign || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Referrer</Text>
                        <Text className={styles.fieldValue}>{lead.referrer || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Landing Page</Text>
                        <Text className={styles.fieldValue}>{lead.landing_page || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>IP Address</Text>
                        <Text className={styles.fieldValue}>{lead.ip_address || '-'}</Text>
                    </div>
                    <div className={styles.field}>
                        <Text className={styles.fieldLabel}>Spam Score</Text>
                        <Badge color={spamBadgeColor}>{lead.spam_score}%</Badge>
                    </div>
                </Card>
            </div>

            {/* Visitor Sessions */}
            {hasVisitorSessions && (
                <>
                    <Divider style={{ margin: '24px 0' }} />
                    <Card className={styles.card} style={{ marginBottom: '20px' }}>
                        <Text className={styles.cardTitle} size={500} weight="semibold">
                            <ArrowTrending24Regular />
                            Visitor Sessions
                            <Badge appearance="outline" style={{ marginLeft: '8px' }}>
                                {visitorSessions.length}
                            </Badge>
                        </Text>

                        <Accordion multiple collapsible defaultOpenItems={['session-0']}>
                            {visitorSessions.map((session, index) => (
                                <AccordionItem key={session.id} value={`session-${index}`}>
                                    <AccordionHeader>
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '12px',
                                                width: '100%',
                                            }}
                                        >
                                            <Badge
                                                style={{
                                                    backgroundColor: getIntentColor(
                                                        session.intent_level
                                                    ),
                                                    color: 'white',
                                                }}
                                            >
                                                {session.intent_score}
                                            </Badge>
                                            <Text>{session.started_at}</Text>
                                            <Text
                                                size={200}
                                                style={{ color: tokens.colorNeutralForeground3 }}
                                            >
                                                {session.page_views_count} pages |{' '}
                                                {formatDuration(session.total_time_seconds)}
                                            </Text>
                                            {session.is_returning && (
                                                <Badge appearance="outline" size="small">
                                                    Returning
                                                </Badge>
                                            )}
                                            <Badge
                                                appearance={
                                                    session.status === 'active'
                                                        ? 'filled'
                                                        : 'outline'
                                                }
                                                color={
                                                    session.status === 'active'
                                                        ? 'success'
                                                        : 'informative'
                                                }
                                                size="small"
                                            >
                                                {session.status}
                                            </Badge>
                                        </div>
                                    </AccordionHeader>
                                    <AccordionPanel>
                                        <div className={styles.sessionCard}>
                                            {/* Session Info */}
                                            <div
                                                style={{
                                                    display: 'grid',
                                                    gridTemplateColumns:
                                                        'repeat(auto-fit, minmax(200px, 1fr))',
                                                    gap: '16px',
                                                    marginBottom: '16px',
                                                }}
                                            >
                                                <div className={styles.infoRow}>
                                                    <Desktop24Regular className={styles.infoIcon} />
                                                    <Text className={styles.infoLabel}>Device</Text>
                                                    <Text size={200}>
                                                        {session.device_type || 'Unknown'} -{' '}
                                                        {session.browser || 'Unknown'}
                                                    </Text>
                                                </div>
                                                <div className={styles.infoRow}>
                                                    <Location24Regular
                                                        className={styles.infoIcon}
                                                    />
                                                    <Text className={styles.infoLabel}>
                                                        Location
                                                    </Text>
                                                    <Text size={200}>
                                                        {session.city || 'Unknown'},{' '}
                                                        {session.country || 'Unknown'}
                                                    </Text>
                                                </div>
                                                <div className={styles.infoRow}>
                                                    <Globe24Regular className={styles.infoIcon} />
                                                    <Text className={styles.infoLabel}>
                                                        Referrer
                                                    </Text>
                                                    <Text size={200}>
                                                        {session.referrer_type || 'Direct'}
                                                    </Text>
                                                </div>
                                                <div className={styles.infoRow}>
                                                    <Timer24Regular className={styles.infoIcon} />
                                                    <Text className={styles.infoLabel}>
                                                        Duration
                                                    </Text>
                                                    <Text size={200}>
                                                        {formatDuration(session.total_time_seconds)}
                                                    </Text>
                                                </div>
                                            </div>

                                            {/* Engagement Flags */}
                                            <Text
                                                weight="semibold"
                                                size={300}
                                                style={{ display: 'block', marginBottom: '8px' }}
                                            >
                                                Engagement Signals
                                            </Text>
                                            <div
                                                style={{
                                                    display: 'flex',
                                                    flexWrap: 'wrap',
                                                    marginBottom: '16px',
                                                }}
                                            >
                                                <span
                                                    className={`${styles.flag} ${session.visited_pricing ? styles.flagActive : ''}`}
                                                >
                                                    <Lightbulb24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Pricing
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.visited_services ? styles.flagActive : ''}`}
                                                >
                                                    <Document24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Services
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.visited_portfolio ? styles.flagActive : ''}`}
                                                >
                                                    <Document24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Portfolio
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.visited_contact ? styles.flagActive : ''}`}
                                                >
                                                    <Person24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Contact
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.started_form ? styles.flagActive : ''}`}
                                                >
                                                    <Form24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Form Started
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.completed_form ? styles.flagActive : ''}`}
                                                >
                                                    <Form24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Form Completed
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.clicked_cta ? styles.flagActive : ''}`}
                                                >
                                                    <Cursor24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    CTA Clicked
                                                </span>
                                                <span
                                                    className={`${styles.flag} ${session.watched_video ? styles.flagActive : ''}`}
                                                >
                                                    <Video24Regular
                                                        style={{ width: '14px', height: '14px' }}
                                                    />
                                                    Video Watched
                                                </span>
                                            </div>

                                            {/* Page Views */}
                                            {session.page_views.length > 0 && (
                                                <>
                                                    <Text
                                                        weight="semibold"
                                                        size={300}
                                                        style={{
                                                            display: 'block',
                                                            marginBottom: '8px',
                                                        }}
                                                    >
                                                        Page Journey
                                                    </Text>
                                                    <Table
                                                        size="small"
                                                        style={{ marginBottom: '16px' }}
                                                    >
                                                        <TableHeader>
                                                            <TableRow>
                                                                <TableHeaderCell>
                                                                    Time
                                                                </TableHeaderCell>
                                                                <TableHeaderCell>
                                                                    Page
                                                                </TableHeaderCell>
                                                                <TableHeaderCell>
                                                                    Type
                                                                </TableHeaderCell>
                                                                <TableHeaderCell>
                                                                    Duration
                                                                </TableHeaderCell>
                                                                <TableHeaderCell>
                                                                    Scroll
                                                                </TableHeaderCell>
                                                            </TableRow>
                                                        </TableHeader>
                                                        <TableBody>
                                                            {session.page_views.map((pv) => (
                                                                <TableRow key={pv.id}>
                                                                    <TableCell>
                                                                        <Text size={200}>
                                                                            {pv.entered_at}
                                                                        </Text>
                                                                    </TableCell>
                                                                    <TableCell>
                                                                        <Text
                                                                            size={200}
                                                                            style={{
                                                                                maxWidth: '200px',
                                                                                overflow: 'hidden',
                                                                                textOverflow:
                                                                                    'ellipsis',
                                                                                whiteSpace:
                                                                                    'nowrap',
                                                                            }}
                                                                        >
                                                                            {pv.path}
                                                                        </Text>
                                                                    </TableCell>
                                                                    <TableCell>
                                                                        <Badge
                                                                            appearance="outline"
                                                                            size="small"
                                                                        >
                                                                            {pv.page_type || 'page'}
                                                                        </Badge>
                                                                    </TableCell>
                                                                    <TableCell>
                                                                        {pv.time_on_page}s
                                                                    </TableCell>
                                                                    <TableCell>
                                                                        <div
                                                                            style={{
                                                                                display: 'flex',
                                                                                alignItems:
                                                                                    'center',
                                                                                gap: '6px',
                                                                            }}
                                                                        >
                                                                            <ProgressBar
                                                                                value={
                                                                                    pv.scroll_depth /
                                                                                    100
                                                                                }
                                                                                style={{
                                                                                    width: '50px',
                                                                                }}
                                                                            />
                                                                            <Text size={200}>
                                                                                {pv.scroll_depth}%
                                                                            </Text>
                                                                        </div>
                                                                    </TableCell>
                                                                </TableRow>
                                                            ))}
                                                        </TableBody>
                                                    </Table>
                                                </>
                                            )}

                                            {/* Events */}
                                            {session.events.length > 0 && (
                                                <>
                                                    <Text
                                                        weight="semibold"
                                                        size={300}
                                                        style={{
                                                            display: 'block',
                                                            marginBottom: '8px',
                                                        }}
                                                    >
                                                        Recent Events
                                                    </Text>
                                                    <div className={styles.timeline}>
                                                        {session.events
                                                            .slice(0, 10)
                                                            .map((event) => (
                                                                <div
                                                                    key={event.id}
                                                                    className={styles.timelineItem}
                                                                >
                                                                    <div
                                                                        className={
                                                                            styles.timelineDot
                                                                        }
                                                                        style={{
                                                                            backgroundColor:
                                                                                event.type ===
                                                                                'cta_click'
                                                                                    ? '#ef4444'
                                                                                    : event.type.includes(
                                                                                            'form'
                                                                                        )
                                                                                      ? '#10b981'
                                                                                      : tokens.colorBrandBackground,
                                                                        }}
                                                                    />
                                                                    <div
                                                                        style={{
                                                                            display: 'flex',
                                                                            justifyContent:
                                                                                'space-between',
                                                                            alignItems: 'center',
                                                                        }}
                                                                    >
                                                                        <div>
                                                                            <Badge
                                                                                appearance="filled"
                                                                                color={getEventTypeColor(
                                                                                    event.type
                                                                                )}
                                                                                size="small"
                                                                            >
                                                                                {event.type}
                                                                            </Badge>
                                                                            {event.label && (
                                                                                <Text
                                                                                    size={200}
                                                                                    style={{
                                                                                        marginLeft:
                                                                                            '8px',
                                                                                        color: tokens.colorNeutralForeground3,
                                                                                    }}
                                                                                >
                                                                                    {event.label}
                                                                                </Text>
                                                                            )}
                                                                        </div>
                                                                        <div
                                                                            style={{
                                                                                display: 'flex',
                                                                                alignItems:
                                                                                    'center',
                                                                                gap: '8px',
                                                                            }}
                                                                        >
                                                                            <Text size={200}>
                                                                                {event.occurred_at}
                                                                            </Text>
                                                                            {event.intent_points >
                                                                                0 && (
                                                                                <Badge
                                                                                    appearance="outline"
                                                                                    color="success"
                                                                                    size="small"
                                                                                >
                                                                                    +
                                                                                    {
                                                                                        event.intent_points
                                                                                    }
                                                                                </Badge>
                                                                            )}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            ))}
                                                    </div>
                                                </>
                                            )}
                                        </div>
                                    </AccordionPanel>
                                </AccordionItem>
                            ))}
                        </Accordion>
                    </Card>
                </>
            )}

            {/* Form Steps */}
            {hasSteps && (
                <Card className={styles.card}>
                    <Text className={styles.cardTitle} size={500} weight="semibold">
                        Form Steps
                    </Text>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHeaderCell>Step</TableHeaderCell>
                                <TableHeaderCell>Name</TableHeaderCell>
                                <TableHeaderCell>Data</TableHeaderCell>
                                <TableHeaderCell>Time Spent</TableHeaderCell>
                                <TableHeaderCell>Completed</TableHeaderCell>
                            </TableRow>
                        </TableHeader>
                        <TableBody>{lead.steps?.map(renderStepRow)}</TableBody>
                    </Table>
                </Card>
            )}
        </AdminLayout>
    );
}
