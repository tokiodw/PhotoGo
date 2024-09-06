<?php

namespace App\Enums;

enum StatusType: string {
    case INACTIVE = '0';
    case ACTIVE = '1';
    case WARNING = '2';
    case ERROR = '3';
    case SUCCESS = '9';

    public function value(): string{
        return $this->value;
    }
}