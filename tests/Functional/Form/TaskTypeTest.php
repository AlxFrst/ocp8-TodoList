<?php

namespace App\Tests\Form;

use App\Form\TaskType;
use App\Entity\Task;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TaskTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'title' => 'Test Task',
            'content' => 'This is a test task content',
            'deadline' => '2023-12-31 23:59:59',
        ];

        $model = new Task();
        $form = $this->factory->create(TaskType::class, $model);

        $expected = new Task();
        $expected->setTitle('Test Task');
        $expected->setContent('This is a test task content');
        $expected->setDeadline(new \DateTime('2023-12-31 23:59:59'));

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }

    public function testFormStructure()
    {
        $form = $this->factory->create(TaskType::class);
        
        $this->assertArrayHasKey('title', $form->all());
        $this->assertInstanceOf(TextType::class, $form->get('title')->getConfig()->getType()->getInnerType());

        $this->assertArrayHasKey('content', $form->all());
        $this->assertInstanceOf(TextareaType::class, $form->get('content')->getConfig()->getType()->getInnerType());

        $this->assertArrayHasKey('deadline', $form->all());
        $this->assertInstanceOf(DateTimeType::class, $form->get('deadline')->getConfig()->getType()->getInnerType());

        $deadlineOptions = $form->get('deadline')->getConfig()->getOptions();
        $this->assertFalse($deadlineOptions['required']);
        $this->assertEquals('single_text', $deadlineOptions['widget']);
        $this->assertEquals(['class' => 'form-control'], $deadlineOptions['attr']);
    }
}