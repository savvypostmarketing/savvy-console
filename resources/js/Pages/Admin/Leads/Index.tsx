import { useCallback, useMemo } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Badge,
    Button,
    Input,
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
} from '@fluentui/react-components';
import {
    Search24Regular,
    ArrowDownload24Regular,
    Eye24Regular,
    Filter24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { useFilters } from '@/hooks';
import { getStatusColor, formatArrayWithLimit, buildSearchParams } from '@/utils';
import type { Lead, LeadFilters, LeadStats, PaginatedData } from '@/interfaces';

// Types
interface LeadsIndexProps {
    leads: PaginatedData<Lead>;
    stats: LeadStats;
    filters: LeadFilters;
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
    filters: {
        display: 'flex',
        ...shorthands.gap('12px'),
        flexWrap: 'wrap',
        marginBottom: '20px',
    },
    card: {
        ...shorthands.padding('20px'),
    },
    statsRow: {
        display: 'flex',
        ...shorthands.gap('16px'),
        marginBottom: '20px',
        flexWrap: 'wrap',
    },
    statBadge: {
        ...shorthands.padding('8px', '16px'),
        ...shorthands.borderRadius('8px'),
        backgroundColor: tokens.colorNeutralBackground3,
    },
    pagination: {
        display: 'flex',
        justifyContent: 'center',
        ...shorthands.gap('8px'),
        marginTop: '20px',
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
});

// Constants
const STATUS_OPTIONS = [
    { value: '', label: 'All Statuses' },
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'converted', label: 'Converted' },
    { value: 'lost', label: 'Lost' },
] as const;

export default function LeadsIndex({ leads, stats, filters: initialFilters }: LeadsIndexProps) {
    const styles = useStyles();
    const { filters, isFiltering, setFilter, applyFilters, handleKeyDown } =
        useFilters<LeadFilters>({
            initialFilters: {
                search: initialFilters.search ?? '',
                status: initialFilters.status ?? '',
            },
            url: '/admin/leads',
        });

    // Handlers
    const handleSearchChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setFilter('search', e.target.value);
        },
        [setFilter]
    );

    const handleStatusChange = useCallback(
        (_: unknown, data: { value: string }) => {
            setFilter('status', data.value);
            // Auto-apply filter on status change
            const params: Record<string, string> = {};
            if (filters.search) {
                params.search = filters.search;
            }
            if (data.value) {
                params.status = data.value;
            }

            router.get('/admin/leads', params, {
                preserveState: false,
                preserveScroll: true,
            });
        },
        [setFilter, filters.search]
    );

    const handleExport = useCallback(() => {
        const params = buildSearchParams({
            status: filters.status,
            search: filters.search,
        });
        window.location.href = `/admin/leads/export?${params}`;
    }, [filters]);

    const handlePageChange = useCallback(
        (page: number) => {
            router.get('/admin/leads', { ...filters, page: String(page) });
        },
        [filters]
    );

    // Computed values
    const hasLeads = leads.data.length > 0;
    const hasSpam = stats.spam > 0;
    const showPagination = leads.last_page > 1;

    const paginationPages = useMemo(() => {
        return Array.from({ length: leads.last_page }, (_, i) => i + 1);
    }, [leads.last_page]);

    // Render helpers
    const renderLeadRow = useCallback(
        (lead: Lead) => {
            const servicesDisplay = formatArrayWithLimit(lead.services, 2);
            const statusColor = getStatusColor(lead.status);

            return (
                <TableRow key={lead.id} className={styles.clickableRow}>
                    <TableCell>
                        {lead.name ?? 'N/A'}
                        {lead.is_spam && (
                            <Badge color="danger" size="small" style={{ marginLeft: '8px' }}>
                                Spam
                            </Badge>
                        )}
                    </TableCell>
                    <TableCell>{lead.email ?? 'N/A'}</TableCell>
                    <TableCell>{lead.company ?? '-'}</TableCell>
                    <TableCell>{servicesDisplay}</TableCell>
                    <TableCell>
                        <Badge appearance="filled" color={statusColor}>
                            {lead.status}
                        </Badge>
                    </TableCell>
                    <TableCell>{lead.utm_source ?? '-'}</TableCell>
                    <TableCell>{lead.created_at}</TableCell>
                    <TableCell>
                        <Link href={`/admin/leads/${lead.id}`}>
                            <Button appearance="subtle" icon={<Eye24Regular />} size="small">
                                View
                            </Button>
                        </Link>
                    </TableCell>
                </TableRow>
            );
        },
        [styles.clickableRow]
    );

    const renderPaginationButton = useCallback(
        (page: number) => {
            const isCurrentPage = page === leads.current_page;

            const handleClick = () => {
                handlePageChange(page);
            };

            return (
                <Button
                    key={page}
                    appearance={isCurrentPage ? 'primary' : 'subtle'}
                    size="small"
                    onClick={handleClick}
                >
                    {page}
                </Button>
            );
        },
        [leads.current_page, handlePageChange]
    );

    return (
        <AdminLayout title="Leads">
            <Head title="Leads" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Leads
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        {stats.total} total leads
                    </Text>
                </div>
                <Button
                    appearance="primary"
                    icon={<ArrowDownload24Regular />}
                    onClick={handleExport}
                >
                    Export CSV
                </Button>
            </div>

            <div className={styles.statsRow}>
                <Badge appearance="outline" className={styles.statBadge}>
                    New: {stats.new}
                </Badge>
                <Badge appearance="outline" className={styles.statBadge}>
                    Contacted: {stats.contacted}
                </Badge>
                <Badge appearance="outline" className={styles.statBadge}>
                    Qualified: {stats.qualified}
                </Badge>
                <Badge appearance="outline" className={styles.statBadge}>
                    Converted: {stats.converted}
                </Badge>
                {hasSpam && (
                    <Badge appearance="outline" color="danger" className={styles.statBadge}>
                        Spam: {stats.spam}
                    </Badge>
                )}
            </div>

            <Card className={styles.card}>
                <div className={styles.filters}>
                    <Input
                        placeholder="Search by name, email, company..."
                        value={filters.search ?? ''}
                        onChange={handleSearchChange}
                        onKeyDown={handleKeyDown}
                        contentBefore={<Search24Regular />}
                        style={{ minWidth: '250px' }}
                    />
                    <Select value={filters.status ?? ''} onChange={handleStatusChange}>
                        {STATUS_OPTIONS.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </Select>
                    <Button
                        appearance="primary"
                        icon={isFiltering ? <Spinner size="tiny" /> : <Filter24Regular />}
                        onClick={applyFilters}
                        disabled={isFiltering}
                    >
                        Filter
                    </Button>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHeaderCell>Name</TableHeaderCell>
                            <TableHeaderCell>Email</TableHeaderCell>
                            <TableHeaderCell>Company</TableHeaderCell>
                            <TableHeaderCell>Services</TableHeaderCell>
                            <TableHeaderCell>Status</TableHeaderCell>
                            <TableHeaderCell>Source</TableHeaderCell>
                            <TableHeaderCell>Date</TableHeaderCell>
                            <TableHeaderCell>Actions</TableHeaderCell>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {hasLeads ? (
                            leads.data.map(renderLeadRow)
                        ) : (
                            <TableRow>
                                <TableCell colSpan={8}>
                                    <Text className={styles.noData}>No leads found</Text>
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>

                {showPagination && (
                    <div className={styles.pagination}>
                        {paginationPages.map(renderPaginationButton)}
                    </div>
                )}
            </Card>
        </AdminLayout>
    );
}
