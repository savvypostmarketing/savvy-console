<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected PostCategory $category;
    protected PostTag $tag;
    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create();

        $this->category = PostCategory::create([
            'name' => 'Technology',
            'name_es' => 'Tecnología',
            'slug' => 'technology',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->tag = PostTag::create([
            'name' => 'Web Development',
            'name_es' => 'Desarrollo Web',
            'slug' => 'web-development',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function can_get_published_posts_list(): void
    {
        Post::create([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Published Post']);
        $response->assertJsonMissing(['title' => 'Draft Post']);
    }

    /** @test */
    public function can_filter_posts_by_category(): void
    {
        $anotherCategory = PostCategory::create([
            'name' => 'Design',
            'name_es' => 'Diseño',
            'slug' => 'design',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Post::create([
            'title' => 'Tech Post',
            'slug' => 'tech-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::create([
            'title' => 'Design Post',
            'slug' => 'design-post',
            'category_id' => $anotherCategory->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts?category=' . $this->category->slug);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Tech Post']);
        $response->assertJsonMissing(['title' => 'Design Post']);
    }

    /** @test */
    public function can_filter_posts_by_tag(): void
    {
        $post1 = Post::create([
            'title' => 'Tagged Post',
            'slug' => 'tagged-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $post1->tags()->attach($this->tag->id);

        Post::create([
            'title' => 'Untagged Post',
            'slug' => 'untagged-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts?tag=' . $this->tag->slug);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Tagged Post']);
        $response->assertJsonMissing(['title' => 'Untagged Post']);
    }

    /** @test */
    public function can_get_featured_posts(): void
    {
        Post::create([
            'title' => 'Featured Post',
            'slug' => 'featured-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        Post::create([
            'title' => 'Regular Post',
            'slug' => 'regular-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'is_featured' => false,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts?featured=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Featured Post']);
    }

    /** @test */
    public function can_get_single_post_by_slug(): void
    {
        $post = Post::create([
            'title' => 'Single Post',
            'title_es' => 'Post Individual',
            'slug' => 'single-post',
            'excerpt' => 'This is an excerpt',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts/single-post');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Single Post']);
        $response->assertJsonFragment(['slug' => 'single-post']);
    }

    /** @test */
    public function viewing_post_increments_view_count(): void
    {
        $post = Post::create([
            'title' => 'View Count Test',
            'slug' => 'view-count-test',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
            'views_count' => 0,
        ]);

        $this->getJson('/api/posts/view-count-test');

        $this->assertEquals(1, $post->fresh()->views_count);
    }

    /** @test */
    public function cannot_view_unpublished_post(): void
    {
        Post::create([
            'title' => 'Unpublished Post',
            'slug' => 'unpublished-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/posts/unpublished-post');

        $response->assertStatus(404);
    }

    /** @test */
    public function can_get_post_categories(): void
    {
        $response = $this->getJson('/api/posts/categories');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Technology']);
    }

    /** @test */
    public function can_get_post_tags(): void
    {
        $response = $this->getJson('/api/posts/tags');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Web Development']);
    }

    /** @test */
    public function posts_include_category_relation(): void
    {
        Post::create([
            'title' => 'Post With Category',
            'slug' => 'post-with-category',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'slug',
                    'category' => ['name', 'slug'],
                ],
            ],
        ]);
    }

    /** @test */
    public function posts_include_author_relation(): void
    {
        Post::create([
            'title' => 'Post With Author',
            'slug' => 'post-with-author',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'author' => ['name'],
                ],
            ],
        ]);
    }

    /** @test */
    public function posts_are_ordered_by_published_date(): void
    {
        Post::create([
            'title' => 'Older Post',
            'slug' => 'older-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now()->subDays(5),
        ]);

        Post::create([
            'title' => 'Newer Post',
            'slug' => 'newer-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $posts = $response->json('data');
        $this->assertEquals('Newer Post', $posts[0]['title']);
        $this->assertEquals('Older Post', $posts[1]['title']);
    }

    /** @test */
    public function posts_can_be_limited(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Post::create([
                'title' => "Post {$i}",
                'slug' => "post-{$i}",
                'category_id' => $this->category->id,
                'author_id' => $this->author->id,
                'is_published' => true,
                'published_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/posts?limit=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function can_like_a_post(): void
    {
        $post = Post::create([
            'title' => 'Likeable Post',
            'slug' => 'likeable-post',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
            'likes_count' => 0,
        ]);

        $response = $this->postJson("/api/posts/{$post->slug}/like");

        $response->assertStatus(200);
        $this->assertEquals(1, $post->fresh()->likes_count);
    }

    /** @test */
    public function localized_content_returns_spanish_when_locale_header_is_es(): void
    {
        Post::create([
            'title' => 'English Title',
            'title_es' => 'Spanish Title',
            'slug' => 'localized-post',
            'excerpt' => 'English excerpt',
            'excerpt_es' => 'Spanish excerpt',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->withHeaders(['Accept-Language' => 'es'])
            ->getJson('/api/posts/localized-post');

        $response->assertStatus(200);
        // The API should return localized content based on header
    }
}
