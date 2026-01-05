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
    Eye24Regular,
    Star24Regular,
    Star24Filled,
    Search24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { usePermissions } from '@/hooks';
import type {
    PaginatedData,
    PortfolioListItem,
    PortfolioStats,
    PortfolioFilters,
    PortfolioIndustry,
    PortfolioService,
} from '@/interfaces';

// Styles
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
    thumbnail: {
        width: '60px',
        height: '40px',
        objectFit: 'cover',
        ...shorthands.borderRadius('4px'),
        backgroundColor: tokens.colorNeutralBackground3,
    },
    serviceBadge: {
        marginRight: '4px',
        marginBottom: '4px',
    },
});

// Types
interface PortfolioIndexProps {
    portfolios: PaginatedData<PortfolioListItem>;
    stats: PortfolioStats;
    filters: PortfolioFilters;
    industries: PortfolioIndustry[];
    services: PortfolioService[];
}

export default function PortfolioIndex({
    portfolios,
    stats,
    filters,
    industries,
    services,
}: PortfolioIndexProps) {
    const styles = useStyles();
    const { checkPermission } = usePermissions();

    // Local state for filters
    const [search, setSearch] = useState(filters.search || '');

    // Permissions
    const canCreate = checkPermission('create-portfolio');
    const canEdit = checkPermission('edit-portfolio');
    const canDelete = checkPermission('delete-portfolio');

    // Handlers
    const handleDelete = useCallback((id: number) => {
        if (confirm('Are you sure you want to delete this portfolio?')) {
            router.delete(`/admin/portfolio/${id}`);
        }
    }, []);

    const handleTogglePublished = useCallback((id: number) => {
        router.patch(`/admin/portfolio/${id}/toggle-published`);
    }, []);

    const handleToggleFeatured = useCallback((id: number) => {
        router.patch(`/admin/portfolio/${id}/toggle-featured`);
    }, []);

    const handlePageChange = useCallback(
        (page: number) => {
            router.get('/admin/portfolio', { ...filters, page: String(page) });
        },
        [filters]
    );

    const handleFilterChange = useCallback(
        (key: keyof PortfolioFilters, value: string) => {
            router.get('/admin/portfolio', {
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

    // Computed values
    const showPagination = portfolios.last_page > 1;

    const paginationPages = useMemo(() => {
        return Array.from({ length: portfolios.last_page }, (_, i) => i + 1);
    }, [portfolios.last_page]);

    // Render helpers
    const renderPortfolioRow = useCallback(
        (portfolio: PortfolioListItem) => {
            const handleDeleteClick = () => handleDelete(portfolio.id);
            const handleTogglePublishedClick = () => handleTogglePublished(portfolio.id);
            const handleToggleFeaturedClick = () => handleToggleFeatured(portfolio.id);

            return (
                <TableRow key={portfolio.id}>
                    <TableCell>
                        {portfolio.featured_image ? (
                            <img
                                src={`/storage/${portfolio.featured_image}`}
                                alt={portfolio.title}
                                className={styles.thumbnail}
                            />
                        ) : (
                            <div className={styles.thumbnail} />
                        )}
                    </TableCell>
                    <TableCell>
                        <div>
                            <Text weight="semibold">{portfolio.title}</Text>
                            {portfolio.title_es && (
                                <Text
                                    size={200}
                                    style={{
                                        display: 'block',
                                        color: tokens.colorNeutralForeground3,
                                    }}
                                >
                                    ES: {portfolio.title_es}
                                </Text>
                            )}
                        </div>
                    </TableCell>
                    <TableCell>
                        {portfolio.industry && (
                            <Badge appearance="outline">{portfolio.industry.name}</Badge>
                        )}
                    </TableCell>
                    <TableCell>
                        <div>
                            {portfolio.services.map((service) => (
                                <Badge
                                    key={service.id}
                                    className={styles.serviceBadge}
                                    style={{
                                        backgroundColor: service.color,
                                        color: 'white',
                                    }}
                                >
                                    {service.name}
                                </Badge>
                            ))}
                        </div>
                    </TableCell>
                    <TableCell>
                        {canEdit ? (
                            <Switch
                                checked={portfolio.is_published}
                                onChange={handleTogglePublishedClick}
                            />
                        ) : (
                            <Badge color={portfolio.is_published ? 'success' : 'warning'}>
                                {portfolio.is_published ? 'Published' : 'Draft'}
                            </Badge>
                        )}
                    </TableCell>
                    <TableCell>
                        {canEdit ? (
                            <Button
                                appearance="subtle"
                                icon={
                                    portfolio.is_featured ? (
                                        <Star24Filled
                                            style={{ color: tokens.colorPaletteYellowForeground1 }}
                                        />
                                    ) : (
                                        <Star24Regular />
                                    )
                                }
                                onClick={handleToggleFeaturedClick}
                            />
                        ) : portfolio.is_featured ? (
                            <Star24Filled style={{ color: tokens.colorPaletteYellowForeground1 }} />
                        ) : null}
                    </TableCell>
                    <TableCell>
                        <div className={styles.actions}>
                            <Link href={`/admin/portfolio/${portfolio.id}`}>
                                <Button appearance="subtle" icon={<Eye24Regular />} size="small" />
                            </Link>
                            {canEdit && (
                                <Link href={`/admin/portfolio/${portfolio.id}/edit`}>
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
        [canEdit, canDelete, handleDelete, handleTogglePublished, handleToggleFeatured, styles]
    );

    const renderPaginationButton = useCallback(
        (page: number) => {
            const isCurrentPage = page === portfolios.current_page;
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
        [portfolios.current_page, handlePageChange]
    );

    return (
        <AdminLayout title="Portfolio">
            <Head title="Portfolio" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Portfolio
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Manage portfolio projects and case studies
                    </Text>
                </div>
                {canCreate && (
                    <Link href="/admin/portfolio/create">
                        <Button appearance="primary" icon={<Add24Regular />}>
                            Add Portfolio
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
                        {stats.published}
                    </Text>
                    <Text
                        size={200}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Published
                    </Text>
                </Card>
                <Card className={styles.statCard}>
                    <Text
                        size={700}
                        weight="bold"
                        style={{ color: tokens.colorPaletteYellowForeground1 }}
                    >
                        {stats.draft}
                    </Text>
                    <Text
                        size={200}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Draft
                    </Text>
                </Card>
                <Card className={styles.statCard}>
                    <Text
                        size={700}
                        weight="bold"
                        style={{ color: tokens.colorPaletteBlueForeground2 }}
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
                        placeholder="All Industries"
                        value={
                            filters.industry
                                ? industries.find((i) => String(i.id) === filters.industry)?.name ||
                                  ''
                                : ''
                        }
                        onOptionSelect={(_, data) =>
                            handleFilterChange('industry', data.optionValue as string)
                        }
                    >
                        <Option value="">All Industries</Option>
                        {industries.map((industry) => (
                            <Option key={industry.id} value={String(industry.id)}>
                                {industry.name}
                            </Option>
                        ))}
                    </Dropdown>
                </div>
                <div className={styles.filterItem}>
                    <Dropdown
                        placeholder="All Services"
                        value={
                            filters.service
                                ? services.find((s) => String(s.id) === filters.service)?.name || ''
                                : ''
                        }
                        onOptionSelect={(_, data) =>
                            handleFilterChange('service', data.optionValue as string)
                        }
                    >
                        <Option value="">All Services</Option>
                        {services.map((service) => (
                            <Option key={service.id} value={String(service.id)}>
                                {service.name}
                            </Option>
                        ))}
                    </Dropdown>
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
                        <Option value="published">Published</Option>
                        <Option value="draft">Draft</Option>
                    </Dropdown>
                </div>
            </div>

            <Card className={styles.card}>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHeaderCell style={{ width: '80px' }}>Image</TableHeaderCell>
                            <TableHeaderCell>Title</TableHeaderCell>
                            <TableHeaderCell>Industry</TableHeaderCell>
                            <TableHeaderCell>Services</TableHeaderCell>
                            <TableHeaderCell style={{ width: '100px' }}>Published</TableHeaderCell>
                            <TableHeaderCell style={{ width: '80px' }}>Featured</TableHeaderCell>
                            <TableHeaderCell style={{ width: '120px' }}>Actions</TableHeaderCell>
                        </TableRow>
                    </TableHeader>
                    <TableBody>{portfolios.data.map(renderPortfolioRow)}</TableBody>
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
