<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Enum;

use Exception;

final class OperationType
{
    const CASH_IN = 'cash_in';

    const CASH_OUT = 'cash_out';

    /**
     * OperationType constructor.
     *
     * @throws Exception
     */
    private function __construct()
    {
        throw new Exception("Can't instantiate of operation type");
    }
}
