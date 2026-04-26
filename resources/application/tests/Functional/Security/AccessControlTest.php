<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\WebTestCase;

class AccessControlTest extends WebTestCase
{
    public function testAdminCanAccessUserList(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/user/');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCannotCreateUser(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/user/new');

        // ROLE_SUPER_ADMIN requis — 403 attendu
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessPostList(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/blog/post/');

        $this->assertResponseIsSuccessful();
    }

    public function testUnauthenticatedCannotAccessAdmin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/user/');

        $this->assertResponseRedirects('/admin/login');
    }
}
