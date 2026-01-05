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
    Accordion,
    AccordionItem,
    AccordionHeader,
    AccordionPanel,
} from '@fluentui/react-components';
import { Add24Regular, Edit24Regular, Delete24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type { Permission, GroupedPermissions } from '@/interfaces';

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
    groupHeader: {
        textTransform: 'capitalize',
    },
});

// Types
interface PermissionsIndexProps {
    groupedPermissions: GroupedPermissions;
}

export default function PermissionsIndex({ groupedPermissions }: PermissionsIndexProps) {
    const styles = useStyles();

    // Computed values
    const defaultOpenItems = useMemo(() => Object.keys(groupedPermissions), [groupedPermissions]);

    // Handlers
    const handleDelete = useCallback((permissionId: number) => {
        if (
            confirm(
                'Are you sure you want to delete this permission? This will remove it from all roles.'
            )
        ) {
            router.delete(`/admin/permissions/${permissionId}`);
        }
    }, []);

    // Render helpers
    const renderPermissionRow = useCallback(
        (permission: Permission) => {
            const handleDeleteClick = () => {
                handleDelete(permission.id);
            };

            return (
                <TableRow key={permission.id}>
                    <TableCell>{permission.name}</TableCell>
                    <TableCell>
                        <Badge appearance="outline">{permission.slug}</Badge>
                    </TableCell>
                    <TableCell>
                        <Text size={200}>{permission.description ?? '-'}</Text>
                    </TableCell>
                    <TableCell>{permission.roles_count}</TableCell>
                    <TableCell>
                        <div className={styles.actions}>
                            <Link href={`/admin/permissions/${permission.id}/edit`}>
                                <Button appearance="subtle" icon={<Edit24Regular />} size="small" />
                            </Link>
                            <Button
                                appearance="subtle"
                                icon={<Delete24Regular />}
                                size="small"
                                onClick={handleDeleteClick}
                            />
                        </div>
                    </TableCell>
                </TableRow>
            );
        },
        [handleDelete, styles.actions]
    );

    const renderPermissionGroup = useCallback(
        ([group, permissions]: [string, Permission[]]) => {
            const groupName = group || 'Ungrouped';

            return (
                <AccordionItem key={group} value={group}>
                    <AccordionHeader>
                        <Text weight="semibold" className={styles.groupHeader}>
                            {groupName} ({permissions.length})
                        </Text>
                    </AccordionHeader>
                    <AccordionPanel>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHeaderCell>Name</TableHeaderCell>
                                    <TableHeaderCell>Slug</TableHeaderCell>
                                    <TableHeaderCell>Description</TableHeaderCell>
                                    <TableHeaderCell>Roles</TableHeaderCell>
                                    <TableHeaderCell>Actions</TableHeaderCell>
                                </TableRow>
                            </TableHeader>
                            <TableBody>{permissions.map(renderPermissionRow)}</TableBody>
                        </Table>
                    </AccordionPanel>
                </AccordionItem>
            );
        },
        [renderPermissionRow, styles.groupHeader]
    );

    return (
        <AdminLayout title="Permissions">
            <Head title="Permissions" />

            <div className={styles.header}>
                <div>
                    <Text size={600} weight="semibold">
                        Permissions
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Manage system permissions (Super Admin only)
                    </Text>
                </div>
                <Link href="/admin/permissions/create">
                    <Button appearance="primary" icon={<Add24Regular />}>
                        Add Permission
                    </Button>
                </Link>
            </div>

            <Card className={styles.card}>
                <Accordion multiple collapsible defaultOpenItems={defaultOpenItems}>
                    {Object.entries(groupedPermissions).map(renderPermissionGroup)}
                </Accordion>
            </Card>
        </AdminLayout>
    );
}
