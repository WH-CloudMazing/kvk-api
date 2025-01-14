<?php

namespace Mantix\KvkApi\Tests\Unit;

use PHPUnit\Framework\TestCase;

class NamespaceTest extends TestCase {
    public function testClassesExist() {
        $this->assertTrue(class_exists(\Mantix\KvkApi\Client::class));
        $this->assertTrue(class_exists(\Mantix\KvkApi\Company\Company::class));
        $this->assertTrue(class_exists(\Mantix\KvkApi\Company\Address::class));
        $this->assertTrue(class_exists(\Mantix\KvkApi\Exceptions\KvkApiException::class));
    }
}
