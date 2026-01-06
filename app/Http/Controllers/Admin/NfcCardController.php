<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NfcCardController extends Controller
{
    /**
     * Display NFC card production queue.
     */
    public function productionQueue(Request $request)
    {
        $query = NfcCard::with(['user', 'profile', 'order', 'package'])
            ->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $nfcCards = $query->paginate(50);
        
        // Get counts for filter badges
        $statusCounts = [
            'all' => NfcCard::count(),
            'pending_production' => NfcCard::where('status', 'pending_production')->count(),
            'in_production' => NfcCard::where('status', 'in_production')->count(),
            'produced' => NfcCard::where('status', 'produced')->count(),
            'shipped' => NfcCard::where('status', 'shipped')->count(),
            'delivered' => NfcCard::where('status', 'delivered')->count(),
        ];
        
        return view('admin.nfc-cards.production-queue', compact('nfcCards', 'statusCounts'));
    }
    
    /**
     * Show NFC card details.
     */
    public function show(NfcCard $card)
    {
        $card->load(['user', 'profile', 'order', 'package']);
        
        return view('admin.nfc-cards.show', compact('card'));
    }
    
    /**
     * Start production for a card.
     */
    public function startProduction(NfcCard $card)
    {
        if ($card->status !== 'pending_production') {
            return back()->with('error', 'Card is not in pending production status.');
        }
        
        $card->startProduction();
        
        Log::info('Card production started', [
            'card_id' => $card->id,
            'card_number' => $card->card_number,
            'admin_id' => auth()->id(),
        ]);
        
        return back()->with('success', 'Card moved to production.');
    }
    
    /**
     * Mark card as produced.
     */
    public function markProduced(NfcCard $card)
    {
        if ($card->status !== 'in_production') {
            return back()->with('error', 'Card is not in production.');
        }
        
        $card->markAsProduced();
        
        Log::info('Card marked as produced', [
            'card_id' => $card->id,
            'card_number' => $card->card_number,
            'admin_id' => auth()->id(),
        ]);
        
        return back()->with('success', 'Card marked as produced.');
    }
    
    /**
     * Ship card.
     */
    public function ship(NfcCard $card, Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);
        
        if ($card->status !== 'produced') {
            return back()->with('error', 'Card must be produced before shipping.');
        }
        
        $card->ship($validated['tracking_number']);
        
        Log::info('Card shipped', [
            'card_id' => $card->id,
            'card_number' => $card->card_number,
            'tracking_number' => $validated['tracking_number'],
            'admin_id' => auth()->id(),
        ]);
        
        // TODO: Send email notification to user
        
        return back()->with('success', 'Card marked as shipped. Tracking number: ' . $validated['tracking_number']);
    }
    
    /**
     * Mark card as delivered.
     */
    public function markDelivered(NfcCard $card)
    {
        if ($card->status !== 'shipped') {
            return back()->with('error', 'Card must be shipped before marking as delivered.');
        }
        
        $card->markAsDelivered();
        
        Log::info('Card marked as delivered', [
            'card_id' => $card->id,
            'card_number' => $card->card_number,
            'admin_id' => auth()->id(),
        ]);
        
        return back()->with('success', 'Card marked as delivered.');
    }
    
    /**
     * Bulk update status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'card_ids' => 'required|array',
            'card_ids.*' => 'required|exists:nfc_cards,id',
            'action' => 'required|string|in:start_production,mark_produced,suspend',
        ]);
        
        $cards = NfcCard::whereIn('id', $validated['card_ids'])->get();
        $successCount = 0;
        
        foreach ($cards as $card) {
            try {
                match($validated['action']) {
                    'start_production' => $card->status === 'pending_production' ? $card->startProduction() : null,
                    'mark_produced' => $card->status === 'in_production' ? $card->markAsProduced() : null,
                    'suspend' => $card->suspend('Bulk suspended by admin'),
                };
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Bulk status update failed for card', [
                    'card_id' => $card->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return back()->with('success', "Updated $successCount cards successfully.");
    }
}
