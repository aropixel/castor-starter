<?php

namespace App\Tests\Functional\Blog;

use App\Tests\Functional\WebTestCase;

class PostTest extends WebTestCase
{
    public function testListContainsPosts(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/blog/post/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Introduction à Symfony');
    }

    public function testNewPostFormIsAccessible(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/blog/post/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditPostFormIsAccessible(): void
    {
        $client = $this->loginAsAdmin();

        // Récupère l'id du premier post via la liste
        $crawler = $client->request('GET', '/admin/blog/post/');
        $link = $crawler->filter('a[href*="/edit"]')->first();
        if ($link->count() === 0) {
            $this->markTestSkipped('Aucun lien d\'édition trouvé dans la liste.');
        }

        $client->click($link->link());
        $this->assertResponseIsSuccessful();
    }

    public function testToggleStatusReturnsJson(): void
    {
        $client = $this->loginAsAdmin();

        // Récupère l'id du premier post
        $crawler = $client->request('GET', '/admin/blog/post/');
        $link = $crawler->filter('a[href*="/status"]')->first();
        if ($link->count() === 0) {
            $this->markTestSkipped('Aucun lien de statut trouvé dans la liste.');
        }

        preg_match('#/blog/post/(\d+)/status#', $link->attr('href'), $m);
        if (empty($m[1])) {
            $this->markTestSkipped('Impossible d\'extraire l\'id du post.');
        }

        $client->request('POST', '/admin/blog/post/' . $m[1] . '/status', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $data);
    }
}
