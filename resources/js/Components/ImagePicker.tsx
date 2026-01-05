import { useState, useCallback, useRef, useEffect } from 'react';
import { Button, Text, makeStyles, shorthands, tokens } from '@fluentui/react-components';
import { Image24Regular, Delete24Regular, ArrowUpload24Regular } from '@fluentui/react-icons';

const useStyles = makeStyles({
    container: {
        display: 'flex',
        flexDirection: 'column',
        ...shorthands.gap('8px'),
    },
    dropzone: {
        ...shorthands.borderWidth('2px'),
        ...shorthands.borderStyle('dashed'),
        ...shorthands.borderColor(tokens.colorNeutralStroke1),
        ...shorthands.borderRadius('8px'),
        ...shorthands.padding('24px'),
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        ...shorthands.gap('12px'),
        cursor: 'pointer',
        transitionProperty: 'all',
        transitionDuration: '0.2s',
        transitionTimingFunction: 'ease',
        backgroundColor: tokens.colorNeutralBackground2,
        minHeight: '150px',
        ':hover': {
            ...shorthands.borderColor(tokens.colorBrandStroke1),
            backgroundColor: tokens.colorNeutralBackground3,
        },
    },
    dropzoneActive: {
        ...shorthands.borderColor(tokens.colorBrandStroke1),
        backgroundColor: tokens.colorBrandBackground2,
    },
    previewContainer: {
        position: 'relative' as const,
        ...shorthands.borderRadius('8px'),
        ...shorthands.overflow('hidden'),
        backgroundColor: tokens.colorNeutralBackground3,
    },
    preview: {
        width: '100%',
        height: 'auto',
        maxHeight: '300px',
        objectFit: 'contain' as const,
        display: 'block',
    },
    previewActions: {
        position: 'absolute' as const,
        top: '8px',
        right: '8px',
        display: 'flex',
        ...shorthands.gap('8px'),
    },
    icon: {
        color: tokens.colorNeutralForeground3,
    },
    hiddenInput: {
        display: 'none',
    },
    existingImage: {
        marginTop: '8px',
        fontSize: '12px',
        color: tokens.colorNeutralForeground3,
    },
});

interface ImagePickerProps {
    value?: File | null;
    existingImage?: string | null;
    onChange: (file: File | null) => void;
    onRemoveExisting?: () => void;
    placeholder?: string;
    accept?: string;
}

export default function ImagePicker({
    value,
    existingImage,
    onChange,
    onRemoveExisting,
    placeholder = 'Drop an image here or click to upload',
    accept = 'image/*',
}: ImagePickerProps) {
    const styles = useStyles();
    const [isDragging, setIsDragging] = useState(false);
    const [preview, setPreview] = useState<string | null>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    // Generate preview when file changes
    useEffect(() => {
        if (value) {
            const url = URL.createObjectURL(value);
            setPreview(url);
            return () => URL.revokeObjectURL(url);
        }
        setPreview(null);
        return undefined;
    }, [value]);

    const handleDragEnter = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(true);
    }, []);

    const handleDragLeave = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
    }, []);

    const handleDragOver = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
    }, []);

    const handleDrop = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            e.stopPropagation();
            setIsDragging(false);

            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    onChange(file);
                }
            }
        },
        [onChange]
    );

    const handleClick = useCallback(() => {
        inputRef.current?.click();
    }, []);

    const handleFileChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            const file = e.target.files?.[0] || null;
            onChange(file);
        },
        [onChange]
    );

    const handleRemove = useCallback(() => {
        onChange(null);
        if (inputRef.current) {
            inputRef.current.value = '';
        }
    }, [onChange]);

    const handleRemoveExisting = useCallback(() => {
        onRemoveExisting?.();
    }, [onRemoveExisting]);

    // Show preview if we have a new file
    if (preview) {
        return (
            <div className={styles.container}>
                <div className={styles.previewContainer}>
                    <img src={preview} alt="Preview" className={styles.preview} />
                    <div className={styles.previewActions}>
                        <Button
                            appearance="primary"
                            icon={<Delete24Regular />}
                            size="small"
                            onClick={handleRemove}
                        >
                            Remove
                        </Button>
                    </div>
                </div>
            </div>
        );
    }

    // Show existing image if available
    if (existingImage) {
        return (
            <div className={styles.container}>
                <div className={styles.previewContainer}>
                    <img src={existingImage} alt="Current" className={styles.preview} />
                    <div className={styles.previewActions}>
                        <Button
                            appearance="subtle"
                            icon={<ArrowUpload24Regular />}
                            size="small"
                            onClick={handleClick}
                        >
                            Change
                        </Button>
                        {onRemoveExisting && (
                            <Button
                                appearance="primary"
                                icon={<Delete24Regular />}
                                size="small"
                                onClick={handleRemoveExisting}
                            >
                                Remove
                            </Button>
                        )}
                    </div>
                </div>
                <input
                    ref={inputRef}
                    type="file"
                    accept={accept}
                    onChange={handleFileChange}
                    className={styles.hiddenInput}
                />
            </div>
        );
    }

    // Show dropzone
    return (
        <div className={styles.container}>
            <div
                className={`${styles.dropzone} ${isDragging ? styles.dropzoneActive : ''}`}
                onDragEnter={handleDragEnter}
                onDragLeave={handleDragLeave}
                onDragOver={handleDragOver}
                onDrop={handleDrop}
                onClick={handleClick}
            >
                <Image24Regular className={styles.icon} style={{ fontSize: '48px' }} />
                <Text>{placeholder}</Text>
                <Text size={200} style={{ color: tokens.colorNeutralForeground3 }}>
                    Supports: JPG, PNG, GIF, WebP
                </Text>
            </div>
            <input
                ref={inputRef}
                type="file"
                accept={accept}
                onChange={handleFileChange}
                className={styles.hiddenInput}
            />
        </div>
    );
}
