<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Repository;

use Carbon\Carbon;
use Danbka\CommissionTask\Entity\Transaction;
use Danbka\CommissionTask\Enum\OperationType;
use Danbka\CommissionTask\Exception\TransactionNoFoundException;
use Danbka\CommissionTask\Interfaces\TransactionRepositoryInterface;

class InMemoryTransactionRepository implements TransactionRepositoryInterface
{
    /** @var Transaction[] */
    private $transactions = [];

    public function all(): array
    {
        return $this->transactions;
    }

    public function add(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /**
     * Get user's previous cash out transactions this week.
     *
     * @param Transaction $transaction
     *
     * @return array
     *
     * @throws TransactionNoFoundException
     */
    public function getCashOutThisWeekPreviousTransactions(Transaction $transaction): array
    {
        $index = $this->getIndexByTransactionUuid($transaction->getUuid());

        $startWeekDate = Carbon::parse($transaction->getDate())->startOfWeek(Carbon::MONDAY);

        $transactions = $this->all();

        $thisWeekTransactions = [];

        for ($i = $index - 1; $i >= 0; --$i) {
            if ($transactions[$i]->getDate() < $startWeekDate) {
                break;
            }

            if ($transactions[$i]->getUserId() === $transaction->getUserId() && $transactions[$i]->getType() === OperationType::CASH_OUT) {
                $thisWeekTransactions[] = $transactions[$i];
            }
        }

        return $thisWeekTransactions;
    }

    /**
     * Get index of transaction (NOT id) by its uuid.
     *
     * @param string $uuid
     *
     * @return int
     *
     * @throws TransactionNoFoundException
     */
    private function getIndexByTransactionUuid(string $uuid): int
    {
        foreach ($this->all() as $index => $transaction) {
            if ($transaction->getUuid() === $uuid) {
                return $index;
            }
        }

        throw new TransactionNoFoundException($uuid);
    }
}
