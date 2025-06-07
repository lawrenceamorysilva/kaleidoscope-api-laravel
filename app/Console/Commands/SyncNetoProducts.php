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
    protected $signature = 'sync:neto-products';


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
        $this->info("⏳ Started syncing at: {$startTime->toDateTimeString()}");
        \Log::channel('neto')->info("⏳ Started syncing at: {$startTime->toDateTimeString()}");

        $client = new \GuzzleHttp\Client();
        $page = 1;
        $receivedSkus = [];

        do {

            $this->info("📄 Browsing page: $page");
            \Log::channel('neto')->info("📄 Browsing page: $page");    

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
                    $this->line("⏭️ Skipped (Not approved or not dropship): $sku");
                    \Log::channel('neto')->info("⏭️ Skipped (Not approved or not dropship): $sku");
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
                    $this->info("✅ Inserted: $sku");
                    \Log::channel('neto')->info("✅ Inserted: $sku");
                } else {
                    $currentData = $existing->only(array_keys($data));

                    // Normalize values for numeric comparison to avoid false diffs like '68.00' vs '68'
                    $hasDiff = false;
                    $diffs = [];

                    foreach ($data as $key => $newValue) {
                        $oldValue = $currentData[$key] ?? null;

                        // Check if both old and new values are numeric strings or numbers
                        if (is_numeric($oldValue) && is_numeric($newValue)) {
                            // Compare as floats after casting
                            if (floatval($oldValue) !== floatval($newValue)) {
                                $hasDiff = true;
                                $diffs[$key] = [
                                    'old' => $oldValue === null ? 'null' : $oldValue,
                                    'new' => $newValue === null ? 'null' : $newValue,
                                ];
                            }
                        } else {
                            // Non-numeric, compare as strings
                            if ((string)$oldValue !== (string)$newValue) {
                                $hasDiff = true;
                                $diffs[$key] = [
                                    'old' => $oldValue === null ? 'null' : $oldValue,
                                    'new' => $newValue === null ? 'null' : $newValue,
                                ];
                            }
                        }
                    }

                    if ($hasDiff) {
                        $existing->update($data);

                        $this->warn("🟡 Updated: $sku");
                        \Log::channel('neto')->warning("🟡 Updated: $sku");

                        foreach ($diffs as $field => $values) {
                            $line = "    - $field: '{$values['old']}' => '{$values['new']}'";
                            $this->line($line);
                            \Log::channel('neto')->info($line);
                        }
                        $this->line(''); // blank line for console clarity
                    } else {
                        $this->line("🟢 No changes (duplicate): $sku");
                        \Log::channel('neto')->info("🟢 No changes (duplicate): $sku");
                    }
                }
            }

            $page++;
        } while (count($items) > 0);

        // Inactivate missing products
        $inactivated = \App\Models\NetoProduct::whereNotIn('sku', $receivedSkus)
            ->where('status', 'active')
            ->get();

        foreach ($inactivated as $product) {
            $product->update([
                'status' => 'inactive',
                'status_reason' => 'Inactivated ' . now()->format('Y-m-d h:i A') . ': SKU not in current Neto API response',
            ]);
            $this->error("🔴 Inactivated: {$product->sku}");
            \Log::channel('neto')->error("🔴 Inactivated: {$product->sku}");
        }

        $this->info('🔁 Neto products synced successfully.');
        \Log::channel('neto')->info('🔁 Neto products synced successfully.');

        $endTime = now();
        $this->info("✅ Finished syncing at: {$endTime->toDateTimeString()}");
        \Log::channel('neto')->info("✅ Finished syncing at: {$endTime->toDateTimeString()}");

    }







}
