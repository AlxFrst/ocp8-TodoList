<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin_user';
    public const USER_REFERENCE = 'user_';
    public const ANONYMOUS_USER_REFERENCE = 'anonymous_user_';

    private UserPasswordHasherInterface $hasher;
    private \Faker\Generator $faker;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
        $this->faker = Factory::create('fr_FR');
    }


    public function load(ObjectManager $manager): void
    {
        // ADMIN
        $userAdmin = new User();
        $password = "adminadmin";
        $passwordEncode = $this->encodePassword($userAdmin, $password);

        $userAdmin->setUsername('admin');
        $userAdmin->setEmail('admin@admin.com');
        $userAdmin->setPassword($passwordEncode);
        $userAdmin->setRoles(['ROLE_ADMIN']);

        $manager->persist($userAdmin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $userAdmin);


        // USER

        for ($nbUser = 1; $nbUser <= 10; $nbUser++) {
            $user = new User();
            $password = "azertyazerty";
            $passwordEncode = $this->encodePassword($user, $password);
            $user->setUsername($this->faker->userName());
            $user->setEmail($this->faker->email());
            $user->setPassword($passwordEncode);
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE . $nbUser, $user);
        }

        for ($nbUserAnonymous = 1; $nbUserAnonymous <= 5; $nbUserAnonymous++) {
            $userAnonymous = new User();
            $password = "azertyazerty";
            $passwordEncode = $this->encodePassword($userAnonymous, $password);
            $userAnonymous->setUsername($this->faker->userName());
            $userAnonymous->setEmail('anonymous-' . $nbUserAnonymous . '@anonymous.com');
            $userAnonymous->setPassword($passwordEncode);
            $userAnonymous->setRoles(['ROLE_ANONYMOUS']);

            $manager->persist($userAnonymous);
            $this->addReference(self::ANONYMOUS_USER_REFERENCE . $nbUserAnonymous, $userAnonymous);

        }
        $manager->flush();


    }

    private function encodePassword($user, $password): string
    {
        return $this->hasher->hashPassword($user, $password);
    }
}