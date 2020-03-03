<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Service;

use Danbka\CommissionTask\Exception\CurrencyNotSupportedException;
use Danbka\CommissionTask\Interfaces\CurrencyConverterInterface;
use Evp\Component\Money\Money;

class CurrencyConverter implements CurrencyConverterInterface
{
    private array $currencies = [];

    /**
     * CurrencyConverter constructor.
     *
     * @param array $currencies
     */
    public function __construct(array $currencies)
    {
        $this->currencies = $currencies;
    }

    private function getExchangeRate(string $from, string $to): string
    {
        return bcdiv((string) $this->currencies[$to]['exchangeRate'], (string) $this->currencies[$from]['exchangeRate']);
    }

    /**
     * @param Money  $money
     * @param string $to
     *
     * @return Money
     *
     * @throws CurrencyNotSupportedException
     */
    public function convert(Money $money, string $to): Money
    {
        if (!isset($this->currencies[$money->getCurrency()])) {
            throw new CurrencyNotSupportedException($money->getCurrency());
        }

        if (!isset($this->currencies[$to])) {
            throw new CurrencyNotSupportedException($to);
        }

        $exchangeRate = $this->getExchangeRate($money->getCurrency(), $to);

        return new Money(
            bcmul($money->getAmount(), $exchangeRate),
            $to
        );
    }

    public function getCurrencies(): array
    {
        return $this->currencies;
    }
}
