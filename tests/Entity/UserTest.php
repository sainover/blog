<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function notVerifiedStatus()
    {
        $user = new User();
        $user->setFullName('Jane Dark')
            ->setEmail('jane@dark.net')
            ->setPassword('12345678')
        ;

        $this->assertEquals(User::STATUS_NOT_VERIFIED, $user->getStatus());
    }

    public function testRole()
    {
        $user = new User();
        $user->setFullName('Tom Dark')
            ->setEmail('tom@dark.net')
            ->setPassword('12345678')
        ;

        $this->assertEquals([User::ROLE_USER], $user->getRoles());
    }

    public function activeStatus()
    {
        $user = new User();
        $user->setFullName('Jarry Dark')
            ->setEmail('jarry@dark.net')
            ->setStatus(User::STATUS_ACTIVE)
            ->setPassword('12345678')
        ;

        $this->assertEquals(true, $user->isActive());
    }
}
