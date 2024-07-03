<?php

namespace App\Console\Commands;

use App\Http\Controllers\CompanyController;
use Illuminate\Console\Command;

class UpdateVisitorCompanyIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:update_visitor_company_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Visitor Company Ids';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo (new CompanyController)->UpdateCompanyIdsForVisitor();
    }
}
