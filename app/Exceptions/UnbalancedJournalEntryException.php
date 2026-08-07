<?php
// app/Exceptions/UnbalancedJournalEntryException.php

namespace App\Exceptions;

use Exception;

class UnbalancedJournalEntryException extends Exception
{
    public static function forTotals(float $totalDebit, float $totalCredit): self
    {
        return new self(sprintf(
            'Journal entry does not balance: total debits %.2f != total credits %.2f',
            $totalDebit,
            $totalCredit
        ));
    }
}
