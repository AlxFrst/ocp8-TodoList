<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    const TASK_EDIT = 'task_edit';
    const TASK_DELETE = 'task_delete';
    const ROLE_ANONYMOUS = 'ROLE_ANONYMOUS';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $task): bool
    {
        return in_array($attribute, [self::TASK_EDIT, self::TASK_DELETE])
            && $task instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface || null !== $task->getUser()) {
            switch ($attribute) {
                case self::TASK_EDIT:
                    return $this->canEdit($task, $user);
                    break;
                case self::TASK_DELETE:
                    return $this->canDelete($task, $user);
                    break;
            }
        }

        if (null === $task->getUser()) {
            return false;
        }

        switch ($attribute) {
            case self::TASK_EDIT:
                return $this->canEdit($task, $user);
                break;
            case self::TASK_DELETE:
                return $this->canDelete($task, $user);
                break;
        }

        return false;
    }

    private function canEdit(Task $task, User $user)
    {
        return $user === $task->getUser();
    }

    private function canDelete(Task $task, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        return $user === $task->getUser();
    }
}