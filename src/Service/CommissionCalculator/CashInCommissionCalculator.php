<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Service\CommissionCalculator;

use Danbka\CommissionTask\Entity\Transaction;
use Danbka\CommissionTask\Interfaces\CommissionCalculatorInterface;
use Danbka\CommissionTask\Interfaces\CurrencyConverterInterface;
use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyException;

class CashInCommissionCalculator implements CommissionCalculatorInterface
{
    const COMMISSION_RATE = '0.0003';

    private Money $maxCommission;

    private CurrencyConverterInterface $currencyConverter;

    public function __construct(CurrencyConverterInterface $currencyConverter)
    {
        $this->maxCommission = new Money(5, 'EUR');

        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Money
     *
     * @throws MoneyException
     */
    public function calculate(Transaction $transaction): Money
    {
        // commission in original currency
        $commissionFee = $transaction->getMoney()->mul(self::COMMISSION_RATE);

        // commission in EUR
        $commissionFeeEur = $this->currencyConverter->convert($commissionFee, 'EUR');

        if ($commissionFeeEur->isGt($this->maxCommission)) {
            $commissionFeeEur = $this->maxCommission;
        }

        return $this->currencyConverter->convert($commissionFeeEur, $transaction->getMoney()->getCurrency());
    }
}
