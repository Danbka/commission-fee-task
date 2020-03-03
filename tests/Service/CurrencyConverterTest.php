<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Tests\Service;

use Danbka\CommissionTask\Exception\CurrencyNotSupportedException;
use Danbka\CommissionTask\Interfaces\CurrencyConverterInterface;
use Danbka\CommissionTask\Service\CurrencyConverter;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    private array $currencies = [];

    private CurrencyConverterInterface$currencyConverter;

    public function setUp(): void
    {
        $this->currencies = [
            'EUR' => [
                'exchangeRate' => 1.0,
                'precision' => 2,
            ],
            'USD' => [
                'exchangeRate' => 1.5,
                'precision' => 2,
            ],
            'JPY' => [
                'exchangeRate' => 100,
                'precision' => 0,
            ],
        ];

        $this->currencyConverter = new CurrencyConverter($this->currencies);

        bcscale(10);
    }

    public function testEurToUsd()
    {
        $this->assertEquals(
            1.50,
            $this->currencyConverter->convert(new Money(1, 'EUR'), "USD")->getAmount()
        );
    }

    public function testUsdToEur()
    {
        $this->assertEquals(
            2.00,
            $this->currencyConverter->convert(new Money(3, 'USD'), "EUR")->ceil()->getAmount()
        );
    }

    public function testEurToJpy()
    {
        $this->assertEquals(
            150,
            $this->currencyConverter->convert(new Money(1.5, 'EUR'), "JPY")->ceil()->getAmount()
        );
    }

    public function testJpyToEur()
    {
        $this->assertEquals(
            10,
            $this->currencyConverter->convert(new Money(1000, 'JPY'), "EUR")->ceil()->getAmount()
        );
    }

    public function testJpyToUsd()
    {
        $this->assertEquals(
            15,
            $this->currencyConverter->convert(new Money(1000, 'JPY'), "USD")->ceil()->getAmount()
        );
    }

    public function testUsdToJpy()
    {
        $this->assertEquals(
            200,
            $this->currencyConverter->convert(new Money(3, 'USD'), "JPY")->ceil()->getAmount()
        );
    }

    public function testNotSupportedCurrencyException()
    {
        $this->expectException(CurrencyNotSupportedException::class);

        $this->currencyConverter->convert(new Money(1, 'RUB'), "EUR");
    }
}
