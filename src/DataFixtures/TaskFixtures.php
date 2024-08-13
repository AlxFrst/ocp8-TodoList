<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            if ($i <= 0) {
                $task = new Task();
                $task->setTitle($this->faker->sentence());
                $task->setContent($this->faker->paragraph());
                $task->setCreatedAt($this->faker->dateTimeBetween('-1 week', '+1 week'));
                $task->setIsDone($this->faker->boolean());
                $task->setUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));


                $manager->persist($task);
            }
            if ($i <= 10 && $i != 0) {
                $task = new Task();
                $task->setTitle($this->faker->sentence());
                $task->setContent($this->faker->paragraph());
                $task->setCreatedAt($this->faker->dateTimeBetween('-1 week', '+1 week'));
                $task->setIsDone($this->faker->boolean());
                $task->setUser($this->getReference(UserFixtures::USER_REFERENCE . $this->faker->numberBetween(1, 10)));

                $manager->persist($task);
            }

            if ($i > 10) {
                $task = new Task();
                $task->setTitle($this->faker->sentence());
                $task->setContent($this->faker->paragraph());
                $task->setCreatedAt($this->faker->dateTimeBetween('-1 week', '+1 week'));
                $task->setIsDone($this->faker->boolean());
                $task->setUser($this->getReference(UserFixtures::ANONYMOUS_USER_REFERENCE . $this->faker->numberBetween(1, 5)));

                $manager->persist($task);
            }
        }
        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}