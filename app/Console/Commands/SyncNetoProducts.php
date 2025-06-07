<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Log;



class SyncNetoProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:neto-products {--full-log : Show full logging output (skips, unchanged, diffs)}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = now();
        $reducedLog = !$this->option('full-log');

        $this->info("Started syncing at: " . $startTime->format('d-m-Y h:i A'));
        //\Log::channel('neto')->info("Started syncing at: " . $startTime->format('d-m-Y h:i A'));

        $client = new \GuzzleHttp\Client();
        $page = 1;
        $receivedSkus = [];
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalPages = 0;

        do {
            $this->info("Browsing page: $page");
            \Log::channel('neto')->info("Browsing page: $page");
            $totalPages++;

            $response = $client->post(config('services.neto.url'), [
                'headers' => [
                    'NETOAPI_ACTION' => 'GetItem',
                    'NETOAPI_KEY' => config('services.neto.key'),
                    'NETOAPI_USERNAME' => config('services.neto.username'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'Filter' => [
                        'Approved' => true,
                        'IsActive' => true,
                        'Visible' => true,
                        'OutputSelector' => [
                            "ID", "Misc15", "Misc24", "Misc25", "Misc11", "SKU", "Brand", "Name",
                            "Approved", "AvailableSellQuantity", "ShippingWeight",
                            "ShippingLength", "ShippingWidth", "ShippingHeight",
                            "Images", "ImagesURL"
                        ],
                        'Limit' => 1000,
                        'OrderBy' => 'ID',
                        'Page' => $page,
                    ],
                ]
            ]);

            $items = json_decode($response->getBody()->getContents(), true)['Item'] ?? [];

            foreach ($items as $item) {
                $approved = filter_var($item['Approved'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $isDropship = strtolower($item['Misc24'] ?? '') === 'yes';
                $sku = $item['SKU'] ?? 'UNKNOWN SKU';

                if (!($approved && $isDropship)) {
                    $totalSkipped++;
                    if (!$reducedLog) {
                        $this->line("⏭️ Skipped (Not approved or not dropship): $sku");
                        \Log::channel('neto')->info("⏭️ Skipped (Not approved or not dropship): $sku");
                    }
                    continue;
                }

                $receivedSkus[] = $sku;

                $data = [
                    'neto_id' => $item['ID'],
                    'name' => $item['Name'],
                    'brand' => $item['Brand'] ?? null,
                    'approved' => $approved,
                    'stock_status' => $item['Misc15'] ?? null,
                    'dropship' => $item['Misc24'] ?? null,
                    'dropship_price' => $item['Misc11'] ?? null,
                    'qty' => $item['AvailableSellQuantity'] ?? null,
                    'shipping_weight' => $item['ShippingWeight'] ?? null,
                    'shipping_length' => $item['ShippingLength'] ?? null,
                    'shipping_width' => $item['ShippingWidth'] ?? null,
                    'shipping_height' => $item['ShippingHeight'] ?? null,
                    'images' => json_encode($item['Images'] ?? []),
                    'status' => 'active',
                    'status_reason' => null,
                ];

                $existing = \App\Models\NetoProduct::where('sku', $sku)->first();

                if (!$existing) {
                    \App\Models\NetoProduct::create(array_merge(['sku' => $sku], $data));
                    $totalInserted++;
                    $this->info("Inserted: $sku");
                    \Log::channel('neto')->info("Inserted: $sku");
                } else {
                    $currentData = $existing->only(array_keys($data));
                    $hasDiff = false;
                    $diffs = [];

                    foreach ($data as $key => $newValue) {
                        $oldValue = $currentData[$key] ?? null;

                        if (is_numeric($oldValue) && is_numeric($newValue)) {
                            if (floatval($oldValue) !== floatval($newValue)) {
                                $hasDiff = true;
                                $diffs[$key] = ['old' => $oldValue, 'new' => $newValue];
                            }
                        } else {
                            if ((string)$oldValue !== (string)$newValue) {
                                $hasDiff = true;
                                $diffs[$key] = ['old' => $oldValue, 'new' => $newValue];
                            }
                        }
                    }

                    if ($hasDiff) {
                        $existing->update($data);
                        $totalUpdated++;

                        $this->warn("Updated: $sku");
                        \Log::channel('neto')->warning("Updated: $sku");

                        if (!$reducedLog) {
                            foreach ($diffs as $field => $values) {
                                $line = "    - $field: '{$values['old']}' => '{$values['new']}'";
                                $this->line($line);
                                \Log::channel('neto')->info($line);
                            }
                            $this->line('');
                        }
                    } else {
                        if (!$reducedLog) {
                            $this->line("No changes (duplicate): $sku");
                            \Log::channel('neto')->info("No changes (duplicate): $sku");
                        }
                    }
                }
            }

            $page++;
        } while (count($items) > 0);


        // Inactivate missing products (commented out at the moment: need to think if this is actually needed...)
        /*$inactivated = \App\Models\NetoProduct::whereNotIn('sku', $receivedSkus)
            ->where('status', 'active')
            ->get();

        foreach ($inactivated as $product) {
            $product->update([
                'status' => 'inactive',
                'status_reason' => 'Inactivated ' . now()->format('Y-m-d h:i A') . ': SKU not in current Neto API response',
            ]);
            $this->error("🔴 Inactivated: {$product->sku}");
            \Log::channel('neto')->error("🔴 Inactivated: {$product->sku}");
        }*/


        // End
        $endTime = now();

        $summary = [
            "🔁 Neto Product Sync Summary",
            "--------------------------------------",
            "-- Pages fetched: $totalPages",
            "-- Inserted: $totalInserted",
            "-- Updated: $totalUpdated",
            "-- Skipped: $totalSkipped",
            "-- Started: " . $startTime->format('d-m-Y h:i A'),
            "-- Ended:   " . $endTime->format('d-m-Y h:i A'),
            "--------------------------------------"
        ];

        foreach ($summary as $line) {
            $this->line($line);
            \Log::channel('neto')->info($line);
        }
    }








}
