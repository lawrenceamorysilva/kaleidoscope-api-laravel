<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TermsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('portal_contents')->updateOrInsert(
            ['key' => 'terms-and-conditions'],
            [
                'title' => 'Drop Shipping Terms and Conditions',
                'content' => '
                    <ol>
                        <li>Minimum invoice value is $60.00 wholesale (this includes drop ship fee and freight, and excludes GST).</li>
                        <li>If an item within an order is out of stock, we will not process any of the other items in the order...</li>
                        <li>Drop shipping is not available on products with minimum order quantities.</li>
                        <li>The delivery address must be a physical street address; no PO boxes, parcel lockers or post offices will be accepted.</li>
                        <li>Authority to leave must be given on all drop ship orders...</li>
                        <li>Tracking Orders: A connote number will be provided to you the same day the order is dispatched...</li>
                        <li>We do not offer drop shipping to all locations...</li>
                        <li>
                            Fees. An $11.00 + GST administration charge will apply to all orders.
                            <ul>
                                <li>Freight will be charged at a fixed price per product, and the minimum freight charge is $15.00 per order (ex GST).</li>
                                <li>Any redelivery charges will be covered by customers (of Kaleidoscope, not the person receiving the goods)...</li>
                            </ul>
                        </li>
                        <li>Cancellations and Returns: Any stock returned must be approved prior...</li>
                        <li>Payment: Customer on pre-paid accounts are required to make full payment within 2 business days of receiving the invoice...</li>
                        <li>Kaleidoscope reserves the right to remove the drop shipping service...</li>
                    </ol>
                    <p><strong>Please Note:</strong> By placing a drop ship order with Kaleidoscope, the purchaser acknowledges acceptance of these conditions.</p>
                ',
                'updated_by' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
