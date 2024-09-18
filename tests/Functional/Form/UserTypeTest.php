<?php

namespace App\Tests\Form;

use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'testuser',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
            'email' => 'test@example.com',
            'roles' => 'ROLE_USER',
        ];

        $model = new User();
        $form = $this->factory->create(UserType::class, $model);

        $expected = new User();
        $expected->setUsername('testuser');
        $expected->setPassword('password123');
        $expected->setEmail('test@example.com');
        $expected->setRoles(['ROLE_USER']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getUsername(), $model->getUsername());
        $this->assertEquals($expected->getEmail(), $model->getEmail());
        $this->assertEquals($expected->getRoles(), $model->getRoles());
        // Note: We don't test the password here as it might be encoded
    }

    public function testFormStructure()
    {
        $form = $this->factory->create(UserType::class);
        
        $this->assertArrayHasKey('username', $form->all());
        $this->assertInstanceOf(TextType::class, $form->get('username')->getConfig()->getType()->getInnerType());

        $this->assertArrayHasKey('password', $form->all());
        $this->assertInstanceOf(RepeatedType::class, $form->get('password')->getConfig()->getType()->getInnerType());

        $this->assertArrayHasKey('email', $form->all());
        $this->assertInstanceOf(EmailType::class, $form->get('email')->getConfig()->getType()->getInnerType());

        $this->assertArrayHasKey('roles', $form->all());
        $this->assertInstanceOf(ChoiceType::class, $form->get('roles')->getConfig()->getType()->getInnerType());

        $rolesOptions = $form->get('roles')->getConfig()->getOptions();
        $this->assertTrue($rolesOptions['required']);
        $this->assertFalse($rolesOptions['multiple']);
        $this->assertFalse($rolesOptions['expanded']);
        $this->assertEquals([
            'Utilisateur' => 'ROLE_USER',
            'Admin' => 'ROLE_ADMIN',
        ], $rolesOptions['choices']);
    }

    public function testRolesTransformer()
    {
        $form = $this->factory->create(UserType::class);
        
        $form->submit([
            'username' => 'testuser',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
            'email' => 'test@example.com',
            'roles' => 'ROLE_ADMIN',
        ]);

        $user = $form->getData();
        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());

        $view = $form->createView();
        $this->assertEquals('ROLE_ADMIN', $view['roles']->vars['value']);
    }
}