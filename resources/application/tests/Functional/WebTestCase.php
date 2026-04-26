<?php

namespace App\Tests\Functional;

use Aropixel\AdminBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected function loginAs(string $email): KernelBrowser
    {
        $client = static::createClient();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
        $client->loginUser($user, 'primary_auth');
        return $client;
    }

    protected function loginAsAdmin(): KernelBrowser
    {
        return $this->loginAs('admin@example.com');
    }

    protected function loginAsSuperAdmin(): KernelBrowser
    {
        return $this->loginAs('superadmin@example.com');
    }
}
