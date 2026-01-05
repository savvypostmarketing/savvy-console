import { useCallback, useMemo } from 'react';
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
} from '@fluentui/react-components';
import { Add24Regular, Edit24Regular, Delete24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { usePermissions } from '@/hooks';
import type { PaginatedData, UserListItem } from '@/interfaces';

// Styles
const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '24px',
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
});

// Types
interface UsersIndexProps {
    users: PaginatedData<UserListItem>;
}

export default function UsersIndex({ users }: UsersIndexProps) {
    const styles = useStyles();
    const { checkPermission, currentUserId } = usePermissions();

    // Permissions
    const canCreate = checkPermission('create-users');
    const canEdit = checkPermission('edit-users');
    const canDelete = checkPermission('delete-users');

    // Handlers
    const handleDelete = useCallback((userId: number) => {
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/admin/users/${userId}`);
        }
    }, []);

    const handlePageChange = useCallback((page: number) => {
        router.get('/admin/users', { page: String(page) });
    }, []);

    // Computed values
    const showPagination = users.last_page > 1;

    const paginationPages = useMemo(() => {
        return Array.from({ length: users.last_page }, (_, i) => i + 1);
    }, [users.last_page]);

    // Render helpers
    const renderUserRow = useCallback(
        (user: UserListItem) => {
            const canDeleteUser = canDelete && !user.is_super_admin && user.id !== currentUserId;

            const handleDeleteClick = () => {
                handleDelete(user.id);
            };

            return (
                <TableRow key={user.id}>
                    <TableCell>
                        {user.name}
                        {user.is_super_admin && (
                            <Badge color="important" size="small" style={{ marginLeft: '8px' }}>
                                Super Admin
                            </Badge>
                        )}
                    </TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>
                        {user.roles.map((role) => (
                            <Badge
                                key={role.slug}
                                appearance="outline"
                                style={{ marginRight: '4px' }}
                            >
                                {role.name}
                            </Badge>
                        ))}
                    </TableCell>
                    <TableCell>{user.created_at}</TableCell>
                    <TableCell>
                        <div className={styles.actions}>
                            {canEdit && (
                                <Link href={`/admin/users/${user.id}/edit`}>
                                    <Button
                                        appearance="subtle"
                                        icon={<Edit24Regular />}
                                        size="small"
                                    />
                                </Link>
                            )}
                            {canDeleteUser && (
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
        [canEdit, canDelete, currentUserId, handleDelete, styles.actions]
    );

    const renderPaginationButton = useCallback(
        (page: number) => {
            const isCurrentPage = page === users.current_page;

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
        [users.current_page, handlePageChange]
    );

    return (
        <AdminLayout title="Users">
            <Head title="Users" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Users
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Manage system users and their roles
                    </Text>
                </div>
                {canCreate && (
                    <Link href="/admin/users/create">
                        <Button appearance="primary" icon={<Add24Regular />}>
                            Add User
                        </Button>
                    </Link>
                )}
            </div>

            <Card className={styles.card}>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHeaderCell>Name</TableHeaderCell>
                            <TableHeaderCell>Email</TableHeaderCell>
                            <TableHeaderCell>Roles</TableHeaderCell>
                            <TableHeaderCell>Created</TableHeaderCell>
                            <TableHeaderCell>Actions</TableHeaderCell>
                        </TableRow>
                    </TableHeader>
                    <TableBody>{users.data.map(renderUserRow)}</TableBody>
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
