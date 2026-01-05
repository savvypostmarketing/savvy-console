import { useCallback, FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Field,
    Textarea,
    Checkbox,
    Switch,
    Dropdown,
    Option,
    makeStyles,
    shorthands,
    tokens,
    SpinButton,
} from '@fluentui/react-components';
import {
    ArrowLeft24Regular,
    Save24Regular,
    Checkmark24Regular,
    Star24Filled,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import ImagePicker from '@/Components/ImagePicker';

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
    servicesGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(180px, 1fr))',
        ...shorthands.gap('12px'),
        marginTop: '12px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
    ratingContainer: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('8px'),
    },
    star: {
        cursor: 'pointer',
        fontSize: '24px',
        color: tokens.colorPaletteYellowForeground1,
        transitionProperty: 'transform',
        transitionDuration: '0.1s',
        ':hover': {
            transform: 'scale(1.2)',
        },
    },
    starInactive: {
        opacity: 0.3,
    },
});

interface ServiceOption {
    [key: string]: string;
}

interface TestimonialData {
    id: number;
    uuid: string;
    name: string;
    role: string | null;
    role_es: string | null;
    company: string | null;
    company_es: string | null;
    avatar: string | null;
    quote: string;
    quote_es: string | null;
    rating: number;
    project_title: string | null;
    project_title_es: string | null;
    project_screenshot: string | null;
    source: string;
    services: string[] | null;
    is_featured: boolean;
    is_published: boolean;
    sort_order: number | null;
    date_label: string | null;
    extra_info: string | null;
}

interface EditTestimonialProps {
    testimonial: TestimonialData;
    sources: string[];
    serviceOptions: ServiceOption;
}

interface TestimonialFormData {
    name: string;
    role: string;
    role_es: string;
    company: string;
    company_es: string;
    avatar: File | null;
    quote: string;
    quote_es: string;
    rating: number;
    project_title: string;
    project_title_es: string;
    project_screenshot: File | null;
    source: string;
    services: string[];
    is_featured: boolean;
    is_published: boolean;
    sort_order: number;
    date_label: string;
    extra_info: string;
    _method: string;
}

export default function EditTestimonial({
    testimonial,
    sources,
    serviceOptions,
}: EditTestimonialProps) {
    const styles = useStyles();

    const { data, setData, post, processing, errors } = useForm<TestimonialFormData>({
        name: testimonial.name,
        role: testimonial.role || '',
        role_es: testimonial.role_es || '',
        company: testimonial.company || '',
        company_es: testimonial.company_es || '',
        avatar: null,
        quote: testimonial.quote,
        quote_es: testimonial.quote_es || '',
        rating: testimonial.rating,
        project_title: testimonial.project_title || '',
        project_title_es: testimonial.project_title_es || '',
        project_screenshot: null,
        source: testimonial.source,
        services: testimonial.services || [],
        is_featured: testimonial.is_featured,
        is_published: testimonial.is_published,
        sort_order: testimonial.sort_order || 0,
        date_label: testimonial.date_label || '',
        extra_info: testimonial.extra_info || '',
        _method: 'PUT',
    });

    const handleBack = useCallback(() => {
        router.get('/admin/testimonials');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent, redirectAfter = false) => {
            e.preventDefault();
            post(`/admin/testimonials/${testimonial.id}`, {
                forceFormData: true,
                onSuccess: () => {
                    if (redirectAfter) {
                        router.get('/admin/testimonials');
                    }
                },
            });
        },
        [post, testimonial.id]
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

    const toggleService = useCallback(
        (serviceKey: string) => {
            if (data.services.includes(serviceKey)) {
                setData(
                    'services',
                    data.services.filter((s) => s !== serviceKey)
                );
            } else {
                setData('services', [...data.services, serviceKey]);
            }
        },
        [data.services, setData]
    );

    const handleRatingClick = useCallback(
        (rating: number) => {
            setData('rating', rating);
        },
        [setData]
    );

    const renderRatingStars = useCallback(() => {
        return (
            <div className={styles.ratingContainer}>
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star24Filled
                        key={star}
                        className={`${styles.star} ${star > data.rating ? styles.starInactive : ''}`}
                        onClick={() => handleRatingClick(star)}
                    />
                ))}
                <Text
                    size={200}
                    style={{ marginLeft: '8px', color: tokens.colorNeutralForeground3 }}
                >
                    {data.rating} / 5
                </Text>
            </div>
        );
    }, [data.rating, handleRatingClick, styles]);

    return (
        <AdminLayout title="Edit Testimonial">
            <Head title="Edit Testimonial" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Edit Testimonial
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    {/* Author Info Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Author Information
                        </Text>

                        <Field
                            label="Name"
                            required
                            validationMessage={errors.name}
                            validationState={errors.name ? 'error' : 'none'}
                        >
                            <Input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="John Doe"
                            />
                        </Field>

                        <div className={styles.row} style={{ marginTop: '16px' }}>
                            <Field label="Role (EN)">
                                <Input
                                    value={data.role}
                                    onChange={(e) => setData('role', e.target.value)}
                                    placeholder="CEO"
                                />
                            </Field>
                            <Field label="Role (ES)">
                                <Input
                                    value={data.role_es}
                                    onChange={(e) => setData('role_es', e.target.value)}
                                    placeholder="Director Ejecutivo"
                                />
                            </Field>
                        </div>

                        <div className={styles.row} style={{ marginTop: '16px' }}>
                            <Field label="Company (EN)">
                                <Input
                                    value={data.company}
                                    onChange={(e) => setData('company', e.target.value)}
                                    placeholder="Acme Inc."
                                />
                            </Field>
                            <Field label="Company (ES)">
                                <Input
                                    value={data.company_es}
                                    onChange={(e) => setData('company_es', e.target.value)}
                                    placeholder="Acme Inc."
                                />
                            </Field>
                        </div>

                        <Field label="Avatar" style={{ marginTop: '16px' }}>
                            <ImagePicker
                                value={data.avatar}
                                existingImage={
                                    testimonial.avatar
                                        ? `/storage/${testimonial.avatar}`
                                        : undefined
                                }
                                onChange={(file) => setData('avatar', file)}
                                placeholder="Drop avatar image here or click to upload"
                            />
                        </Field>
                    </div>

                    {/* Testimonial Content Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Testimonial Content
                        </Text>

                        <div className={styles.row}>
                            <Field
                                label="Quote (EN)"
                                required
                                validationMessage={errors.quote}
                                validationState={errors.quote ? 'error' : 'none'}
                            >
                                <Textarea
                                    value={data.quote}
                                    onChange={(e) => setData('quote', e.target.value)}
                                    placeholder="The testimonial quote in English..."
                                    rows={4}
                                />
                            </Field>
                            <Field label="Quote (ES)">
                                <Textarea
                                    value={data.quote_es}
                                    onChange={(e) => setData('quote_es', e.target.value)}
                                    placeholder="El testimonio en Español..."
                                    rows={4}
                                />
                            </Field>
                        </div>

                        <Field label="Rating" required style={{ marginTop: '16px' }}>
                            {renderRatingStars()}
                        </Field>
                    </div>

                    {/* Source & Services Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Categorization
                        </Text>

                        <Field
                            label="Source"
                            required
                            validationMessage={errors.source}
                            validationState={errors.source ? 'error' : 'none'}
                        >
                            <Dropdown
                                value={data.source.charAt(0).toUpperCase() + data.source.slice(1)}
                                onOptionSelect={(_, opt) =>
                                    setData('source', opt.optionValue as string)
                                }
                            >
                                {sources.map((source) => (
                                    <Option key={source} value={source}>
                                        {source.charAt(0).toUpperCase() + source.slice(1)}
                                    </Option>
                                ))}
                            </Dropdown>
                        </Field>

                        <div style={{ marginTop: '16px' }}>
                            <Text weight="semibold">Services</Text>
                            <div className={styles.servicesGrid}>
                                {Object.entries(serviceOptions).map(([key, label]) => (
                                    <Checkbox
                                        key={key}
                                        checked={data.services.includes(key)}
                                        onChange={() => toggleService(key)}
                                        label={label}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Project Info Section (Optional) */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Project Information (Optional)
                        </Text>

                        <div className={styles.row}>
                            <Field label="Project Title (EN)">
                                <Input
                                    value={data.project_title}
                                    onChange={(e) => setData('project_title', e.target.value)}
                                    placeholder="Website Redesign"
                                />
                            </Field>
                            <Field label="Project Title (ES)">
                                <Input
                                    value={data.project_title_es}
                                    onChange={(e) => setData('project_title_es', e.target.value)}
                                    placeholder="Rediseño del Sitio Web"
                                />
                            </Field>
                        </div>

                        <Field label="Project Screenshot" style={{ marginTop: '16px' }}>
                            <ImagePicker
                                value={data.project_screenshot}
                                existingImage={
                                    testimonial.project_screenshot
                                        ? `/storage/${testimonial.project_screenshot}`
                                        : undefined
                                }
                                onChange={(file) => setData('project_screenshot', file)}
                                placeholder="Drop project screenshot here or click to upload"
                            />
                        </Field>
                    </div>

                    {/* Meta Info Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Display Settings
                        </Text>

                        <div className={styles.row}>
                            <Field label="Date Label">
                                <Input
                                    value={data.date_label}
                                    onChange={(e) => setData('date_label', e.target.value)}
                                    placeholder="a year ago"
                                />
                            </Field>
                            <Field label="Extra Info">
                                <Input
                                    value={data.extra_info}
                                    onChange={(e) => setData('extra_info', e.target.value)}
                                    placeholder="Local Guide·14 reviews·31 photos"
                                />
                            </Field>
                        </div>

                        <div className={styles.row} style={{ marginTop: '16px' }}>
                            <Field label="Sort Order">
                                <SpinButton
                                    value={data.sort_order}
                                    onChange={(_, d) => setData('sort_order', d.value || 0)}
                                    min={0}
                                />
                            </Field>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
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
                        </div>
                    </div>

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
