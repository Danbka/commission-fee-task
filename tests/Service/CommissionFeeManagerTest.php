<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Tests\Service;

use Carbon\Carbon;
use Danbka\CommissionTask\Entity\Transaction;
use Danbka\CommissionTask\Enum\OperationType;
use Danbka\CommissionTask\Enum\UserType;
use Danbka\CommissionTask\Exception\CommissionFeeManagerException;
use Danbka\CommissionTask\Interfaces\CurrencyConverterInterface;
use Danbka\CommissionTask\Interfaces\TransactionRepositoryInterface;
use Danbka\CommissionTask\Repository\InMemoryTransactionRepository;
use Danbka\CommissionTask\Service\CommissionFeeManager;
use Danbka\CommissionTask\Service\CurrencyConverter;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;

class CommissionFeeManagerTest extends TestCase
{
    private array $currencies;

    private CurrencyConverterInterface $currencyConverter;

    private TransactionRepositoryInterface $transactionRepository;

    public function setUp(): void
    {
        $this->currencies = [
            'EUR' => [
                'exchangeRate' => 1.0,
                'precision' => 2,
            ],
            'USD' => [
                'exchangeRate' => 1.1497,
                'precision' => 2,
            ],
            'JPY' => [
                'exchangeRate' => 129.53,
                'precision' => 0,
            ],
        ];

        $this->currencyConverter = new CurrencyConverter($this->currencies);

        $this->transactionRepository = new InMemoryTransactionRepository();

        bcscale(10);
    }

    /**
     * @dataProvider additionProvider
     *
     * @param $userType
     * @param $type
     * @param $amount
     * @param $expected
     *
     * @throws CommissionFeeManagerException
     */
    public function testSingleOperationCommission($userType, $type, $amount, $expected)
    {
        $commissionManager = new CommissionFeeManager($this->transactionRepository, $this->currencyConverter);

        $transaction = (new Transaction())
            ->setDate(Carbon::today())
            ->setUserType($userType)
            ->setType($type)
            ->setUserId(1)
            ->setMoney(new Money($amount, 'EUR'))
        ;

        $this->transactionRepository->add($transaction);

        $this->assertEquals(
            $expected,
            $commissionManager->calculate($transaction)->getAmount()
        );
    }

    /**
     * @throws CommissionFeeManagerException
     */
    public function testExampleDemo()
    {
        $data = [
            ['2014-12-31', '4', 'natural', 'cash_out', '1200.00', 'EUR'],
            ['2015-01-01', '4', 'natural', 'cash_out', '1000.00', 'EUR'],
            ['2016-01-05', '4', 'natural', 'cash_out', '1000.00', 'EUR'],
            ['2016-01-05', '1', 'natural', 'cash_in', '200.00', 'EUR'],
            ['2016-01-06', '2', 'legal', 'cash_out', '300.00', 'EUR'],
            ['2016-01-06', '1', 'natural', 'cash_out', '30000', 'JPY'],
            ['2016-01-07', '1', 'natural', 'cash_out', '1000.00', 'EUR'],
            ['2016-01-07', '1', 'natural', 'cash_out', '100.00', 'USD'],
            ['2016-01-10', '1', 'natural', 'cash_out', '100.00', 'EUR'],
            ['2016-01-10', '2', 'legal', 'cash_in', '1000000.00', 'EUR'],
            ['2016-01-10', '3', 'natural', 'cash_out', '1000.00', 'EUR'],
            ['2016-02-15', '1', 'natural', 'cash_out', '300.00', 'EUR'],
            ['2016-02-19', '5', 'natural', 'cash_out', '3000000', 'JPY'],
        ];

        $commissionManager = new CommissionFeeManager($this->transactionRepository, $this->currencyConverter);

        $expected = ['0.60', '3.00', '0.00', '0.06', '0.90', '0', '0.70', '0.30', '0.30', '5.00', '0.00', '0.00', '8612'];
        $result = [];
        foreach ($data as $item) {
            $transaction = (new Transaction())
                ->setDate(Carbon::parse($item[0]))
                ->setType($item[3])
                ->setUserType($item[2])
                ->setUserId((int) $item[1])
                ->setMoney(new Money($item[4], $item[5]))
            ;

            $this->transactionRepository->add($transaction);

            $commissionFee = $commissionManager->calculate($transaction);

            $result[] = $commissionFee->ceil(Money::getFraction($commissionFee->getCurrency()))->getAmount();
        }

        $this->assertEquals($expected, $result);
    }

    public function additionProvider()
    {
        // userType, operationType, amount, expected
        return [
            [UserType::LEGAL, OperationType::CASH_IN, 100, 0.03],
            [UserType::LEGAL, OperationType::CASH_IN, 50000, 5.00],
            [UserType::NATURAL, OperationType::CASH_IN, 50000, 5.00],
            [UserType::NATURAL, OperationType::CASH_IN, 50000, 5.00],
            [UserType::LEGAL, OperationType::CASH_OUT, 100, 0.50],
            [UserType::LEGAL, OperationType::CASH_OUT, 50000, 150.00],
            [UserType::NATURAL, OperationType::CASH_OUT, 1000, 0.00],
            [UserType::NATURAL, OperationType::CASH_OUT, 2000, 3.00],
        ];
    }
}
