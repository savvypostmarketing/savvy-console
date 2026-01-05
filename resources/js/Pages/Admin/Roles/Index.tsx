import { useCallback } from 'react';
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
import {
    Add24Regular,
    Edit24Regular,
    Delete24Regular,
    LockClosed24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { usePermissions } from '@/hooks';
import type { Role } from '@/interfaces';

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
    actions: {
        display: 'flex',
        ...shorthands.gap('8px'),
    },
    permissionBadge: {
        marginRight: '4px',
        marginBottom: '4px',
    },
    roleCell: {
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
    },
});

// Types
interface RolesIndexProps {
    roles: Role[];
}

export default function RolesIndex({ roles }: RolesIndexProps) {
    const styles = useStyles();
    const { checkPermission } = usePermissions();

    // Permissions
    const canCreate = checkPermission('create-roles');
    const canEdit = checkPermission('edit-roles');
    const canDelete = checkPermission('delete-roles');

    // Handlers
    const handleDelete = useCallback((roleId: number, isSystem: boolean) => {
        if (isSystem) {
            alert('System roles cannot be deleted.');
            return;
        }
        if (confirm('Are you sure you want to delete this role?')) {
            router.delete(`/admin/roles/${roleId}`);
        }
    }, []);

    // Render helpers
    const renderRoleRow = useCallback(
        (role: Role) => {
            const handleDeleteClick = () => {
                handleDelete(role.id, role.is_system);
            };

            const isSuperAdmin = role.slug === 'super-admin';
            const visiblePermissions = role.permissions.slice(0, 3);
            const remainingCount = role.permissions.length - 3;

            return (
                <TableRow key={role.id}>
                    <TableCell>
                        <div className={styles.roleCell}>
                            {role.is_system && (
                                <LockClosed24Regular
                                    style={{ color: tokens.colorNeutralForeground3 }}
                                />
                            )}
                            <div>
                                <Text weight="semibold">{role.name}</Text>
                                {role.description && (
                                    <Text
                                        size={200}
                                        style={{
                                            display: 'block',
                                            color: tokens.colorNeutralForeground3,
                                        }}
                                    >
                                        {role.description}
                                    </Text>
                                )}
                            </div>
                        </div>
                    </TableCell>
                    <TableCell>
                        <Badge appearance="outline">{role.level}</Badge>
                    </TableCell>
                    <TableCell>{role.users_count}</TableCell>
                    <TableCell style={{ maxWidth: '300px' }}>
                        {isSuperAdmin ? (
                            <Badge color="important">All Permissions</Badge>
                        ) : (
                            <>
                                {visiblePermissions.map((perm) => (
                                    <Badge
                                        key={perm}
                                        appearance="outline"
                                        className={styles.permissionBadge}
                                    >
                                        {perm}
                                    </Badge>
                                ))}
                                {remainingCount > 0 && (
                                    <Badge appearance="outline" className={styles.permissionBadge}>
                                        +{remainingCount} more
                                    </Badge>
                                )}
                            </>
                        )}
                    </TableCell>
                    <TableCell>
                        <div className={styles.actions}>
                            {canEdit && (
                                <Link href={`/admin/roles/${role.id}/edit`}>
                                    <Button
                                        appearance="subtle"
                                        icon={<Edit24Regular />}
                                        size="small"
                                    />
                                </Link>
                            )}
                            {canDelete && !role.is_system && (
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
        [canEdit, canDelete, handleDelete, styles.actions, styles.permissionBadge, styles.roleCell]
    );

    return (
        <AdminLayout title="Roles">
            <Head title="Roles" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Roles
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Manage roles and their permissions
                    </Text>
                </div>
                {canCreate && (
                    <Link href="/admin/roles/create">
                        <Button appearance="primary" icon={<Add24Regular />}>
                            Add Role
                        </Button>
                    </Link>
                )}
            </div>

            <Card className={styles.card}>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHeaderCell>Role</TableHeaderCell>
                            <TableHeaderCell>Level</TableHeaderCell>
                            <TableHeaderCell>Users</TableHeaderCell>
                            <TableHeaderCell>Permissions</TableHeaderCell>
                            <TableHeaderCell>Actions</TableHeaderCell>
                        </TableRow>
                    </TableHeader>
                    <TableBody>{roles.map(renderRoleRow)}</TableBody>
                </Table>
            </Card>
        </AdminLayout>
    );
}
