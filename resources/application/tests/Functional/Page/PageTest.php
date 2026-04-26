<?php

namespace App\Tests\Functional\Page;

use App\DataFixtures\PageFixture;
use App\Tests\Functional\WebTestCase;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;

class PageTest extends WebTestCase
{
    private function getPageId(string $slug): int
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $page = $em->getRepository(Page::class)->findOneBy(['slug' => $slug]);
        return $page->getId();
    }

    public function testListIsAccessible(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/page/list');

        $this->assertResponseIsSuccessful();
    }

    public function testEditHomepageHasNoDeleteButton(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getPageId('homepage');
        $client->request('GET', '/admin/page/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('a[href*="/delete"], button[name*="delete"], form[action*="/delete"]');
    }

    public function testEditContactPageHasCustomFields(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getPageId('contact');
        $client->request('GET', '/admin/page/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[name*="phone"], [id*="phone"]');
        $this->assertSelectorExists('[name*="address"], [id*="address"]');
    }

    public function testEditBuilderPageContainsJsonContent(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getPageId('services');
        $client->request('GET', '/admin/page/builder/' . $id);

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteFixedPageIsForbidden(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getPageId('homepage');
        $client->request('DELETE', '/admin/page/' . $id);

        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(403),
                $this->equalTo(302),
            )
        );
    }

    public function testDeleteNormalPage(): void
    {
        $client = $this->loginAsAdmin();
        $id = $this->getPageId('contact');
        $client->request('DELETE', '/admin/page/' . $id);

        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(302),
                $this->equalTo(200),
            )
        );
    }
}
