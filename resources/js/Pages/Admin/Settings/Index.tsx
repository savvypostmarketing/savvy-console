import { useCallback, useState, FormEvent } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    Card,
    Text,
    Button,
    Input,
    Field,
    Switch,
    makeStyles,
    shorthands,
    tokens,
    Tab,
    TabList,
    Divider,
    MessageBar,
    MessageBarBody,
    MessageBarTitle,
    type SelectTabData,
    type SelectTabEvent,
} from '@fluentui/react-components';
import {
    Globe24Regular,
    Mail24Regular,
    Send24Regular,
    Save24Regular,
    Settings24Regular,
} from '@fluentui/react-icons';
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
        ...shorthands.padding('20px'),
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.borderRadius('8px'),
    },
    sectionHeader: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
        marginBottom: '16px',
    },
    actions: {
        display: 'flex',
        ...shorthands.gap('12px'),
        marginTop: '8px',
    },
    tabContent: {
        marginTop: '24px',
    },
    infoText: {
        color: tokens.colorNeutralForeground3,
        fontSize: '12px',
        marginTop: '4px',
    },
});

interface ApiSettings {
    frontend_url_production?: string;
    frontend_url_development?: string;
    api_url_production?: string;
    api_url_development?: string;
    cors_allowed_origins?: string;
}

interface EmailSettings {
    resend_api_key?: string;
    email_from_address?: string;
    email_from_name?: string;
    email_reply_to?: string;
    notification_email?: string;
    email_enabled?: boolean;
}

interface SettingsIndexProps {
    apiSettings?: ApiSettings;
    emailSettings?: EmailSettings;
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function SettingsIndex({
    apiSettings = {},
    emailSettings = {},
    flash,
}: SettingsIndexProps) {
    const styles = useStyles();
    const [activeTab, setActiveTab] = useState('api');

    // API Settings Form
    const apiForm = useForm<ApiSettings>({
        frontend_url_production: apiSettings.frontend_url_production || '',
        frontend_url_development: apiSettings.frontend_url_development || '',
        api_url_production: apiSettings.api_url_production || '',
        api_url_development: apiSettings.api_url_development || '',
        cors_allowed_origins: apiSettings.cors_allowed_origins || '',
    });

    // Email Settings Form
    const emailForm = useForm<EmailSettings>({
        resend_api_key: emailSettings.resend_api_key || '',
        email_from_address: emailSettings.email_from_address || '',
        email_from_name: emailSettings.email_from_name || '',
        email_reply_to: emailSettings.email_reply_to || '',
        notification_email: emailSettings.notification_email || '',
        email_enabled: emailSettings.email_enabled || false,
    });

    const [testEmail, setTestEmail] = useState('');

    // Handlers
    const handleTabChange = useCallback((_: SelectTabEvent, data: SelectTabData) => {
        setActiveTab(data.value as string);
    }, []);

    const handleSaveApi = useCallback(
        (e: FormEvent) => {
            e.preventDefault();
            apiForm.post('/admin/settings/api');
        },
        [apiForm]
    );

    const handleSaveEmail = useCallback(
        (e: FormEvent) => {
            e.preventDefault();
            emailForm.post('/admin/settings/email');
        },
        [emailForm]
    );

    const handleTestEmail = useCallback(() => {
        if (!testEmail) {
            return;
        }
        router.post('/admin/settings/email/test', { test_email: testEmail });
    }, [testEmail]);

    return (
        <AdminLayout title="Settings">
            <Head title="Settings" />

            <div className={styles.header}>
                <Settings24Regular style={{ fontSize: '28px' }} />
                <div>
                    <Text size={600} weight="semibold">
                        Settings
                    </Text>
                    <Text
                        size={300}
                        style={{ display: 'block', color: tokens.colorNeutralForeground3 }}
                    >
                        Configure application settings and integrations
                    </Text>
                </div>
            </div>

            {flash?.success && (
                <MessageBar intent="success" style={{ marginBottom: '16px' }}>
                    <MessageBarBody>
                        <MessageBarTitle>Success</MessageBarTitle>
                        {flash.success}
                    </MessageBarBody>
                </MessageBar>
            )}

            {flash?.error && (
                <MessageBar intent="error" style={{ marginBottom: '16px' }}>
                    <MessageBarBody>
                        <MessageBarTitle>Error</MessageBarTitle>
                        {flash.error}
                    </MessageBarBody>
                </MessageBar>
            )}

            <Card className={styles.card}>
                <TabList selectedValue={activeTab} onTabSelect={handleTabChange}>
                    <Tab value="api" icon={<Globe24Regular />}>
                        API Configuration
                    </Tab>
                    <Tab value="email" icon={<Mail24Regular />}>
                        Email (Resend)
                    </Tab>
                </TabList>

                <div className={styles.tabContent}>
                    {/* API Configuration Tab */}
                    {activeTab === 'api' && (
                        <form onSubmit={handleSaveApi} className={styles.form}>
                            <div className={styles.section}>
                                <div className={styles.sectionHeader}>
                                    <Globe24Regular />
                                    <Text weight="semibold" size={400}>
                                        Frontend URLs
                                    </Text>
                                </div>
                                <div className={styles.row}>
                                    <Field
                                        label="Production URL"
                                        hint="The frontend URL for production environment"
                                    >
                                        <Input
                                            type="url"
                                            value={apiForm.data.frontend_url_production}
                                            onChange={(e) =>
                                                apiForm.setData(
                                                    'frontend_url_production',
                                                    e.target.value
                                                )
                                            }
                                            placeholder="https://example.com"
                                        />
                                    </Field>
                                    <Field
                                        label="Development URL"
                                        hint="The frontend URL for development environment"
                                    >
                                        <Input
                                            type="url"
                                            value={apiForm.data.frontend_url_development}
                                            onChange={(e) =>
                                                apiForm.setData(
                                                    'frontend_url_development',
                                                    e.target.value
                                                )
                                            }
                                            placeholder="http://localhost:3000"
                                        />
                                    </Field>
                                </div>
                            </div>

                            <div className={styles.section}>
                                <div className={styles.sectionHeader}>
                                    <Globe24Regular />
                                    <Text weight="semibold" size={400}>
                                        API URLs
                                    </Text>
                                </div>
                                <div className={styles.row}>
                                    <Field
                                        label="Production API URL"
                                        hint="The API URL for production environment"
                                    >
                                        <Input
                                            type="url"
                                            value={apiForm.data.api_url_production}
                                            onChange={(e) =>
                                                apiForm.setData(
                                                    'api_url_production',
                                                    e.target.value
                                                )
                                            }
                                            placeholder="https://api.example.com"
                                        />
                                    </Field>
                                    <Field
                                        label="Development API URL"
                                        hint="The API URL for development environment"
                                    >
                                        <Input
                                            type="url"
                                            value={apiForm.data.api_url_development}
                                            onChange={(e) =>
                                                apiForm.setData(
                                                    'api_url_development',
                                                    e.target.value
                                                )
                                            }
                                            placeholder="http://localhost:8000"
                                        />
                                    </Field>
                                </div>
                            </div>

                            <div className={styles.section}>
                                <div className={styles.sectionHeader}>
                                    <Globe24Regular />
                                    <Text weight="semibold" size={400}>
                                        CORS Configuration
                                    </Text>
                                </div>
                                <Field
                                    label="Allowed Origins"
                                    hint="Comma-separated list of allowed origins for CORS"
                                >
                                    <Input
                                        value={apiForm.data.cors_allowed_origins}
                                        onChange={(e) =>
                                            apiForm.setData('cors_allowed_origins', e.target.value)
                                        }
                                        placeholder="http://localhost:3000,https://example.com"
                                    />
                                </Field>
                            </div>

                            <div className={styles.actions}>
                                <Button
                                    appearance="primary"
                                    icon={<Save24Regular />}
                                    type="submit"
                                    disabled={apiForm.processing}
                                >
                                    {apiForm.processing ? 'Saving...' : 'Save API Settings'}
                                </Button>
                            </div>
                        </form>
                    )}

                    {/* Email Configuration Tab */}
                    {activeTab === 'email' && (
                        <form onSubmit={handleSaveEmail} className={styles.form}>
                            <div className={styles.section}>
                                <div className={styles.sectionHeader}>
                                    <Mail24Regular />
                                    <Text weight="semibold" size={400}>
                                        Resend Configuration
                                    </Text>
                                </div>

                                <Switch
                                    checked={emailForm.data.email_enabled}
                                    onChange={(_, d) =>
                                        emailForm.setData('email_enabled', d.checked)
                                    }
                                    label="Enable Email Sending"
                                    style={{ marginBottom: '16px' }}
                                />

                                <Field
                                    label="Resend API Key"
                                    hint="Your Resend API key for sending emails"
                                >
                                    <Input
                                        type="password"
                                        value={emailForm.data.resend_api_key}
                                        onChange={(e) =>
                                            emailForm.setData('resend_api_key', e.target.value)
                                        }
                                        placeholder="re_xxxxxxxxx"
                                    />
                                </Field>
                            </div>

                            <div className={styles.section}>
                                <div className={styles.sectionHeader}>
                                    <Mail24Regular />
                                    <Text weight="semibold" size={400}>
                                        Email Addresses
                                    </Text>
                                </div>
                                <div className={styles.row}>
                                    <Field
                                        label="From Address"
                                        hint="The email address emails will be sent from"
                                    >
                                        <Input
                                            type="email"
                                            value={emailForm.data.email_from_address}
                                            onChange={(e) =>
                                                emailForm.setData(
                                                    'email_from_address',
                                                    e.target.value
                                                )
                                            }
                                            placeholder="hello@example.com"
                                        />
                                    </Field>
                                    <Field
                                        label="From Name"
                                        hint="The display name for sent emails"
                                    >
                                        <Input
                                            value={emailForm.data.email_from_name}
                                            onChange={(e) =>
                                                emailForm.setData('email_from_name', e.target.value)
                                            }
                                            placeholder="Savvy Marketing"
                                        />
                                    </Field>
                                </div>
                                <div className={styles.row}>
                                    <Field
                                        label="Reply-To Address"
                                        hint="The email address for replies"
                                    >
                                        <Input
                                            type="email"
                                            value={emailForm.data.email_reply_to}
                                            onChange={(e) =>
                                                emailForm.setData('email_reply_to', e.target.value)
                                            }
                                            placeholder="reply@example.com"
                                        />
                                    </Field>
                                    <Field
                                        label="Notification Email"
                                        hint="Email for system notifications"
                                    >
                                        <Input
                                            type="email"
                                            value={emailForm.data.notification_email}
                                            onChange={(e) =>
                                                emailForm.setData(
                                                    'notification_email',
                                                    e.target.value
                                                )
                                            }
                                            placeholder="notifications@example.com"
                                        />
                                    </Field>
                                </div>
                            </div>

                            <Divider />

                            <div className={styles.section}>
                                <div className={styles.sectionHeader}>
                                    <Send24Regular />
                                    <Text weight="semibold" size={400}>
                                        Test Email
                                    </Text>
                                </div>
                                <div className={styles.row}>
                                    <Field
                                        label="Test Email Address"
                                        hint="Send a test email to verify configuration"
                                    >
                                        <Input
                                            type="email"
                                            value={testEmail}
                                            onChange={(e) => setTestEmail(e.target.value)}
                                            placeholder="test@example.com"
                                        />
                                    </Field>
                                    <div style={{ display: 'flex', alignItems: 'flex-end' }}>
                                        <Button
                                            appearance="secondary"
                                            icon={<Send24Regular />}
                                            onClick={handleTestEmail}
                                            disabled={!testEmail || !emailForm.data.email_enabled}
                                        >
                                            Send Test Email
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div className={styles.actions}>
                                <Button
                                    appearance="primary"
                                    icon={<Save24Regular />}
                                    type="submit"
                                    disabled={emailForm.processing}
                                >
                                    {emailForm.processing ? 'Saving...' : 'Save Email Settings'}
                                </Button>
                            </div>
                        </form>
                    )}
                </div>
            </Card>
        </AdminLayout>
    );
}
