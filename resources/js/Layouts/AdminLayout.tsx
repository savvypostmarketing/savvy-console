import { ReactNode, useState, useCallback, useMemo } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    Drawer,
    DrawerBody,
    DrawerHeader,
    DrawerHeaderTitle,
    Button,
    Avatar,
    Menu,
    MenuTrigger,
    MenuPopover,
    MenuList,
    MenuItem,
    Text,
    tokens,
    makeStyles,
    shorthands,
    Divider,
    Toaster,
} from '@fluentui/react-components';
import {
    Navigation24Regular,
    Home24Regular,
    People24Regular,
    PersonAccounts24Regular,
    Shield24Regular,
    SignOut24Regular,
    ChevronDown16Regular,
    Mail24Regular,
    Folder24Regular,
    Settings24Regular,
    Star24Regular,
    DataUsage24Regular,
    Briefcase24Regular,
} from '@fluentui/react-icons';
import type { PageProps } from '@/interfaces';
import { useFlash, usePermissions } from '@/hooks';

// Types
interface NavItem {
    name: string;
    href: string;
    icon: typeof Home24Regular;
    permission: string | null;
    superAdminOnly?: boolean;
}

interface NavSection {
    section: string;
    items: NavItem[];
}

interface AdminLayoutProps {
    children: ReactNode;
    title?: string;
}

// Styles
const useStyles = makeStyles({
    root: {
        display: 'flex',
        minHeight: '100vh',
        backgroundColor: tokens.colorNeutralBackground2,
    },
    sidebar: {
        width: '260px',
        backgroundColor: tokens.colorNeutralBackground1,
        borderRight: `1px solid ${tokens.colorNeutralStroke2}`,
        display: 'flex',
        flexDirection: 'column',
        position: 'fixed',
        height: '100vh',
        zIndex: 100,
        '@media (max-width: 768px)': {
            display: 'none',
        },
    },
    sidebarHeader: {
        ...shorthands.padding('20px'),
        borderBottom: `1px solid ${tokens.colorNeutralStroke2}`,
    },
    sidebarNav: {
        ...shorthands.padding('16px', '12px'),
        flexGrow: 1,
        overflowY: 'auto',
    },
    navItem: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('12px'),
        ...shorthands.padding('12px', '16px'),
        ...shorthands.borderRadius('8px'),
        textDecoration: 'none',
        color: tokens.colorNeutralForeground2,
        marginBottom: '4px',
        '&:hover': {
            backgroundColor: tokens.colorNeutralBackground3,
        },
    },
    navItemActive: {
        backgroundColor: tokens.colorBrandBackground2,
        color: tokens.colorBrandForeground1,
        '&:hover': {
            backgroundColor: tokens.colorBrandBackground2,
        },
    },
    content: {
        flexGrow: 1,
        marginLeft: '260px',
        '@media (max-width: 768px)': {
            marginLeft: 0,
        },
    },
    header: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        ...shorthands.padding('16px', '24px'),
        backgroundColor: tokens.colorNeutralBackground1,
        borderBottom: `1px solid ${tokens.colorNeutralStroke2}`,
        position: 'sticky',
        top: 0,
        zIndex: 50,
    },
    main: {
        ...shorthands.padding('24px'),
    },
    mobileMenuButton: {
        display: 'none',
        '@media (max-width: 768px)': {
            display: 'block',
        },
    },
    logo: {
        fontSize: '20px',
        fontWeight: 700,
        color: tokens.colorBrandForeground1,
    },
    navSection: {
        marginBottom: '16px',
    },
    navSectionTitle: {
        ...shorthands.padding('8px', '16px'),
        fontSize: '12px',
        fontWeight: 600,
        color: tokens.colorNeutralForeground3,
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
    },
});

// Navigation configuration
const NAV_ITEMS: NavSection[] = [
    {
        section: 'Dashboard',
        items: [
            { name: 'Dashboard', href: '/admin/dashboard', icon: Home24Regular, permission: null },
        ],
    },
    {
        section: 'Management',
        items: [
            { name: 'Leads', href: '/admin/leads', icon: Mail24Regular, permission: 'view-leads' },
            {
                name: 'Portfolio',
                href: '/admin/portfolio',
                icon: Folder24Regular,
                permission: 'view-portfolio',
            },
            {
                name: 'Testimonials',
                href: '/admin/testimonials',
                icon: Star24Regular,
                permission: 'view-testimonials',
            },
            {
                name: 'Job Positions',
                href: '/admin/job-positions',
                icon: Briefcase24Regular,
                permission: 'manage-settings',
            },
            {
                name: 'Users',
                href: '/admin/users',
                icon: People24Regular,
                permission: 'view-users',
            },
            {
                name: 'Roles',
                href: '/admin/roles',
                icon: PersonAccounts24Regular,
                permission: 'view-roles',
            },
        ],
    },
    {
        section: 'Administration',
        items: [
            {
                name: 'Analytics',
                href: '/admin/analytics',
                icon: DataUsage24Regular,
                permission: null,
                superAdminOnly: true,
            },
            {
                name: 'Settings',
                href: '/admin/settings',
                icon: Settings24Regular,
                permission: null,
                superAdminOnly: true,
            },
            {
                name: 'Permissions',
                href: '/admin/permissions',
                icon: Shield24Regular,
                permission: null,
                superAdminOnly: true,
            },
        ],
    },
];

export default function AdminLayout({ children, title }: AdminLayoutProps) {
    const styles = useStyles();
    const { auth } = usePage<PageProps>().props;
    const { toasterId } = useFlash();
    const { checkPermission, isSuperAdmin } = usePermissions();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

    // Handlers
    const handleLogout = useCallback(() => {
        router.post('/logout');
    }, []);

    const handleOpenMobileMenu = useCallback(() => {
        setIsMobileMenuOpen(true);
    }, []);

    const handleCloseMobileMenu = useCallback((_: unknown, { open }: { open: boolean }) => {
        setIsMobileMenuOpen(open);
    }, []);

    // Computed values
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    const isNavItemVisible = useCallback(
        (item: NavItem): boolean => {
            if (item.superAdminOnly && !isSuperAdmin) {
                return false;
            }
            if (item.permission && !checkPermission(item.permission)) {
                return false;
            }
            return true;
        },
        [isSuperAdmin, checkPermission]
    );

    const visibleNavSections = useMemo(() => {
        return NAV_ITEMS.map((section) => ({
            ...section,
            items: section.items.filter(isNavItemVisible),
        })).filter((section) => section.items.length > 0);
    }, [isNavItemVisible]);

    // Render helpers
    const renderNavItem = useCallback(
        (item: NavItem) => {
            const Icon = item.icon;
            const isActive = currentPath === item.href || currentPath.startsWith(`${item.href}/`);
            const className = `${styles.navItem} ${isActive ? styles.navItemActive : ''}`;

            return (
                <Link key={item.href} href={item.href} className={className}>
                    <Icon />
                    <span>{item.name}</span>
                </Link>
            );
        },
        [currentPath, styles.navItem, styles.navItemActive]
    );

    const sidebarContent = (
        <>
            <div className={styles.sidebarHeader}>
                <Text className={styles.logo}>Savvy Admin</Text>
            </div>
            <nav className={styles.sidebarNav}>
                {visibleNavSections.map((section) => (
                    <div key={section.section} className={styles.navSection}>
                        <Text className={styles.navSectionTitle}>{section.section}</Text>
                        {section.items.map(renderNavItem)}
                    </div>
                ))}
            </nav>
        </>
    );

    return (
        <div className={styles.root}>
            <Toaster toasterId={toasterId} position="top-end" />

            {/* Desktop Sidebar */}
            <aside className={styles.sidebar}>{sidebarContent}</aside>

            {/* Mobile Drawer */}
            <Drawer
                type="overlay"
                open={isMobileMenuOpen}
                onOpenChange={handleCloseMobileMenu}
                position="start"
            >
                <DrawerHeader>
                    <DrawerHeaderTitle>
                        <Text className={styles.logo}>Savvy Admin</Text>
                    </DrawerHeaderTitle>
                </DrawerHeader>
                <DrawerBody>{sidebarContent}</DrawerBody>
            </Drawer>

            {/* Main Content */}
            <div className={styles.content}>
                <header className={styles.header}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                        <Button
                            className={styles.mobileMenuButton}
                            appearance="subtle"
                            icon={<Navigation24Regular />}
                            onClick={handleOpenMobileMenu}
                        />
                        {title && (
                            <Text size={500} weight="semibold">
                                {title}
                            </Text>
                        )}
                    </div>

                    <Menu>
                        <MenuTrigger disableButtonEnhancement>
                            <Button
                                appearance="subtle"
                                icon={<ChevronDown16Regular />}
                                iconPosition="after"
                            >
                                <Avatar
                                    name={auth.user?.name}
                                    size={28}
                                    style={{ marginRight: '8px' }}
                                />
                                {auth.user?.name}
                            </Button>
                        </MenuTrigger>
                        <MenuPopover>
                            <MenuList>
                                <MenuItem disabled>
                                    <Text
                                        size={200}
                                        style={{ color: tokens.colorNeutralForeground3 }}
                                    >
                                        {auth.user?.email}
                                    </Text>
                                </MenuItem>
                                <Divider />
                                <MenuItem icon={<SignOut24Regular />} onClick={handleLogout}>
                                    Logout
                                </MenuItem>
                            </MenuList>
                        </MenuPopover>
                    </Menu>
                </header>

                <main className={styles.main}>{children}</main>
            </div>
        </div>
    );
}
