import { useEffect, useRef, memo } from 'react';
import EditorJS, { API } from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import ImageTool from '@editorjs/image';
import Embed from '@editorjs/embed';
import Table from '@editorjs/table';
import Quote from '@editorjs/quote';
import Code from '@editorjs/code';
import Delimiter from '@editorjs/delimiter';
import InlineCode from '@editorjs/inline-code';
import Marker from '@editorjs/marker';
import Checklist from '@editorjs/checklist';
import LinkTool from '@editorjs/link';
import Raw from '@editorjs/raw';
import Warning from '@editorjs/warning';
import Paragraph from '@editorjs/paragraph';
import { makeStyles, shorthands, tokens, Text } from '@fluentui/react-components';

const useStyles = makeStyles({
    container: {
        ...shorthands.borderWidth('1px'),
        ...shorthands.borderStyle('solid'),
        ...shorthands.borderColor(tokens.colorNeutralStroke1),
        ...shorthands.borderRadius('8px'),
        backgroundColor: tokens.colorNeutralBackground1,
        minHeight: '400px',
    },
    editor: {
        ...shorthands.padding('16px'),
    },
    label: {
        display: 'block',
        marginBottom: '8px',
        fontWeight: 500,
    },
});

export interface EditorJSData {
    time?: number;
    blocks: Array<{
        id?: string;
        type: string;
        data: Record<string, unknown>;
    }>;
    version?: string;
}

interface BlockEditorProps {
    value: EditorJSData | null;
    onChange: (data: EditorJSData) => void;
    placeholder?: string;
    readOnly?: boolean;
    label?: string;
    uploadEndpoint?: string;
}

function BlockEditor({
    value,
    onChange,
    placeholder = 'Start writing your content...',
    readOnly = false,
    label,
    uploadEndpoint = '/admin/posts/upload-image',
}: BlockEditorProps) {
    const styles = useStyles();
    const editorRef = useRef<EditorJS | null>(null);
    const holderRef = useRef<HTMLDivElement>(null);
    const isReady = useRef(false);

    // Initialize editor
    useEffect(() => {
        if (!holderRef.current || editorRef.current) {
            return;
        }

        const editor = new EditorJS({
            holder: holderRef.current,
            readOnly,
            placeholder,
            data: value ?? undefined,
            /* eslint-disable @typescript-eslint/no-unsafe-assignment */
            tools: {
                paragraph: {
                    class: Paragraph,
                    inlineToolbar: true,
                },
                header: {
                    class: Header,
                    config: {
                        placeholder: 'Enter a header',
                        levels: [1, 2, 3, 4, 5, 6],
                        defaultLevel: 2,
                    },
                    inlineToolbar: true,
                },
                list: {
                    class: List,
                    inlineToolbar: true,
                    config: {
                        defaultStyle: 'unordered',
                    },
                },
                checklist: {
                    class: Checklist,
                    inlineToolbar: true,
                },
                image: {
                    class: ImageTool,
                    config: {
                        endpoints: {
                            byFile: uploadEndpoint,
                            byUrl: uploadEndpoint,
                        },
                        field: 'image',
                        types: 'image/*',
                        captionPlaceholder: 'Image caption',
                    },
                },
                embed: {
                    class: Embed,
                    config: {
                        services: {
                            youtube: true,
                            vimeo: true,
                            twitter: true,
                            instagram: true,
                            facebook: true,
                            codepen: true,
                            github: true,
                        },
                    },
                },
                table: {
                    class: Table,
                    inlineToolbar: true,
                    config: {
                        rows: 2,
                        cols: 3,
                    },
                },
                quote: {
                    class: Quote,
                    inlineToolbar: true,
                    config: {
                        quotePlaceholder: 'Enter a quote',
                        captionPlaceholder: 'Quote author',
                    },
                },
                code: {
                    class: Code,
                    config: {
                        placeholder: 'Enter code here...',
                    },
                },
                delimiter: Delimiter,
                inlineCode: {
                    class: InlineCode,
                },
                marker: {
                    class: Marker,
                },
                linkTool: {
                    class: LinkTool,
                    config: {
                        endpoint: '/admin/posts/fetch-link',
                    },
                },
                raw: {
                    class: Raw,
                    config: {
                        placeholder: 'Enter raw HTML...',
                    },
                },
                warning: {
                    class: Warning,
                    inlineToolbar: true,
                    config: {
                        titlePlaceholder: 'Title',
                        messagePlaceholder: 'Message',
                    },
                },
            },
            /* eslint-enable @typescript-eslint/no-unsafe-assignment */
            onChange: (api: API) => {
                if (!isReady.current) {
                    return;
                }
                void api.saver
                    .save()
                    .then((data) => {
                        onChange(data as EditorJSData);
                    })
                    .catch((err: unknown) => {
                        console.error('Editor.js save error:', err);
                    });
            },
            onReady: () => {
                isReady.current = true;
            },
        });

        editorRef.current = editor;

        return () => {
            if (editorRef.current?.destroy) {
                editorRef.current.destroy();
                editorRef.current = null;
                isReady.current = false;
            }
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Update editor content when value changes externally
    useEffect(() => {
        if (!editorRef.current || !isReady.current || !value) {
            return;
        }

        // Only render if the editor is empty or if we need to reset
        void editorRef.current.isReady.then(() => {
            if (value.blocks && value.blocks.length > 0) {
                void editorRef.current?.render(value);
            }
        });
    }, [value?.time]);

    return (
        <div>
            {label && <Text className={styles.label}>{label}</Text>}
            <div className={styles.container}>
                <div ref={holderRef} className={styles.editor} />
            </div>
        </div>
    );
}

export default memo(BlockEditor);
