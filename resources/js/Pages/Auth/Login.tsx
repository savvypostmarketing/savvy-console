import { useCallback, FormEvent } from 'react';
import { useForm, Head } from '@inertiajs/react';
import {
    Card,
    Text,
    Input,
    Button,
    Field,
    Checkbox,
    makeStyles,
    shorthands,
    tokens,
} from '@fluentui/react-components';

// Styles
const useStyles = makeStyles({
    root: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        minHeight: '100vh',
        backgroundColor: tokens.colorNeutralBackground2,
        ...shorthands.padding('20px'),
    },
    card: {
        width: '100%',
        maxWidth: '400px',
        ...shorthands.padding('32px'),
    },
    logo: {
        textAlign: 'center',
        marginBottom: '24px',
    },
    logoText: {
        fontSize: '28px',
        fontWeight: 700,
        color: tokens.colorBrandForeground1,
    },
    form: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('20px'),
    },
});

// Types
interface LoginFormData {
    email: string;
    password: string;
    remember: boolean;
}

export default function Login() {
    const styles = useStyles();
    const { data, setData, post, processing, errors } = useForm<LoginFormData>({
        email: '',
        password: '',
        remember: false,
    });

    // Handlers
    const handleSubmit = useCallback(
        (e: FormEvent) => {
            e.preventDefault();
            post('/login');
        },
        [post]
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

    const handleRememberChange = useCallback(
        (_: unknown, { checked }: { checked: boolean | 'mixed' }) => {
            setData('remember', Boolean(checked));
        },
        [setData]
    );

    return (
        <>
            <Head title="Login" />
            <div className={styles.root}>
                <Card className={styles.card}>
                    <div className={styles.logo}>
                        <Text className={styles.logoText}>Savvy Admin</Text>
                        <Text
                            size={300}
                            style={{
                                display: 'block',
                                marginTop: '8px',
                                color: tokens.colorNeutralForeground3,
                            }}
                        >
                            Sign in to your account
                        </Text>
                    </div>

                    <form onSubmit={handleSubmit} className={styles.form}>
                        <Field
                            label="Email"
                            validationMessage={errors.email}
                            validationState={errors.email ? 'error' : 'none'}
                        >
                            <Input
                                type="email"
                                value={data.email}
                                onChange={handleEmailChange}
                                placeholder="admin@savvy.com"
                                size="large"
                            />
                        </Field>

                        <Field
                            label="Password"
                            validationMessage={errors.password}
                            validationState={errors.password ? 'error' : 'none'}
                        >
                            <Input
                                type="password"
                                value={data.password}
                                onChange={handlePasswordChange}
                                placeholder="Enter your password"
                                size="large"
                            />
                        </Field>

                        <Checkbox
                            checked={data.remember}
                            onChange={handleRememberChange}
                            label="Remember me"
                        />

                        <Button
                            appearance="primary"
                            type="submit"
                            disabled={processing}
                            size="large"
                        >
                            {processing ? 'Signing in...' : 'Sign in'}
                        </Button>
                    </form>
                </Card>
            </div>
        </>
    );
}
