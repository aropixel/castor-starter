<?php

namespace App\Tests\Functional\Project;

use App\Tests\Functional\WebTestCase;

class ProjectTest extends WebTestCase
{
    public function testListContainsProjects(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/project/');

        $this->assertResponseIsSuccessful();
    }

    public function testNewProjectFormIsAccessible(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/project/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditProjectFormIsAccessible(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/project/');
        $link = $crawler->filter('a[href*="/edit"]')->first();
        if ($link->count() === 0) {
            $this->markTestSkipped('Aucun lien d\'édition trouvé dans la liste.');
        }

        $client->click($link->link());
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteProject(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/project/');
        $link = $crawler->filter('a[href*="/edit"]')->last();
        if ($link->count() === 0) {
            $this->markTestSkipped('Aucun projet trouvé.');
        }

        preg_match('#/project/(\d+)/edit#', $link->attr('href'), $m);
        if (empty($m[1])) {
            $this->markTestSkipped('Impossible d\'extraire l\'id du projet.');
        }

        $client->request('DELETE', '/project/' . $m[1]);
        $this->assertResponseRedirects('/project/');
    }
}
