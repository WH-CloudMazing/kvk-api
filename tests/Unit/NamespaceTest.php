<?php

namespace Cloudmazing\KvkApi\Tests\Unit;

use PHPUnit\Framework\TestCase;

class NamespaceTest extends TestCase {
    public function testClassesExist() {
        $this->assertTrue(class_exists(\Cloudmazing\KvkApi\Client::class));
        $this->assertTrue(class_exists(\Cloudmazing\KvkApi\Company\Company::class));
        $this->assertTrue(class_exists(\Cloudmazing\KvkApi\Company\Address::class));
        $this->assertTrue(class_exists(\Cloudmazing\KvkApi\Exceptions\KvkApiException::class));
    }
}
