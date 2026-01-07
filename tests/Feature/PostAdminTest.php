<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PostAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PostCategory $category;
    protected PostTag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with admin role
        $this->user = User::factory()->create();

        // Create super admin role (bypasses all permission checks)
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'level' => 100,
            'is_system' => true,
        ]);

        $this->user->assignRole($superAdminRole);

        // Create test category
        $this->category = PostCategory::create([
            'name' => 'Test Category',
            'name_es' => 'Categoría de Prueba',
            'slug' => 'test-category',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create test tag
        $this->tag = PostTag::create([
            'name' => 'Test Tag',
            'name_es' => 'Etiqueta de Prueba',
            'slug' => 'test-tag',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function guests_cannot_access_posts_admin(): void
    {
        $response = $this->get('/admin/posts');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_users_can_access_posts_index(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/posts');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_access_create_page(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/posts/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_users_can_access_edit_page(): void
    {
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/admin/posts/{$post->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function post_model_uses_soft_deletes(): void
    {
        $post = Post::create([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $post->delete();

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);

        $this->assertNull(Post::find($post->id));
        $this->assertNotNull(Post::withTrashed()->find($post->id));
    }

    /** @test */
    public function post_model_auto_generates_uuid(): void
    {
        $post = Post::create([
            'title' => 'Test UUID',
            'slug' => 'test-uuid',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $this->assertNotNull($post->uuid);
        $this->assertTrue(Str::isUuid($post->uuid));
    }

    /** @test */
    public function post_belongs_to_category(): void
    {
        $post = Post::create([
            'title' => 'Test Category Relation',
            'slug' => 'test-category-relation',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $this->assertEquals($this->category->id, $post->category->id);
        $this->assertEquals('Test Category', $post->category->name);
    }

    /** @test */
    public function post_belongs_to_author(): void
    {
        $post = Post::create([
            'title' => 'Test Author Relation',
            'slug' => 'test-author-relation',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $this->assertEquals($this->user->id, $post->author->id);
    }

    /** @test */
    public function post_has_many_tags(): void
    {
        $post = Post::create([
            'title' => 'Test Tags Relation',
            'slug' => 'test-tags-relation',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $post->tags()->attach($this->tag->id);

        $this->assertCount(1, $post->tags);
        $this->assertEquals($this->tag->id, $post->tags->first()->id);
    }

    /** @test */
    public function published_scope_filters_posts(): void
    {
        Post::create([
            'title' => 'Published',
            'slug' => 'published',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::create([
            'title' => 'Draft',
            'slug' => 'draft',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'is_published' => false,
        ]);

        $published = Post::published()->get();

        $this->assertCount(1, $published);
        $this->assertEquals('Published', $published->first()->title);
    }

    /** @test */
    public function featured_scope_filters_posts(): void
    {
        Post::create([
            'title' => 'Featured',
            'slug' => 'featured',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'is_featured' => true,
        ]);

        Post::create([
            'title' => 'Regular',
            'slug' => 'regular',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'is_featured' => false,
        ]);

        $featured = Post::featured()->get();

        $this->assertCount(1, $featured);
        $this->assertEquals('Featured', $featured->first()->title);
    }

    /** @test */
    public function post_can_have_bilingual_content(): void
    {
        $post = Post::create([
            'title' => 'English Title',
            'title_es' => 'Spanish Title',
            'slug' => 'bilingual-post',
            'excerpt' => 'English excerpt',
            'excerpt_es' => 'Spanish excerpt',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        $this->assertEquals('English Title', $post->title);
        $this->assertEquals('Spanish Title', $post->title_es);
        $this->assertEquals('English excerpt', $post->excerpt);
        $this->assertEquals('Spanish excerpt', $post->excerpt_es);
    }

    /** @test */
    public function post_localized_title_returns_spanish_when_locale_is_es(): void
    {
        $post = Post::create([
            'title' => 'English Title',
            'title_es' => 'Spanish Title',
            'slug' => 'localization-test',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
        ]);

        app()->setLocale('es');
        $this->assertEquals('Spanish Title', $post->localized_title);

        app()->setLocale('en');
        $this->assertEquals('English Title', $post->localized_title);
    }

    /** @test */
    public function post_can_have_seo_fields(): void
    {
        $post = Post::create([
            'title' => 'SEO Test',
            'slug' => 'seo-test',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'meta_title' => 'Custom SEO Title',
            'meta_title_es' => 'Título SEO Personalizado',
            'meta_description' => 'Custom meta description',
            'meta_description_es' => 'Descripción meta personalizada',
        ]);

        $this->assertEquals('Custom SEO Title', $post->meta_title);
        $this->assertEquals('Título SEO Personalizado', $post->meta_title_es);
        $this->assertEquals('Custom meta description', $post->meta_description);
        $this->assertEquals('Descripción meta personalizada', $post->meta_description_es);
    }

    /** @test */
    public function post_can_store_json_content(): void
    {
        $content = json_encode([
            'time' => time(),
            'blocks' => [
                ['type' => 'header', 'data' => ['text' => 'Hello', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'This is content']],
            ],
            'version' => '2.28.0',
        ]);

        $post = Post::create([
            'title' => 'JSON Content Test',
            'slug' => 'json-content-test',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'content' => $content,
        ]);

        $this->assertNotNull($post->content);
        $decoded = json_decode($post->content, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('blocks', $decoded);
    }

    /** @test */
    public function post_increments_views_count(): void
    {
        $post = Post::create([
            'title' => 'Views Test',
            'slug' => 'views-test',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'views_count' => 0,
        ]);

        $this->assertEquals(0, $post->views_count);

        $post->increment('views_count');

        $this->assertEquals(1, $post->fresh()->views_count);
    }

    /** @test */
    public function post_can_toggle_published_status(): void
    {
        $post = Post::create([
            'title' => 'Toggle Test',
            'slug' => 'toggle-test',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/admin/posts/{$post->id}/toggle-published");

        $response->assertStatus(200);
        $this->assertTrue($post->fresh()->is_published);
    }

    /** @test */
    public function post_can_toggle_featured_status(): void
    {
        $post = Post::create([
            'title' => 'Featured Toggle Test',
            'slug' => 'featured-toggle-test',
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'is_featured' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/admin/posts/{$post->id}/toggle-featured");

        $response->assertStatus(200);
        $this->assertTrue($post->fresh()->is_featured);
    }
}
