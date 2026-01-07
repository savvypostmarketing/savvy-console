import { useCallback, FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Field,
    Textarea,
    Switch,
    makeStyles,
    shorthands,
    tokens,
} from '@fluentui/react-components';
import {
    ArrowLeft24Regular,
    Save24Regular,
    Checkmark24Regular,
    Delete24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';

interface PostCategory {
    id: number;
    name: string;
    name_es: string | null;
    slug: string;
    description: string | null;
    description_es: string | null;
    icon: string | null;
    color: string | null;
    is_active: boolean;
}

interface Props {
    category: PostCategory;
}

interface FormData {
    name: string;
    name_es: string;
    slug: string;
    description: string;
    description_es: string;
    icon: string;
    color: string;
    is_active: boolean;
}

const useStyles = makeStyles({
    header: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
        marginBottom: '24px',
    },
    headerActions: {
        marginLeft: 'auto',
        display: 'flex',
        ...shorthands.gap('8px'),
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
    colorInput: {
        width: '100px',
        height: '40px',
        ...shorthands.padding('4px'),
        ...shorthands.borderRadius('4px'),
        cursor: 'pointer',
    },
});

export default function EditPostCategory({ category }: Props) {
    const styles = useStyles();

    const { data, setData, put, processing, errors } = useForm<FormData>({
        name: category.name,
        name_es: category.name_es || '',
        slug: category.slug,
        description: category.description || '',
        description_es: category.description_es || '',
        icon: category.icon || '',
        color: category.color || '#3b82f6',
        is_active: category.is_active,
    });

    const handleBack = useCallback(() => {
        router.get('/admin/post-categories');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent, redirectAfter = false) => {
            e.preventDefault();
            put(`/admin/post-categories/${category.id}`, {
                onSuccess: () => {
                    if (redirectAfter) {
                        router.get('/admin/post-categories');
                    }
                },
            });
        },
        [put, category.id]
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

    const handleDelete = useCallback(() => {
        if (confirm('Are you sure you want to delete this category?')) {
            router.delete(`/admin/post-categories/${category.id}`);
        }
    }, [category.id]);

    return (
        <AdminLayout title="Edit Category">
            <Head title={`Edit: ${category.name}`} />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Edit Category
                </Text>
                <div className={styles.headerActions}>
                    <Button
                        appearance="subtle"
                        icon={<Delete24Regular />}
                        onClick={handleDelete}
                        style={{ color: tokens.colorPaletteRedForeground1 }}
                    >
                        Delete
                    </Button>
                </div>
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
                                placeholder="Category name in English"
                            />
                        </Field>
                        <Field label="Name (ES)">
                            <Input
                                value={data.name_es}
                                onChange={(e) => setData('name_es', e.target.value)}
                                placeholder="Category name in Spanish"
                            />
                        </Field>
                    </div>

                    <Field label="Slug">
                        <Input
                            value={data.slug}
                            onChange={(e) => setData('slug', e.target.value)}
                            placeholder="category-slug"
                        />
                    </Field>

                    <div className={styles.row}>
                        <Field label="Description (EN)">
                            <Textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                placeholder="Brief description of the category"
                                rows={3}
                            />
                        </Field>
                        <Field label="Description (ES)">
                            <Textarea
                                value={data.description_es}
                                onChange={(e) => setData('description_es', e.target.value)}
                                placeholder="Descripción breve de la categoría"
                                rows={3}
                            />
                        </Field>
                    </div>

                    <div className={styles.row}>
                        <Field label="Icon (emoji or icon code)">
                            <Input
                                value={data.icon}
                                onChange={(e) => setData('icon', e.target.value)}
                                placeholder="e.g., 📱 or icon-name"
                            />
                        </Field>
                        <Field label="Color">
                            <input
                                type="color"
                                value={data.color}
                                onChange={(e) => setData('color', e.target.value)}
                                className={styles.colorInput}
                            />
                        </Field>
                    </div>

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
                            {processing ? 'Saving...' : 'Save & Exit'}
                        </Button>
                        <Button
                            appearance="secondary"
                            icon={<Save24Regular />}
                            onClick={handleSave}
                            disabled={processing}
                        >
                            Save & Continue
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
