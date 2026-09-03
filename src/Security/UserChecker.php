<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Twoje konto jest nieaktywne.'
            );
        }

        if (
            in_array('ROLE_CLIENT', $user->getRoles(), true)
            && $user->getCompany()
            && !$user->getCompany()->isActive()
        ) {
            throw new CustomUserMessageAccountStatusException(
                'Konto Twojej firmy jest nieaktywne.'
            );
        }
    }
}