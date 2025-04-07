<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FinalSummariesSeeder extends Seeder
{
    public function run()
    {
        $baseFranchise = '03795-000';
        $businessDate = '2025-03-30';

        // Generate records with franchise_store numbers 03795-00015 to 03795-00025
        for ($i = 15; $i <= 25; $i++) {
            $franchiseStore = $baseFranchise . sprintf('%02d', $i);

            DB::table('final_summaries')->insert([
                'franchise_store'          => $franchiseStore,
                'business_date'            => $businessDate,
                'total_sales'              => mt_rand(1000000, 5000000) / 100,  // e.g. between 10,000.00 and 50,000.00
                'modified_order_qty'       => mt_rand(1, 100),
                'refunded_order_qty'       => mt_rand(0, 50),
                'customer_count'           => mt_rand(100, 500),
                'phone_sales'              => mt_rand(100000, 500000) / 100,
                'call_center_sales'        => mt_rand(100000, 500000) / 100,
                'drive_thru_sales'         => mt_rand(100000, 500000) / 100,
                'website_sales'            => mt_rand(100000, 500000) / 100,
                'mobile_sales'             => mt_rand(100000, 500000) / 100,
                'doordash_sales'           => mt_rand(100000, 500000) / 100,
                'grubhub_sales'            => mt_rand(100000, 500000) / 100,
                'ubereats_sales'           => mt_rand(100000, 500000) / 100,
                'delivery_sales'           => mt_rand(100000, 500000) / 100,
                'digital_sales_percent'    => mt_rand(0, 100) / 100,
                'portal_transactions'      => mt_rand(1, 50),
                'put_into_portal'          => mt_rand(1, 50),
                'portal_used_percent'      => mt_rand(0, 100) / 100,
                'put_in_portal_on_time'    => mt_rand(1, 50),
                'in_portal_on_time_percent'=> mt_rand(0, 100) / 100,
                'delivery_tips'            => mt_rand(10000, 50000) / 100,
                'prepaid_delivery_tips'    => mt_rand(10000, 50000) / 100,
                'in_store_tip_amount'      => mt_rand(10000, 50000) / 100,
                'prepaid_instore_tip_amount'=> mt_rand(10000, 50000) / 100,
                'total_tips'               => mt_rand(10000, 50000) / 100,
                'over_short'               => mt_rand(-5000, 5000) / 100,
                'cash_sales'               => mt_rand(1000000, 5000000) / 100,
                'total_cash'               => mt_rand(1000000, 5000000) / 100,
                'total_waste_cost'         => mt_rand(10000, 100000) / 100,
                'created_at'               => Carbon::now(),
                'updated_at'               => Carbon::now(),
            ]);
        }
    }
}
