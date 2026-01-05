import { useCallback, useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Badge,
    Button,
    Select,
    makeStyles,
    shorthands,
    tokens,
    Table,
    TableHeader,
    TableRow,
    TableHeaderCell,
    TableBody,
    TableCell,
    Spinner,
    ProgressBar,
} from '@fluentui/react-components';
import {
    Eye24Regular,
    ArrowSync24Regular,
    People24Regular,
    Document24Regular,
    Timer24Regular,
    ArrowTrending24Regular,
    Lightbulb24Regular,
    ArrowRepeatAll24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type {
    AnalyticsStats,
    VisitorSession,
    TrafficSource,
    IntentDistribution,
    TopPage,
    DailyVisitor,
} from '@/interfaces';

// Types
interface AnalyticsIndexProps {
    stats: AnalyticsStats;
    hotSessions: VisitorSession[];
    activeSessions: VisitorSession[];
    trafficSources: TrafficSource[];
    intentDistribution: IntentDistribution[];
    topPages: TopPage[];
    dailyVisitors: DailyVisitor[];
    period: string;
    periods: Record<string, string>;
}

// Styles
const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '24px',
        flexWrap: 'wrap',
        ...shorthands.gap('16px'),
    },
    statsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
        ...shorthands.gap('16px'),
        marginBottom: '24px',
    },
    statCard: {
        ...shorthands.padding('20px'),
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('8px'),
    },
    statValue: {
        fontSize: '28px',
        fontWeight: '600',
        color: tokens.colorBrandForeground1,
    },
    statLabel: {
        fontSize: '14px',
        color: tokens.colorNeutralForeground3,
    },
    grid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))',
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
    listItem: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        ...shorthands.padding('12px', '0'),
        borderBottom: `1px solid ${tokens.colorNeutralStroke2}`,
        '&:last-child': {
            borderBottom: 'none',
        },
    },
    intentBadge: {
        ...shorthands.padding('4px', '12px'),
        ...shorthands.borderRadius('12px'),
        fontSize: '12px',
        fontWeight: '600',
    },
    clickableRow: {
        cursor: 'pointer',
        '&:hover': {
            backgroundColor: tokens.colorNeutralBackground3,
        },
    },
    noData: {
        color: tokens.colorNeutralForeground3,
        textAlign: 'center',
        display: 'block',
        ...shorthands.padding('20px'),
    },
    chartContainer: {
        height: '200px',
        display: 'flex',
        alignItems: 'flex-end',
        ...shorthands.gap('4px'),
        ...shorthands.padding('16px', '0'),
    },
    chartBar: {
        flexGrow: 1,
        backgroundColor: tokens.colorBrandBackground,
        ...shorthands.borderRadius('4px', '4px', '0', '0'),
        minHeight: '4px',
    },
    liveIndicator: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('8px'),
    },
    liveDot: {
        width: '8px',
        height: '8px',
        ...shorthands.borderRadius('50%'),
        backgroundColor: '#10b981',
        animation: 'pulse 2s infinite',
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

export default function AnalyticsIndex({
    stats,
    hotSessions,
    activeSessions,
    trafficSources,
    intentDistribution,
    topPages,
    dailyVisitors,
    period,
    periods,
}: AnalyticsIndexProps) {
    const styles = useStyles();
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [liveCount, setLiveCount] = useState(activeSessions.length);

    // Auto-refresh live sessions
    useEffect(() => {
        const interval = setInterval(() => {
            void (async () => {
                try {
                    const response = await fetch('/admin/analytics/live');
                    const data = (await response.json()) as { count: number };
                    setLiveCount(data.count);
                } catch {
                    // Silent fail
                }
            })();
        }, 30000); // Every 30 seconds

        return () => clearInterval(interval);
    }, []);

    // Handlers
    const handlePeriodChange = useCallback((_: unknown, data: { value: string }) => {
        router.get(
            '/admin/analytics',
            { period: data.value },
            {
                preserveState: false,
                preserveScroll: true,
            }
        );
    }, []);

    const handleRefresh = useCallback(() => {
        setIsRefreshing(true);
        router.reload({
            onFinish: () => setIsRefreshing(false),
        });
    }, []);

    // Calculate max value for chart
    const maxVisitors = Math.max(...dailyVisitors.map((d) => d.sessions), 1);

    return (
        <AdminLayout title="Visitor Analytics">
            <Head title="Visitor Analytics" />

            {/* Header */}
            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Visitor Analytics
                    </Text>
                    <div className={styles.liveIndicator}>
                        <div className={styles.liveDot} />
                        <Text size={300} style={{ color: tokens.colorNeutralForeground3 }}>
                            {liveCount} visitors online now
                        </Text>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                    <Select value={period} onChange={handlePeriodChange}>
                        {Object.entries(periods).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </Select>
                    <Button
                        appearance="subtle"
                        icon={isRefreshing ? <Spinner size="tiny" /> : <ArrowSync24Regular />}
                        onClick={handleRefresh}
                        disabled={isRefreshing}
                    >
                        Refresh
                    </Button>
                </div>
            </div>

            {/* Stats Grid */}
            <div className={styles.statsGrid}>
                <Card className={styles.statCard}>
                    <People24Regular style={{ color: tokens.colorBrandForeground1 }} />
                    <Text className={styles.statValue}>
                        {stats.unique_visitors.toLocaleString()}
                    </Text>
                    <Text className={styles.statLabel}>Unique Visitors</Text>
                </Card>
                <Card className={styles.statCard}>
                    <Document24Regular style={{ color: tokens.colorBrandForeground1 }} />
                    <Text className={styles.statValue}>
                        {stats.total_page_views.toLocaleString()}
                    </Text>
                    <Text className={styles.statLabel}>Page Views</Text>
                </Card>
                <Card className={styles.statCard}>
                    <Timer24Regular style={{ color: tokens.colorBrandForeground1 }} />
                    <Text className={styles.statValue}>
                        {formatDuration(stats.avg_session_duration)}
                    </Text>
                    <Text className={styles.statLabel}>Avg. Session Duration</Text>
                </Card>
                <Card className={styles.statCard}>
                    <ArrowTrending24Regular style={{ color: tokens.colorBrandForeground1 }} />
                    <Text className={styles.statValue}>{stats.bounce_rate}%</Text>
                    <Text className={styles.statLabel}>Bounce Rate</Text>
                </Card>
                <Card className={styles.statCard}>
                    <Lightbulb24Regular style={{ color: '#ef4444' }} />
                    <Text className={styles.statValue} style={{ color: '#ef4444' }}>
                        {stats.hot_leads}
                    </Text>
                    <Text className={styles.statLabel}>Hot Leads</Text>
                </Card>
                <Card className={styles.statCard}>
                    <ArrowRepeatAll24Regular style={{ color: tokens.colorBrandForeground1 }} />
                    <Text className={styles.statValue}>{stats.returning_rate}%</Text>
                    <Text className={styles.statLabel}>Returning Visitors</Text>
                </Card>
            </div>

            {/* Daily Visitors Chart */}
            <Card className={styles.card} style={{ marginBottom: '24px' }}>
                <div className={styles.cardHeader}>
                    <Text weight="semibold">Daily Visitors</Text>
                </div>
                <div className={styles.chartContainer}>
                    {dailyVisitors.map((day, index) => (
                        <div
                            key={index}
                            className={styles.chartBar}
                            style={{
                                height: `${(day.sessions / maxVisitors) * 100}%`,
                            }}
                            title={`${day.date}: ${day.sessions} sessions`}
                        />
                    ))}
                </div>
            </Card>

            {/* Main Grid */}
            <div className={styles.grid}>
                {/* Hot Sessions */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">High Intent Sessions</Text>
                        <Badge appearance="filled" color="danger">
                            {hotSessions.length}
                        </Badge>
                    </div>
                    {hotSessions.length > 0 ? (
                        <Table size="small">
                            <TableHeader>
                                <TableRow>
                                    <TableHeaderCell>Visitor</TableHeaderCell>
                                    <TableHeaderCell>Intent</TableHeaderCell>
                                    <TableHeaderCell>Pages</TableHeaderCell>
                                    <TableHeaderCell>Actions</TableHeaderCell>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {hotSessions.slice(0, 5).map((session) => (
                                    <TableRow key={session.id} className={styles.clickableRow}>
                                        <TableCell>
                                            <div>
                                                <Text size={200}>{session.visitor_id}</Text>
                                                <Text
                                                    size={100}
                                                    style={{
                                                        display: 'block',
                                                        color: tokens.colorNeutralForeground3,
                                                    }}
                                                >
                                                    {session.country || 'Unknown'}{' '}
                                                    {session.city && `- ${session.city}`}
                                                </Text>
                                            </div>
                                        </TableCell>
                                        <TableCell>
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
                                        </TableCell>
                                        <TableCell>{session.page_views}</TableCell>
                                        <TableCell>
                                            <Link href={`/admin/analytics/sessions/${session.id}`}>
                                                <Button
                                                    appearance="subtle"
                                                    icon={<Eye24Regular />}
                                                    size="small"
                                                />
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <Text className={styles.noData}>No high intent sessions yet</Text>
                    )}
                </Card>

                {/* Active Sessions */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">Active Sessions</Text>
                        <div className={styles.liveIndicator}>
                            <div className={styles.liveDot} />
                            <Badge appearance="outline">{liveCount}</Badge>
                        </div>
                    </div>
                    {activeSessions.length > 0 ? (
                        <Table size="small">
                            <TableHeader>
                                <TableRow>
                                    <TableHeaderCell>Visitor</TableHeaderCell>
                                    <TableHeaderCell>Page</TableHeaderCell>
                                    <TableHeaderCell>Time</TableHeaderCell>
                                    <TableHeaderCell>Actions</TableHeaderCell>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {activeSessions.slice(0, 5).map((session) => (
                                    <TableRow key={session.id} className={styles.clickableRow}>
                                        <TableCell>
                                            <Text size={200}>{session.visitor_id}</Text>
                                        </TableCell>
                                        <TableCell>
                                            <Text
                                                size={200}
                                                style={{
                                                    maxWidth: '150px',
                                                    overflow: 'hidden',
                                                    textOverflow: 'ellipsis',
                                                    whiteSpace: 'nowrap',
                                                }}
                                            >
                                                {session.landing_page || '/'}
                                            </Text>
                                        </TableCell>
                                        <TableCell>
                                            <Text size={200}>{session.last_activity}</Text>
                                        </TableCell>
                                        <TableCell>
                                            <Link href={`/admin/analytics/sessions/${session.id}`}>
                                                <Button
                                                    appearance="subtle"
                                                    icon={<Eye24Regular />}
                                                    size="small"
                                                />
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    ) : (
                        <Text className={styles.noData}>No active sessions</Text>
                    )}
                </Card>

                {/* Traffic Sources */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">Traffic Sources</Text>
                    </div>
                    {trafficSources.length > 0 ? (
                        <div>
                            {trafficSources.map((source, index) => {
                                const total = trafficSources.reduce((sum, s) => sum + s.count, 0);
                                const percentage = total > 0 ? (source.count / total) * 100 : 0;
                                return (
                                    <div key={index} className={styles.listItem}>
                                        <Text>{source.referrer_type || 'Direct'}</Text>
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '12px',
                                            }}
                                        >
                                            <ProgressBar
                                                value={percentage / 100}
                                                style={{ width: '100px' }}
                                            />
                                            <Text size={200}>{source.count}</Text>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        <Text className={styles.noData}>No traffic data</Text>
                    )}
                </Card>

                {/* Intent Distribution */}
                <Card className={styles.card}>
                    <div className={styles.cardHeader}>
                        <Text weight="semibold">Intent Distribution</Text>
                    </div>
                    {intentDistribution.length > 0 ? (
                        <div>
                            {intentDistribution.map((intent, index) => {
                                const total = intentDistribution.reduce(
                                    (sum, i) => sum + i.count,
                                    0
                                );
                                const percentage = total > 0 ? (intent.count / total) * 100 : 0;
                                return (
                                    <div key={index} className={styles.listItem}>
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '8px',
                                            }}
                                        >
                                            <div
                                                style={{
                                                    width: '12px',
                                                    height: '12px',
                                                    borderRadius: '50%',
                                                    backgroundColor: getIntentColor(
                                                        intent.intent_level
                                                    ),
                                                }}
                                            />
                                            <Text style={{ textTransform: 'capitalize' }}>
                                                {intent.intent_level}
                                            </Text>
                                        </div>
                                        <div
                                            style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '12px',
                                            }}
                                        >
                                            <Text size={200}>{percentage.toFixed(1)}%</Text>
                                            <Text
                                                size={200}
                                                style={{ color: tokens.colorNeutralForeground3 }}
                                            >
                                                ({intent.count})
                                            </Text>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        <Text className={styles.noData}>No intent data</Text>
                    )}
                </Card>
            </div>

            {/* Top Pages */}
            <Card className={styles.card}>
                <div className={styles.cardHeader}>
                    <Text weight="semibold">Top Pages</Text>
                </div>
                {topPages.length > 0 ? (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHeaderCell>Page</TableHeaderCell>
                                <TableHeaderCell>Type</TableHeaderCell>
                                <TableHeaderCell>Views</TableHeaderCell>
                                <TableHeaderCell>Avg. Time</TableHeaderCell>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {topPages.map((page, index) => (
                                <TableRow key={index}>
                                    <TableCell>{page.path}</TableCell>
                                    <TableCell>
                                        <Badge appearance="outline">
                                            {page.page_type || 'page'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{page.views}</TableCell>
                                    <TableCell>
                                        {formatDuration(Math.round(page.avg_time || 0))}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                ) : (
                    <Text className={styles.noData}>No page data</Text>
                )}
            </Card>
        </AdminLayout>
    );
}
