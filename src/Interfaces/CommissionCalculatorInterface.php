<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Interfaces;

use Danbka\CommissionTask\Entity\Transaction;
use Evp\Component\Money\Money;

interface CommissionCalculatorInterface
{
    public function calculate(Transaction $transaction): Money;
}
