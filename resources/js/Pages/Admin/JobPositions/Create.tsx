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
    Dropdown,
    Option,
    makeStyles,
    shorthands,
    tokens,
    SpinButton,
} from '@fluentui/react-components';
import { ArrowLeft24Regular, Save24Regular, Checkmark24Regular } from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';

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
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
});

interface CreateJobPositionProps {
    employmentTypes: Record<string, string>;
    locationTypes: Record<string, string>;
}

interface JobPositionFormData {
    title: string;
    title_es: string;
    department: string;
    employment_type: string;
    location_type: string;
    location: string;
    description: string;
    description_es: string;
    linkedin_url: string;
    apply_url: string;
    salary_range: string;
    is_active: boolean;
    is_featured: boolean;
    sort_order: number;
}

export default function CreateJobPosition({
    employmentTypes,
    locationTypes,
}: CreateJobPositionProps) {
    const styles = useStyles();

    const { data, setData, post, processing, errors } = useForm<JobPositionFormData>({
        title: '',
        title_es: '',
        department: '',
        employment_type: 'full-time',
        location_type: 'remote',
        location: '',
        description: '',
        description_es: '',
        linkedin_url: '',
        apply_url: '',
        salary_range: '',
        is_active: true,
        is_featured: false,
        sort_order: 0,
    });

    const handleBack = useCallback(() => {
        router.get('/admin/job-positions');
    }, []);

    const handleSubmit = useCallback(
        (e: FormEvent, redirectAfter = false) => {
            e.preventDefault();
            post('/admin/job-positions', {
                onSuccess: () => {
                    if (redirectAfter) {
                        router.get('/admin/job-positions');
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
        <AdminLayout title="Create Job Position">
            <Head title="Create Job Position" />

            <div className={styles.header}>
                <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                    Back
                </Button>
                <Text size={600} weight="semibold">
                    Create Job Position
                </Text>
            </div>

            <Card className={styles.card}>
                <form onSubmit={handleSubmit} className={styles.form}>
                    {/* Basic Info Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Basic Information
                        </Text>

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
                                    placeholder="Software Developer"
                                />
                            </Field>
                            <Field label="Title (ES)">
                                <Input
                                    value={data.title_es}
                                    onChange={(e) => setData('title_es', e.target.value)}
                                    placeholder="Desarrollador de Software"
                                />
                            </Field>
                        </div>

                        <Field label="Department" style={{ marginTop: '16px' }}>
                            <Input
                                value={data.department}
                                onChange={(e) => setData('department', e.target.value)}
                                placeholder="Engineering, Marketing, Design..."
                            />
                        </Field>
                    </div>

                    {/* Employment Details Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Employment Details
                        </Text>

                        <div className={styles.row}>
                            <Field
                                label="Employment Type"
                                required
                                validationMessage={errors.employment_type}
                                validationState={errors.employment_type ? 'error' : 'none'}
                            >
                                <Dropdown
                                    value={employmentTypes[data.employment_type] || ''}
                                    onOptionSelect={(_, opt) =>
                                        setData('employment_type', opt.optionValue as string)
                                    }
                                >
                                    {Object.entries(employmentTypes).map(([value, label]) => (
                                        <Option key={value} value={value}>
                                            {label}
                                        </Option>
                                    ))}
                                </Dropdown>
                            </Field>
                            <Field
                                label="Location Type"
                                required
                                validationMessage={errors.location_type}
                                validationState={errors.location_type ? 'error' : 'none'}
                            >
                                <Dropdown
                                    value={locationTypes[data.location_type] || ''}
                                    onOptionSelect={(_, opt) =>
                                        setData('location_type', opt.optionValue as string)
                                    }
                                >
                                    {Object.entries(locationTypes).map(([value, label]) => (
                                        <Option key={value} value={value}>
                                            {label}
                                        </Option>
                                    ))}
                                </Dropdown>
                            </Field>
                        </div>

                        <div className={styles.row} style={{ marginTop: '16px' }}>
                            <Field label="Location (City/Country)">
                                <Input
                                    value={data.location}
                                    onChange={(e) => setData('location', e.target.value)}
                                    placeholder="New York, USA"
                                />
                            </Field>
                            <Field label="Salary Range">
                                <Input
                                    value={data.salary_range}
                                    onChange={(e) => setData('salary_range', e.target.value)}
                                    placeholder="$50,000 - $70,000"
                                />
                            </Field>
                        </div>
                    </div>

                    {/* Description Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Description (Optional)
                        </Text>

                        <div className={styles.row}>
                            <Field label="Description (EN)">
                                <Textarea
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Job description in English..."
                                    rows={4}
                                />
                            </Field>
                            <Field label="Description (ES)">
                                <Textarea
                                    value={data.description_es}
                                    onChange={(e) => setData('description_es', e.target.value)}
                                    placeholder="Descripción del puesto en Español..."
                                    rows={4}
                                />
                            </Field>
                        </div>
                    </div>

                    {/* Apply Links Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Application Links
                        </Text>

                        <div className={styles.row}>
                            <Field
                                label="LinkedIn Job URL"
                                hint="Link to the LinkedIn job posting"
                                validationMessage={errors.linkedin_url}
                                validationState={errors.linkedin_url ? 'error' : 'none'}
                            >
                                <Input
                                    type="url"
                                    value={data.linkedin_url}
                                    onChange={(e) => setData('linkedin_url', e.target.value)}
                                    placeholder="https://www.linkedin.com/jobs/view/..."
                                />
                            </Field>
                            <Field
                                label="Alternative Apply URL"
                                hint="Used if LinkedIn URL is not provided"
                                validationMessage={errors.apply_url}
                                validationState={errors.apply_url ? 'error' : 'none'}
                            >
                                <Input
                                    type="url"
                                    value={data.apply_url}
                                    onChange={(e) => setData('apply_url', e.target.value)}
                                    placeholder="https://yoursite.com/careers/apply"
                                />
                            </Field>
                        </div>
                    </div>

                    {/* Display Settings Section */}
                    <div className={styles.section}>
                        <Text
                            weight="semibold"
                            className={styles.sectionTitle}
                            style={{ display: 'block' }}
                        >
                            Display Settings
                        </Text>

                        <div className={styles.row}>
                            <Field label="Sort Order">
                                <SpinButton
                                    value={data.sort_order}
                                    onChange={(_, d) => setData('sort_order', d.value || 0)}
                                    min={0}
                                />
                            </Field>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                <Switch
                                    checked={data.is_active}
                                    onChange={(_, d) => setData('is_active', d.checked)}
                                    label="Active (visible on website)"
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
                            {processing ? 'Creating...' : 'Create & Exit'}
                        </Button>
                        <Button
                            appearance="secondary"
                            icon={<Save24Regular />}
                            onClick={handleSave}
                            disabled={processing}
                        >
                            Create & Add Another
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
