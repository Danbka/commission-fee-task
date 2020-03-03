<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Service\CommissionCalculator;

use Danbka\CommissionTask\Entity\Transaction;
use Danbka\CommissionTask\Interfaces\CommissionCalculatorInterface;
use Danbka\CommissionTask\Interfaces\CurrencyConverterInterface;
use Danbka\CommissionTask\Interfaces\TransactionRepositoryInterface;
use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyException;

class CashOutNaturalCommissionCalculator implements CommissionCalculatorInterface
{
    const COMMISSION_RATE = '0.003';

    const FREE_OPERATIONS_COUNT = 3;

    private Money $freeChargePerWeek;

    private CurrencyConverterInterface $currencyConverter;

    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(CurrencyConverterInterface $currencyConverter, TransactionRepositoryInterface $transactionRepository)
    {
        $this->freeChargePerWeek = new Money(1000, 'EUR');

        $this->currencyConverter = $currencyConverter;

        $this->transactionRepository = $transactionRepository;
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
        // previous transactions this week
        $thisWeekTransactions = $this->transactionRepository->getCashOutThisWeekPreviousTransactions($transaction);

        // if user has already had 3 or more cash out transactions this week, calculate commission
        if (count($thisWeekTransactions) >= self::FREE_OPERATIONS_COUNT) {
            // commission in original currency
            return new Money(
                bcmul($transaction->getMoney()->getAmount(), self::COMMISSION_RATE),
                $transaction->getMoney()->getCurrency()
            );
        }

        // amount of previous transactions this week in EUR
        $prevTotalAmountEur = new Money(0, 'EUR');

        foreach ($thisWeekTransactions as $thisWeekTransaction) {
            $prevTotalAmountEur = $prevTotalAmountEur->add($this->currencyConverter->convert(
                $thisWeekTransaction->getMoney(),
                'EUR'
            ));
        }

        // current amount in EUR
        $currentAmountEur = $this->currencyConverter->convert(
            $transaction->getMoney(),
            'EUR'
        );

        // if previous amount is exceeded, get commission from all current amount
        // else get commission only from exceeded amount

        if ($prevTotalAmountEur->isGt($this->freeChargePerWeek)) {
            $commissionableAmountEur = $currentAmountEur;
        } else {
            $commissionableAmountEur = $prevTotalAmountEur->add($currentAmountEur)->sub($this->freeChargePerWeek);
        }

        if ($commissionableAmountEur->isGt(new Money(0, 'EUR'))) {
            return $this->currencyConverter->convert(
                $commissionableAmountEur,
                $transaction->getMoney()->getCurrency()
            )->mul(self::COMMISSION_RATE);
        }

        return new Money(0, $transaction->getMoney()->getCurrency());
    }
}
