import { useCallback, useState } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Dropdown,
    Option,
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
    Switch,
    Menu,
    MenuTrigger,
    MenuList,
    MenuItem,
    MenuPopover,
} from '@fluentui/react-components';
import {
    Add24Regular,
    Search24Regular,
    Edit24Regular,
    Delete24Regular,
    Eye24Regular,
    MoreHorizontal24Regular,
    Filter24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type { PostsIndexProps } from '@/interfaces/post';

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
        marginBottom: '20px',
        flexWrap: 'wrap',
    },
    searchInput: {
        minWidth: '250px',
    },
    card: {
        ...shorthands.padding('0'),
    },
    table: {
        width: '100%',
    },
    thumbnail: {
        width: '60px',
        height: '40px',
        objectFit: 'cover' as const,
        ...shorthands.borderRadius('4px'),
        backgroundColor: tokens.colorNeutralBackground3,
    },
    titleCell: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('4px'),
    },
    pagination: {
        display: 'flex',
        justifyContent: 'center',
        ...shorthands.gap('8px'),
        ...shorthands.padding('16px'),
    },
    emptyState: {
        ...shorthands.padding('48px'),
        textAlign: 'center' as const,
    },
    statsCell: {
        display: 'flex',
        ...shorthands.gap('12px'),
    },
});

export default function PostsIndex({ posts, categories, filters }: PostsIndexProps) {
    const styles = useStyles();
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = useCallback(() => {
        router.get('/admin/posts', { ...filters, search }, { preserveState: true });
    }, [filters, search]);

    const handleSearchKeyDown = useCallback(
        (e: React.KeyboardEvent) => {
            if (e.key === 'Enter') {
                handleSearch();
            }
        },
        [handleSearch]
    );

    const handleCategoryFilter = useCallback(
        (_: unknown, data: { optionValue?: string }) => {
            router.get(
                '/admin/posts',
                { ...filters, category: data.optionValue || '' },
                { preserveState: true }
            );
        },
        [filters]
    );

    const handleStatusFilter = useCallback(
        (_: unknown, data: { optionValue?: string }) => {
            router.get(
                '/admin/posts',
                { ...filters, status: data.optionValue || '' },
                { preserveState: true }
            );
        },
        [filters]
    );

    const handleTogglePublished = useCallback((postId: number) => {
        router.post(`/admin/posts/${postId}/toggle-published`, {}, { preserveState: true });
    }, []);

    const handleToggleFeatured = useCallback((postId: number) => {
        router.post(`/admin/posts/${postId}/toggle-featured`, {}, { preserveState: true });
    }, []);

    const handleDelete = useCallback((postId: number) => {
        if (confirm('Are you sure you want to delete this post?')) {
            router.delete(`/admin/posts/${postId}`);
        }
    }, []);

    const clearFilters = useCallback(() => {
        router.get('/admin/posts');
    }, []);

    return (
        <AdminLayout title="Blog Posts">
            <Head title="Blog Posts" />

            <div className={styles.header}>
                <Text size={600} weight="semibold">
                    Blog Posts
                </Text>
                <Link href="/admin/posts/create">
                    <Button appearance="primary" icon={<Add24Regular />}>
                        New Post
                    </Button>
                </Link>
            </div>

            <div className={styles.filters}>
                <Input
                    className={styles.searchInput}
                    placeholder="Search posts..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={handleSearchKeyDown}
                    contentAfter={
                        <Button
                            appearance="transparent"
                            icon={<Search24Regular />}
                            size="small"
                            onClick={handleSearch}
                        />
                    }
                />

                <Dropdown
                    placeholder="Category"
                    value={
                        filters.category
                            ? categories.find((c) => String(c.id) === filters.category)?.name || ''
                            : ''
                    }
                    onOptionSelect={handleCategoryFilter}
                >
                    <Option value="">All Categories</Option>
                    {categories.map((category) => (
                        <Option key={category.id} value={String(category.id)}>
                            {category.name}
                        </Option>
                    ))}
                </Dropdown>

                <Dropdown
                    placeholder="Status"
                    value={filters.status || ''}
                    onOptionSelect={handleStatusFilter}
                >
                    <Option value="">All Status</Option>
                    <Option value="published">Published</Option>
                    <Option value="draft">Draft</Option>
                    <Option value="featured">Featured</Option>
                </Dropdown>

                {(filters.category || filters.status || filters.search) && (
                    <Button appearance="subtle" icon={<Filter24Regular />} onClick={clearFilters}>
                        Clear Filters
                    </Button>
                )}
            </div>

            <Card className={styles.card}>
                {posts.data.length === 0 ? (
                    <div className={styles.emptyState}>
                        <Text size={400}>No posts found</Text>
                        <br />
                        <Link href="/admin/posts/create">
                            <Button appearance="primary" style={{ marginTop: '16px' }}>
                                Create your first post
                            </Button>
                        </Link>
                    </div>
                ) : (
                    <>
                        <Table className={styles.table}>
                            <TableHeader>
                                <TableRow>
                                    <TableHeaderCell style={{ width: '80px' }}>
                                        Image
                                    </TableHeaderCell>
                                    <TableHeaderCell>Title</TableHeaderCell>
                                    <TableHeaderCell>Category</TableHeaderCell>
                                    <TableHeaderCell style={{ width: '100px' }}>
                                        Stats
                                    </TableHeaderCell>
                                    <TableHeaderCell style={{ width: '100px' }}>
                                        Published
                                    </TableHeaderCell>
                                    <TableHeaderCell style={{ width: '100px' }}>
                                        Featured
                                    </TableHeaderCell>
                                    <TableHeaderCell style={{ width: '80px' }}>
                                        Actions
                                    </TableHeaderCell>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {posts.data.map((post) => (
                                    <TableRow key={post.id}>
                                        <TableCell>
                                            {post.featured_image ? (
                                                <img
                                                    src={`/storage/${post.featured_image}`}
                                                    alt={post.title}
                                                    className={styles.thumbnail}
                                                />
                                            ) : (
                                                <div className={styles.thumbnail} />
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className={styles.titleCell}>
                                                <Text weight="semibold">{post.title}</Text>
                                                {post.title_es && (
                                                    <Text
                                                        size={200}
                                                        style={{
                                                            color: tokens.colorNeutralForeground3,
                                                        }}
                                                    >
                                                        {post.title_es}
                                                    </Text>
                                                )}
                                                <Text
                                                    size={200}
                                                    style={{
                                                        color: tokens.colorNeutralForeground4,
                                                    }}
                                                >
                                                    /{post.slug}
                                                </Text>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {post.category && (
                                                <Badge
                                                    appearance="outline"
                                                    style={{
                                                        borderColor:
                                                            post.category.color || undefined,
                                                        color: post.category.color || undefined,
                                                    }}
                                                >
                                                    {post.category.name}
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className={styles.statsCell}>
                                                <Text size={200}>
                                                    <Eye24Regular style={{ fontSize: '14px' }} />{' '}
                                                    {post.views_count}
                                                </Text>
                                                <Text size={200}>♥ {post.likes_count}</Text>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Switch
                                                checked={post.is_published}
                                                onChange={() => handleTogglePublished(post.id)}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Switch
                                                checked={post.is_featured}
                                                onChange={() => handleToggleFeatured(post.id)}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Menu>
                                                <MenuTrigger disableButtonEnhancement>
                                                    <Button
                                                        appearance="subtle"
                                                        icon={<MoreHorizontal24Regular />}
                                                    />
                                                </MenuTrigger>
                                                <MenuPopover>
                                                    <MenuList>
                                                        <Link href={`/admin/posts/${post.id}/edit`}>
                                                            <MenuItem icon={<Edit24Regular />}>
                                                                Edit
                                                            </MenuItem>
                                                        </Link>
                                                        <MenuItem
                                                            icon={<Delete24Regular />}
                                                            onClick={() => handleDelete(post.id)}
                                                        >
                                                            Delete
                                                        </MenuItem>
                                                    </MenuList>
                                                </MenuPopover>
                                            </Menu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {posts.last_page > 1 && (
                            <div className={styles.pagination}>
                                {posts.links.map((link, index) => (
                                    <Button
                                        key={index}
                                        appearance={link.active ? 'primary' : 'subtle'}
                                        disabled={!link.url}
                                        onClick={() => link.url && router.get(link.url)}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        )}
                    </>
                )}
            </Card>
        </AdminLayout>
    );
}
