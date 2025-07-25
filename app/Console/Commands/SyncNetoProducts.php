<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;



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
        $threshold = config('shipping.surcharge_threshold');
        $percentage = config('shipping.surcharge_percentage');

        $this->info("Started syncing at: " . $startTime->format('Y-m-d H:i:s'));
        \Log::channel('neto')->info("Started syncing at: " . $startTime->format('Y-m-d H:i:s'));

        $client = new \GuzzleHttp\Client();

        $this->info("Fetching items from Neto...");
        \Log::channel('neto')->info("Fetching items from Neto...");

        $receivedSkus = [];
        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;

        try {
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
                            "ID", "Misc15", "Misc24", "Misc25", "Misc11", "Misc41", "Misc31",
                            "SKU", "Brand", "Name", "Approved",
                            "AvailableSellQuantity", "ShippingWeight",
                            "ShippingLength", "ShippingWidth", "ShippingHeight",
                            "Images", "ImagesURL"
                        ],
                        'Limit' => 3000,
                    ]
                ]
            ]);

            $items = json_decode($response->getBody()->getContents(), true)['Item'] ?? [];

        } catch (\Throwable $e) {
            \Log::channel('neto')->error('❌ Neto sync failed: ' . $e->getMessage());
            $this->error('❌ Failed to fetch data from Neto.');
            return Command::FAILURE;
        }

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

            // Calculate surcharge
            $rawPrice = $item['Misc11'] ?? '0';
            $cleanedPrice = floatval(str_replace([',', '$'], '', $rawPrice));
            $dropshipPrice = $cleanedPrice;

            $surcharge = 0;
            if ($dropshipPrice > $threshold) {
                $surcharge = round($dropshipPrice * ($percentage / 100), 2);
            }

            $data = [
                'neto_id' => $item['ID'],
                'name' => $item['Misc41'],
                'brand' => $item['Brand'] ?? null,
                'approved' => $approved,
                'stock_status' => $item['Misc15'] ?? null,
                'dropship' => $item['Misc24'] ?? null,
                'dropship_price' => $dropshipPrice,
                'surcharge' => $surcharge,
                'qty' => $item['AvailableSellQuantity'] ?? null,
                'qty_buffer' => (int)($item['Misc31'] ?? 0),
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

        $endTime = now();

        $summary = [
            "--------------------------------------",
            "**Neto Product Sync Summary**",
            "-- Inserted: $totalInserted",
            "-- Updated: $totalUpdated",
            "-- Skipped: $totalSkipped",
            "-- Started: " . $startTime->format('Y-m-d H:i:s'),
            "-- Ended:   " . $endTime->format('Y-m-d H:i:s'),
            "--------------------------------------"
        ];

        foreach ($summary as $line) {
            $this->line($line);
            \Log::channel('neto')->info($line);
        }

        // Refresh cache
        \Log::channel('neto')->info("Rebuilding neto_products_cache...");

        Cache::forget('neto_products_cache'); // 🧹 Clean up old cache first

        $allProducts = \App\Models\NetoProduct::all()->mapWithKeys(function ($product) {
            $sku = strtoupper($product->sku);
            return [
                $sku => [
                    'sku' => $sku,
                    'name' => $product->name,
                    'dropship_price' => number_format($product->dropship_price ?? 0, 2, '.', ''),
                    'shipping_weight' => $product->shipping_weight,
                    'qty_available' => ($product->qty ?? 0) - ($product->qty_buffer ?? 0),
                    'qty_buffer' => $product->qty_buffer,
                ],
            ];
        })->toArray();

        Cache::put('neto_products_cache', $allProducts, now()->addHours(6));

        \Log::channel('neto')->info("✅ neto_products_cache refreshed. Total SKUs: " . count($allProducts));
        \Log::channel('neto')->info('Sample SKUs in cache: ' . implode(', ', array_slice(array_keys($allProducts), 0, 3)));

        Cache::forget('neto_products_all');

        return Command::SUCCESS;
    }



    /**
     * Fix any products with dropship_price > threshold but null surcharge.
     */
    private function fixMissingSurcharges()
    {
        $threshold = config('shipping.surcharge_threshold');
        $percentage = config('shipping.surcharge_percentage');

        $toUpdate = \App\Models\NetoProduct::whereNull('surcharge')
            ->where('dropship_price', '>', $threshold)
            ->get();

        $count = 0;

        foreach ($toUpdate as $product) {
            $product->surcharge = round($product->dropship_price * ($percentage / 100), 2);
            $product->save();
            $count++;
        }

        if ($count > 0) {
            $this->info("✅ Fixed $count products with missing surcharge.");
            \Log::channel('neto')->info("✅ Fixed $count products with missing surcharge.");
        }
    }









}
