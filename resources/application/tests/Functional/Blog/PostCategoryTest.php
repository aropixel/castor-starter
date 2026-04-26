<?php

namespace App\Tests\Functional\Blog;

use App\Tests\Functional\WebTestCase;

class PostCategoryTest extends WebTestCase
{
    public function testListContainsCategories(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/blog/category/index');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Actualités');
    }

    public function testNewCategoryFormIsAccessible(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/blog/category/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditCategoryFormIsAccessible(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/admin/blog/category/index');
        $link = $crawler->filter('a[href*="/edit"]')->first();
        if ($link->count() === 0) {
            $this->markTestSkipped('Aucun lien d\'édition trouvé dans la liste.');
        }

        $client->click($link->link());
        $this->assertResponseIsSuccessful();
    }
}
