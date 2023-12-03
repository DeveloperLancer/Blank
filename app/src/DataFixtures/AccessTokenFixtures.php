<?php

namespace App\DataFixtures;

use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccessTokenFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $accessToken = new AccessToken();
            $accessToken->setToken($user->getEmail());
            $accessToken->setUser($user);
            $accessToken->setExpiresAt(new \DateTimeImmutable("+24 hours"));
            $accessToken->setActive(true);
            $manager->persist($accessToken);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}
