import { useCallback, FormEvent, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Textarea,
    Field,
    Checkbox,
    Badge,
    makeStyles,
    shorthands,
    tokens,
    Divider,
    MessageBar,
    MessageBarBody,
} from '@fluentui/react-components';
import { ArrowLeft24Regular, LockClosed24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type { Permission, GroupedPermissions } from '@/interfaces';

// Styles
const useStyles = makeStyles({
    header: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
        marginBottom: '24px',
    },
    card: {
        ...shorthands.padding('24px'),
        maxWidth: '800px',
    },
    form: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('20px'),
    },
    permissionsSection: {
        ...shorthands.padding('16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
    },
    permissionGroup: {
        marginTop: '16px',
    },
    permissionGroupTitle: {
        textTransform: 'capitalize',
        marginBottom: '12px',
    },
    permissionGroupHeader: {
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        marginTop: '12px',
    },
    permissionsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
        ...shorthands.gap('12px'),
        marginLeft: '32px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
});

// Types
interface EditRoleFormData {
    name: string;
    description: string;
    level: number;
    permissions: string[];
}

interface RoleData {
    id: number;
    name: string;
    slug: string;
    description: string;
    level: number;
    is_system: boolean;
    permissions: string[];
}

interface EditRoleProps {
    role: RoleData;
    permissions: GroupedPermissions;
}

export default function EditRole({ role, permissions }: EditRoleProps) {
    const styles = useStyles();
    const { data, setData, put, processing, errors } = useForm<EditRoleFormData>({
        name: role.name,
        description: role.description ?? '',
        level: role.level,
        permissions: role.permissions,
    });

    // Computed values
    const isSuperAdmin = useMemo(() => role.slug === 'super-admin', [role.slug]);
    const pageTitle = `Edit Role - ${role.name}`;

    // Handlers
    const handleBack = useCallback(() => {
        router.get('/admin/roles');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent) => {
            e.preventDefault();
            put(`/admin/roles/${role.id}`);
        },
        [put, role.id]
    );

    const handleNameChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('name', e.target.value);
        },
        [setData]
    );

    const handleDescriptionChange = useCallback(
        (e: React.ChangeEvent<HTMLTextAreaElement>) => {
            setData('description', e.target.value);
        },
        [setData]
    );

    const handleLevelChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('level', parseInt(e.target.value) || 0);
        },
        [setData]
    );

    const togglePermission = useCallback(
        (slug: string) => {
            if (data.permissions.includes(slug)) {
                setData(
                    'permissions',
                    data.permissions.filter((p) => p !== slug)
                );
            } else {
                setData('permissions', [...data.permissions, slug]);
            }
        },
        [data.permissions, setData]
    );

    const toggleGroup = useCallback(
        (group: string) => {
            const groupPerms = permissions[group]?.map((p) => p.slug) ?? [];
            const allSelected = groupPerms.every((p) => data.permissions.includes(p));

            if (allSelected) {
                setData(
                    'permissions',
                    data.permissions.filter((p) => !groupPerms.includes(p))
                );
            } else {
                setData('permissions', [...new Set([...data.permissions, ...groupPerms])]);
            }
        },
        [data.permissions, permissions, setData]
    );

    // Render helpers
    const renderPermissionCheckbox = useCallback(
        (perm: Permission) => {
            const handleChange = () => {
                togglePermission(perm.slug);
            };

            return (
                <Checkbox
                    key={perm.slug}
                    checked={data.permissions.includes(perm.slug)}
                    onChange={handleChange}
                    label={
                        <div>
                            <Text size={300}>{perm.name}</Text>
                            {perm.description && (
                                <Text
                                    size={200}
                                    style={{
                                        display: 'block',
                                        color: tokens.colorNeutralForeground3,
                                    }}
                                >
                                    {perm.description}
                                </Text>
                            )}
                        </div>
                    }
                />
            );
        },
        [data.permissions, togglePermission]
    );

    const renderPermissionGroup = useCallback(
        ([group, groupPerms]: [string, Permission[]]) => {
            const allSelected = groupPerms.every((p) => data.permissions.includes(p.slug));
            const someSelected = groupPerms.some((p) => data.permissions.includes(p.slug));

            const handleGroupChange = () => {
                toggleGroup(group);
            };

            return (
                <div key={group} className={styles.permissionGroup}>
                    <Divider />
                    <div className={styles.permissionGroupHeader}>
                        <Checkbox
                            checked={allSelected ? true : someSelected ? 'mixed' : false}
                            onChange={handleGroupChange}
                        />
                        <Text weight="semibold" className={styles.permissionGroupTitle}>
                            {group}
                        </Text>
                    </div>
                    <div className={styles.permissionsGrid}>
                        {groupPerms.map(renderPermissionCheckbox)}
                    </div>
                </div>
            );
        },
        [
            data.permissions,
            toggleGroup,
            renderPermissionCheckbox,
            styles.permissionGroup,
            styles.permissionGroupHeader,
            styles.permissionGroupTitle,
            styles.permissionsGrid,
        ]
    );

    return (
        <AdminLayout title="Edit Role">
            <Head title={pageTitle} />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Edit Role
                    {role.is_system && (
                        <Badge appearance="outline" style={{ marginLeft: '12px' }}>
                            <LockClosed24Regular style={{ marginRight: '4px' }} />
                            System Role
                        </Badge>
                    )}
                </Text>
            </div>

            <Card className={styles.card}>
                {isSuperAdmin && (
                    <MessageBar intent="warning" style={{ marginBottom: '20px' }}>
                        <MessageBarBody>
                            Super Admin role has all permissions and cannot be modified.
                        </MessageBarBody>
                    </MessageBar>
                )}

                <form onSubmit={handleSubmit} className={styles.form}>
                    <Field
                        label="Role Name"
                        required
                        validationMessage={errors.name}
                        validationState={errors.name ? 'error' : 'none'}
                    >
                        <Input
                            value={data.name}
                            onChange={handleNameChange}
                            placeholder="e.g., Content Editor"
                            disabled={role.is_system}
                        />
                    </Field>

                    <Field label="Description">
                        <Textarea
                            value={data.description}
                            onChange={handleDescriptionChange}
                            placeholder="Brief description of this role"
                        />
                    </Field>

                    <Field
                        label="Level"
                        hint="Higher level = more privileges. Users can only manage users with lower levels."
                        validationMessage={errors.level}
                        validationState={errors.level ? 'error' : 'none'}
                    >
                        <Input
                            type="number"
                            value={String(data.level)}
                            onChange={handleLevelChange}
                            min={0}
                            max={99}
                            disabled={role.is_system}
                        />
                    </Field>

                    {!isSuperAdmin && (
                        <div className={styles.permissionsSection}>
                            <Text weight="semibold" size={400}>
                                Permissions
                            </Text>
                            {errors.permissions && (
                                <Text
                                    size={200}
                                    style={{ color: tokens.colorPaletteRedForeground1 }}
                                >
                                    {errors.permissions}
                                </Text>
                            )}

                            {Object.entries(permissions).map(renderPermissionGroup)}
                        </div>
                    )}

                    <div className={styles.actions}>
                        <Button
                            appearance="primary"
                            type="submit"
                            disabled={processing || isSuperAdmin}
                        >
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                        <Button appearance="subtle" onClick={handleBack}>
                            Cancel
                        </Button>
                    </div>
                </form>
            </Card>
        </AdminLayout>
    );
}
