<?php

namespace App\Tests\Functional\Menu;

use App\Tests\Functional\WebTestCase;

class MenuTest extends WebTestCase
{
    public function testMainMenuIsAccessible(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/menu/main/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Navigation principale');
    }

    public function testFooterMenuIsAccessible(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/menu/footer/edit');

        $this->assertResponseIsSuccessful();
    }

    public function testSaveMenuItemReturnsSuccess(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('POST', '/admin/menu/save', [
            'type' => 'main',
            'menu' => [],
        ]);

        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302),
            )
        );
    }
}
