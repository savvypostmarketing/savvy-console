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

interface PostTag {
    id: number;
    name: string;
    name_es: string | null;
    slug: string;
    is_active: boolean;
    posts_count: number;
}

interface Props {
    tags: PostTag[];
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

export default function PostTagsIndex({ tags }: Props) {
    const styles = useStyles();

    const handleToggleActive = useCallback((tagId: number) => {
        router.patch(`/admin/post-tags/${tagId}/toggle-active`, {}, { preserveState: true });
    }, []);

    const handleDelete = useCallback((tagId: number) => {
        if (
            confirm('Are you sure you want to delete this tag? It will be removed from all posts.')
        ) {
            router.delete(`/admin/post-tags/${tagId}`);
        }
    }, []);

    return (
        <AdminLayout title="Post Tags">
            <Head title="Post Tags" />

            <div className={styles.header}>
                <Text size={600} weight="semibold">
                    Post Tags
                </Text>
                <Link href="/admin/post-tags/create">
                    <Button appearance="primary" icon={<Add24Regular />}>
                        New Tag
                    </Button>
                </Link>
            </div>

            <Card className={styles.card}>
                {tags.length === 0 ? (
                    <div className={styles.emptyState}>
                        <Text size={400}>No tags found</Text>
                        <br />
                        <Link href="/admin/post-tags/create">
                            <Button appearance="primary" style={{ marginTop: '16px' }}>
                                Create your first tag
                            </Button>
                        </Link>
                    </div>
                ) : (
                    <Table className={styles.table}>
                        <TableHeader>
                            <TableRow>
                                <TableHeaderCell>Name</TableHeaderCell>
                                <TableHeaderCell>Slug</TableHeaderCell>
                                <TableHeaderCell style={{ width: '80px' }}>Posts</TableHeaderCell>
                                <TableHeaderCell style={{ width: '100px' }}>Active</TableHeaderCell>
                                <TableHeaderCell style={{ width: '80px' }}>Actions</TableHeaderCell>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {tags.map((tag) => (
                                <TableRow key={tag.id}>
                                    <TableCell>
                                        <div className={styles.titleCell}>
                                            <Text weight="semibold">{tag.name}</Text>
                                            {tag.name_es && (
                                                <Text
                                                    size={200}
                                                    style={{
                                                        color: tokens.colorNeutralForeground3,
                                                    }}
                                                >
                                                    {tag.name_es}
                                                </Text>
                                            )}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Text
                                            size={200}
                                            style={{ color: tokens.colorNeutralForeground4 }}
                                        >
                                            /{tag.slug}
                                        </Text>
                                    </TableCell>
                                    <TableCell>
                                        <Badge appearance="outline">{tag.posts_count}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Switch
                                            checked={tag.is_active}
                                            onChange={() => handleToggleActive(tag.id)}
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
                                                    <Link href={`/admin/post-tags/${tag.id}/edit`}>
                                                        <MenuItem icon={<Edit24Regular />}>
                                                            Edit
                                                        </MenuItem>
                                                    </Link>
                                                    <MenuItem
                                                        icon={<Delete24Regular />}
                                                        onClick={() => handleDelete(tag.id)}
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
