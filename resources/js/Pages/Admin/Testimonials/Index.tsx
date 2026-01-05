import { useCallback, useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    type DragEndEvent,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
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
    Avatar,
} from '@fluentui/react-components';
import {
    Add24Regular,
    Edit24Regular,
    Delete24Regular,
    Star24Regular,
    Star24Filled,
    Search24Regular,
    Person24Regular,
    ReOrder24Regular,
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
    quoteCell: {
        maxWidth: '300px',
        whiteSpace: 'nowrap',
        ...shorthands.overflow('hidden'),
        textOverflow: 'ellipsis',
    },
    ratingStars: {
        display: 'flex',
        ...shorthands.gap('2px'),
    },
    star: {
        color: tokens.colorPaletteYellowForeground1,
        fontSize: '14px',
    },
    sourceBadge: {
        textTransform: 'capitalize',
    },
    dragHandle: {
        cursor: 'grab',
        color: tokens.colorNeutralForeground3,
        '&:active': {
            cursor: 'grabbing',
        },
    },
    sortableRow: {
        backgroundColor: tokens.colorNeutralBackground1,
    },
    sortableRowDragging: {
        backgroundColor: tokens.colorNeutralBackground1Hover,
        ...shorthands.borderRadius('4px'),
        boxShadow: tokens.shadow8,
        opacity: 0.9,
    },
    reorderInfo: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('8px'),
        ...shorthands.padding('8px', '12px'),
        backgroundColor: tokens.colorNeutralBackground3,
        ...shorthands.borderRadius('4px'),
        marginBottom: '16px',
    },
});

interface Testimonial {
    id: number;
    uuid: string;
    name: string;
    role: string | null;
    company: string | null;
    avatar: string | null;
    quote: string;
    rating: number;
    source: string;
    services: string[] | null;
    is_featured: boolean;
    is_published: boolean;
    sort_order: number;
    created_at: string;
}

interface Stats {
    total: number;
    published: number;
    draft: number;
    featured: number;
}

interface Filters {
    source?: string;
    status?: string;
    featured?: string;
    search?: string;
}

interface PaginatedData {
    data: Testimonial[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface TestimonialsIndexProps {
    testimonials: PaginatedData;
    stats: Stats;
    filters: Filters;
    sources: string[];
}

// Sortable row component
function SortableRow({
    testimonial,
    canEdit,
    canDelete,
    onDelete,
    onTogglePublished,
    onToggleFeatured,
    renderStars,
}: {
    testimonial: Testimonial;
    canEdit: boolean;
    canDelete: boolean;
    onDelete: (id: number) => void;
    onTogglePublished: (id: number) => void;
    onToggleFeatured: (id: number) => void;
    renderStars: (rating: number) => React.ReactNode;
}) {
    const styles = useStyles();
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: testimonial.id,
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const handleDeleteClick = () => onDelete(testimonial.id);
    const handleTogglePublishedClick = () => onTogglePublished(testimonial.id);
    const handleToggleFeaturedClick = () => onToggleFeatured(testimonial.id);

    return (
        <TableRow
            ref={setNodeRef}
            style={style}
            className={isDragging ? styles.sortableRowDragging : styles.sortableRow}
        >
            <TableCell>
                {canEdit && (
                    <div {...attributes} {...listeners} className={styles.dragHandle}>
                        <ReOrder24Regular />
                    </div>
                )}
            </TableCell>
            <TableCell>
                {testimonial.avatar ? (
                    <Avatar image={{ src: testimonial.avatar }} name={testimonial.name} size={36} />
                ) : (
                    <Avatar icon={<Person24Regular />} name={testimonial.name} size={36} />
                )}
            </TableCell>
            <TableCell>
                <div>
                    <Text weight="semibold">{testimonial.name}</Text>
                    {(testimonial.role || testimonial.company) && (
                        <Text
                            size={200}
                            style={{
                                display: 'block',
                                color: tokens.colorNeutralForeground3,
                            }}
                        >
                            {[testimonial.role, testimonial.company].filter(Boolean).join(' at ')}
                        </Text>
                    )}
                </div>
            </TableCell>
            <TableCell className={styles.quoteCell}>
                <Text size={200}>{testimonial.quote}</Text>
            </TableCell>
            <TableCell>{renderStars(testimonial.rating)}</TableCell>
            <TableCell>
                <Badge
                    appearance="outline"
                    className={styles.sourceBadge}
                    color={
                        testimonial.source === 'google'
                            ? 'informative'
                            : testimonial.source === 'website'
                              ? 'success'
                              : 'warning'
                    }
                >
                    {testimonial.source}
                </Badge>
            </TableCell>
            <TableCell>
                {canEdit ? (
                    <Switch
                        checked={testimonial.is_published}
                        onChange={handleTogglePublishedClick}
                    />
                ) : (
                    <Badge color={testimonial.is_published ? 'success' : 'warning'}>
                        {testimonial.is_published ? 'Published' : 'Draft'}
                    </Badge>
                )}
            </TableCell>
            <TableCell>
                {canEdit ? (
                    <Button
                        appearance="subtle"
                        icon={
                            testimonial.is_featured ? (
                                <Star24Filled
                                    style={{ color: tokens.colorPaletteYellowForeground1 }}
                                />
                            ) : (
                                <Star24Regular />
                            )
                        }
                        onClick={handleToggleFeaturedClick}
                    />
                ) : testimonial.is_featured ? (
                    <Star24Filled style={{ color: tokens.colorPaletteYellowForeground1 }} />
                ) : null}
            </TableCell>
            <TableCell>
                <div className={styles.actions}>
                    {canEdit && (
                        <Link href={`/admin/testimonials/${testimonial.id}/edit`}>
                            <Button appearance="subtle" icon={<Edit24Regular />} size="small" />
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
}

export default function TestimonialsIndex({
    testimonials,
    stats,
    filters,
    sources,
}: TestimonialsIndexProps) {
    const styles = useStyles();
    const { checkPermission } = usePermissions();

    const [search, setSearch] = useState(filters.search || '');
    const [items, setItems] = useState(testimonials.data);
    const [hasOrderChanged, setHasOrderChanged] = useState(false);

    const canCreate = checkPermission('create-testimonials');
    const canEdit = checkPermission('edit-testimonials');
    const canDelete = checkPermission('delete-testimonials');

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    const handleDelete = useCallback((id: number) => {
        if (confirm('Are you sure you want to delete this testimonial?')) {
            router.delete(`/admin/testimonials/${id}`);
        }
    }, []);

    const handleTogglePublished = useCallback((id: number) => {
        router.patch(`/admin/testimonials/${id}/toggle-published`);
    }, []);

    const handleToggleFeatured = useCallback((id: number) => {
        router.patch(`/admin/testimonials/${id}/toggle-featured`);
    }, []);

    const handleDragEnd = useCallback((event: DragEndEvent) => {
        const { active, over } = event;

        if (over && active.id !== over.id) {
            setItems((currentItems) => {
                const oldIndex = currentItems.findIndex((item) => item.id === active.id);
                const newIndex = currentItems.findIndex((item) => item.id === over.id);
                const newItems = arrayMove(currentItems, oldIndex, newIndex);
                return newItems;
            });
            setHasOrderChanged(true);
        }
    }, []);

    const handleSaveOrder = useCallback(() => {
        const orderedItems = items.map((item, index) => ({
            id: item.id,
            sort_order: index,
        }));

        router.patch('/admin/testimonials/update-order', {
            testimonials: orderedItems,
        });
        setHasOrderChanged(false);
    }, [items]);

    const handlePageChange = useCallback(
        (page: number) => {
            router.get('/admin/testimonials', { ...filters, page: String(page) });
        },
        [filters]
    );

    const handleFilterChange = useCallback(
        (key: keyof Filters, value: string) => {
            router.get('/admin/testimonials', {
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

    const showPagination = testimonials.last_page > 1;

    const paginationPages = useMemo(() => {
        return Array.from({ length: testimonials.last_page }, (_, i) => i + 1);
    }, [testimonials.last_page]);

    const renderStars = useCallback(
        (rating: number) => {
            return (
                <div className={styles.ratingStars}>
                    {Array.from({ length: 5 }, (_, i) => (
                        <Star24Filled
                            key={i}
                            className={styles.star}
                            style={{ opacity: i < rating ? 1 : 0.3 }}
                        />
                    ))}
                </div>
            );
        },
        [styles]
    );

    const renderPaginationButton = useCallback(
        (page: number) => {
            const isCurrentPage = page === testimonials.current_page;
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
        [testimonials.current_page, handlePageChange]
    );

    return (
        <AdminLayout title="Testimonials">
            <Head title="Testimonials" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Testimonials
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Manage customer testimonials and reviews
                    </Text>
                </div>
                {canCreate && (
                    <Link href="/admin/testimonials/create">
                        <Button appearance="primary" icon={<Add24Regular />}>
                            Add Testimonial
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
                        placeholder="All Sources"
                        value={filters.source || ''}
                        onOptionSelect={(_, data) =>
                            handleFilterChange('source', data.optionValue as string)
                        }
                    >
                        <Option value="">All Sources</Option>
                        {sources.map((source) => (
                            <Option
                                key={source}
                                value={source}
                                style={{ textTransform: 'capitalize' }}
                            >
                                {source.charAt(0).toUpperCase() + source.slice(1)}
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

            {/* Reorder info and save button */}
            {canEdit && (
                <div className={styles.reorderInfo}>
                    <ReOrder24Regular />
                    <Text size={200}>Drag rows to reorder testimonials</Text>
                    {hasOrderChanged && (
                        <Button
                            appearance="primary"
                            size="small"
                            onClick={handleSaveOrder}
                            style={{ marginLeft: 'auto' }}
                        >
                            Save Order
                        </Button>
                    )}
                </div>
            )}

            <Card className={styles.card}>
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragEnd={handleDragEnd}
                >
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHeaderCell style={{ width: '40px' }} />
                                <TableHeaderCell style={{ width: '60px' }}>Avatar</TableHeaderCell>
                                <TableHeaderCell style={{ width: '180px' }}>Name</TableHeaderCell>
                                <TableHeaderCell>Quote</TableHeaderCell>
                                <TableHeaderCell style={{ width: '100px' }}>Rating</TableHeaderCell>
                                <TableHeaderCell style={{ width: '100px' }}>Source</TableHeaderCell>
                                <TableHeaderCell style={{ width: '100px' }}>
                                    Published
                                </TableHeaderCell>
                                <TableHeaderCell style={{ width: '80px' }}>
                                    Featured
                                </TableHeaderCell>
                                <TableHeaderCell style={{ width: '100px' }}>
                                    Actions
                                </TableHeaderCell>
                            </TableRow>
                        </TableHeader>
                        <SortableContext
                            items={items.map((item) => item.id)}
                            strategy={verticalListSortingStrategy}
                        >
                            <TableBody>
                                {items.map((testimonial) => (
                                    <SortableRow
                                        key={testimonial.id}
                                        testimonial={testimonial}
                                        canEdit={canEdit}
                                        canDelete={canDelete}
                                        onDelete={handleDelete}
                                        onTogglePublished={handleTogglePublished}
                                        onToggleFeatured={handleToggleFeatured}
                                        renderStars={renderStars}
                                    />
                                ))}
                            </TableBody>
                        </SortableContext>
                    </Table>
                </DndContext>

                {showPagination && (
                    <div className={styles.pagination}>
                        {paginationPages.map(renderPaginationButton)}
                    </div>
                )}
            </Card>
        </AdminLayout>
    );
}
