import { useState, useCallback, useMemo } from 'react';
import {
    Button,
    Popover,
    PopoverTrigger,
    PopoverSurface,
    Input,
    makeStyles,
    shorthands,
    tokens,
    Text,
} from '@fluentui/react-components';
import {
    Code24Regular,
    PaintBrush24Regular,
    Phone24Regular,
    DataTrending24Regular,
    Search24Regular,
    Emoji24Regular,
    Video24Regular,
    ArrowRight24Regular,
    Navigation24Regular,
    ChevronDown24Regular,
    Call24Regular,
    Mail24Regular,
    Location24Regular,
    Clock24Regular,
    Window24Regular,
    TextT24Regular,
    Settings24Regular,
    Cart24Regular,
    Flash24Regular,
    Home24Regular,
    Star24Regular,
    Heart24Regular,
    Bookmark24Regular,
    Flag24Regular,
    Target24Regular,
    Lightbulb24Regular,
    Rocket24Regular,
    Globe24Regular,
    Shield24Regular,
    Key24Regular,
    Person24Regular,
    People24Regular,
    Building24Regular,
    Briefcase24Regular,
    Money24Regular,
    Wallet24Regular,
    ChartMultiple24Regular,
    DocumentText24Regular,
    Folder24Regular,
    Image24Regular,
    Camera24Regular,
    Mic24Regular,
    Cloud24Regular,
    Desktop24Regular,
    Laptop24Regular,
    Apps24Regular,
    Grid24Regular,
    List24Regular,
    Filter24Regular,
    Edit24Regular,
    Delete24Regular,
    Add24Regular,
    Checkmark24Regular,
    Dismiss24Regular,
    Info24Regular,
    Warning24Regular,
    ErrorCircle24Regular,
    QuestionCircle24Regular,
    Calendar24Regular,
    Send24Regular,
    Share24Regular,
    Link24Regular,
    Attach24Regular,
    Print24Regular,
    Save24Regular,
    Eye24Regular,
    Play24Regular,
    Pause24Regular,
    Stop24Regular,
    ZoomIn24Regular,
    ZoomOut24Regular,
    FullScreenMaximize24Regular,
    Storage24Regular,
    LockClosed24Regular,
} from '@fluentui/react-icons';

// Available icons mapping
const AVAILABLE_ICONS = {
    // Development & Tech
    Code: { icon: Code24Regular, label: 'Code' },
    Desktop: { icon: Desktop24Regular, label: 'Desktop' },
    Laptop: { icon: Laptop24Regular, label: 'Laptop' },
    Phone: { icon: Phone24Regular, label: 'Phone' },
    Apps: { icon: Apps24Regular, label: 'Apps' },
    Cloud: { icon: Cloud24Regular, label: 'Cloud' },
    Globe: { icon: Globe24Regular, label: 'Globe' },
    Storage: { icon: Storage24Regular, label: 'Storage' },

    // Design & Creative
    PaintBrush: { icon: PaintBrush24Regular, label: 'Paint Brush' },
    Image: { icon: Image24Regular, label: 'Image' },
    Camera: { icon: Camera24Regular, label: 'Camera' },
    Video: { icon: Video24Regular, label: 'Video' },

    // Business & Commerce
    Cart: { icon: Cart24Regular, label: 'Cart' },
    Briefcase: { icon: Briefcase24Regular, label: 'Briefcase' },
    Building: { icon: Building24Regular, label: 'Building' },
    Money: { icon: Money24Regular, label: 'Money' },
    Wallet: { icon: Wallet24Regular, label: 'Wallet' },

    // Marketing & Analytics
    DataTrending: { icon: DataTrending24Regular, label: 'Trending' },
    ChartMultiple: { icon: ChartMultiple24Regular, label: 'Chart' },
    Target: { icon: Target24Regular, label: 'Target' },
    Rocket: { icon: Rocket24Regular, label: 'Rocket' },
    Lightbulb: { icon: Lightbulb24Regular, label: 'Lightbulb' },

    // Communication
    Mail: { icon: Mail24Regular, label: 'Mail' },
    Call: { icon: Call24Regular, label: 'Call' },
    Send: { icon: Send24Regular, label: 'Send' },
    Share: { icon: Share24Regular, label: 'Share' },
    Mic: { icon: Mic24Regular, label: 'Microphone' },

    // Files & Documents
    DocumentText: { icon: DocumentText24Regular, label: 'Document' },
    Folder: { icon: Folder24Regular, label: 'Folder' },
    Attach: { icon: Attach24Regular, label: 'Attach' },
    Link: { icon: Link24Regular, label: 'Link' },
    Print: { icon: Print24Regular, label: 'Print' },

    // Users & Security
    Person: { icon: Person24Regular, label: 'Person' },
    People: { icon: People24Regular, label: 'People' },
    Shield: { icon: Shield24Regular, label: 'Shield' },
    Key: { icon: Key24Regular, label: 'Key' },
    Lock: { icon: LockClosed24Regular, label: 'Lock' },

    // UI & Navigation
    Search: { icon: Search24Regular, label: 'Search' },
    Settings: { icon: Settings24Regular, label: 'Settings' },
    Home: { icon: Home24Regular, label: 'Home' },
    Navigation: { icon: Navigation24Regular, label: 'Navigation' },
    Grid: { icon: Grid24Regular, label: 'Grid' },
    List: { icon: List24Regular, label: 'List' },
    Filter: { icon: Filter24Regular, label: 'Filter' },
    Window: { icon: Window24Regular, label: 'Window' },
    Location: { icon: Location24Regular, label: 'Location' },

    // Actions
    Edit: { icon: Edit24Regular, label: 'Edit' },
    Delete: { icon: Delete24Regular, label: 'Delete' },
    Add: { icon: Add24Regular, label: 'Add' },
    Save: { icon: Save24Regular, label: 'Save' },
    Eye: { icon: Eye24Regular, label: 'View' },

    // Status & Feedback
    Checkmark: { icon: Checkmark24Regular, label: 'Checkmark' },
    Dismiss: { icon: Dismiss24Regular, label: 'Dismiss' },
    Info: { icon: Info24Regular, label: 'Info' },
    Warning: { icon: Warning24Regular, label: 'Warning' },
    ErrorCircle: { icon: ErrorCircle24Regular, label: 'Error' },
    QuestionCircle: { icon: QuestionCircle24Regular, label: 'Question' },

    // Symbols
    Star: { icon: Star24Regular, label: 'Star' },
    Heart: { icon: Heart24Regular, label: 'Heart' },
    Bookmark: { icon: Bookmark24Regular, label: 'Bookmark' },
    Flag: { icon: Flag24Regular, label: 'Flag' },
    Emoji: { icon: Emoji24Regular, label: 'Emoji' },

    // Time & Calendar
    Clock: { icon: Clock24Regular, label: 'Clock' },
    Calendar: { icon: Calendar24Regular, label: 'Calendar' },

    // Media Controls
    Play: { icon: Play24Regular, label: 'Play' },
    Pause: { icon: Pause24Regular, label: 'Pause' },
    Stop: { icon: Stop24Regular, label: 'Stop' },

    // Text
    TextT: { icon: TextT24Regular, label: 'Text' },

    // Tools
    Flash: { icon: Flash24Regular, label: 'Flash' },

    // Arrows
    ArrowRight: { icon: ArrowRight24Regular, label: 'Arrow Right' },
    ChevronDown: { icon: ChevronDown24Regular, label: 'Chevron Down' },
    ZoomIn: { icon: ZoomIn24Regular, label: 'Zoom In' },
    ZoomOut: { icon: ZoomOut24Regular, label: 'Zoom Out' },
    FullScreen: { icon: FullScreenMaximize24Regular, label: 'Full Screen' },
};

const useStyles = makeStyles({
    trigger: {
        minWidth: '200px',
        justifyContent: 'flex-start',
    },
    surface: {
        ...shorthands.padding('12px'),
        maxWidth: '400px',
    },
    searchBox: {
        marginBottom: '12px',
    },
    iconsGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(6, 1fr)',
        ...shorthands.gap('4px'),
        maxHeight: '300px',
        overflowY: 'auto',
    },
    iconButton: {
        minWidth: '40px',
        height: '40px',
        ...shorthands.padding('8px'),
    },
    iconButtonSelected: {
        backgroundColor: tokens.colorBrandBackground,
        color: tokens.colorNeutralForegroundOnBrand,
        '&:hover': {
            backgroundColor: tokens.colorBrandBackgroundHover,
        },
    },
    selectedIcon: {
        display: 'flex',
        alignItems: 'center',
        ...shorthands.gap('8px'),
    },
    noIcon: {
        color: tokens.colorNeutralForeground3,
    },
});

interface IconPickerProps {
    value?: string;
    onChange: (iconName: string) => void;
    placeholder?: string;
}

export default function IconPicker({
    value,
    onChange,
    placeholder = 'Select icon',
}: IconPickerProps) {
    const styles = useStyles();
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');

    const filteredIcons = useMemo(() => {
        if (!search) {
            return Object.entries(AVAILABLE_ICONS);
        }
        const searchLower = search.toLowerCase();
        return Object.entries(AVAILABLE_ICONS).filter(
            ([key, { label }]) =>
                key.toLowerCase().includes(searchLower) || label.toLowerCase().includes(searchLower)
        );
    }, [search]);

    const handleSelect = useCallback(
        (iconName: string) => {
            onChange(iconName);
            setOpen(false);
            setSearch('');
        },
        [onChange]
    );

    const handleClear = useCallback(() => {
        onChange('');
        setOpen(false);
    }, [onChange]);

    const SelectedIconComponent = value
        ? AVAILABLE_ICONS[value as keyof typeof AVAILABLE_ICONS]?.icon
        : null;

    return (
        <Popover open={open} onOpenChange={(_, data) => setOpen(data.open)}>
            <PopoverTrigger disableButtonEnhancement>
                <Button appearance="outline" className={styles.trigger}>
                    <div className={styles.selectedIcon}>
                        {SelectedIconComponent ? (
                            <>
                                <SelectedIconComponent />
                                <span>
                                    {AVAILABLE_ICONS[value as keyof typeof AVAILABLE_ICONS]?.label}
                                </span>
                            </>
                        ) : (
                            <span className={styles.noIcon}>{placeholder}</span>
                        )}
                    </div>
                </Button>
            </PopoverTrigger>
            <PopoverSurface className={styles.surface}>
                <Input
                    className={styles.searchBox}
                    placeholder="Search icons..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    contentBefore={<Search24Regular />}
                />

                {value && (
                    <Button
                        appearance="subtle"
                        size="small"
                        onClick={handleClear}
                        style={{ marginBottom: '8px', width: '100%' }}
                    >
                        Clear selection
                    </Button>
                )}

                <div className={styles.iconsGrid}>
                    {filteredIcons.map(([key, { icon: Icon, label }]) => (
                        <Button
                            key={key}
                            appearance="subtle"
                            className={`${styles.iconButton} ${value === key ? styles.iconButtonSelected : ''}`}
                            onClick={() => handleSelect(key)}
                            title={label}
                        >
                            <Icon />
                        </Button>
                    ))}
                </div>

                {filteredIcons.length === 0 && (
                    <Text style={{ textAlign: 'center', display: 'block', padding: '20px' }}>
                        No icons found
                    </Text>
                )}
            </PopoverSurface>
        </Popover>
    );
}

// Export icon names for reference
export const ICON_NAMES = Object.keys(AVAILABLE_ICONS);
export { AVAILABLE_ICONS };
