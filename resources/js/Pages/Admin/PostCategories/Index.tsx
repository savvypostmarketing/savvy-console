import { useCallback } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
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
    Edit24Regular,
    Delete24Regular,
    MoreHorizontal24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';

interface PostCategory {
    id: number;
    name: string;
    name_es: string | null;
    slug: string;
    description: string | null;
    icon: string | null;
    color: string | null;
    is_active: boolean;
    sort_order: number;
    posts_count: number;
}

interface Props {
    categories: PostCategory[];
}

const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '24px',
        flexWrap: 'wrap',
        ...shorthands.gap('16px'),
    },
    card: {
        ...shorthands.padding('0'),
    },
    table: {
        width: '100%',
    },
    colorBadge: {
        width: '24px',
        height: '24px',
        ...shorthands.borderRadius('4px'),
        display: 'inline-block',
    },
    emptyState: {
        ...shorthands.padding('48px'),
        textAlign: 'center' as const,
    },
    titleCell: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('4px'),
    },
});

export default function PostCategoriesIndex({ categories }: Props) {
    const styles = useStyles();

    const handleToggleActive = useCallback((categoryId: number) => {
        router.patch(
            `/admin/post-categories/${categoryId}/toggle-active`,
            {},
            { preserveState: true }
        );
    }, []);

    const handleDelete = useCallback((categoryId: number) => {
        if (confirm('Are you sure you want to delete this category?')) {
            router.delete(`/admin/post-categories/${categoryId}`);
        }
    }, []);

    return (
        <AdminLayout title="Post Categories">
            <Head title="Post Categories" />

            <div className={styles.header}>
                <Text size={600} weight="semibold">
                    Post Categories
                </Text>
                <Link href="/admin/post-categories/create">
                    <Button appearance="primary" icon={<Add24Regular />}>
                        New Category
                    </Button>
                </Link>
            </div>

            <Card className={styles.card}>
                {categories.length === 0 ? (
                    <div className={styles.emptyState}>
                        <Text size={400}>No categories found</Text>
                        <br />
                        <Link href="/admin/post-categories/create">
                            <Button appearance="primary" style={{ marginTop: '16px' }}>
                                Create your first category
                            </Button>
                        </Link>
                    </div>
                ) : (
                    <Table className={styles.table}>
                        <TableHeader>
                            <TableRow>
                                <TableHeaderCell style={{ width: '40px' }}>Color</TableHeaderCell>
                                <TableHeaderCell>Name</TableHeaderCell>
                                <TableHeaderCell>Slug</TableHeaderCell>
                                <TableHeaderCell style={{ width: '80px' }}>Posts</TableHeaderCell>
                                <TableHeaderCell style={{ width: '100px' }}>Active</TableHeaderCell>
                                <TableHeaderCell style={{ width: '80px' }}>Actions</TableHeaderCell>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {categories.map((category) => (
                                <TableRow key={category.id}>
                                    <TableCell>
                                        {category.color && (
                                            <span
                                                className={styles.colorBadge}
                                                style={{ backgroundColor: category.color }}
                                            />
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <div className={styles.titleCell}>
                                            <Text weight="semibold">
                                                {category.icon && `${category.icon} `}
                                                {category.name}
                                            </Text>
                                            {category.name_es && (
                                                <Text
                                                    size={200}
                                                    style={{
                                                        color: tokens.colorNeutralForeground3,
                                                    }}
                                                >
                                                    {category.name_es}
                                                </Text>
                                            )}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Text
                                            size={200}
                                            style={{ color: tokens.colorNeutralForeground4 }}
                                        >
                                            /{category.slug}
                                        </Text>
                                    </TableCell>
                                    <TableCell>
                                        <Badge appearance="outline">{category.posts_count}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Switch
                                            checked={category.is_active}
                                            onChange={() => handleToggleActive(category.id)}
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
                                                    <Link
                                                        href={`/admin/post-categories/${category.id}/edit`}
                                                    >
                                                        <MenuItem icon={<Edit24Regular />}>
                                                            Edit
                                                        </MenuItem>
                                                    </Link>
                                                    <MenuItem
                                                        icon={<Delete24Regular />}
                                                        onClick={() => handleDelete(category.id)}
                                                        disabled={category.posts_count > 0}
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
                )}
            </Card>
        </AdminLayout>
    );
}
