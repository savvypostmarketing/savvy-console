import { useCallback, FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Field,
    Checkbox,
    makeStyles,
    shorthands,
    tokens,
} from '@fluentui/react-components';
import { ArrowLeft24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import type { RoleOption } from '@/interfaces';

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
        maxWidth: '600px',
    },
    form: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('20px'),
    },
    rolesSection: {
        ...shorthands.padding('16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
    },
    rolesGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))',
        ...shorthands.gap('12px'),
        marginTop: '12px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
});

// Types
interface CreateUserFormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    roles: string[];
}

interface CreateUserProps {
    roles: RoleOption[];
}

export default function CreateUser({ roles }: CreateUserProps) {
    const styles = useStyles();
    const { data, setData, post, processing, errors } = useForm<CreateUserFormData>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: [],
    });

    // Handlers
    const handleBack = useCallback(() => {
        router.get('/admin/users');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent) => {
            e.preventDefault();
            post('/admin/users');
        },
        [post]
    );

    const handleNameChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('name', e.target.value);
        },
        [setData]
    );

    const handleEmailChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('email', e.target.value);
        },
        [setData]
    );

    const handlePasswordChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('password', e.target.value);
        },
        [setData]
    );

    const handlePasswordConfirmationChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('password_confirmation', e.target.value);
        },
        [setData]
    );

    const toggleRole = useCallback(
        (slug: string) => {
            if (data.roles.includes(slug)) {
                setData(
                    'roles',
                    data.roles.filter((r) => r !== slug)
                );
            } else {
                setData('roles', [...data.roles, slug]);
            }
        },
        [data.roles, setData]
    );

    // Render helpers
    const renderRoleCheckbox = useCallback(
        (role: RoleOption) => {
            const handleChange = () => {
                toggleRole(role.slug);
            };

            return (
                <Checkbox
                    key={role.slug}
                    checked={data.roles.includes(role.slug)}
                    onChange={handleChange}
                    label={role.name}
                />
            );
        },
        [data.roles, toggleRole]
    );

    return (
        <AdminLayout title="Create User">
            <Head title="Create User" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Create User
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    <Field
                        label="Name"
                        required
                        validationMessage={errors.name}
                        validationState={errors.name ? 'error' : 'none'}
                    >
                        <Input
                            value={data.name}
                            onChange={handleNameChange}
                            placeholder="Full name"
                        />
                    </Field>

                    <Field
                        label="Email"
                        required
                        validationMessage={errors.email}
                        validationState={errors.email ? 'error' : 'none'}
                    >
                        <Input
                            type="email"
                            value={data.email}
                            onChange={handleEmailChange}
                            placeholder="email@example.com"
                        />
                    </Field>

                    <Field
                        label="Password"
                        required
                        validationMessage={errors.password}
                        validationState={errors.password ? 'error' : 'none'}
                    >
                        <Input
                            type="password"
                            value={data.password}
                            onChange={handlePasswordChange}
                            placeholder="Minimum 8 characters"
                        />
                    </Field>

                    <Field label="Confirm Password" required>
                        <Input
                            type="password"
                            value={data.password_confirmation}
                            onChange={handlePasswordConfirmationChange}
                            placeholder="Repeat password"
                        />
                    </Field>

                    <div className={styles.rolesSection}>
                        <Text weight="semibold">Roles</Text>
                        {errors.roles && (
                            <Text size={200} style={{ color: tokens.colorPaletteRedForeground1 }}>
                                {errors.roles}
                            </Text>
                        )}
                        <div className={styles.rolesGrid}>{roles.map(renderRoleCheckbox)}</div>
                    </div>

                    <div className={styles.actions}>
                        <Button appearance="primary" type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create User'}
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
