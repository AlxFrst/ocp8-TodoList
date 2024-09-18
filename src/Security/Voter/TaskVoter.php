<?php

namespace App\Security\Voter;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const EDIT = 'TASK_EDIT';
    public const CREATE = 'TASK_CREATE';
    public const VIEW = 'TASK_VIEW';
    public const DELETE = 'TASK_DELETE';

    private function isAuthorOrAdmin(mixed $subject, UserInterface $user): bool
    {
        if ($subject->getAuthor()->getUsername() === 'Anonyme' && in_array('ROLE_ADMIN',$user->getRoles())) {
            return true;
        }
        if ($subject->getAuthor() === $user) {
            return true;
        }
        return false;
    }

    private function isAuthor(mixed $subject, UserInterface $user): bool
    {
        if ($subject->getAuthor() === $user) {
            return true;
        }
        return false;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::CREATE, self::DELETE])
            && $subject instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->isAuthor($subject, $user);
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                return true;
                break;
            case self::DELETE:
                return $this->isAuthorOrAdmin($subject, $user);
                break;
            case self::CREATE:
                return true;
                break;
        }

        return false;
    }

}
