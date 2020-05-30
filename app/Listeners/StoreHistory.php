<?php

namespace App\Listeners;

use App\Events\ItemSearched;
use App\Models\SearchHistory;
use App\Models\SearchHistoryResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class StoreHistory
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ItemSearched  $event
     * @return void
     */
    public function handle(ItemSearched $event)
    {
        DB::beginTransaction();

        $search_history = SearchHistory::create([
            'user_id' => $event->user_id,
            'searched_word' => $event->search
        ]);
        foreach ($event->items as $item) {
            SearchHistoryResult::create([
                'search_history_id' => $search_history->id,
                'item_id' => $item->id
            ]);
        }
        DB::commit();

    }
}
