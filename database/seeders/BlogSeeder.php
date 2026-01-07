<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class BlogSeeder extends Seeder
{
    private string $graphqlEndpoint = 'https://cms.savvypostmarketing.com/graphql';
    private string $faustKey = '94398e22-8e0e-4747-b2e9-4e42701ed209';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::first();

        if (!$author) {
            $this->command->warn('No user found. Run AdminUserSeeder first.');
            return;
        }

        $this->command->info('Fetching blog data from WordPress API...');

        // Fetch categories from WordPress
        $categories = $this->fetchCategories();
        $this->command->info('Found ' . count($categories) . ' categories');

        // Create categories in local database
        $categoryMap = $this->createCategories($categories);

        // Fetch posts from WordPress
        $posts = $this->fetchPosts();
        $this->command->info('Found ' . count($posts) . ' posts');

        // Create posts in local database
        $this->createPosts($posts, $categoryMap, $author);

        $this->command->info('Blog seeded successfully from WordPress API!');
    }

    /**
     * Fetch categories from WordPress GraphQL API.
     */
    private function fetchCategories(): array
    {
        $query = '
            query GetCategories {
                categories(first: 50) {
                    edges {
                        node {
                            categoryId
                            name
                            slug
                            description
                            language { code }
                        }
                    }
                }
            }
        ';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-faustwp-secret' => $this->faustKey,
        ])->post($this->graphqlEndpoint, ['query' => $query]);

        if (!$response->successful()) {
            $this->command->error('Failed to fetch categories');
            return [];
        }

        $data = $response->json();
        return $data['data']['categories']['edges'] ?? [];
    }

    /**
     * Fetch posts from WordPress GraphQL API.
     */
    private function fetchPosts(): array
    {
        $query = '
            query GetAllPosts {
                posts(first: 100, where: { status: PUBLISH }) {
                    edges {
                        node {
                            postId
                            title
                            slug
                            excerpt
                            content
                            date
                            modified
                            featuredImage {
                                node {
                                    sourceUrl
                                    altText
                                }
                            }
                            author {
                                node {
                                    name
                                }
                            }
                            categories {
                                edges {
                                    node {
                                        name
                                        slug
                                    }
                                }
                            }
                            tags {
                                edges {
                                    node {
                                        name
                                        slug
                                    }
                                }
                            }
                            language { code }
                            translations {
                                language { code }
                                slug
                            }
                        }
                    }
                }
            }
        ';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-faustwp-secret' => $this->faustKey,
        ])->post($this->graphqlEndpoint, ['query' => $query]);

        if (!$response->successful()) {
            $this->command->error('Failed to fetch posts');
            return [];
        }

        $data = $response->json();
        return $data['data']['posts']['edges'] ?? [];
    }

    /**
     * Create categories in local database.
     */
    private function createCategories(array $wpCategories): array
    {
        $categoryMap = [];
        $processedSlugs = [];

        // Category colors for visual distinction
        $colors = [
            'marketing' => '#3B82F6',
            'app-mobile-development' => '#10B981',
            'app-development' => '#10B981',
            'web-development' => '#8B5CF6',
            'uncategorized' => '#6B7280',
        ];

        // Category icons
        $icons = [
            'marketing' => 'Megaphone',
            'app-mobile-development' => 'DevicePhoneMobile',
            'app-development' => 'DevicePhoneMobile',
            'web-development' => 'Code',
            'uncategorized' => 'Folder',
        ];

        // Group categories by base slug (EN categories with ES translations)
        $categoryGroups = [];

        foreach ($wpCategories as $edge) {
            $cat = $edge['node'];
            $slug = $cat['slug'];
            $lang = $cat['language']['code'] ?? 'EN';

            // Normalize slug (remove -es suffix for matching)
            $baseSlug = preg_replace('/-es$/', '', $slug);

            if (!isset($categoryGroups[$baseSlug])) {
                $categoryGroups[$baseSlug] = ['en' => null, 'es' => null];
            }

            if ($lang === 'EN') {
                $categoryGroups[$baseSlug]['en'] = $cat;
            } else {
                $categoryGroups[$baseSlug]['es'] = $cat;
            }
        }

        // Create categories
        foreach ($categoryGroups as $baseSlug => $group) {
            $enCat = $group['en'];
            $esCat = $group['es'];

            // Skip if no English version (we use EN as base)
            if (!$enCat) {
                continue;
            }

            // Skip test/dummy categories
            if (in_array($baseSlug, ['cumque', 'dolorem', 'in', 'pariatur', 'porro'])) {
                continue;
            }

            $category = PostCategory::updateOrCreate(
                ['slug' => $baseSlug],
                [
                    'name' => $enCat['name'],
                    'name_es' => $esCat['name'] ?? $enCat['name'],
                    'slug' => $baseSlug,
                    'description' => $enCat['description'],
                    'description_es' => $esCat['description'] ?? $enCat['description'],
                    'icon' => $icons[$baseSlug] ?? 'Document',
                    'color' => $colors[$baseSlug] ?? '#6B7280',
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );

            // Map both EN and ES slugs to this category
            $categoryMap[$enCat['slug']] = $category;
            if ($esCat) {
                $categoryMap[$esCat['slug']] = $category;
            }

            $this->command->info("Created category: {$enCat['name']}");
        }

        return $categoryMap;
    }

    /**
     * Create posts in local database.
     */
    private function createPosts(array $wpPosts, array $categoryMap, User $author): void
    {
        // Separate posts by language
        $enPosts = [];
        $esPosts = [];

        foreach ($wpPosts as $edge) {
            $post = $edge['node'];
            $lang = $post['language']['code'] ?? 'EN';

            if ($lang === 'EN') {
                $enPosts[$post['slug']] = $post;
            } else {
                $esPosts[$post['slug']] = $post;
            }
        }

        // Create posts (use EN as base, add ES as translation)
        foreach ($enPosts as $enSlug => $enPost) {
            // Find Spanish translation
            $translations = $enPost['translations'] ?? [];
            $esSlug = null;
            foreach ($translations as $trans) {
                if ($trans['language']['code'] === 'ES') {
                    $esSlug = $trans['slug'];
                    break;
                }
            }
            $esPost = $esSlug ? ($esPosts[$esSlug] ?? null) : null;

            // Get category
            $catEdges = $enPost['categories']['edges'] ?? [];
            $catSlug = $catEdges[0]['node']['slug'] ?? 'uncategorized';
            $category = $categoryMap[$catSlug] ?? $categoryMap['uncategorized'] ?? null;

            if (!$category) {
                // Create uncategorized if doesn't exist
                $category = PostCategory::firstOrCreate(
                    ['slug' => 'uncategorized'],
                    [
                        'name' => 'Uncategorized',
                        'name_es' => 'Sin Categoría',
                        'is_active' => true,
                        'sort_order' => 99,
                    ]
                );
            }

            // Get featured image
            $featuredImage = $enPost['featuredImage']['node']['sourceUrl'] ?? null;
            $featuredImageAlt = $enPost['featuredImage']['node']['altText'] ?? null;

            // Clean excerpt (remove HTML)
            $excerptEn = strip_tags($enPost['excerpt'] ?? '');
            $excerptEs = $esPost ? strip_tags($esPost['excerpt'] ?? '') : null;

            // Calculate reading time (approx 200 words per minute)
            $contentEn = strip_tags($enPost['content'] ?? '');
            $wordCount = str_word_count($contentEn);
            $readingTime = max(1, ceil($wordCount / 200));

            // Convert HTML content to Editor.js format
            $contentEditorJs = $this->htmlToEditorJs($enPost['content'] ?? '');
            $contentEditorJsEs = $esPost ? $this->htmlToEditorJs($esPost['content'] ?? '') : null;

            $post = Post::updateOrCreate(
                ['slug' => $enSlug],
                [
                    'title' => $enPost['title'],
                    'title_es' => $esPost['title'] ?? null,
                    'slug' => $enSlug,
                    'category_id' => $category->id,
                    'author_id' => $author->id,
                    'excerpt' => $excerptEn,
                    'excerpt_es' => $excerptEs,
                    'content' => $contentEditorJs,
                    'content_es' => $contentEditorJsEs,
                    'featured_image' => $featuredImage,
                    'featured_image_alt' => $featuredImageAlt,
                    'featured_image_alt_es' => $esPost['featuredImage']['node']['altText'] ?? $featuredImageAlt,
                    'reading_time_minutes' => $readingTime,
                    'is_featured' => false,
                    'is_published' => true,
                    'published_at' => $enPost['date'] ? \Carbon\Carbon::parse($enPost['date']) : now(),
                    'meta_title' => $enPost['title'],
                    'meta_title_es' => $esPost['title'] ?? null,
                    'meta_description' => substr($excerptEn, 0, 160),
                    'meta_description_es' => $excerptEs ? substr($excerptEs, 0, 160) : null,
                    'sort_order' => 0,
                    'views_count' => rand(50, 500),
                    'likes_count' => rand(5, 50),
                ]
            );

            // Handle tags
            $tagIds = $this->createTags($enPost, $esPost);
            if (!empty($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            $this->command->info("Created post: {$enPost['title']}");
        }
    }

    /**
     * Create tags from post data.
     */
    private function createTags(array $enPost, ?array $esPost): array
    {
        $tagIds = [];

        $enTags = $enPost['tags']['edges'] ?? [];
        $esTags = $esPost['tags']['edges'] ?? [];

        // Create a map of ES tags by slug for matching
        $esTagMap = [];
        foreach ($esTags as $edge) {
            $esTagMap[$edge['node']['slug']] = $edge['node']['name'];
        }

        foreach ($enTags as $edge) {
            $tagNode = $edge['node'];
            $slug = $tagNode['slug'];
            $nameEn = $tagNode['name'];
            $nameEs = $esTagMap[$slug] ?? $nameEn;

            $tag = PostTag::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $nameEn,
                    'name_es' => $nameEs,
                    'is_active' => true,
                ]
            );

            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }

    /**
     * Convert HTML content to Editor.js format.
     */
    private function htmlToEditorJs(string $html): string
    {
        $blocks = [];

        // Clean up the HTML
        $html = trim($html);

        // Split by block-level elements
        $pattern = '/<(h[1-6]|p|ul|ol|blockquote)[^>]*>(.*?)<\/\1>/is';

        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tag = strtolower($match[1]);
            $content = $match[2];

            switch ($tag) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    $level = (int) substr($tag, 1);
                    $text = strip_tags($content);
                    // Remove "wp-block-heading" class text if present
                    $text = preg_replace('/class="[^"]*"/', '', $text);
                    $text = trim($text);
                    if (!empty($text)) {
                        $blocks[] = [
                            'type' => 'header',
                            'data' => [
                                'text' => $text,
                                'level' => $level,
                            ],
                        ];
                    }
                    break;

                case 'p':
                    // Keep basic HTML formatting (links, bold, italic)
                    $text = strip_tags($content, '<a><strong><em><b><i>');
                    $text = trim($text);
                    if (!empty($text) && $text !== '&nbsp;') {
                        $blocks[] = [
                            'type' => 'paragraph',
                            'data' => [
                                'text' => $text,
                            ],
                        ];
                    }
                    break;

                case 'ul':
                case 'ol':
                    preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $content, $liMatches);
                    $items = array_map(function ($item) {
                        return strip_tags($item, '<a><strong><em><b><i>');
                    }, $liMatches[1] ?? []);

                    if (!empty($items)) {
                        $blocks[] = [
                            'type' => 'list',
                            'data' => [
                                'style' => $tag === 'ol' ? 'ordered' : 'unordered',
                                'items' => $items,
                            ],
                        ];
                    }
                    break;

                case 'blockquote':
                    $text = strip_tags($content);
                    if (!empty($text)) {
                        $blocks[] = [
                            'type' => 'quote',
                            'data' => [
                                'text' => trim($text),
                                'caption' => '',
                            ],
                        ];
                    }
                    break;
            }
        }

        // If no blocks were parsed, create a single paragraph with the content
        if (empty($blocks)) {
            $text = strip_tags($html, '<a><strong><em><b><i>');
            if (!empty(trim($text))) {
                $blocks[] = [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => trim($text),
                    ],
                ];
            }
        }

        return json_encode([
            'time' => time() * 1000,
            'blocks' => $blocks,
            'version' => '2.28.0',
        ], JSON_UNESCAPED_UNICODE);
    }
}
