<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testAdminRedirectsToLoginWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/admin/login');
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithWrongPasswordShowsError(): void
    {
        $client = static::createClient();
        $client->request('POST', '/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        // Symfony security redirects back to login on failure
        $this->assertResponseRedirects('/admin/login');
    }

    public function testLoginAsAdmin(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginAsSuperAdmin(): void
    {
        $client = $this->loginAsSuperAdmin();
        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
    }

    public function testLogout(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/admin/logout');

        $this->assertResponseRedirects();
    }
}
