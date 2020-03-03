<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Service;

use Danbka\CommissionTask\Entity\Transaction;
use Danbka\CommissionTask\Enum\OperationType;
use Danbka\CommissionTask\Enum\UserType;
use Danbka\CommissionTask\Exception\CommissionFeeManagerException;
use Danbka\CommissionTask\Interfaces\CommissionCalculatorInterface;
use Danbka\CommissionTask\Interfaces\CurrencyConverterInterface;
use Danbka\CommissionTask\Interfaces\TransactionRepositoryInterface;
use Danbka\CommissionTask\Service\CommissionCalculator\CashInCommissionCalculator;
use Danbka\CommissionTask\Service\CommissionCalculator\CashOutLegalCommissionCalculator;
use Danbka\CommissionTask\Service\CommissionCalculator\CashOutNaturalCommissionCalculator;
use Evp\Component\Money\Money;

class CommissionFeeManager
{
    const BCMATH_SCALE = 10;

    private TransactionRepositoryInterface $transactionRepository;

    private CurrencyConverterInterface $currencyConverter;

    public function __construct(TransactionRepositoryInterface $transactionRepository, CurrencyConverterInterface $currencyConverter)
    {
        $this->transactionRepository = $transactionRepository;

        $this->currencyConverter = $currencyConverter;

        // set precision for bcmath functions
        bcscale(self::BCMATH_SCALE);
    }

    /**
     * @param Transaction $transaction
     *
     * @return Money
     *
     * @throws CommissionFeeManagerException
     */
    public function calculate(Transaction $transaction): Money
    {
        $calculator = $this->getCalculator($transaction->getType(), $transaction->getUserType());

        return $calculator->calculate($transaction);
    }

    /**
     * Factory method for getting particular calculator.
     *
     * @param string $transactionType
     * @param string $userType
     *
     * @return CommissionCalculatorInterface
     *
     * @throws CommissionFeeManagerException
     */
    private function getCalculator(string $transactionType, string $userType): CommissionCalculatorInterface
    {
        $calculator = null;

        if (OperationType::CASH_IN === $transactionType) {
            $calculator = new CashInCommissionCalculator($this->currencyConverter);
        } elseif (OperationType::CASH_OUT === $transactionType) {
            if (UserType::LEGAL === $userType) {
                $calculator = new CashOutLegalCommissionCalculator($this->currencyConverter);
            } elseif (UserType::NATURAL === $userType) {
                $calculator = new CashOutNaturalCommissionCalculator($this->currencyConverter, $this->transactionRepository);
            }
        }

        if (is_null($calculator)) {
            throw new CommissionFeeManagerException('Commission calculator not found for transaction type ('.$transactionType.') and user type ('.$userType.')');
        }

        return $calculator;
    }
}
