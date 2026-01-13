import { Head, Link } from '@inertiajs/react';
import {
    Card,
    Text,
    Badge,
    Button,
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
} from '@fluentui/react-components';
import {
    ArrowLeft24Regular,
    Location24Regular,
    Desktop24Regular,
    Globe24Regular,
    Timer24Regular,
    Document24Regular,
    Cursor24Regular,
    ArrowTrending24Regular,
    Lightbulb24Regular,
    Person24Regular,
    Form24Regular,
    Video24Regular,
    Building24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type {
    VisitorSessionDetailed,
    PageViewRecord,
    EventRecord,
    IntentBreakdown,
} from '@/interfaces';

// Types
interface SessionDetailProps {
    session: VisitorSessionDetailed;
    pageViews: PageViewRecord[];
    events: EventRecord[];
    intentBreakdown: IntentBreakdown;
}

// Styles
const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: '24px',
        flexWrap: 'wrap',
        ...shorthands.gap('16px'),
    },
    backButton: {
        marginBottom: '16px',
    },
    sessionId: {
        fontSize: '12px',
        color: tokens.colorNeutralForeground3,
        fontFamily: 'monospace',
    },
    grid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
        ...shorthands.gap('24px'),
        marginBottom: '24px',
    },
    card: {
        ...shorthands.padding('20px'),
    },
    cardHeader: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '16px',
    },
    infoRow: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
        ...shorthands.padding('12px', '0'),
        borderBottom: `1px solid ${tokens.colorNeutralStroke2}`,
        '&:last-child': {
            borderBottom: 'none',
        },
    },
    infoIcon: {
        color: tokens.colorNeutralForeground3,
    },
    infoLabel: {
        color: tokens.colorNeutralForeground3,
        minWidth: '120px',
    },
    intentMeter: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
        marginBottom: '24px',
    },
    intentScore: {
        fontSize: '48px',
        fontWeight: '700',
    },
    intentBreakdown: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('8px'),
    },
    breakdownItem: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
    },
    breakdownLabel: {
        minWidth: '150px',
        fontSize: '14px',
    },
    timeline: {
        position: 'relative',
        ...shorthands.padding('0', '0', '0', '24px'),
    },
    timelineItem: {
        position: 'relative',
        ...shorthands.padding('16px', '0'),
        borderLeft: `2px solid ${tokens.colorNeutralStroke2}`,
        ...shorthands.padding('16px', '0', '16px', '24px'),
        marginLeft: '-1px',
    },
    timelineDot: {
        position: 'absolute',
        left: '-7px',
        top: '20px',
        width: '12px',
        height: '12px',
        ...shorthands.borderRadius('50%'),
        backgroundColor: tokens.colorBrandBackground,
        ...shorthands.border('2px', 'solid', tokens.colorNeutralBackground1),
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
    noData: {
        color: tokens.colorNeutralForeground3,
        textAlign: 'center',
        display: 'block',
        ...shorthands.padding('20px'),
    },
});

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

export default function SessionDetail({
    session,
    pageViews,
    events,
    intentBreakdown,
}: SessionDetailProps) {
    const styles = useStyles();

    const intentColor = getIntentColor(session.intent_level);

    return (
        <AdminLayout title="Session Details">
            <Head title="Session Details" />

            {/* Back Button */}
            <Link href="/admin/analytics" className={styles.backButton}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />}>
                    Back to Analytics
                </Button>
            </Link>

            {/* Header */}
            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Session Details
                        {session.site_display && (
                            <Badge appearance="outline" style={{ marginLeft: '12px' }}>
                                {session.site_display}
                            </Badge>
                        )}
                    </Text>
                    <Text className={styles.sessionId}>{session.uuid}</Text>
                </div>
                <div style={{ display: 'flex', gap: '8px' }}>
                    <Badge
                        appearance="filled"
                        style={{
                            backgroundColor: intentColor,
                            color: 'white',
                            padding: '8px 16px',
                            fontSize: '14px',
                        }}
                    >
                        {session.intent_level.toUpperCase()} ({session.intent_score})
                    </Badge>
                    <Badge
                        appearance={session.status === 'active' ? 'filled' : 'outline'}
                        color={session.status === 'active' ? 'success' : 'informative'}
                    >
                        {session.status}
                    </Badge>
                </div>
            </div>

            {/* Main Grid */}
            <div className={styles.grid}>
                {/* Intent Score Breakdown */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">Intent Score Breakdown</Text>
                    </div>
                    <div className={styles.intentMeter}>
                        <Text className={styles.intentScore} style={{ color: intentColor }}>
                            {intentBreakdown.total}
                        </Text>
                        <div>
                            <Badge
                                style={{
                                    backgroundColor: intentColor,
                                    color: 'white',
                                    textTransform: 'uppercase',
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
                    <div className={styles.intentBreakdown}>
                        <div className={styles.breakdownItem}>
                            <Text className={styles.breakdownLabel}>Page Views</Text>
                            <ProgressBar
                                value={intentBreakdown.components.page_views / 15}
                                style={{ width: '100px' }}
                            />
                            <Text size={200}>{intentBreakdown.components.page_views}/15</Text>
                        </div>
                        <div className={styles.breakdownItem}>
                            <Text className={styles.breakdownLabel}>Time on Site</Text>
                            <ProgressBar
                                value={intentBreakdown.components.time_on_site / 15}
                                style={{ width: '100px' }}
                            />
                            <Text size={200}>{intentBreakdown.components.time_on_site}/15</Text>
                        </div>
                        <div className={styles.breakdownItem}>
                            <Text className={styles.breakdownLabel}>Engagement</Text>
                            <ProgressBar
                                value={intentBreakdown.components.engagement / 20}
                                style={{ width: '100px' }}
                            />
                            <Text size={200}>{intentBreakdown.components.engagement}/20</Text>
                        </div>
                        <div className={styles.breakdownItem}>
                            <Text className={styles.breakdownLabel}>Form Interaction</Text>
                            <ProgressBar
                                value={intentBreakdown.components.form_interaction / 25}
                                style={{ width: '100px' }}
                            />
                            <Text size={200}>{intentBreakdown.components.form_interaction}/25</Text>
                        </div>
                        <div className={styles.breakdownItem}>
                            <Text className={styles.breakdownLabel}>Conversion Signals</Text>
                            <ProgressBar
                                value={intentBreakdown.components.conversion_signals / 15}
                                style={{ width: '100px' }}
                            />
                            <Text size={200}>
                                {intentBreakdown.components.conversion_signals}/15
                            </Text>
                        </div>
                        <div className={styles.breakdownItem}>
                            <Text className={styles.breakdownLabel}>Returning Visitor</Text>
                            <ProgressBar
                                value={intentBreakdown.components.returning_visitor / 10}
                                style={{ width: '100px' }}
                            />
                            <Text size={200}>
                                {intentBreakdown.components.returning_visitor}/10
                            </Text>
                        </div>
                    </div>
                </Card>

                {/* Session Info */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">Session Information</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Building24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Source Site</Text>
                        <Badge appearance="filled" color="brand">
                            {session.site_display || session.source_site || 'Unknown'}
                        </Badge>
                    </div>
                    <div className={styles.infoRow}>
                        <Person24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Visitor ID</Text>
                        <Text style={{ fontFamily: 'monospace', fontSize: '12px' }}>
                            {session.visitor_id}
                        </Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Location24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Location</Text>
                        <Text>
                            {session.city || 'Unknown'},{' '}
                            {session.country_name || session.country || 'Unknown'}
                        </Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Desktop24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Device</Text>
                        <Text>
                            {session.device || 'Unknown'} - {session.browser || 'Unknown'}
                        </Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Globe24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Referrer</Text>
                        <Text>{session.referrer_type || 'Direct'}</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Timer24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Duration</Text>
                        <Text>{session.duration_formatted}</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Document24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Pages Viewed</Text>
                        <Text>{session.page_views}</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Cursor24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Events</Text>
                        <Text>{session.events}</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <ArrowTrending24Regular className={styles.infoIcon} />
                        <Text className={styles.infoLabel}>Max Scroll</Text>
                        <Text>{session.scroll_depth_max}%</Text>
                    </div>
                </Card>

                {/* UTM Parameters */}
                {(session.utm_source || session.utm_medium || session.utm_campaign) && (
                    <Card className={styles.card}>
                        <div className={styles.cardHeader}>
                            <Text weight="semibold">UTM Parameters</Text>
                        </div>
                        {session.utm_source && (
                            <div className={styles.infoRow}>
                                <Text className={styles.infoLabel}>Source</Text>
                                <Badge appearance="outline">{session.utm_source}</Badge>
                            </div>
                        )}
                        {session.utm_medium && (
                            <div className={styles.infoRow}>
                                <Text className={styles.infoLabel}>Medium</Text>
                                <Badge appearance="outline">{session.utm_medium}</Badge>
                            </div>
                        )}
                        {session.utm_campaign && (
                            <div className={styles.infoRow}>
                                <Text className={styles.infoLabel}>Campaign</Text>
                                <Badge appearance="outline">{session.utm_campaign}</Badge>
                            </div>
                        )}
                    </Card>
                )}

                {/* Engagement Flags */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">Engagement Signals</Text>
                    </div>
                    <div style={{ display: 'flex', flexWrap: 'wrap' }}>
                        <span
                            className={`${styles.flag} ${session.visited_pricing ? styles.flagActive : ''}`}
                        >
                            <Lightbulb24Regular style={{ width: '14px', height: '14px' }} />
                            Pricing
                        </span>
                        <span
                            className={`${styles.flag} ${session.visited_services ? styles.flagActive : ''}`}
                        >
                            <Document24Regular style={{ width: '14px', height: '14px' }} />
                            Services
                        </span>
                        <span
                            className={`${styles.flag} ${session.visited_portfolio ? styles.flagActive : ''}`}
                        >
                            <Document24Regular style={{ width: '14px', height: '14px' }} />
                            Portfolio
                        </span>
                        <span
                            className={`${styles.flag} ${session.visited_contact ? styles.flagActive : ''}`}
                        >
                            <Person24Regular style={{ width: '14px', height: '14px' }} />
                            Contact
                        </span>
                        <span
                            className={`${styles.flag} ${session.started_form ? styles.flagActive : ''}`}
                        >
                            <Form24Regular style={{ width: '14px', height: '14px' }} />
                            Form Started
                        </span>
                        <span
                            className={`${styles.flag} ${session.completed_form ? styles.flagActive : ''}`}
                        >
                            <Form24Regular style={{ width: '14px', height: '14px' }} />
                            Form Completed
                        </span>
                        <span
                            className={`${styles.flag} ${session.clicked_cta ? styles.flagActive : ''}`}
                        >
                            <Cursor24Regular style={{ width: '14px', height: '14px' }} />
                            CTA Clicked
                        </span>
                        <span
                            className={`${styles.flag} ${session.watched_video ? styles.flagActive : ''}`}
                        >
                            <Video24Regular style={{ width: '14px', height: '14px' }} />
                            Video Watched
                        </span>
                        <span
                            className={`${styles.flag} ${session.is_returning ? styles.flagActive : ''}`}
                        >
                            <Person24Regular style={{ width: '14px', height: '14px' }} />
                            Returning
                        </span>
                    </div>
                </Card>
            </div>

            <Divider style={{ margin: '24px 0' }} />

            {/* Page Views */}
            <Card className={styles.card} style={{ marginBottom: '24px' }}>
                <div className={styles.cardHeader}>
                    <Text weight="semibold">Page Views Journey</Text>
                    <Badge appearance="outline">{pageViews.length} pages</Badge>
                </div>
                {pageViews.length > 0 ? (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHeaderCell>Time</TableHeaderCell>
                                <TableHeaderCell>Page</TableHeaderCell>
                                <TableHeaderCell>Type</TableHeaderCell>
                                <TableHeaderCell>Duration</TableHeaderCell>
                                <TableHeaderCell>Scroll</TableHeaderCell>
                                <TableHeaderCell>Status</TableHeaderCell>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {pageViews.map((pv) => (
                                <TableRow key={pv.id}>
                                    <TableCell>
                                        <Text size={200}>{pv.entered_at}</Text>
                                    </TableCell>
                                    <TableCell>
                                        <Text
                                            style={{
                                                maxWidth: '300px',
                                                overflow: 'hidden',
                                                textOverflow: 'ellipsis',
                                                whiteSpace: 'nowrap',
                                            }}
                                        >
                                            {pv.path}
                                        </Text>
                                    </TableCell>
                                    <TableCell>
                                        <Badge appearance="outline" size="small">
                                            {pv.page_type || 'page'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{pv.time_on_page}s</TableCell>
                                    <TableCell>
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '8px',
                                            }}
                                        >
                                            <ProgressBar
                                                value={pv.scroll_depth / 100}
                                                style={{ width: '60px' }}
                                            />
                                            <Text size={200}>{pv.scroll_depth}%</Text>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {pv.bounced ? (
                                            <Badge color="danger" size="small">
                                                Bounced
                                            </Badge>
                                        ) : pv.interacted ? (
                                            <Badge color="success" size="small">
                                                Engaged
                                            </Badge>
                                        ) : (
                                            <Badge appearance="outline" size="small">
                                                Viewed
                                            </Badge>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                ) : (
                    <Text className={styles.noData}>No page views recorded</Text>
                )}
            </Card>

            {/* Events Timeline */}
            <Card className={styles.card}>
                <div className={styles.cardHeader}>
                    <Text weight="semibold">Events Timeline</Text>
                    <Badge appearance="outline">{events.length} events</Badge>
                </div>
                {events.length > 0 ? (
                    <div className={styles.timeline}>
                        {events.slice(0, 50).map((event) => (
                            <div key={event.id} className={styles.timelineItem}>
                                <div
                                    className={styles.timelineDot}
                                    style={{
                                        backgroundColor:
                                            event.type === 'cta_click'
                                                ? '#ef4444'
                                                : event.type.includes('form')
                                                  ? '#10b981'
                                                  : tokens.colorBrandBackground,
                                    }}
                                />
                                <div
                                    style={{
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'flex-start',
                                    }}
                                >
                                    <div>
                                        <Badge
                                            appearance="filled"
                                            color={getEventTypeColor(event.type)}
                                            size="small"
                                            style={{ marginBottom: '4px' }}
                                        >
                                            {event.type}
                                        </Badge>
                                        {event.label && (
                                            <Text
                                                size={200}
                                                style={{
                                                    display: 'block',
                                                    color: tokens.colorNeutralForeground3,
                                                }}
                                            >
                                                {event.label}
                                            </Text>
                                        )}
                                        {event.element_text && (
                                            <Text
                                                size={200}
                                                style={{
                                                    display: 'block',
                                                    color: tokens.colorNeutralForeground3,
                                                    fontStyle: 'italic',
                                                }}
                                            >
                                                "{event.element_text.substring(0, 50)}..."
                                            </Text>
                                        )}
                                    </div>
                                    <div style={{ textAlign: 'right' }}>
                                        <Text size={200}>{event.occurred_at}</Text>
                                        {event.intent_points > 0 && (
                                            <Badge
                                                appearance="outline"
                                                color="success"
                                                size="small"
                                                style={{ marginLeft: '8px' }}
                                            >
                                                +{event.intent_points}
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <Text className={styles.noData}>No events recorded</Text>
                )}
            </Card>
        </AdminLayout>
    );
}
