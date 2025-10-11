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
                    'heading' => 'Ordering',
                    'items' => [
                        [
                            'question' => 'How do I place an order?',
                            'answer'   => 'Simply add items to your cart, proceed to checkout, and submit.',
                        ],
                    ],
                ],
                [
                    'heading' => 'Shipping',
                    'items' => [
                        [
                            'question' => 'How long does delivery take?',
                            'answer'   => 'Standard delivery is 3–5 business days. Express options are available.',
                        ],
                    ],
                ],
                [
                    'heading' => 'Payments',
                    'items' => [
                        [
                            'question' => 'What payment methods are accepted?',
                            'answer'   => 'We accept major credit cards, PayPal, and direct bank transfer.',
                        ],
                    ],
                ],
            ],
        ];

        DB::table('portal_contents')->updateOrInsert(
            ['key' => 'faq'], // make sure the key matches what your controller expects
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
