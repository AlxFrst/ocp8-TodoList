<?php

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoterTest extends TestCase
{
    private UserVoter $voter;
    private Security $security;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->voter = new UserVoter($this->security);
    }

    public function testVoteOnAttributeView()
    {
        $user = $this->createMock(User::class);
        $subject = $this->createMock(User::class);
        $token = $this->createMock(TokenInterface::class);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        // Test when user is viewing their own profile
        $user->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $subject->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->assertTrue($this->voter->vote($token, $subject, [UserVoter::USER_VIEW]));

        // Test when admin is viewing any profile
        $this->security->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $this->assertTrue($this->voter->vote($token, $subject, [UserVoter::USER_VIEW]));
    }

    public function testVoteOnAttributeEdit()
    {
        $user = $this->createMock(User::class);
        $subject = $this->createMock(User::class);
        $token = $this->createMock(TokenInterface::class);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        // Test when user is editing their own profile
        $user->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $subject->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->assertTrue($this->voter->vote($token, $subject, [UserVoter::USER_EDIT]));

        // Test when admin is editing any profile
        $this->security->expects($this->any())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $this->assertTrue($this->voter->vote($token, $subject, [UserVoter::USER_EDIT]));
    }

    public function testVoteOnAttributeDelete()
    {
        $user = $this->createMock(User::class);
        $subject = $this->createMock(User::class);
        $token = $this->createMock(TokenInterface::class);

        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        // Test when non-admin tries to delete
        $this->security->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $this->assertFalse($this->voter->vote($token, $subject, [UserVoter::USER_DELETE]));

        // Test when admin tries to delete
        $this->security->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $this->assertTrue($this->voter->vote($token, $subject, [UserVoter::USER_DELETE]));
    }
}