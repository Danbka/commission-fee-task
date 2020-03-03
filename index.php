<?php

declare(strict_types=1);

use Carbon\Carbon;
use Danbka\CommissionTask\Entity\Transaction;
use Danbka\CommissionTask\Exception\CommissionFeeManagerException;
use Danbka\CommissionTask\Repository\InMemoryTransactionRepository;
use Danbka\CommissionTask\Service\CommissionFeeManager;
use Danbka\CommissionTask\Service\CurrencyConverter;
use Evp\Component\Money\Money;

require __DIR__.'/vendor/autoload.php';

try {
    $params = getopt('', ['file:']);

    if (empty($params['file'])) {
        throw new Exception('Parameter --file is required');
    }

    $filePath = $params['file'];

    if (!file_exists($filePath)) {
        throw new Exception("File doesn't exist");
    }

    $fp = fopen($filePath, 'r');
    if ($fp === false) {
        throw new Exception('Could not open the file');
    }

    $currencies = [
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

    // Repository. If it's necessary we will be able to change it
    $transactionRepository = new InMemoryTransactionRepository();

    $currencyConverter = new CurrencyConverter($currencies);

    $commissionFeeManager = new CommissionFeeManager($transactionRepository, $currencyConverter);

    // string format: 2014-12-31,4,natural,cash_out,1200.00,EUR
    while ($data = fgetcsv($fp)) {
        // if something go wrong won't stop the process
        try {
            $transaction = (new Transaction())
                ->setDate(Carbon::parse($data[0]))
                ->setType($data[3])
                ->setUserId((int) $data[1])
                ->setUserType($data[2])
                ->setMoney(new Money($data[4], $data[5]))
            ;

            // add transaction to repository
            $transactionRepository->add($transaction);

            // and calculate its commission
            $commissionFee = $commissionFeeManager->calculate($transaction);

            // output format is client's responsibility
            fwrite(STDOUT, $commissionFee->ceil(Money::getFraction($commissionFee->getCurrency()))->getAmount().PHP_EOL);
        } catch (CommissionFeeManagerException $exception) {
            fwrite(STDOUT, $exception->getMessage());
        }
    }

    fclose($fp);
} catch (Exception $exception) {
    fwrite(STDOUT, 'Exception occured: '.$exception->getMessage());
}
