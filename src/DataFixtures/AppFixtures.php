<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUser(
            $manager,
            'admin@example.com',
            'admin123',
            ['ROLE_ADMIN'],
            'Admin',
            'System'
        );

        $this->createUser(
            $manager,
            'employee@example.com',
            'employee123',
            ['ROLE_EMPLOYEE'],
            'Employee',
            'System'
        );

        $this->createUser(
            $manager,
            'client@example.com',
            'client123',
            ['ROLE_CLIENT'],
            'Client',
            'System'
        );

        $manager->flush();
    }

    private function createUser(
        ObjectManager $manager,
        string $email,
        string $plainPassword,
        array $roles,
        string $firstName,
        string $lastName
    ): void {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plainPassword
        );

        $user->setPassword($hashedPassword);

        $manager->persist($user);
    }
}