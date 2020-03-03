<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Interfaces;

use Evp\Component\Money\Money;

interface CurrencyConverterInterface
{
    public function convert(Money $money, string $to): Money;

    public function getCurrencies(): array;
}
