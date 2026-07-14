<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'type_of_service' => 'Individual Tax Return',
                'category' => 'Tax',
                'description' => 'Preparation and lodgement of individual income tax return including salary, investment income, and deductions.',
                'qty' => 1,
                'fees' => 350.00,
            ],
            [
                'type_of_service' => 'Company Tax Return',
                'category' => 'Tax',
                'description' => 'Preparation and lodgement of company tax return with financial statements reconciliation.',
                'qty' => 1,
                'fees' => 1200.00,
            ],
            [
                'type_of_service' => 'Trust Tax Return',
                'category' => 'Tax',
                'description' => 'Preparation and lodgement of trust tax return including distribution statements.',
                'qty' => 1,
                'fees' => 900.00,
            ],
            [
                'type_of_service' => 'BAS Lodgement (Quarterly)',
                'category' => 'BAS',
                'description' => 'Quarterly Business Activity Statement preparation and lodgement including GST, PAYG withholding, and PAYG instalments.',
                'qty' => 1,
                'fees' => 275.00,
            ],
            [
                'type_of_service' => 'BAS Lodgement (Monthly)',
                'category' => 'BAS',
                'description' => 'Monthly Business Activity Statement preparation and lodgement.',
                'qty' => 1,
                'fees' => 150.00,
            ],
            [
                'type_of_service' => 'Annual GST Reconciliation',
                'category' => 'BAS',
                'description' => 'Full year GST reconciliation and reporting to ensure accuracy across all quarterly BAS submissions.',
                'qty' => 1,
                'fees' => 450.00,
            ],
            [
                'type_of_service' => 'Payroll Setup',
                'category' => 'Payroll',
                'description' => 'Initial payroll system setup including employee records, pay rates, superannuation, and STP configuration.',
                'qty' => 1,
                'fees' => 500.00,
            ],
            [
                'type_of_service' => 'Payroll Processing (per employee)',
                'category' => 'Payroll',
                'description' => 'Ongoing payroll processing per employee including payslips, super, and PAYG withholding.',
                'qty' => 1,
                'fees' => 25.00,
            ],
            [
                'type_of_service' => 'STP Phase 2 Reporting',
                'category' => 'Payroll',
                'description' => 'Single Touch Payroll Phase 2 compliance reporting to ATO.',
                'qty' => 1,
                'fees' => 120.00,
            ],
            [
                'type_of_service' => 'Business Advisory Session',
                'category' => 'Advisory',
                'description' => 'One-hour advisory session covering business structure, tax planning, cash flow, and growth strategy.',
                'qty' => 1,
                'fees' => 250.00,
            ],
            [
                'type_of_service' => 'Tax Planning & Strategy',
                'category' => 'Advisory',
                'description' => 'Comprehensive tax planning review to minimise tax liability and maximise deductions for the financial year.',
                'qty' => 1,
                'fees' => 600.00,
            ],
            [
                'type_of_service' => 'Company Registration',
                'category' => 'Registration',
                'description' => 'ASIC company registration including ABN, TFN, and constitution preparation.',
                'qty' => 1,
                'fees' => 850.00,
            ],
            [
                'type_of_service' => 'Trust Setup',
                'category' => 'Registration',
                'description' => 'Family or unit trust establishment including trust deed, ABN, TFN, and GST registration.',
                'qty' => 1,
                'fees' => 1100.00,
            ],
            [
                'type_of_service' => 'SMSF Setup',
                'category' => 'Registration',
                'description' => 'Self-Managed Super Fund establishment including trust deed, corporate trustee, and ATO registration.',
                'qty' => 1,
                'fees' => 1500.00,
            ],
            [
                'type_of_service' => 'SMSF Annual Audit',
                'category' => 'Superannuation',
                'description' => 'Independent audit of Self-Managed Super Fund for ATO compliance.',
                'qty' => 1,
                'fees' => 750.00,
            ],
            [
                'type_of_service' => 'SMSF Tax Return',
                'category' => 'Superannuation',
                'description' => 'Preparation and lodgement of SMSF annual tax return.',
                'qty' => 1,
                'fees' => 650.00,
            ],
            [
                'type_of_service' => 'Bookkeeping (Monthly)',
                'category' => 'Bookkeeping',
                'description' => 'Monthly bookkeeping service including bank reconciliation, categorisation, and financial reporting.',
                'qty' => 1,
                'fees' => 350.00,
            ],
            [
                'type_of_service' => 'Xero Setup & Training',
                'category' => 'Bookkeeping',
                'description' => 'Xero accounting software setup, chart of accounts configuration, and staff training session.',
                'qty' => 1,
                'fees' => 450.00,
            ],
        ];

        foreach ($jobs as $job) {
            Job::updateOrCreate(
                ['type_of_service' => $job['type_of_service']],
                $job,
            );
        }
    }
}
