<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Validation\Validator;

class ValidatorTest extends TestCase
{
    public function testValidateNotEmpty(): void
    {
        $validator = new Validator();
        $validator->validateNotEmpty('name', '', 'Name is required');

        $this->assertFalse($validator->isValid());
        $this->assertArrayHasKey('name', $validator->getErrors());
    }

    public function testValidateNotEmptyPasses(): void
    {
        $validator = new Validator();
        $validator->validateNotEmpty('name', 'John', 'Name is required');

        $this->assertTrue($validator->isValid());
    }

    public function testValidateMinLength(): void
    {
        $validator = new Validator();
        $validator->validateMinLength('password', '123', 5, 'Password too short');

        $this->assertFalse($validator->isValid());
    }

    public function testValidateMinLengthPasses(): void
    {
        $validator = new Validator();
        $validator->validateMinLength('password', '12345', 5);

        $this->assertTrue($validator->isValid());
    }

    public function testValidateMaxLength(): void
    {
        $validator = new Validator();
        $validator->validateMaxLength('username', 'verylongusername', 5, 'Too long');

        $this->assertFalse($validator->isValid());
    }

    public function testValidateUnique(): void
    {
        $validator = new Validator();
        $existsFn = function ($value) {
            return $value === 'existing@email.com';
        };

        $validator->validateUnique('email', 'existing@email.com', $existsFn, 'Email exists');

        $this->assertFalse($validator->isValid());
    }

    public function testGetFirstError(): void
    {
        $validator = new Validator();
        $validator->validateNotEmpty('name', '', 'Name is required');
        $validator->validateNotEmpty('email', '', 'Email is required');

        $firstError = $validator->getFirstError();
        $this->assertEquals('Name is required', $firstError);
    }

    public function testChainable(): void
    {
        $validator = new Validator();
        $result = $validator
            ->validateNotEmpty('name', '', 'Name required')
            ->validateMinLength('name', '', 3);

        $this->assertSame($validator, $result);
    }
}
