<?php

namespace App\Exceptions;

use RuntimeException;

class DailyServiceQueueFull extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This branch has reached its limit of 50 confirmed laundry services for today.');
    }
}
