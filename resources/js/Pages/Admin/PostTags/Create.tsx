import { useCallback, FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Field,
    Switch,
    makeStyles,
    shorthands,
} from '@fluentui/react-components';
import { ArrowLeft24Regular, Save24Regular, Checkmark24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';

interface FormData {
    name: string;
    name_es: string;
    slug: string;
    is_active: boolean;
}

const useStyles = makeStyles({
    header: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
        marginBottom: '24px',
    },
    card: {
        ...shorthands.padding('24px'),
    },
    form: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('20px'),
    },
    row: {
        display: 'grid',
        gridTemplateColumns: '1fr 1fr',
        ...shorthands.gap('20px'),
        '@media (max-width: 768px)': {
            gridTemplateColumns: '1fr',
        },
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
});

export default function CreatePostTag() {
    const styles = useStyles();

    const { data, setData, post, processing, errors } = useForm<FormData>({
        name: '',
        name_es: '',
        slug: '',
        is_active: true,
    });

    const handleBack = useCallback(() => {
        router.get('/admin/post-tags');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent, redirectAfter = false) => {
            e.preventDefault();
            post('/admin/post-tags', {
                onSuccess: () => {
                    if (redirectAfter) {
                        router.get('/admin/post-tags');
                    }
                },
            });
        },
        [post]
    );

    const handleSave = useCallback(
        (e: FormEvent) => {
            handleSubmit(e, false);
        },
        [handleSubmit]
    );

    const handleSaveAndExit = useCallback(
        (e: FormEvent) => {
            handleSubmit(e, true);
        },
        [handleSubmit]
    );

    return (
        <AdminLayout title="Create Tag">
            <Head title="Create Tag" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Create Tag
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    <div className={styles.row}>
                        <Field
                            label="Name (EN)"
                            required
                            validationMessage={errors.name}
                            validationState={errors.name ? 'error' : 'none'}
                        >
                            <Input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Tag name in English"
                            />
                        </Field>
                        <Field label="Name (ES)">
                            <Input
                                value={data.name_es}
                                onChange={(e) => setData('name_es', e.target.value)}
                                placeholder="Tag name in Spanish"
                            />
                        </Field>
                    </div>

                    <Field label="Slug">
                        <Input
                            value={data.slug}
                            onChange={(e) => setData('slug', e.target.value)}
                            placeholder="tag-slug (auto-generated if empty)"
                        />
                    </Field>

                    <Switch
                        checked={data.is_active}
                        onChange={(_, d) => setData('is_active', d.checked)}
                        label="Active"
                    />

                    <div className={styles.actions}>
                        <Button
                            appearance="primary"
                            icon={<Checkmark24Regular />}
                            onClick={handleSaveAndExit}
                            disabled={processing}
                        >
                            {processing ? 'Creating...' : 'Create & Exit'}
                        </Button>
                        <Button
                            appearance="secondary"
                            icon={<Save24Regular />}
                            onClick={handleSave}
                            disabled={processing}
                        >
                            Create & Continue
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
