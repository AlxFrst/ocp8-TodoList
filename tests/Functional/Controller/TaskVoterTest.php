<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class TaskVoterTest extends TestCase
{
    private TaskVoter $voter;
    private Security $security;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->voter = new TaskVoter($this->security);
    }

    public function testVoteOnAttributeEditAsOwner()
    {
        $user = $this->createMock(User::class);
        $task = $this->createMock(Task::class);
        $token = $this->createMock(TokenInterface::class);

        $task->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $task, [TaskVoter::TASK_EDIT])
        );
    }

    public function testVoteOnAttributeEditAsNonOwner()
    {
        $user = $this->createMock(User::class);
        $otherUser = $this->createMock(User::class);
        $task = $this->createMock(Task::class);
        $token = $this->createMock(TokenInterface::class);

        $task->expects($this->any())
            ->method('getUser')
            ->willReturn($otherUser);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $task, [TaskVoter::TASK_EDIT])
        );
    }

    public function testVoteOnAttributeDeleteAsOwner()
    {
        $user = $this->createMock(User::class);
        $task = $this->createMock(Task::class);
        $token = $this->createMock(TokenInterface::class);

        $task->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $task, [TaskVoter::TASK_DELETE])
        );
    }

    public function testVoteOnAttributeDeleteAsAdmin()
    {
        $admin = $this->createMock(User::class);
        $task = $this->createMock(Task::class);
        $token = $this->createMock(TokenInterface::class);

        $task->expects($this->any())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($admin);

        $this->security->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $task, [TaskVoter::TASK_DELETE])
        );
    }

    public function testVoteOnAttributeDeleteAsNonOwnerNonAdmin()
    {
        $user = $this->createMock(User::class);
        $otherUser = $this->createMock(User::class);
        $task = $this->createMock(Task::class);
        $token = $this->createMock(TokenInterface::class);

        $task->expects($this->any())
            ->method('getUser')
            ->willReturn($otherUser);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->security->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $task, [TaskVoter::TASK_DELETE])
        );
    }
}