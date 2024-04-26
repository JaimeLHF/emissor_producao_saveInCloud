<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fatura;
use Carbon\Carbon;

class UpdateInvoiceStatus extends Command
{
    protected $signature = 'invoices:update-status';
    protected $description = 'Atualiza o status das faturas vencidas';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $invoices = Fatura::where('vencimento', '<', Carbon::now())
            ->where('status', 'paga')
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => 'vencida']);
        }

        $this->info('Status das faturas atualizado com sucesso.');
    }
}
