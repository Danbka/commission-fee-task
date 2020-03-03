<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Interfaces;

use Danbka\CommissionTask\Entity\Transaction;

interface TransactionRepositoryInterface
{
    public function all(): array;

    public function add(Transaction $transaction): void;

    /**
     * @param Transaction $transaction
     *
     * @return Transaction[]
     */
    public function getCashOutThisWeekPreviousTransactions(Transaction $transaction): array;
}
