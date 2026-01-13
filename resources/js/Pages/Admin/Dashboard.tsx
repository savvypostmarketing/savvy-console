import { useCallback, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import {
    Card,
    Text,
    Badge,
    makeStyles,
    shorthands,
    tokens,
    Table,
    TableHeader,
    TableRow,
    TableHeaderCell,
    TableBody,
    TableCell,
} from '@fluentui/react-components';
import {
    Mail24Regular,
    People24Regular,
    CheckmarkCircle24Regular,
    Clock24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { getStatusColor } from '@/utils';
import type { LeadStatus } from '@/interfaces';

// Styles
const useStyles = makeStyles({
    statsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))',
        ...shorthands.gap('20px'),
        marginBottom: '24px',
    },
    statCard: {
        ...shorthands.padding('20px'),
    },
    statHeader: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        marginBottom: '12px',
    },
    statIcon: {
        width: '48px',
        height: '48px',
        ...shorthands.borderRadius('12px'),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
    },
    statValue: {
        fontSize: '32px',
        fontWeight: 700,
        lineHeight: 1,
    },
    statLabel: {
        color: tokens.colorNeutralForeground3,
        marginTop: '4px',
    },
    recentCard: {
        ...shorthands.padding('20px'),
    },
    cardTitle: {
        marginBottom: '16px',
    },
    noData: {
        color: tokens.colorNeutralForeground3,
    },
});

// Types
interface RecentLead {
    id: number;
    uuid: string;
    name: string | null;
    email: string | null;
    status: LeadStatus;
    services: string[];
    source_site: string | null;
    site_display: string | null;
    created_at: string;
}

interface DashboardStats {
    total_leads: number;
    new_leads: number;
    contacted_leads: number;
    converted_leads: number;
    total_users: number;
    recent_leads: RecentLead[];
    leads_by_site?: {
        savvypostmarketing: number;
        savvytechinnovation: number;
    };
}

interface DashboardProps {
    stats: DashboardStats;
}

interface StatCardData {
    label: string;
    value: number;
    icon: typeof Mail24Regular;
    color: string;
}

export default function Dashboard({ stats }: DashboardProps) {
    const styles = useStyles();

    // Computed values
    const statCards = useMemo<StatCardData[]>(
        () => [
            {
                label: 'Total Leads',
                value: stats.total_leads,
                icon: Mail24Regular,
                color: tokens.colorBrandBackground,
            },
            {
                label: 'New Leads',
                value: stats.new_leads,
                icon: Clock24Regular,
                color: tokens.colorPaletteBlueBackground2,
            },
            {
                label: 'Contacted',
                value: stats.contacted_leads,
                icon: CheckmarkCircle24Regular,
                color: tokens.colorPaletteGreenBackground2,
            },
            {
                label: 'Total Users',
                value: stats.total_users,
                icon: People24Regular,
                color: tokens.colorPalettePurpleBackground2,
            },
        ],
        [stats]
    );

    const hasRecentLeads = stats.recent_leads.length > 0;

    // Render helpers
    const renderStatCard = useCallback(
        (stat: StatCardData) => {
            const Icon = stat.icon;

            return (
                <Card key={stat.label} className={styles.statCard}>
                    <div className={styles.statHeader}>
                        <div>
                            <Text className={styles.statValue}>{stat.value}</Text>
                            <Text className={styles.statLabel} size={300}>
                                {stat.label}
                            </Text>
                        </div>
                        <div className={styles.statIcon} style={{ backgroundColor: stat.color }}>
                            <Icon style={{ color: 'white' }} />
                        </div>
                    </div>
                </Card>
            );
        },
        [styles.statCard, styles.statHeader, styles.statIcon, styles.statLabel, styles.statValue]
    );

    const renderLeadRow = useCallback((lead: RecentLead) => {
        const displayName = lead.name ?? 'N/A';
        const displayEmail = lead.email ?? 'N/A';
        const displayServices = lead.services?.slice(0, 2).join(', ') || 'N/A';
        const extraServicesCount = (lead.services?.length ?? 0) - 2;
        const statusColor = getStatusColor(lead.status);

        return (
            <TableRow key={lead.id}>
                <TableCell>{displayName}</TableCell>
                <TableCell>{displayEmail}</TableCell>
                <TableCell>
                    {displayServices}
                    {extraServicesCount > 0 && ` +${extraServicesCount}`}
                </TableCell>
                <TableCell>
                    <Badge appearance="filled" color={statusColor}>
                        {lead.status}
                    </Badge>
                </TableCell>
                <TableCell>
                    <Badge appearance="outline" size="small">
                        {lead.site_display || lead.source_site || '-'}
                    </Badge>
                </TableCell>
                <TableCell>{lead.created_at}</TableCell>
            </TableRow>
        );
    }, []);

    return (
        <AdminLayout title="Dashboard">
            <Head title="Dashboard" />

            <div className={styles.statsGrid}>{statCards.map(renderStatCard)}</div>

            {/* Site Stats */}
            {stats.leads_by_site && (
                <div style={{ display: 'flex', gap: '16px', marginBottom: '20px' }}>
                    <Badge appearance="outline" style={{ padding: '8px 16px' }}>
                        Savvy Post Marketing: {stats.leads_by_site.savvypostmarketing} leads
                    </Badge>
                    <Badge appearance="outline" style={{ padding: '8px 16px' }}>
                        Savvy Tech Innovation: {stats.leads_by_site.savvytechinnovation} leads
                    </Badge>
                </div>
            )}

            <Card className={styles.recentCard}>
                <Text className={styles.cardTitle} size={500} weight="semibold">
                    Recent Leads
                </Text>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHeaderCell>Name</TableHeaderCell>
                            <TableHeaderCell>Email</TableHeaderCell>
                            <TableHeaderCell>Services</TableHeaderCell>
                            <TableHeaderCell>Status</TableHeaderCell>
                            <TableHeaderCell>Site</TableHeaderCell>
                            <TableHeaderCell>Created</TableHeaderCell>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {hasRecentLeads ? (
                            stats.recent_leads.map(renderLeadRow)
                        ) : (
                            <TableRow>
                                <TableCell colSpan={6}>
                                    <Text className={styles.noData}>No leads yet</Text>
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </Card>
        </AdminLayout>
    );
}
