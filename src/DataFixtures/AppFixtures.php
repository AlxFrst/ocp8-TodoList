<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\TaskFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const USER_REFERENCE = 'user';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        
        $user = UserFactory::createOne([
            'email' => 'anonym@.com',
            'password' => '$2y$13$2mdoEUC2eiMVAwrd.uks4uMGhu7Ug6MNSda2cj0ykd2JFVD4q4Aem',
            'roles' => ["ROLE_USER"],
            'username' => 'Anonyme',

        ]);

        $user1 = UserFactory::createOne([
            'email' => 'test@gmail.com',
            'password' => 'test',
            'roles' => ["ROLE_USER"],
            'username' => 'test',

        ]);

        $user2 = UserFactory::createOne([
            'email' => 'admin@gmail.com',
            'password' => 'admin',
            'roles' => ["ROLE_ADMIN"],
            'username' => 'Admin1',

        ]);

        $user3 = UserFactory::createOne([
            'email' => 'admin2@gmail.com',
            'password' => 'admin',
            'roles' => ["ROLE_ADMIN"],
            'username' => 'Admin2',

        ]);



        UserFactory::createMany(10);

        TaskFactory::createMany(50, function() {
            return [
                'author' => UserFactory::random()
            ];
        });
        TaskFactory::createOne([
            'title' => 'Test task 1',
            'content' => 'This is a test task',
            'isDone' => true,
            'author' => $user1
        ]);
        TaskFactory::createOne([
            'title' => 'Test task 2',
            'content' => 'This is a test task',
            'isDone' => false,
            'author' => $user1
        ]);

        
    }
}
