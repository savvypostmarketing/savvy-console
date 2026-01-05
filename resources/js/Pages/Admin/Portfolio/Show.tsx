import { useCallback } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Card,
    Text,
    Badge,
    Button,
    makeStyles,
    shorthands,
    tokens,
    Divider,
} from '@fluentui/react-components';
import {
    ArrowLeft24Regular,
    Edit24Regular,
    Delete24Regular,
    Globe24Regular,
    Star24Filled,
} from '@fluentui/react-icons';
import AdminLayout from '@/Layouts/AdminLayout';
import { usePermissions } from '@/hooks';
import type { Portfolio } from '@/interfaces';

// Styles
const useStyles = makeStyles({
    header: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: '24px',
    },
    headerLeft: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('16px'),
    },
    headerRight: {
        display: 'flex',
        ...shorthands.gap('12px'),
    },
    card: {
        ...shorthands.padding('24px'),
        marginBottom: '24px',
    },
    section: {
        marginBottom: '24px',
    },
    sectionTitle: {
        marginBottom: '16px',
    },
    grid: {
        display: 'grid',
        gridTemplateColumns: '1fr 1fr',
        ...shorthands.gap('24px'),
        '@media (max-width: 768px)': {
            gridTemplateColumns: '1fr',
        },
    },
    featuredImage: {
        width: '100%',
        maxWidth: '400px',
        height: 'auto',
        ...shorthands.borderRadius('8px'),
        marginBottom: '16px',
    },
    statsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))',
        ...shorthands.gap('16px'),
    },
    statCard: {
        ...shorthands.padding('16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
        textAlign: 'center',
    },
    featureItem: {
        ...shorthands.padding('16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
        marginBottom: '12px',
    },
    resultItem: {
        ...shorthands.padding('12px', '16px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
        marginBottom: '8px',
    },
    serviceBadge: {
        marginRight: '8px',
        marginBottom: '8px',
    },
    label: {
        color: tokens.colorNeutralForeground3,
        fontSize: '12px',
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
        marginBottom: '4px',
    },
    testimonialCard: {
        ...shorthands.padding('24px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('12px'),
        position: 'relative',
    },
    quoteIcon: {
        fontSize: '48px',
        color: tokens.colorBrandForeground1,
        opacity: 0.3,
        position: 'absolute',
        top: '16px',
        left: '16px',
    },
    galleryGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))',
        ...shorthands.gap('12px'),
    },
    galleryImage: {
        width: '100%',
        height: '100px',
        objectFit: 'cover',
        ...shorthands.borderRadius('8px'),
    },
});

// Types
interface ShowPortfolioProps {
    portfolio: Portfolio;
}

export default function ShowPortfolio({ portfolio }: ShowPortfolioProps) {
    const styles = useStyles();
    const { checkPermission } = usePermissions();

    const canEdit = checkPermission('edit-portfolio');
    const canDelete = checkPermission('delete-portfolio');

    const handleDelete = useCallback(() => {
        if (confirm('Are you sure you want to delete this portfolio?')) {
            router.delete(`/admin/portfolio/${portfolio.id}`);
        }
    }, [portfolio.id]);

    const handleBack = useCallback(() => {
        router.get('/admin/portfolio');
    }, []);

    return (
        <AdminLayout title={portfolio.title}>
            <Head title={portfolio.title} />

            <div className={styles.header}>
                <div className={styles.headerLeft}>
                    <Button appearance="subtle" icon={<ArrowLeft24Regular />} onClick={handleBack}>
                        Back
                    </Button>
                    <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            <Text size={600} weight="semibold">
                                {portfolio.title}
                            </Text>
                            {portfolio.is_featured && (
                                <Star24Filled
                                    style={{ color: tokens.colorPaletteYellowForeground1 }}
                                />
                            )}
                        </div>
                        {portfolio.title_es && (
                            <Text
                                size={300}
                                style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                            >
                                ES: {portfolio.title_es}
                            </Text>
                        )}
                    </div>
                </div>
                <div className={styles.headerRight}>
                    <Badge color={portfolio.is_published ? 'success' : 'warning'} size="large">
                        {portfolio.is_published ? 'Published' : 'Draft'}
                    </Badge>
                    {portfolio.website_url && (
                        <a href={portfolio.website_url} target="_blank" rel="noopener noreferrer">
                            <Button appearance="subtle" icon={<Globe24Regular />}>
                                Visit Site
                            </Button>
                        </a>
                    )}
                    {canEdit && (
                        <Link href={`/admin/portfolio/${portfolio.id}/edit`}>
                            <Button appearance="primary" icon={<Edit24Regular />}>
                                Edit
                            </Button>
                        </Link>
                    )}
                    {canDelete && (
                        <Button
                            appearance="subtle"
                            icon={<Delete24Regular />}
                            onClick={handleDelete}
                        />
                    )}
                </div>
            </div>

            {/* Basic Info */}
            <Card className={styles.card}>
                <div className={styles.grid}>
                    <div>
                        {portfolio.featured_image && (
                            <img
                                src={`/storage/${portfolio.featured_image}`}
                                alt={portfolio.title}
                                className={styles.featuredImage}
                            />
                        )}

                        <div className={styles.section}>
                            <Text className={styles.label}>Industry</Text>
                            {portfolio.industry && (
                                <Badge appearance="outline" size="large">
                                    {portfolio.industry.name}
                                </Badge>
                            )}
                        </div>

                        <div className={styles.section}>
                            <Text className={styles.label}>Services</Text>
                            <div>
                                {portfolio.services.map((service) => (
                                    <Badge
                                        key={service.id}
                                        className={styles.serviceBadge}
                                        style={{
                                            backgroundColor: service.color,
                                            color: 'white',
                                        }}
                                    >
                                        {service.name}
                                    </Badge>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div>
                        {portfolio.description && (
                            <div className={styles.section}>
                                <Text className={styles.label}>Description (EN)</Text>
                                <Text>{portfolio.description}</Text>
                            </div>
                        )}
                        {portfolio.description_es && (
                            <div className={styles.section}>
                                <Text className={styles.label}>Description (ES)</Text>
                                <Text>{portfolio.description_es}</Text>
                            </div>
                        )}
                    </div>
                </div>
            </Card>

            {/* Challenge & Solution */}
            {(portfolio.challenge || portfolio.solution) && (
                <Card className={styles.card}>
                    <div className={styles.grid}>
                        {portfolio.challenge && (
                            <div>
                                <Text weight="semibold" className={styles.sectionTitle}>
                                    Challenge
                                </Text>
                                <Text>{portfolio.challenge}</Text>
                                {portfolio.challenge_es && (
                                    <>
                                        <Text
                                            className={styles.label}
                                            style={{ marginTop: '12px', display: 'block' }}
                                        >
                                            ES:
                                        </Text>
                                        <Text>{portfolio.challenge_es}</Text>
                                    </>
                                )}
                            </div>
                        )}
                        {portfolio.solution && (
                            <div>
                                <Text weight="semibold" className={styles.sectionTitle}>
                                    Solution
                                </Text>
                                <Text>{portfolio.solution}</Text>
                                {portfolio.solution_es && (
                                    <>
                                        <Text
                                            className={styles.label}
                                            style={{ marginTop: '12px', display: 'block' }}
                                        >
                                            ES:
                                        </Text>
                                        <Text>{portfolio.solution_es}</Text>
                                    </>
                                )}
                            </div>
                        )}
                    </div>
                </Card>
            )}

            {/* Stats */}
            {portfolio.stats.length > 0 && (
                <Card className={styles.card}>
                    <Text weight="semibold" className={styles.sectionTitle}>
                        Stats
                    </Text>
                    <div className={styles.statsGrid}>
                        {portfolio.stats.map((stat) => (
                            <div key={stat.id} className={styles.statCard}>
                                <Text size={600} weight="bold">
                                    {stat.value}
                                </Text>
                                <Text
                                    size={200}
                                    style={{
                                        display: 'block',
                                        color: tokens.colorNeutralForeground3,
                                    }}
                                >
                                    {stat.label}
                                </Text>
                                {stat.label_es && (
                                    <Text
                                        size={100}
                                        style={{
                                            display: 'block',
                                            color: tokens.colorNeutralForeground4,
                                        }}
                                    >
                                        ES: {stat.label_es}
                                    </Text>
                                )}
                            </div>
                        ))}
                    </div>
                </Card>
            )}

            {/* Features */}
            {portfolio.features.length > 0 && (
                <Card className={styles.card}>
                    <Text weight="semibold" className={styles.sectionTitle}>
                        Features
                    </Text>
                    {portfolio.features.map((feature) => (
                        <div key={feature.id} className={styles.featureItem}>
                            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '16px' }}>
                                <Badge appearance="filled" color="brand" size="large">
                                    {feature.number}
                                </Badge>
                                <div>
                                    <Text weight="semibold">{feature.title}</Text>
                                    {feature.title_es && (
                                        <Text
                                            size={200}
                                            style={{
                                                display: 'block',
                                                color: tokens.colorNeutralForeground3,
                                            }}
                                        >
                                            ES: {feature.title_es}
                                        </Text>
                                    )}
                                    {feature.description && (
                                        <Text style={{ marginTop: '8px', display: 'block' }}>
                                            {feature.description}
                                        </Text>
                                    )}
                                    {feature.description_es && (
                                        <Text
                                            size={200}
                                            style={{
                                                display: 'block',
                                                color: tokens.colorNeutralForeground3,
                                                marginTop: '4px',
                                            }}
                                        >
                                            ES: {feature.description_es}
                                        </Text>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </Card>
            )}

            {/* Results */}
            {portfolio.results.length > 0 && (
                <Card className={styles.card}>
                    <Text weight="semibold" className={styles.sectionTitle}>
                        Results
                    </Text>
                    {portfolio.results.map((result) => (
                        <div key={result.id} className={styles.resultItem}>
                            <Text>{result.result}</Text>
                            {result.result_es && (
                                <Text
                                    size={200}
                                    style={{
                                        display: 'block',
                                        color: tokens.colorNeutralForeground3,
                                        marginTop: '4px',
                                    }}
                                >
                                    ES: {result.result_es}
                                </Text>
                            )}
                        </div>
                    ))}
                </Card>
            )}

            {/* Testimonial */}
            {portfolio.testimonial_quote && (
                <Card className={styles.card}>
                    <Text weight="semibold" className={styles.sectionTitle}>
                        Testimonial
                    </Text>
                    <div className={styles.testimonialCard}>
                        <Text size={500} style={{ fontStyle: 'italic', display: 'block' }}>
                            "{portfolio.testimonial_quote}"
                        </Text>
                        {portfolio.testimonial_quote_es && (
                            <Text
                                size={300}
                                style={{
                                    fontStyle: 'italic',
                                    display: 'block',
                                    color: tokens.colorNeutralForeground3,
                                    marginTop: '8px',
                                }}
                            >
                                ES: "{portfolio.testimonial_quote_es}"
                            </Text>
                        )}
                        <Divider style={{ margin: '16px 0' }} />
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            {portfolio.testimonial_avatar && (
                                <img
                                    src={`/storage/${portfolio.testimonial_avatar}`}
                                    alt={portfolio.testimonial_author || 'Author'}
                                    style={{
                                        width: '48px',
                                        height: '48px',
                                        borderRadius: '50%',
                                        objectFit: 'cover',
                                    }}
                                />
                            )}
                            <div>
                                <Text weight="semibold">{portfolio.testimonial_author}</Text>
                                {portfolio.testimonial_role && (
                                    <Text
                                        size={200}
                                        style={{
                                            display: 'block',
                                            color: tokens.colorNeutralForeground3,
                                        }}
                                    >
                                        {portfolio.testimonial_role}
                                    </Text>
                                )}
                            </div>
                        </div>
                    </div>
                </Card>
            )}

            {/* Gallery */}
            {portfolio.gallery.length > 0 && (
                <Card className={styles.card}>
                    <Text weight="semibold" className={styles.sectionTitle}>
                        Gallery ({portfolio.gallery.length} images)
                    </Text>
                    <div className={styles.galleryGrid}>
                        {portfolio.gallery.map((image) => (
                            <img
                                key={image.id}
                                src={`/storage/${image.image_path}`}
                                alt={image.alt_text || 'Gallery image'}
                                className={styles.galleryImage}
                            />
                        ))}
                    </div>
                </Card>
            )}

            {/* Video Features */}
            {portfolio.video_features.length > 0 && (
                <Card className={styles.card}>
                    <Text weight="semibold" className={styles.sectionTitle}>
                        Video Features
                    </Text>
                    {portfolio.video_url && (
                        <div style={{ marginBottom: '16px' }}>
                            <Text className={styles.label}>Video URL</Text>
                            <a href={portfolio.video_url} target="_blank" rel="noopener noreferrer">
                                {portfolio.video_url}
                            </a>
                        </div>
                    )}
                    {portfolio.video_features.map((vf) => (
                        <div key={vf.id} className={styles.featureItem}>
                            <Text weight="semibold">{vf.title}</Text>
                            {vf.title_es && (
                                <Text
                                    size={200}
                                    style={{
                                        display: 'block',
                                        color: tokens.colorNeutralForeground3,
                                    }}
                                >
                                    ES: {vf.title_es}
                                </Text>
                            )}
                            {vf.description && (
                                <Text style={{ marginTop: '8px', display: 'block' }}>
                                    {vf.description}
                                </Text>
                            )}
                            {vf.description_es && (
                                <Text
                                    size={200}
                                    style={{
                                        display: 'block',
                                        color: tokens.colorNeutralForeground3,
                                        marginTop: '4px',
                                    }}
                                >
                                    ES: {vf.description_es}
                                </Text>
                            )}
                        </div>
                    ))}
                </Card>
            )}

            {/* Metadata */}
            <Card className={styles.card}>
                <Text weight="semibold" className={styles.sectionTitle}>
                    Metadata
                </Text>
                <div className={styles.grid}>
                    <div>
                        <Text className={styles.label}>Slug</Text>
                        <Text>{portfolio.slug}</Text>
                    </div>
                    <div>
                        <Text className={styles.label}>Sort Order</Text>
                        <Text>{portfolio.sort_order}</Text>
                    </div>
                    <div>
                        <Text className={styles.label}>Created</Text>
                        <Text>{portfolio.created_at}</Text>
                    </div>
                    <div>
                        <Text className={styles.label}>Updated</Text>
                        <Text>{portfolio.updated_at}</Text>
                    </div>
                </div>
            </Card>
        </AdminLayout>
    );
}
