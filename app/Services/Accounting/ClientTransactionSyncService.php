<?php
// app/Services/Accounting/ClientTransactionSyncService.php

namespace App\Services\Accounting;

use App\Models\ClientTransaction;
use App\Models\Invoice;

class ClientTransactionSyncService
{
    /**
     * Mirror a posted invoice into a ClientTransaction row so the existing goAML DPMSR
     * reporting pipeline (built on client_transactions) keeps working unmodified — it sees
     * this exactly as if a staff member had logged the visit manually.
     */
    public function createFromInvoice(Invoice $invoice): ClientTransaction
    {
        $transaction = ClientTransaction::create([
            'bullion_client_id' => $invoice->bullion_client_id,
            'tenant_id'         => $invoice->tenant_id,
            'visit_date'        => $invoice->invoice_date,
            'invoice_number'    => $invoice->invoice_number,
            'invoice_amount'    => $invoice->total,
            'transaction_type'  => $invoice->invoice_type,
            'notes'             => $invoice->notes,
            'created_by'        => $invoice->created_by,
        ]);

        $invoice->update(['client_transaction_id' => $transaction->id]);

        return $transaction;
    }
}
