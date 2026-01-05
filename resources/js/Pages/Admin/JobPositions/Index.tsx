import { useCallback, useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
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
    Input,
    Dropdown,
    Option,
    Switch,
} from '@fluentui/react-components';
import {
    Add24Regular,
    Edit24Regular,
    Delete24Regular,
    Star24Regular,
    Star24Filled,
    Search24Regular,
    Briefcase24Regular,
    Location24Regular,
    Open24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { usePermissions } from '@/hooks';

const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '24px',
    },
    statsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
        ...shorthands.gap('16px'),
        marginBottom: '24px',
    },
    statCard: {
        ...shorthands.padding('16px'),
        textAlign: 'center',
    },
    filters: {
        display: 'flex',
        ...shorthands.gap('16px'),
        marginBottom: '20px',
        flexWrap: 'wrap',
    },
    filterItem: {
        minWidth: '180px',
    },
    card: {
        ...shorthands.padding('20px'),
    },
    pagination: {
        display: 'flex',
        justifyContent: 'center',
        ...shorthands.gap('8px'),
        marginTop: '20px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('8px'),
    },
    titleCell: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
    },
    iconWrapper: {
        width: '40px',
        height: '40px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: tokens.colorBrandBackground2,
        ...shorthands.borderRadius('8px'),
    },
    tags: {
        display: 'flex',
        ...shorthands.gap('4px'),
        flexWrap: 'wrap',
    },
});

interface JobPosition {
    id: number;
    title: string;
    title_es: string | null;
    department: string | null;
    employment_type: string;
    employment_type_label: string;
    location_type: string;
    location_type_label: string;
    location: string | null;
    linkedin_url: string | null;
    apply_url: string | null;
    is_active: boolean;
    is_featured: boolean;
    sort_order: number;
    created_at: string;
}

interface Stats {
    total: number;
    active: number;
    inactive: number;
    featured: number;
}

interface Filters {
    status?: string;
    employment_type?: string;
    location_type?: string;
    search?: string;
}

interface PaginatedData {
    data: JobPosition[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface JobPositionsIndexProps {
    positions: PaginatedData;
    stats: Stats;
    filters: Filters;
    employmentTypes: Record<string, string>;
    locationTypes: Record<string, string>;
}

export default function JobPositionsIndex({
    positions,
    stats,
    filters,
    employmentTypes,
    locationTypes,
}: JobPositionsIndexProps) {
    const styles = useStyles();
    const { checkPermission } = usePermissions();

    const [search, setSearch] = useState(filters.search || '');

    const canCreate = checkPermission('manage-settings');
    const canEdit = checkPermission('manage-settings');
    const canDelete = checkPermission('manage-settings');

    const handleDelete = useCallback((id: number) => {
        if (confirm('Are you sure you want to delete this position?')) {
            router.delete(`/admin/job-positions/${id}`);
        }
    }, []);

    const handleToggleActive = useCallback((id: number) => {
        router.patch(`/admin/job-positions/${id}/toggle-active`);
    }, []);

    const handleToggleFeatured = useCallback((id: number) => {
        router.patch(`/admin/job-positions/${id}/toggle-featured`);
    }, []);

    const handlePageChange = useCallback(
        (page: number) => {
            router.get('/admin/job-positions', { ...filters, page: String(page) });
        },
        [filters]
    );

    const handleFilterChange = useCallback(
        (key: keyof Filters, value: string) => {
            router.get('/admin/job-positions', {
                ...filters,
                [key]: value || undefined,
                page: undefined,
            });
        },
        [filters]
    );

    const handleSearchSubmit = useCallback(() => {
        handleFilterChange('search', search);
    }, [search, handleFilterChange]);

    const handleSearchKeyDown = useCallback(
        (e: React.KeyboardEvent) => {
            if (e.key === 'Enter') {
                handleSearchSubmit();
            }
        },
        [handleSearchSubmit]
    );

    const showPagination = positions.last_page > 1;

    const paginationPages = useMemo(() => {
        return Array.from({ length: positions.last_page }, (_, i) => i + 1);
    }, [positions.last_page]);

    const renderPositionRow = useCallback(
        (position: JobPosition) => {
            const handleDeleteClick = () => handleDelete(position.id);
            const handleToggleActiveClick = () => handleToggleActive(position.id);
            const handleToggleFeaturedClick = () => handleToggleFeatured(position.id);
            const applyLink = position.linkedin_url || position.apply_url;

            return (
                <TableRow key={position.id}>
                    <TableCell>
                        <div className={styles.titleCell}>
                            <div className={styles.iconWrapper}>
                                <Briefcase24Regular />
                            </div>
                            <div>
                                <Text weight="semibold">{position.title}</Text>
                                {position.department && (
                                    <Text
                                        size={200}
                                        style={{
                                            display: 'block',
                                            color: tokens.colorNeutralForeground3,
                                        }}
                                    >
                                        {position.department}
                                    </Text>
                                )}
                            </div>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div className={styles.tags}>
                            <Badge appearance="outline" color="informative">
                                {position.employment_type_label}
                            </Badge>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div className={styles.tags}>
                            <Badge appearance="outline" color="subtle">
                                <Location24Regular
                                    style={{ fontSize: '12px', marginRight: '4px' }}
                                />
                                {position.location_type_label}
                            </Badge>
                            {position.location && (
                                <Badge appearance="outline" color="subtle">
                                    {position.location}
                                </Badge>
                            )}
                        </div>
                    </TableCell>
                    <TableCell>
                        {applyLink ? (
                            <a href={applyLink} target="_blank" rel="noopener noreferrer">
                                <Button appearance="subtle" size="small" icon={<Open24Regular />}>
                                    {position.linkedin_url ? 'LinkedIn' : 'Link'}
                                </Button>
                            </a>
                        ) : (
                            <Text size={200} style={{ color: tokens.colorNeutralForeground4 }}>
                                No link
                            </Text>
                        )}
                    </TableCell>
                    <TableCell>
                        {canEdit ? (
                            <Switch
                                checked={position.is_active}
                                onChange={handleToggleActiveClick}
                            />
                        ) : (
                            <Badge color={position.is_active ? 'success' : 'warning'}>
                                {position.is_active ? 'Active' : 'Inactive'}
                            </Badge>
                        )}
                    </TableCell>
                    <TableCell>
                        {canEdit ? (
                            <Button
                                appearance="subtle"
                                icon={
                                    position.is_featured ? (
                                        <Star24Filled
                                            style={{ color: tokens.colorPaletteYellowForeground1 }}
                                        />
                                    ) : (
                                        <Star24Regular />
                                    )
                                }
                                onClick={handleToggleFeaturedClick}
                            />
                        ) : position.is_featured ? (
                            <Star24Filled style={{ color: tokens.colorPaletteYellowForeground1 }} />
                        ) : null}
                    </TableCell>
                    <TableCell>
                        <div className={styles.actions}>
                            {canEdit && (
                                <Link href={`/admin/job-positions/${position.id}/edit`}>
                                    <Button
                                        appearance="subtle"
                                        icon={<Edit24Regular />}
                                        size="small"
                                    />
                                </Link>
                            )}
                            {canDelete && (
                                <Button
                                    appearance="subtle"
                                    icon={<Delete24Regular />}
                                    size="small"
                                    onClick={handleDeleteClick}
                                />
                            )}
                        </div>
                    </TableCell>
                </TableRow>
            );
        },
        [canEdit, canDelete, handleDelete, handleToggleActive, handleToggleFeatured, styles]
    );

    const renderPaginationButton = useCallback(
        (page: number) => {
            const isCurrentPage = page === positions.current_page;
            const handleClick = () => handlePageChange(page);

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
        [positions.current_page, handlePageChange]
    );

    return (
        <AdminLayout title="Job Positions">
            <Head title="Job Positions" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Job Positions
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Manage career opportunities displayed on the website
                    </Text>
                </div>
                {canCreate && (
                    <Link href="/admin/job-positions/create">
                        <Button appearance="primary" icon={<Add24Regular />}>
                            Add Position
                        </Button>
                    </Link>
                )}
            </div>

            {/* Stats */}
            <div className={styles.statsGrid}>
                <Card className={styles.statCard}>
                    <Text size={700} weight="bold">
                        {stats.total}
                    </Text>
                    <Text
                        size={200}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Total
                    </Text>
                </Card>
                <Card className={styles.statCard}>
                    <Text
                        size={700}
                        weight="bold"
                        style={{ color: tokens.colorPaletteGreenForeground1 }}
                    >
                        {stats.active}
                    </Text>
                    <Text
                        size={200}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Active
                    </Text>
                </Card>
                <Card className={styles.statCard}>
                    <Text
                        size={700}
                        weight="bold"
                        style={{ color: tokens.colorNeutralForeground4 }}
                    >
                        {stats.inactive}
                    </Text>
                    <Text
                        size={200}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Inactive
                    </Text>
                </Card>
                <Card className={styles.statCard}>
                    <Text
                        size={700}
                        weight="bold"
                        style={{ color: tokens.colorPaletteYellowForeground1 }}
                    >
                        {stats.featured}
                    </Text>
                    <Text
                        size={200}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Featured
                    </Text>
                </Card>
            </div>

            {/* Filters */}
            <div className={styles.filters}>
                <div className={styles.filterItem}>
                    <Input
                        placeholder="Search..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={handleSearchKeyDown}
                        contentAfter={
                            <Button
                                appearance="transparent"
                                icon={<Search24Regular />}
                                size="small"
                                onClick={handleSearchSubmit}
                            />
                        }
                    />
                </div>
                <div className={styles.filterItem}>
                    <Dropdown
                        placeholder="All Status"
                        value={filters.status || ''}
                        onOptionSelect={(_, data) =>
                            handleFilterChange('status', data.optionValue as string)
                        }
                    >
                        <Option value="">All Status</Option>
                        <Option value="active">Active</Option>
                        <Option value="inactive">Inactive</Option>
                    </Dropdown>
                </div>
                <div className={styles.filterItem}>
                    <Dropdown
                        placeholder="Employment Type"
                        value={filters.employment_type || ''}
                        onOptionSelect={(_, data) =>
                            handleFilterChange('employment_type', data.optionValue as string)
                        }
                    >
                        <Option value="">All Types</Option>
                        {Object.entries(employmentTypes).map(([value, label]) => (
                            <Option key={value} value={value}>
                                {label}
                            </Option>
                        ))}
                    </Dropdown>
                </div>
                <div className={styles.filterItem}>
                    <Dropdown
                        placeholder="Location Type"
                        value={filters.location_type || ''}
                        onOptionSelect={(_, data) =>
                            handleFilterChange('location_type', data.optionValue as string)
                        }
                    >
                        <Option value="">All Locations</Option>
                        {Object.entries(locationTypes).map(([value, label]) => (
                            <Option key={value} value={value}>
                                {label}
                            </Option>
                        ))}
                    </Dropdown>
                </div>
            </div>

            <Card className={styles.card}>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHeaderCell style={{ minWidth: '200px' }}>
                                Position
                            </TableHeaderCell>
                            <TableHeaderCell style={{ width: '120px' }}>Type</TableHeaderCell>
                            <TableHeaderCell style={{ width: '180px' }}>Location</TableHeaderCell>
                            <TableHeaderCell style={{ width: '100px' }}>Apply Link</TableHeaderCell>
                            <TableHeaderCell style={{ width: '80px' }}>Active</TableHeaderCell>
                            <TableHeaderCell style={{ width: '80px' }}>Featured</TableHeaderCell>
                            <TableHeaderCell style={{ width: '100px' }}>Actions</TableHeaderCell>
                        </TableRow>
                    </TableHeader>
                    <TableBody>{positions.data.map(renderPositionRow)}</TableBody>
                </Table>

                {positions.data.length === 0 && (
                    <div style={{ textAlign: 'center', padding: '40px' }}>
                        <Briefcase24Regular
                            style={{ fontSize: '48px', color: tokens.colorNeutralForeground4 }}
                        />
                        <Text
                            size={400}
                            style={{
                                display: 'block',
                                marginTop: '16px',
                                color: tokens.colorNeutralForeground3,
                            }}
                        >
                            No job positions found
                        </Text>
                    </div>
                )}

                {showPagination && (
                    <div className={styles.pagination}>
                        {paginationPages.map(renderPaginationButton)}
                    </div>
                )}
            </Card>
        </AdminLayout>
    );
}
