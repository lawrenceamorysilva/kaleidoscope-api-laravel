<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GeneralSettingsController extends Controller
{
    /**
     * Load all general settings data in one go
     */
    /*public function index()
    {
        // 🔹 Fetch portal constants
        $settings = DB::table('portal_settings')->pluck('value', 'key');

        // 🔹 Fetch portal_contents rows for terms and faq
        $contents = Cache::remember('portal_contents_all', now()->addHours(6), function () {
            return DB::table('portal_contents')
                ->whereIn('key', ['terms-and-conditions', 'faq'])
                ->get()
                ->keyBy('key'); // access by key
        });

        // 🔹 Map Terms safely
        $terms = $contents->has('terms-and-conditions')
            ? $contents['terms-and-conditions']->content
            : null;

        // 🔹 Map FAQ safely and decode JSON
        $faq = $contents->has('faq')
            ? json_decode($contents['faq']->content, true)
            : null;

        // 🔹 Return unified payload
        return response()->json([
            'settings' => $settings,
            'terms'    => $terms,
            'faq'      => $faq,
        ]);
    }*/

    public function index()
    {
        // 🔹 Fetch all settings including type
        $rawSettings = DB::table('portal_settings')->select('key', 'value', 'type')->get();

        // 🔹 Convert settings to proper types AND include type info
        $settings = [];
        foreach ($rawSettings as $row) {
            switch ($row->type) {
                case 'number':
                    $value = is_numeric($row->value) ? (float) $row->value : null;
                    break;

                case 'boolean':
                    $value = filter_var($row->value, FILTER_VALIDATE_BOOLEAN);
                    break;

                case 'json':
                    $decoded = json_decode($row->value, true);
                    $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
                    break;

                default:
                    $value = $row->value;
                    break;
            }

            // Include both value and type for frontend dynamic validation
            $settings[$row->key] = [
                'value' => $value,
                'type'  => $row->type,
            ];
        }

        // 🔹 Fetch portal_contents (cached)
        $contents = Cache::remember('portal_contents_all', now()->addHours(6), function () {
            return DB::table('portal_contents')
                ->whereIn('key', ['terms-and-conditions', 'faq'])
                ->get()
                ->keyBy('key');
        });

        // 🔹 Map Terms safely
        $terms = $contents->has('terms-and-conditions')
            ? $contents['terms-and-conditions']->content
            : null;

        // 🔹 Map FAQ safely and decode JSON
        $faq = $contents->has('faq')
            ? json_decode($contents['faq']->content, true)
            : null;

        // 🔹 Return unified payload
        return response()->json([
            'settings' => $settings,
            'terms'    => $terms,
            'faq'      => $faq,
        ]);
    }


    /**
     * Save all general settings, terms, and FAQ in one shot
     */
    public function saveAll(Request $request)
    {
        $data = $request->all();

        // 🔹 Update portal_settings
        if (isset($data['settings']) && is_array($data['settings'])) {
            foreach ($data['settings'] as $key => $value) {
                DB::table('portal_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
            Cache::forget('portal_settings');
        }

        // 🔹 Update terms-and-conditions
        if (isset($data['terms'])) {
            DB::table('portal_contents')->updateOrInsert(
                ['key' => 'terms-and-conditions'],
                [
                    'title' => 'Drop Shipping Terms and Conditions',
                    'content' => $data['terms'],
                    'updated_by' => auth()->id() ?? null,
                    'updated_at' => now(),
                ]
            );
            Cache::forget('portal_contents_terms');
        }

        // 🔹 Update FAQ (encode JSON)
        if (isset($data['faq']) && is_array($data['faq'])) {
            DB::table('portal_contents')->updateOrInsert(
                ['key' => 'faq'],
                [
                    'title' => 'Frequently Asked Questions',
                    'content' => json_encode($data['faq'], JSON_PRETTY_PRINT),
                    'updated_by' => auth()->id() ?? null,
                    'updated_at' => now(),
                ]
            );
            Cache::forget('portal_contents_faq');
        }

        // 🔹 Clear combined cache
        Cache::forget('portal_contents_all');

        return response()->json(['success' => true, 'message' => 'Settings saved successfully.']);
    }






    /**
     * Update simple key/value settings
     */
    public function updateSettings(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            DB::table('portal_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget('portal_settings');

        return response()->json(['message' => 'Settings updated successfully.']);
    }

    /**
     * Get single content (faq or terms)
     */
    public function showContent($key)
    {
        $content = Cache::remember("portal_contents_{$key}", now()->addHours(6), function () use ($key) {
            return DB::table('portal_contents')->where('key', $key)->first();
        });

        if (!$content) {
            return response()->json(['error' => 'Content not found.'], 404);
        }

        $parsed = json_decode($content->content, true);

        return response()->json([
            'key' => $content->key,
            'content' => $parsed ?? $content->content,
        ]);
    }

    /**
     * Update single content (faq or terms)
     */
    public function updateContent(Request $request, $key)
    {
        $newContent = $request->input('content');

        DB::table('portal_contents')->updateOrInsert(
            ['key' => $key],
            ['content' => is_array($newContent) ? json_encode($newContent, JSON_PRETTY_PRINT) : $newContent]
        );

        Cache::forget("portal_contents_{$key}");

        return response()->json(['message' => 'Content updated successfully.']);
    }
}
