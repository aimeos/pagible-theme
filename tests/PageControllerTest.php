<?php

/**
 * @license LGPL, https://opensource.org/license/lgpl-3-0
 */


namespace Tests;

use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Resource;
use Aimeos\Cms\Tenancy;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;


class PageControllerTest extends ThemeTestAbstract
{
    use CmsWithMigrations;
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new \App\Models\User();
        $this->user->name = 'Test';
        $this->user->email = 'test@example.com';
        $this->user->cmsperms = ['admin'];
    }


    public function testLatestFindsChangedPath()
    {
        Tenancy::$callback = fn() => 'demo';

        $this->seed( CmsSeeder::class );

        $page = Page::where( 'tag', 'blog' )->firstOrFail();

        // Save with a new path
        Resource::savePage(
            $page->id,
            ['path' => 'new-blog-path', 'domain' => $page->domain ?? ''],
            $this->user,
            'test@example.com',
        );

        // Now try to access the page via the new path (as an editor would)
        $response = $this->actingAs( $this->user )->get( '/new-blog-path' );
        $response->assertStatus( 200 );
    }


    public function testLatestFindsChangedPathNoDomain()
    {
        Tenancy::$callback = fn() => 'demo';

        $this->seed( CmsSeeder::class );

        $page = Page::where( 'tag', 'article' )->firstOrFail();

        // Save with a new path (page has empty domain)
        Resource::savePage(
            $page->id,
            ['path' => 'changed-article-path'],
            $this->user,
            'test@example.com',
        );

        // Try to access via new path
        $response = $this->actingAs( $this->user )->get( '/changed-article-path' );
        $response->assertStatus( 200 );
    }
}
