// Type declarations for Editor.js plugins without types

declare module '@editorjs/embed' {
    import { BlockTool, BlockToolConstructorOptions } from '@editorjs/editorjs';

    interface EmbedConfig {
        services?: Record<string, boolean | object>;
    }

    export default class Embed implements BlockTool {
        static get toolbox(): { title: string; icon: string };
        constructor(options: BlockToolConstructorOptions<object, EmbedConfig>);
        render(): HTMLElement;
        save(block: HTMLElement): object;
    }
}

declare module '@editorjs/marker' {
    import { InlineTool, InlineToolConstructorOptions } from '@editorjs/editorjs';

    export default class Marker implements InlineTool {
        static get isInline(): boolean;
        static get sanitize(): object;
        static get shortcut(): string;
        static get title(): string;
        constructor(options: InlineToolConstructorOptions);
        render(): HTMLElement;
        surround(range: Range): void;
        checkState(): boolean;
    }
}

declare module '@editorjs/checklist' {
    import { BlockTool, BlockToolConstructorOptions } from '@editorjs/editorjs';

    interface ChecklistData {
        items: Array<{ text: string; checked: boolean }>;
    }

    export default class Checklist implements BlockTool {
        static get toolbox(): { title: string; icon: string };
        constructor(options: BlockToolConstructorOptions<ChecklistData>);
        render(): HTMLElement;
        save(block: HTMLElement): ChecklistData;
    }
}

declare module '@editorjs/link' {
    import { BlockTool, BlockToolConstructorOptions } from '@editorjs/editorjs';

    interface LinkConfig {
        endpoint?: string;
    }

    interface LinkData {
        link: string;
        meta?: {
            title?: string;
            description?: string;
            image?: { url: string };
        };
    }

    export default class LinkTool implements BlockTool {
        static get toolbox(): { title: string; icon: string };
        constructor(options: BlockToolConstructorOptions<LinkData, LinkConfig>);
        render(): HTMLElement;
        save(block: HTMLElement): LinkData;
    }
}

declare module '@editorjs/raw' {
    import { BlockTool, BlockToolConstructorOptions } from '@editorjs/editorjs';

    interface RawConfig {
        placeholder?: string;
    }

    interface RawData {
        html: string;
    }

    export default class Raw implements BlockTool {
        static get toolbox(): { title: string; icon: string };
        constructor(options: BlockToolConstructorOptions<RawData, RawConfig>);
        render(): HTMLElement;
        save(block: HTMLElement): RawData;
    }
}
