<?php

     namespace App\Console\Commands;

     use Illuminate\Console\Command;
     use Illuminate\Support\Facades\DB;
     use Illuminate\Support\Facades\Log;

     class ImportShippingCosts extends Command
     {
         protected $signature = 'shipping:import {courier} {file}';
         protected $description = 'Import shipping costs from CSV, handling duplicates and updates';

         public function handle()
         {
             ini_set('memory_limit', '512M');
             set_time_limit(1200);

             $courier = $this->argument('courier');
             $file = $this->argument('file');

             $this->info("Starting import for {$courier} from {$file}");

             $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
             if (!file_exists($file)) {
                 $this->error("File {$file} not found!");
                 Log::error("Import failed: File {$file} not found");
                 return 1;
             }

             $handle = fopen($file, 'r');
             if (!$handle) {
                 $this->error("Failed to open {$file}!");
                 Log::error("Import failed: Cannot open {$file}");
                 return 1;
             }

             $header = fgetcsv($handle);
             if (count($header) !== 102) {
                 $this->error("Invalid CSV header: expected 102 columns, got " . count($header));
                 Log::error("Invalid CSV header: " . implode(',', array_slice($header, 0, 5)) . "...");
                 fclose($handle);
                 return 1;
             }
             $this->info("Header read: " . implode(',', array_slice($header, 0, 5)) . "...");

             $inserts = [];
             $updates = [];
             $batchSize = 250;
             $rowCount = 0;
             $inserted = 0;
             $updated = 0;
             $skipped = 0;
             $now = now();
             DB::connection()->disableQueryLog();

             while ($row = fgetcsv($handle)) {
                 $rowCount++;
                 if (count($row) !== 102) {
                     $this->warn("Row {$rowCount} has " . count($row) . " columns, skipping");
                     Log::warning("Row {$rowCount} has " . count($row) . " columns: " . implode(',', array_slice($row, 0, 5)) . "...");
                     continue;
                 }

                 $this->info("Processing row {$rowCount}: Postcode {$row[0]}, Suburb {$row[1]}");
                 for ($i = 1; $i <= 100; $i++) {
                     $cost = str_replace(['$', ','], '', $row[$i + 1]);

                     if ($cost === '' || $cost === null) {
                         $this->warn("Skipping weight_kg {$i} for postcode {$row[0]}: empty cost");
                         continue;
                     }

                     $record = [
                         'courier' => $courier,
                         'suburb' => $row[1],
                         'postcode' => $row[0],
                         'weight_kg' => $i,
                         'cost_aud' => floatval($cost),
                         'created_at' => $now,
                         'updated_at' => $now,
                     ];

                     $existing = DB::table('shipping_costs')
                         ->where('courier', $courier)
                         ->where('suburb', $row[1])
                         ->where('postcode', $row[0])
                         ->where('weight_kg', $i)
                         ->first();

                     if ($existing) {
                         if (abs($existing->cost_aud - floatval($cost)) > 0.001) {
                             $updates[] = [
                                 'id' => $existing->id,
                                 'cost_aud' => floatval($cost),
                                 'updated_at' => $now,
                             ];
                         } else {
                             $skipped++;
                             continue;
                         }
                     } else {
                         $inserts[] = $record;
                     }
                 }

                 if (count($inserts) >= $batchSize) {
                     $inserted += $this->insertChunk($inserts, $rowCount);
                     $inserts = [];
                 }
                 if (count($updates) >= $batchSize) {
                     $updated += $this->updateChunk($updates, $rowCount);
                     $updates = [];
                 }
             }
             fclose($handle);

             if (!empty($inserts)) {
                 $inserted += $this->insertChunk($inserts, $rowCount);
             }
             if (!empty($updates)) {
                 $updated += $this->updateChunk($updates, $rowCount);
             }

             if ($rowCount === 0) {
                 $this->error("No data processed!");
                 Log::error("Import failed: No valid data parsed from {$file}");
                 return 1;
             }

             $this->info("Imported {$courier} shipping costs from {$file}: {$rowCount} rows processed, {$inserted} inserted, {$updated} updated, {$skipped} skipped");
             Log::info("Imported {$courier} shipping costs: {$rowCount} rows processed, {$inserted} inserted, {$updated} updated, {$skipped} skipped");
             return 0;
         }

         private function insertChunk(array $inserts, int $rowCount): int
         {
             try {
                 DB::table('shipping_costs')->insert($inserts);
                 $count = count($inserts);
                 $this->info("Inserted batch for row {$rowCount} ({$count} rows)");
                 return $count;
             } catch (\Exception $e) {
                 $this->error("Failed to insert batch for row {$rowCount}: " . $e->getMessage());
                 Log::error("Insert failed for row {$rowCount}: " . $e->getMessage());
                 throw $e;
             }
         }

         private function updateChunk(array $updates, int $rowCount): int
         {
             try {
                 $count = 0;
                 DB::transaction(function () use ($updates, &$count) {
                     foreach ($updates as $update) {
                         DB::table('shipping_costs')
                             ->where('id', $update['id'])
                             ->update([
                                 'cost_aud' => $update['cost_aud'],
                                 'updated_at' => $update['updated_at'],
                             ]);
                         $count++;
                     }
                 });
                 $this->info("Updated batch for row {$rowCount} ({$count} rows)");
                 return $count;
             } catch (\Exception $e) {
                 $this->error("Failed to update batch for row {$rowCount}: " . $e->getMessage());
                 Log::error("Update failed for row {$rowCount}: " . $e->getMessage());
                 throw $e;
             }
         }
     }