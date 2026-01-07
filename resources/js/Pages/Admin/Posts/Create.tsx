import { useCallback, FormEvent, useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Field,
    Textarea,
    Switch,
    Dropdown,
    Option,
    Checkbox,
    makeStyles,
    shorthands,
    tokens,
    Tab,
    TabList,
    Divider,
    type SelectTabData,
    type SelectTabEvent,
} from '@fluentui/react-components';
import { ArrowLeft24Regular, Save24Regular, Checkmark24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import ImagePicker from '@/Components/ImagePicker';
import BlockEditor from '@/Components/BlockEditor';
import type { PostCreateProps, EditorJSData } from '@/interfaces/post';

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
    section: {
        ...shorthands.padding('16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
    },
    sectionTitle: {
        marginBottom: '16px',
    },
    tagsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))',
        ...shorthands.gap('8px'),
        marginTop: '12px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
    tabContent: {
        marginTop: '20px',
    },
});

export default function CreatePost({ categories, tags }: PostCreateProps) {
    const styles = useStyles();
    const [activeTab, setActiveTab] = useState('basic');

    interface FormData {
        title: string;
        title_es: string;
        slug: string;
        category_id: string | number;
        excerpt: string;
        excerpt_es: string;
        content: EditorJSData | null;
        content_es: EditorJSData | null;
        featured_image: File | null;
        featured_image_alt: string;
        featured_image_alt_es: string;
        reading_time_minutes: number;
        is_published: boolean;
        is_featured: boolean;
        meta_title: string;
        meta_title_es: string;
        meta_description: string;
        meta_description_es: string;
        tags: number[];
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const { data, setData, post, processing, errors } = useForm<any>({
        title: '',
        title_es: '',
        slug: '',
        category_id: '',
        excerpt: '',
        excerpt_es: '',
        content: null,
        content_es: null,
        featured_image: null,
        featured_image_alt: '',
        featured_image_alt_es: '',
        reading_time_minutes: 5,
        is_published: false,
        is_featured: false,
        meta_title: '',
        meta_title_es: '',
        meta_description: '',
        meta_description_es: '',
        tags: [],
    }) as unknown as {
        data: FormData;
        setData: <K extends keyof FormData>(key: K, value: FormData[K]) => void;
        post: (url: string, options?: object) => void;
        processing: boolean;
        errors: Partial<Record<keyof FormData, string>>;
    };

    const handleBack = useCallback(() => {
        router.get('/admin/posts');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent, redirectAfter = false) => {
            e.preventDefault();
            post('/admin/posts', {
                forceFormData: true,
                onSuccess: () => {
                    if (redirectAfter) {
                        router.get('/admin/posts');
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

    const handleTabChange = useCallback((_: SelectTabEvent, tabData: SelectTabData) => {
        setActiveTab(tabData.value as string);
    }, []);

    const toggleTag = useCallback(
        (id: number) => {
            if (data.tags.includes(id)) {
                setData(
                    'tags',
                    data.tags.filter((t) => t !== id)
                );
            } else {
                setData('tags', [...data.tags, id]);
            }
        },
        [data.tags, setData]
    );

    const handleContentChange = useCallback(
        (content: EditorJSData) => {
            setData('content', content);
        },
        [setData]
    );

    const handleContentEsChange = useCallback(
        (content: EditorJSData) => {
            setData('content_es', content);
        },
        [setData]
    );

    return (
        <AdminLayout title="Create Post">
            <Head title="Create Post" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Create Post
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    <TabList selectedValue={activeTab} onTabSelect={handleTabChange}>
                        <Tab value="basic">Basic Info</Tab>
                        <Tab value="content_en">Content (EN)</Tab>
                        <Tab value="content_es">Content (ES)</Tab>
                        <Tab value="seo">SEO</Tab>
                    </TabList>

                    <div className={styles.tabContent}>
                        {/* Basic Info Tab */}
                        {activeTab === 'basic' && (
                            <>
                                <div className={styles.row}>
                                    <Field
                                        label="Title (EN)"
                                        required
                                        validationMessage={errors.title}
                                        validationState={errors.title ? 'error' : 'none'}
                                    >
                                        <Input
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            placeholder="Post title in English"
                                        />
                                    </Field>
                                    <Field label="Title (ES)">
                                        <Input
                                            value={data.title_es}
                                            onChange={(e) => setData('title_es', e.target.value)}
                                            placeholder="Post title in Spanish"
                                        />
                                    </Field>
                                </div>

                                <Field label="Slug">
                                    <Input
                                        value={data.slug}
                                        onChange={(e) => setData('slug', e.target.value)}
                                        placeholder="post-slug (auto-generated if empty)"
                                    />
                                </Field>

                                <Field label="Category">
                                    <Dropdown
                                        placeholder="Select category"
                                        value={
                                            data.category_id
                                                ? categories.find(
                                                      (c) => c.id === Number(data.category_id)
                                                  )?.name || ''
                                                : ''
                                        }
                                        onOptionSelect={(_, opt) =>
                                            setData('category_id', opt.optionValue as string)
                                        }
                                    >
                                        {categories.map((category) => (
                                            <Option
                                                key={category.id}
                                                value={String(category.id)}
                                                text={`${category.name} / ${category.name_es ?? ''}`}
                                            >
                                                {category.name} / {category.name_es}
                                            </Option>
                                        ))}
                                    </Dropdown>
                                </Field>

                                <div className={styles.section}>
                                    <Text weight="semibold" className={styles.sectionTitle}>
                                        Tags
                                    </Text>
                                    <div className={styles.tagsGrid}>
                                        {tags.map((tag) => (
                                            <Checkbox
                                                key={tag.id}
                                                checked={data.tags.includes(tag.id)}
                                                onChange={() => toggleTag(tag.id)}
                                                label={`${tag.name}${tag.name_es ? ` / ${tag.name_es}` : ''}`}
                                            />
                                        ))}
                                    </div>
                                </div>

                                <div className={styles.row}>
                                    <Field label="Excerpt (EN)">
                                        <Textarea
                                            value={data.excerpt}
                                            onChange={(e) => setData('excerpt', e.target.value)}
                                            placeholder="Brief summary of the post"
                                            rows={3}
                                        />
                                    </Field>
                                    <Field label="Excerpt (ES)">
                                        <Textarea
                                            value={data.excerpt_es}
                                            onChange={(e) => setData('excerpt_es', e.target.value)}
                                            placeholder="Resumen breve del post"
                                            rows={3}
                                        />
                                    </Field>
                                </div>

                                <Field label="Featured Image">
                                    <ImagePicker
                                        value={data.featured_image}
                                        onChange={(file) => setData('featured_image', file)}
                                        placeholder="Drop featured image here or click to upload"
                                    />
                                </Field>

                                <div className={styles.row}>
                                    <Field label="Image Alt Text (EN)">
                                        <Input
                                            value={data.featured_image_alt}
                                            onChange={(e) =>
                                                setData('featured_image_alt', e.target.value)
                                            }
                                            placeholder="Describe the image"
                                        />
                                    </Field>
                                    <Field label="Image Alt Text (ES)">
                                        <Input
                                            value={data.featured_image_alt_es}
                                            onChange={(e) =>
                                                setData('featured_image_alt_es', e.target.value)
                                            }
                                            placeholder="Describe la imagen"
                                        />
                                    </Field>
                                </div>

                                <Field label="Reading Time (minutes)">
                                    <Input
                                        type="number"
                                        value={String(data.reading_time_minutes)}
                                        onChange={(e) =>
                                            setData(
                                                'reading_time_minutes',
                                                parseInt(e.target.value) || 5
                                            )
                                        }
                                        min={1}
                                    />
                                </Field>

                                <Divider />

                                <div className={styles.row}>
                                    <Switch
                                        checked={data.is_published}
                                        onChange={(_, d) => setData('is_published', d.checked)}
                                        label="Published"
                                    />
                                    <Switch
                                        checked={data.is_featured}
                                        onChange={(_, d) => setData('is_featured', d.checked)}
                                        label="Featured"
                                    />
                                </div>
                            </>
                        )}

                        {/* Content EN Tab */}
                        {activeTab === 'content_en' && (
                            <BlockEditor
                                value={data.content}
                                onChange={handleContentChange}
                                label="Content (English)"
                                placeholder="Start writing your post content..."
                                uploadEndpoint="/admin/posts/upload-image"
                            />
                        )}

                        {/* Content ES Tab */}
                        {activeTab === 'content_es' && (
                            <BlockEditor
                                value={data.content_es}
                                onChange={handleContentEsChange}
                                label="Content (Spanish)"
                                placeholder="Comienza a escribir el contenido del post..."
                                uploadEndpoint="/admin/posts/upload-image"
                            />
                        )}

                        {/* SEO Tab */}
                        {activeTab === 'seo' && (
                            <>
                                <div className={styles.row}>
                                    <Field label="Meta Title (EN)">
                                        <Input
                                            value={data.meta_title}
                                            onChange={(e) => setData('meta_title', e.target.value)}
                                            placeholder="SEO title (defaults to post title)"
                                        />
                                    </Field>
                                    <Field label="Meta Title (ES)">
                                        <Input
                                            value={data.meta_title_es}
                                            onChange={(e) =>
                                                setData('meta_title_es', e.target.value)
                                            }
                                            placeholder="Título SEO (usa el título del post por defecto)"
                                        />
                                    </Field>
                                </div>

                                <div className={styles.row}>
                                    <Field label="Meta Description (EN)">
                                        <Textarea
                                            value={data.meta_description}
                                            onChange={(e) =>
                                                setData('meta_description', e.target.value)
                                            }
                                            placeholder="SEO description (defaults to excerpt)"
                                            rows={3}
                                        />
                                    </Field>
                                    <Field label="Meta Description (ES)">
                                        <Textarea
                                            value={data.meta_description_es}
                                            onChange={(e) =>
                                                setData('meta_description_es', e.target.value)
                                            }
                                            placeholder="Descripción SEO (usa el extracto por defecto)"
                                            rows={3}
                                        />
                                    </Field>
                                </div>
                            </>
                        )}
                    </div>

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
