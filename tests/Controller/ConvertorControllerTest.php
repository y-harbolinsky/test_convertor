<?php declare(strict_types = 1);

namespace App\Tests\Controller\API;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConvertorControllerTest extends WebTestCase
{
    public function testGetRates(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/convertor/rates');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $rates = json_decode((string)$client->getResponse()->getContent(), true);
        $this->assertNotNull($rates);
    }

    public function testConvertValidation(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/convertor/AFK/TES/10');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);
        $this->assertEquals('Validation Failed', $res['title']);

        $client->request('GET', '/api/v1/convertor/USD/TES/10');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);
        $this->assertEquals('Validation Failed', $res['title']);
        $this->assertEquals('This value is not a valid currency.', $res['violations'][0]['title']);

        $client->request('GET', '/api/v1/convertor/AFK/RUB/10');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);
        $this->assertEquals('Validation Failed', $res['title']);
        $this->assertEquals('This value is not a valid currency.', $res['violations'][0]['title']);

        $client->request('GET', '/api/v1/convertor/EUR/USD/0');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);
        $this->assertEquals('Validation Failed', $res['title']);
        $this->assertEquals('This value should be greater than 0.', $res['violations'][0]['title']);
    }

    public function testConvertation(): void
    {
        $base = 'EUR';
        $target = 'EUR';
        $amount = 77.5;
        $client = static::createClient();
        $client->request('GET', '/api/v1/convertor/' . $base . '/' . $target . '/' . $amount);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);

        $this->assertEquals($base, $res['baseCurrency']);
        $this->assertEquals($amount, $res['baseAmount']);
        $this->assertEquals($target, $res['targetCurrency']);
        $this->assertEquals($amount, $res['targetAmount']);

        $base = 'NOK';
        $target = 'THB';
        $amount = 100;
        $client->request('GET', '/api/v1/convertor/' . $base . '/' . $target . '/' . $amount);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);

        $this->assertEquals($base, $res['baseCurrency']);
        $this->assertEquals($amount, $res['baseAmount']);
        $this->assertEquals($target, $res['targetCurrency']);
        $this->assertGreaterThan(0, $res['targetAmount']);

        $base = 'INR';
        $target = 'CZK';
        $amount = 50;
        $client->request('GET', '/api/v1/convertor/' . $base . '/' . $target . '/' . $amount);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $res = json_decode((string)$client->getResponse()->getContent(), true);

        $this->assertEquals($base, $res['baseCurrency']);
        $this->assertEquals($amount, $res['baseAmount']);
        $this->assertEquals($target, $res['targetCurrency']);
        $this->assertGreaterThan(0, $res['targetAmount']);
    }
}
