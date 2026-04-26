<?php

namespace App\Tests\Functional\Page;

use App\Tests\Functional\WebTestCase;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;

class PageBuilderTest extends WebTestCase
{
    private function getServicesPageId(): int
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $page = $em->getRepository(Page::class)->findOneBy(['slug' => 'services']);
        return $page->getId();
    }

    public function testBuilderRequiresAuth(): void
    {
        $client = static::createClient();
        $id = $this->getServicesPageId();
        $client->request('GET', '/admin/page/builder/' . $id);

        $this->assertResponseRedirects('/admin/login');
    }

    public function testBuilderAuthenticatedReturns200(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getServicesPageId();
        $client->request('GET', '/admin/page/builder/' . $id);

        $this->assertResponseIsSuccessful();
    }

    public function testNewBuilderPageWithoutId(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/page/builder');

        $this->assertResponseIsSuccessful();
    }

    public function testPreviewBuilderPage(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getServicesPageId();
        $client->request('GET', '/admin/page/builder/' . $id . '/preview');

        $this->assertResponseIsSuccessful();
    }

    public function testJsonListReturnsArray(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/page/builder/json/list');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('slug', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
    }

    public function testSaveWithInvalidJsonReturns400(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('POST', '/admin/page/builder/save', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '');

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Invalid JSON', $data['error']);
    }

    public function testSaveWithNonExistentPageReturns404(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('POST', '/admin/page/builder/save', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['id' => 99999]));

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Page not found', $data['error']);
    }

    public function testSaveExistingPage(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getServicesPageId();

        $client->request('POST', '/admin/page/builder/save', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'id' => $id,
            'title' => 'Nouveau titre services',
            'content' => ['sections' => []],
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame($id, $data['id']);
    }

    public function testSaveCreatesNewPage(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('POST', '/admin/page/builder/save', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Nouvelle page builder',
            'content' => ['sections' => []],
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('id', $data);
        $this->assertNotNull($data['id']);
    }

    public function testSaveThenPreviewContainsRenderedContent(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getServicesPageId();

        // Sauvegarde avec un titre bloc connu
        $client->request('POST', '/admin/page/builder/save', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'id' => $id,
            'title' => 'Services',
            'content' => [
                'sections' => [
                    [
                        'background' => null,
                        'layout' => 'container',
                        'visibleDesktop' => true,
                        'visibleMobile' => true,
                        'rows' => [
                            [
                                'slider' => false,
                                'type' => 'default',
                                'align' => null,
                                'justify' => null,
                                'columns' => [
                                    [
                                        'widths' => ['s' => '1-1'],
                                        'align' => null,
                                        'link' => null,
                                        'blocks' => [
                                            ['type' => 'title', 'content' => 'Titre unique test', 'size' => 'h2'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]));

        $this->assertResponseIsSuccessful();

        // Preview doit contenir le contenu rendu
        $client->request('GET', '/admin/page/builder/' . $id . '/preview');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Titre unique test');
    }
}
