<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    const USER_EDIT = 'user_edit';
    const USER_DELETE = 'user_delete';
    const USER_VIEW = 'user_view';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::USER_EDIT, self::USER_DELETE, self::USER_VIEW])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // you know $subject is a User object, thanks to `supports()`
        /** @var User $userSubject */
        $userSubject = $subject;

        switch ($attribute) {
            case self::USER_VIEW:
                return $this->canView($userSubject, $user);
            case self::USER_EDIT:
                return $this->canEdit($userSubject, $user);
            case self::USER_DELETE:
                return $this->canDelete($userSubject, $user);
        }

        return false;
    }

    private function canView(User $userSubject, UserInterface $user): bool
    {
        // Admins can view all users
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Users can view their own profile
        return $user === $userSubject;
    }

    private function canEdit(User $userSubject, UserInterface $user): bool
    {
        // Admins can edit all users
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Users can edit their own profile
        return $user === $userSubject;
    }

    private function canDelete(User $userSubject, UserInterface $user): bool
    {
        // Only admins can delete users
        return $this->security->isGranted('ROLE_ADMIN');
    }
}