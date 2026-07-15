<?php

namespace App\Models;

enum Answer: int
{
    case YES = 1;
    case NOT_SURE = 2;
    case NO = 3;
}
