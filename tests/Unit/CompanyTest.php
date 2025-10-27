<?php

namespace Cloudmazing\KvkApi\Tests\Unit;

use Cloudmazing\KvkApi\Company\Address;
use Cloudmazing\KvkApi\Company\Company;
use PHPUnit\Framework\TestCase;
use stdClass;

class CompanyTest extends TestCase {
    public function testCanCreateCompanyInstance() {
        $address = new stdClass();
        $address->type = 'bezoekadres';
        $address->straatnaam = 'Teststraat';
        $address->huisnummer = '1';
        $address->postcode = '1234AB';
        $address->plaats = 'Amsterdam';
        $address->land = 'Nederland';

        $company = new Company(
            '12345678',
            '000012345678',
            'Test BV',
            [$address],
            ['www.test.nl']
        );

        $this->assertEquals('12345678', $company->getKvkNumber());
        $this->assertEquals('000012345678', $company->getEstablishmentNumber());
        $this->assertEquals('Test BV', $company->getTradeName());
        $this->assertEquals(['www.test.nl'], $company->getWebsites());
    }

    public function testCanHandleNullValues() {
        $company = new Company(
            '12345678',
            null,
            null,
            null,
            null
        );

        $this->assertEquals('12345678', $company->getKvkNumber());
        $this->assertNull($company->getEstablishmentNumber());
        $this->assertNull($company->getTradeName());
        $this->assertNull($company->getAddresses());
        $this->assertNull($company->getWebsites());
    }

    public function testFormatsAddressesCorrectly() {
        $address = new stdClass();
        $address->type = 'bezoekadres';
        $address->straatnaam = 'Teststraat';
        $address->huisnummer = '1';
        $address->postcode = '1234AB';
        $address->plaats = 'Amsterdam';
        $address->land = 'Nederland';

        $company = new Company(
            '12345678',
            null,
            'Test BV',
            [$address],
            null
        );

        $addresses = $company->getAddresses();

        $this->assertIsArray($addresses);
        $this->assertInstanceOf(Address::class, $addresses[0]);
        $this->assertEquals('bezoekadres', $addresses[0]->getType());
        $this->assertEquals('Teststraat', $addresses[0]->getStreet());
        $this->assertEquals('1', $addresses[0]->getHouseNumber());
        $this->assertEquals('1234AB', $addresses[0]->getPostalCode());
        $this->assertEquals('Amsterdam', $addresses[0]->getCity());
        $this->assertEquals('Nederland', $addresses[0]->getCountry());
    }

    public function testCanGetCompanyArrayRepresentation() {
        $address = new stdClass();
        $address->type = 'bezoekadres';
        $address->straatnaam = 'Teststraat';
        $address->huisnummer = '1';
        $address->postcode = '1234AB';
        $address->plaats = 'Amsterdam';
        $address->land = 'Nederland';

        $company = new Company(
            '12345678',
            '000012345678',
            'Test BV',
            [$address],
            ['www.test.nl']
        );

        $array = $company->get();

        $this->assertIsArray($array);
        $this->assertEquals('12345678', $array['kvkNumber']);
        $this->assertEquals('000012345678', $array['establishmentNumber']);
        $this->assertEquals('Test BV', $array['tradeName']);
        $this->assertEquals(['www.test.nl'], $array['websites']);
        $this->assertIsArray($array['addresses']);
    }
}
