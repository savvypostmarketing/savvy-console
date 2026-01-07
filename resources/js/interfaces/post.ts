export interface PostCategory {
    id: number;
    name: string;
    name_es: string | null;
    slug: string;
    description: string | null;
    description_es: string | null;
    icon: string | null;
    color: string | null;
    is_active: boolean;
    sort_order: number;
}

export interface PostTag {
    id: number;
    name: string;
    name_es: string | null;
    slug: string;
    is_active: boolean;
}

export interface PostAuthor {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
}

export interface EditorJSBlock {
    id?: string;
    type: string;
    data: Record<string, unknown>;
}

export interface EditorJSData {
    time?: number;
    blocks: EditorJSBlock[];
    version?: string;
}

export interface Post {
    id: number;
    uuid: string;
    title: string;
    title_es: string | null;
    slug: string;
    category_id: number | null;
    author_id: number | null;
    excerpt: string | null;
    excerpt_es: string | null;
    content: string | EditorJSData | null;
    content_es: string | EditorJSData | null;
    featured_image: string | null;
    featured_image_url?: string | null;
    featured_image_alt: string | null;
    featured_image_alt_es: string | null;
    reading_time_minutes: number;
    views_count: number;
    likes_count: number;
    is_featured: boolean;
    is_published: boolean;
    published_at: string | null;
    sort_order: number;
    meta_title: string | null;
    meta_title_es: string | null;
    meta_description: string | null;
    meta_description_es: string | null;
    created_at: string;
    updated_at: string;
    category?: PostCategory | null;
    author?: PostAuthor | null;
    tags?: PostTag[];
    tag_ids?: number[];
}

// Using simplified types for content to satisfy Inertia's FormDataType constraint
export interface PostFormData {
    title: string;
    title_es: string;
    slug: string;
    category_id: string | number;
    excerpt: string;
    excerpt_es: string;
    content: EditorJSData | string | null;
    content_es: EditorJSData | string | null;
    featured_image: File | null;
    featured_image_alt: string;
    featured_image_alt_es: string;
    reading_time_minutes: number;
    is_published: boolean;
    is_featured: boolean;
    meta_title: string;
    meta_title_es: string;
    meta_description: string;
    meta_description_es: string;
    tags: number[];
    remove_featured_image?: boolean;
}

export interface PostsIndexProps {
    posts: {
        data: Post[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    categories: PostCategory[];
    filters: {
        category?: string;
        status?: string;
        search?: string;
    };
}

export interface PostCreateProps {
    categories: PostCategory[];
    tags: PostTag[];
}

export interface PostEditProps {
    post: Post & {
        content: EditorJSData | null;
        content_es: EditorJSData | null;
        tag_ids: number[];
        featured_image_url: string | null;
    };
    categories: PostCategory[];
    tags: PostTag[];
}
