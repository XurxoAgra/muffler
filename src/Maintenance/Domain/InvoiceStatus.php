<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
}
