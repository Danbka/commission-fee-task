<?php

declare(strict_types=1);

namespace Danbka\CommissionTask\Enum;

use Exception;

final class UserType
{
    const LEGAL = 'legal';

    const NATURAL = 'natural';

    /**
     * UserType constructor.
     *
     * @throws Exception
     */
    private function __construct()
    {
        throw new Exception("Can't instantiate of user type");
    }
}
