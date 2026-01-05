import { useCallback, FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Textarea,
    Field,
    Combobox,
    Option,
    makeStyles,
    shorthands,
} from '@fluentui/react-components';
import { ArrowLeft24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';

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
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
});

// Types
interface CreatePermissionFormData {
    name: string;
    group: string;
    description: string;
}

interface CreatePermissionProps {
    groups: string[];
}

export default function CreatePermission({ groups }: CreatePermissionProps) {
    const styles = useStyles();
    const { data, setData, post, processing, errors } = useForm<CreatePermissionFormData>({
        name: '',
        group: '',
        description: '',
    });

    // Handlers
    const handleBack = useCallback(() => {
        router.get('/admin/permissions');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent) => {
            e.preventDefault();
            post('/admin/permissions');
        },
        [post]
    );

    const handleNameChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setData('name', e.target.value);
        },
        [setData]
    );

    const handleGroupSelect = useCallback(
        (_: unknown, option: { optionValue?: string }) => {
            setData('group', option.optionValue ?? '');
        },
        [setData]
    );

    const handleDescriptionChange = useCallback(
        (e: React.ChangeEvent<HTMLTextAreaElement>) => {
            setData('description', e.target.value);
        },
        [setData]
    );

    // Render helpers
    const renderGroupOption = useCallback((group: string) => {
        return (
            <Option key={group} value={group}>
                {group}
            </Option>
        );
    }, []);

    return (
        <AdminLayout title="Create Permission">
            <Head title="Create Permission" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Create Permission
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    <Field
                        label="Permission Name"
                        required
                        hint="Use a descriptive name like 'View Users' or 'Edit Leads'"
                        validationMessage={errors.name}
                        validationState={errors.name ? 'error' : 'none'}
                    >
                        <Input
                            value={data.name}
                            onChange={handleNameChange}
                            placeholder="e.g., View Reports"
                        />
                    </Field>

                    <Field label="Group" hint="Group permissions by category for easier management">
                        <Combobox
                            value={data.group}
                            onOptionSelect={handleGroupSelect}
                            placeholder="Select or type a group"
                            freeform
                        >
                            {groups.map(renderGroupOption)}
                        </Combobox>
                    </Field>

                    <Field label="Description">
                        <Textarea
                            value={data.description}
                            onChange={handleDescriptionChange}
                            placeholder="Brief description of what this permission allows"
                        />
                    </Field>

                    <div className={styles.actions}>
                        <Button appearance="primary" type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create Permission'}
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
