<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Entity;

use Carbon\Carbon;
use Evp\Component\Money\Money;

class Transaction
{
    private string $uuid;

    private Carbon $date;

    private string $type;

    private int $userId;

    private string $userType;

    private Money $money;

    public function __construct()
    {
        $this->uuid = uniqid();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDate(): Carbon
    {
        return $this->date;
    }

    public function setDate(Carbon $date)
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType)
    {
        $this->userType = $userType;

        return $this;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function setMoney(Money $money)
    {
        $this->money = $money;

        return $this;
    }
}
