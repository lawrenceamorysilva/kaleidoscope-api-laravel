<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        // Build FAQ JSON
        $faqJson = [
            'sections' => [
                [
                    'heading' => 'Shipping & Delivery',
                    'items' => [
                        [
                            'question' => 'How long will it take for my customer to receive their order?',
                            'answer'   => <<<HTML
Orders placed before <strong>11:00am</strong> will be dispatched the same day. Orders placed after 11:00am will be dispatched the following business day.<br><br>
<strong>Delivery times are as follows:</strong><br>
<ul>
<li>Brisbane: 1 business day (Direct Freight), 3 business days (Australia Post)</li>
<li>Sydney: 1 business day (Direct Freight), 3 business days (Australia Post)</li>
<li>Melbourne: 2 business days (Direct Freight), 4 business days (Australia Post)</li>
<li>Canberra: 2 business days (Direct Freight), 3 business days (Australia Post)</li>
<li>Adelaide: 3 business days (Direct Freight), 5 business days (Australia Post)</li>
<li>Perth: 5 business days (Direct Freight), 5 business days (Australia Post)</li>
<li>Darwin: 6 business days (Direct Freight), 6 business days (Australia Post)</li>
<li>Hobart: 5 business days (Direct Freight), 5 business days (Australia Post)</li>
</ul>
HTML,
                        ],
                    ],
                ],
                [
                    'heading' => 'Products',
                    'items' => [
                        [
                            'question' => 'Why are some products not available on the drop shipping website?',
                            'answer'   => <<<HTML
Drop shipping is most suitable for higher value items. Some products that are only sold in pack quantities are not offered for drop shipping. These are generally lower value items or products that are difficult to pack individually, such as umbrellas or kites.
HTML,
                        ],
                    ],
                ],
                [
                    'heading' => 'Orders & Processing',
                    'items' => [
                        [
                            'question' => 'What happens after I submit my order to Kaleidoscope?',
                            'answer'   => <<<HTML
If you do not have a credit account with us, we will hold your order (and stock) for up to <strong>2 days</strong> until payment is received.<br><br>
If you do have a credit account with us, your order will be processed immediately, and you will receive a notification once it has been dispatched.
HTML,
                        ],
                    ],
                ],
            ],
        ];

        DB::table('portal_contents')->updateOrInsert(
            ['key' => 'faq'],
            [
                'title'      => 'Frequently Asked Questions',
                'content'    => json_encode($faqJson, JSON_PRETTY_PRINT),
                'updated_by' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
