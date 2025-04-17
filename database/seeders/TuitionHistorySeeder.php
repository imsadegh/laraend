<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TuitionHistorySeeder extends Seeder
{
    public function run(): void
    {
        // reset for id consistency
        DB::table('tuition_history')->truncate();

        $rows = [
            [
                'id'                      => 1,
                'user_id'                 => 3,
                'course_id'               => 1,
                'amount_paid'             => 10000000,
                'installment_number'      => 1,
                'payment_status'          => 'paid',
                'payment_date'            => '2025-04-14 11:20:45',
                'payment_method'          => 'credit_card',
                'payment_provider'        => null,
                'payment_reference'       => null,
                'payment_receipt'         => null,
                'payment_description'     => null,
                'payment_status_message'  => null,
                'payment_failure_reason'  => null,
                'transaction_id'          => 'trx_67fcef8dd6afd',
                'payment_details'         => null,
                'last_updated_status_at'  => '2025-04-14 11:20:45',
                'is_refundable'           => false,
                'refund_amount'           => null,
                'refund_status'           => 'not_requested',
                'last_refunded_at'        => null,
                'deleted_at'              => null,
                'created_at'              => '2025-04-14 11:20:45',
                'updated_at'              => '2025-04-14 11:20:45',
            ],
            [
                'id'                     => 2,
                'user_id'                => 3,
                'course_id'              => 1,
                'amount_paid'            => 20000000,
                'installment_number'     => 1,
                'payment_status'         => 'paid',
                'payment_date'           => '2025-04-17 12:53:03',
                'payment_method'         => 'zarinpal',
                'payment_provider'        => null,
                'payment_reference'       => null,
                'payment_receipt'         => null,
                'payment_description'     => null,
                'payment_status_message'  => null,
                'payment_failure_reason'  => null,
                'transaction_id'         => 'trx_6800f9afde19d',
                'payment_details'        => null,
                'last_updated_status_at' => '2025-04-17 12:53:03',
                'is_refundable'           => false,
                'refund_amount'           => null,
                'refund_status'           => 'not_requested',
                'last_refunded_at'        => null,
                'deleted_at'              => null,
                'created_at'             => '2025-04-17 12:53:03',
                'updated_at'             => '2025-04-17 12:53:03',
            ],
        ];

        DB::table('tuition_history')->insert($rows);
    }
}
