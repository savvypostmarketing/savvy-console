import { useCallback, FormEvent, useState } from 'react';
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
    Tab,
    TabList,
    Divider,
    type SelectTabData,
    type SelectTabEvent,
} from '@fluentui/react-components';
import {
    ArrowLeft24Regular,
    Add24Regular,
    Delete24Regular,
    Save24Regular,
    Checkmark24Regular,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import IconPicker from '@/Components/IconPicker';
import ImagePicker from '@/Components/ImagePicker';
import type { PortfolioIndustry, PortfolioService, PortfolioFormData } from '@/interfaces';

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
    servicesGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(180px, 1fr))',
        ...shorthands.gap('12px'),
        marginTop: '12px',
    },
    section: {
        ...shorthands.padding('16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
    },
    sectionTitle: {
        marginBottom: '16px',
    },
    itemRow: {
        display: 'flex',
        alignItems: 'flex-start',
        ...shorthands.gap('12px'),
        marginBottom: '12px',
    },
    itemFields: {
        flexGrow: 1,
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('8px'),
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

// Types
interface CreatePortfolioProps {
    industries: PortfolioIndustry[];
    services: PortfolioService[];
}

export default function CreatePortfolio({ industries, services }: CreatePortfolioProps) {
    const styles = useStyles();
    const [activeTab, setActiveTab] = useState('basic');

    const { data, setData, post, processing, errors } = useForm<PortfolioFormData>({
        title: '',
        title_es: '',
        slug: '',
        industry_id: '',
        services: [],
        description: '',
        description_es: '',
        challenge: '',
        challenge_es: '',
        solution: '',
        solution_es: '',
        featured_image: null,
        website_url: '',
        testimonial_quote: '',
        testimonial_quote_es: '',
        testimonial_author: '',
        testimonial_role: '',
        testimonial_role_es: '',
        testimonial_avatar: null,
        video_url: '',
        video_thumbnail: null,
        is_published: false,
        is_featured: false,
        sort_order: 0,
        stats: [],
        features: [],
        results: [],
        video_features: [],
    });

    // Handlers
    const handleBack = useCallback(() => {
        router.get('/admin/portfolio');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent, redirectAfter = false) => {
            e.preventDefault();
            post('/admin/portfolio', {
                forceFormData: true,
                onSuccess: () => {
                    if (redirectAfter) {
                        router.get('/admin/portfolio');
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

    const handleTabChange = useCallback((_: SelectTabEvent, data: SelectTabData) => {
        setActiveTab(data.value as string);
    }, []);

    const toggleService = useCallback(
        (id: number) => {
            if (data.services.includes(id)) {
                setData(
                    'services',
                    data.services.filter((s) => s !== id)
                );
            } else {
                setData('services', [...data.services, id]);
            }
        },
        [data.services, setData]
    );

    // Dynamic field handlers
    const addStat = useCallback(() => {
        setData('stats', [...data.stats, { label: '', label_es: '', value: '' }]);
    }, [data.stats, setData]);

    const removeStat = useCallback(
        (index: number) => {
            setData(
                'stats',
                data.stats.filter((_, i) => i !== index)
            );
        },
        [data.stats, setData]
    );

    const updateStat = useCallback(
        (index: number, field: string, value: string) => {
            const newStats = [...data.stats];
            newStats[index] = { ...newStats[index], [field]: value };
            setData('stats', newStats);
        },
        [data.stats, setData]
    );

    const addFeature = useCallback(() => {
        const number = String(data.features.length + 1).padStart(2, '0');
        setData('features', [
            ...data.features,
            { number, title: '', title_es: '', description: '', description_es: '', icon: '' },
        ]);
    }, [data.features, setData]);

    const removeFeature = useCallback(
        (index: number) => {
            setData(
                'features',
                data.features.filter((_, i) => i !== index)
            );
        },
        [data.features, setData]
    );

    const updateFeature = useCallback(
        (index: number, field: string, value: string) => {
            const newFeatures = [...data.features];
            newFeatures[index] = { ...newFeatures[index], [field]: value };
            setData('features', newFeatures);
        },
        [data.features, setData]
    );

    const addResult = useCallback(() => {
        setData('results', [...data.results, { result: '', result_es: '' }]);
    }, [data.results, setData]);

    const removeResult = useCallback(
        (index: number) => {
            setData(
                'results',
                data.results.filter((_, i) => i !== index)
            );
        },
        [data.results, setData]
    );

    const updateResult = useCallback(
        (index: number, field: string, value: string) => {
            const newResults = [...data.results];
            newResults[index] = { ...newResults[index], [field]: value };
            setData('results', newResults);
        },
        [data.results, setData]
    );

    const addVideoFeature = useCallback(() => {
        setData('video_features', [
            ...data.video_features,
            { title: '', title_es: '', description: '', description_es: '' },
        ]);
    }, [data.video_features, setData]);

    const removeVideoFeature = useCallback(
        (index: number) => {
            setData(
                'video_features',
                data.video_features.filter((_, i) => i !== index)
            );
        },
        [data.video_features, setData]
    );

    const updateVideoFeature = useCallback(
        (index: number, field: string, value: string) => {
            const newVideoFeatures = [...data.video_features];
            newVideoFeatures[index] = { ...newVideoFeatures[index], [field]: value };
            setData('video_features', newVideoFeatures);
        },
        [data.video_features, setData]
    );

    return (
        <AdminLayout title="Create Portfolio">
            <Head title="Create Portfolio" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Create Portfolio
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    <TabList selectedValue={activeTab} onTabSelect={handleTabChange}>
                        <Tab value="basic">Basic Info</Tab>
                        <Tab value="content">Content</Tab>
                        <Tab value="stats">Stats & Features</Tab>
                        <Tab value="testimonial">Testimonial</Tab>
                        <Tab value="video">Video</Tab>
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
                                            placeholder="Project title in English"
                                        />
                                    </Field>
                                    <Field label="Title (ES)">
                                        <Input
                                            value={data.title_es}
                                            onChange={(e) => setData('title_es', e.target.value)}
                                            placeholder="Project title in Spanish"
                                        />
                                    </Field>
                                </div>

                                <Field label="Slug">
                                    <Input
                                        value={data.slug}
                                        onChange={(e) => setData('slug', e.target.value)}
                                        placeholder="project-slug (auto-generated if empty)"
                                    />
                                </Field>

                                <Field
                                    label="Industry"
                                    required
                                    validationMessage={errors.industry_id}
                                    validationState={errors.industry_id ? 'error' : 'none'}
                                >
                                    <Dropdown
                                        placeholder="Select industry"
                                        value={
                                            data.industry_id
                                                ? industries.find(
                                                      (i) => i.id === Number(data.industry_id)
                                                  )?.name || ''
                                                : ''
                                        }
                                        onOptionSelect={(_, opt) =>
                                            setData('industry_id', opt.optionValue as string)
                                        }
                                    >
                                        {industries.map((industry) => (
                                            <Option
                                                key={industry.id}
                                                value={String(industry.id)}
                                                text={`${industry.name} / ${industry.name_es}`}
                                            >
                                                {industry.name} / {industry.name_es}
                                            </Option>
                                        ))}
                                    </Dropdown>
                                </Field>

                                <div className={styles.section}>
                                    <Text weight="semibold" className={styles.sectionTitle}>
                                        Services
                                    </Text>
                                    {errors.services && (
                                        <Text
                                            size={200}
                                            style={{ color: tokens.colorPaletteRedForeground1 }}
                                        >
                                            {errors.services}
                                        </Text>
                                    )}
                                    <div className={styles.servicesGrid}>
                                        {services.map((service) => (
                                            <Checkbox
                                                key={service.id}
                                                checked={data.services.includes(service.id)}
                                                onChange={() => toggleService(service.id)}
                                                label={`${service.name} / ${service.name_es}`}
                                            />
                                        ))}
                                    </div>
                                </div>

                                <Field label="Website URL">
                                    <Input
                                        type="url"
                                        value={data.website_url}
                                        onChange={(e) => setData('website_url', e.target.value)}
                                        placeholder="https://example.com"
                                    />
                                </Field>

                                <Field label="Featured Image">
                                    <ImagePicker
                                        value={data.featured_image}
                                        onChange={(file) => setData('featured_image', file)}
                                        placeholder="Drop featured image here or click to upload"
                                    />
                                </Field>

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

                        {/* Content Tab */}
                        {activeTab === 'content' && (
                            <>
                                <div className={styles.row}>
                                    <Field label="Description (EN)">
                                        <Textarea
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Project description"
                                            rows={4}
                                        />
                                    </Field>
                                    <Field label="Description (ES)">
                                        <Textarea
                                            value={data.description_es}
                                            onChange={(e) =>
                                                setData('description_es', e.target.value)
                                            }
                                            placeholder="Descripcion del proyecto"
                                            rows={4}
                                        />
                                    </Field>
                                </div>

                                <div className={styles.row}>
                                    <Field label="Challenge (EN)">
                                        <Textarea
                                            value={data.challenge}
                                            onChange={(e) => setData('challenge', e.target.value)}
                                            placeholder="The challenge faced"
                                            rows={4}
                                        />
                                    </Field>
                                    <Field label="Challenge (ES)">
                                        <Textarea
                                            value={data.challenge_es}
                                            onChange={(e) =>
                                                setData('challenge_es', e.target.value)
                                            }
                                            placeholder="El desafio enfrentado"
                                            rows={4}
                                        />
                                    </Field>
                                </div>

                                <div className={styles.row}>
                                    <Field label="Solution (EN)">
                                        <Textarea
                                            value={data.solution}
                                            onChange={(e) => setData('solution', e.target.value)}
                                            placeholder="The solution provided"
                                            rows={4}
                                        />
                                    </Field>
                                    <Field label="Solution (ES)">
                                        <Textarea
                                            value={data.solution_es}
                                            onChange={(e) => setData('solution_es', e.target.value)}
                                            placeholder="La solucion proporcionada"
                                            rows={4}
                                        />
                                    </Field>
                                </div>

                                <Divider />

                                <div className={styles.section}>
                                    <div
                                        style={{
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            alignItems: 'center',
                                        }}
                                    >
                                        <Text weight="semibold">Results</Text>
                                        <Button
                                            appearance="subtle"
                                            icon={<Add24Regular />}
                                            onClick={addResult}
                                        >
                                            Add Result
                                        </Button>
                                    </div>
                                    {data.results.map((result, index) => (
                                        <div key={index} className={styles.itemRow}>
                                            <div className={styles.itemFields}>
                                                <Input
                                                    placeholder="Result (EN)"
                                                    value={result.result}
                                                    onChange={(e) =>
                                                        updateResult(
                                                            index,
                                                            'result',
                                                            e.target.value
                                                        )
                                                    }
                                                />
                                                <Input
                                                    placeholder="Result (ES)"
                                                    value={result.result_es || ''}
                                                    onChange={(e) =>
                                                        updateResult(
                                                            index,
                                                            'result_es',
                                                            e.target.value
                                                        )
                                                    }
                                                />
                                            </div>
                                            <Button
                                                appearance="subtle"
                                                icon={<Delete24Regular />}
                                                onClick={() => removeResult(index)}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </>
                        )}

                        {/* Stats & Features Tab */}
                        {activeTab === 'stats' && (
                            <>
                                <div className={styles.section}>
                                    <div
                                        style={{
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            alignItems: 'center',
                                        }}
                                    >
                                        <Text weight="semibold">Stats</Text>
                                        <Button
                                            appearance="subtle"
                                            icon={<Add24Regular />}
                                            onClick={addStat}
                                        >
                                            Add Stat
                                        </Button>
                                    </div>
                                    {data.stats.map((stat, index) => (
                                        <div key={index} className={styles.itemRow}>
                                            <div className={styles.itemFields}>
                                                <div className={styles.row}>
                                                    <Input
                                                        placeholder="Label (EN)"
                                                        value={stat.label}
                                                        onChange={(e) =>
                                                            updateStat(
                                                                index,
                                                                'label',
                                                                e.target.value
                                                            )
                                                        }
                                                    />
                                                    <Input
                                                        placeholder="Label (ES)"
                                                        value={stat.label_es || ''}
                                                        onChange={(e) =>
                                                            updateStat(
                                                                index,
                                                                'label_es',
                                                                e.target.value
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <Input
                                                    placeholder="Value (e.g., +245%)"
                                                    value={stat.value}
                                                    onChange={(e) =>
                                                        updateStat(index, 'value', e.target.value)
                                                    }
                                                />
                                            </div>
                                            <Button
                                                appearance="subtle"
                                                icon={<Delete24Regular />}
                                                onClick={() => removeStat(index)}
                                            />
                                        </div>
                                    ))}
                                </div>

                                <Divider />

                                <div className={styles.section}>
                                    <div
                                        style={{
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            alignItems: 'center',
                                        }}
                                    >
                                        <Text weight="semibold">Features</Text>
                                        <Button
                                            appearance="subtle"
                                            icon={<Add24Regular />}
                                            onClick={addFeature}
                                        >
                                            Add Feature
                                        </Button>
                                    </div>
                                    {data.features.map((feature, index) => (
                                        <div key={index} className={styles.itemRow}>
                                            <div className={styles.itemFields}>
                                                <div className={styles.row}>
                                                    <Input
                                                        placeholder="Number (e.g., 01)"
                                                        value={feature.number}
                                                        onChange={(e) =>
                                                            updateFeature(
                                                                index,
                                                                'number',
                                                                e.target.value
                                                            )
                                                        }
                                                        style={{ maxWidth: '80px' }}
                                                    />
                                                    <IconPicker
                                                        value={feature.icon || ''}
                                                        onChange={(iconName) =>
                                                            updateFeature(index, 'icon', iconName)
                                                        }
                                                        placeholder="Select icon"
                                                    />
                                                </div>
                                                <div className={styles.row}>
                                                    <Input
                                                        placeholder="Title (EN)"
                                                        value={feature.title}
                                                        onChange={(e) =>
                                                            updateFeature(
                                                                index,
                                                                'title',
                                                                e.target.value
                                                            )
                                                        }
                                                    />
                                                    <Input
                                                        placeholder="Title (ES)"
                                                        value={feature.title_es || ''}
                                                        onChange={(e) =>
                                                            updateFeature(
                                                                index,
                                                                'title_es',
                                                                e.target.value
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className={styles.row}>
                                                    <Textarea
                                                        placeholder="Description (EN)"
                                                        value={feature.description || ''}
                                                        onChange={(e) =>
                                                            updateFeature(
                                                                index,
                                                                'description',
                                                                e.target.value
                                                            )
                                                        }
                                                        rows={2}
                                                    />
                                                    <Textarea
                                                        placeholder="Description (ES)"
                                                        value={feature.description_es || ''}
                                                        onChange={(e) =>
                                                            updateFeature(
                                                                index,
                                                                'description_es',
                                                                e.target.value
                                                            )
                                                        }
                                                        rows={2}
                                                    />
                                                </div>
                                            </div>
                                            <Button
                                                appearance="subtle"
                                                icon={<Delete24Regular />}
                                                onClick={() => removeFeature(index)}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </>
                        )}

                        {/* Testimonial Tab */}
                        {activeTab === 'testimonial' && (
                            <>
                                <div className={styles.row}>
                                    <Field label="Quote (EN)">
                                        <Textarea
                                            value={data.testimonial_quote}
                                            onChange={(e) =>
                                                setData('testimonial_quote', e.target.value)
                                            }
                                            placeholder="Client testimonial quote"
                                            rows={4}
                                        />
                                    </Field>
                                    <Field label="Quote (ES)">
                                        <Textarea
                                            value={data.testimonial_quote_es}
                                            onChange={(e) =>
                                                setData('testimonial_quote_es', e.target.value)
                                            }
                                            placeholder="Cita del testimonio del cliente"
                                            rows={4}
                                        />
                                    </Field>
                                </div>

                                <Field label="Author Name">
                                    <Input
                                        value={data.testimonial_author}
                                        onChange={(e) =>
                                            setData('testimonial_author', e.target.value)
                                        }
                                        placeholder="John Doe"
                                    />
                                </Field>

                                <div className={styles.row}>
                                    <Field label="Role (EN)">
                                        <Input
                                            value={data.testimonial_role}
                                            onChange={(e) =>
                                                setData('testimonial_role', e.target.value)
                                            }
                                            placeholder="CEO, Company Name"
                                        />
                                    </Field>
                                    <Field label="Role (ES)">
                                        <Input
                                            value={data.testimonial_role_es}
                                            onChange={(e) =>
                                                setData('testimonial_role_es', e.target.value)
                                            }
                                            placeholder="CEO, Nombre de la empresa"
                                        />
                                    </Field>
                                </div>

                                <Field label="Author Avatar">
                                    <ImagePicker
                                        value={data.testimonial_avatar}
                                        onChange={(file) => setData('testimonial_avatar', file)}
                                        placeholder="Drop avatar image here or click to upload"
                                    />
                                </Field>
                            </>
                        )}

                        {/* Video Tab */}
                        {activeTab === 'video' && (
                            <>
                                <Field label="Video URL">
                                    <Input
                                        type="url"
                                        value={data.video_url}
                                        onChange={(e) => setData('video_url', e.target.value)}
                                        placeholder="https://youtube.com/watch?v=..."
                                    />
                                </Field>

                                <Field label="Video Thumbnail">
                                    <ImagePicker
                                        value={data.video_thumbnail}
                                        onChange={(file) => setData('video_thumbnail', file)}
                                        placeholder="Drop video thumbnail here or click to upload"
                                    />
                                </Field>

                                <Divider />

                                <div className={styles.section}>
                                    <div
                                        style={{
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            alignItems: 'center',
                                        }}
                                    >
                                        <Text weight="semibold">Video Features</Text>
                                        <Button
                                            appearance="subtle"
                                            icon={<Add24Regular />}
                                            onClick={addVideoFeature}
                                        >
                                            Add Feature
                                        </Button>
                                    </div>
                                    {data.video_features.map((vf, index) => (
                                        <div key={index} className={styles.itemRow}>
                                            <div className={styles.itemFields}>
                                                <div className={styles.row}>
                                                    <Input
                                                        placeholder="Title (EN)"
                                                        value={vf.title}
                                                        onChange={(e) =>
                                                            updateVideoFeature(
                                                                index,
                                                                'title',
                                                                e.target.value
                                                            )
                                                        }
                                                    />
                                                    <Input
                                                        placeholder="Title (ES)"
                                                        value={vf.title_es || ''}
                                                        onChange={(e) =>
                                                            updateVideoFeature(
                                                                index,
                                                                'title_es',
                                                                e.target.value
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className={styles.row}>
                                                    <Textarea
                                                        placeholder="Description (EN)"
                                                        value={vf.description || ''}
                                                        onChange={(e) =>
                                                            updateVideoFeature(
                                                                index,
                                                                'description',
                                                                e.target.value
                                                            )
                                                        }
                                                        rows={2}
                                                    />
                                                    <Textarea
                                                        placeholder="Description (ES)"
                                                        value={vf.description_es || ''}
                                                        onChange={(e) =>
                                                            updateVideoFeature(
                                                                index,
                                                                'description_es',
                                                                e.target.value
                                                            )
                                                        }
                                                        rows={2}
                                                    />
                                                </div>
                                            </div>
                                            <Button
                                                appearance="subtle"
                                                icon={<Delete24Regular />}
                                                onClick={() => removeVideoFeature(index)}
                                            />
                                        </div>
                                    ))}
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
