<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        return view('tenant.accounting.ai.index', compact('tenant'));
    }

    public function query(Request $request)
    {
        $tenant = app('tenant');
        $request->validate(['question' => 'required|string|max:2000']);

        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return response()->json(['error' => 'AI service not configured.'], 503);
        }

        // Build lightweight context summary
        $totalAccounts = ChartOfAccount::where('tenant_id', $tenant->id)->count();
        $latestEntries = JournalEntry::where('tenant_id', $tenant->id)
            ->orderByDesc('entry_date')
            ->limit(5)
            ->get(['entry_date', 'reference', 'memo'])
            ->map(fn($e) => "{$e->entry_date->format('d M Y')}: {$e->reference} — {$e->memo}")
            ->implode("\n");

        $systemPrompt = "You are an accounting assistant for {$tenant->name}. "
            . "The company has {$totalAccounts} chart-of-account entries. "
            . "Recent journal entries:\n{$latestEntries}\n"
            . "Answer questions about their accounts, entries, and financial topics clearly and concisely.";

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5',
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => [['role' => 'user', 'content' => $request->question]],
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'AI query failed. Please try again.'], 500);
        }

        $answer = $response->json('content.0.text', 'No response.');

        return response()->json(['answer' => $answer]);
    }
}
