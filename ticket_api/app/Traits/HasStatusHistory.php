<?php

namespace App\Traits;

use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;

trait HasStatusHistory
{
    public function statusHistories()
    {
        return $this->morphMany(StatusHistory::class, 'statusable');
    }

    public function recordStatusChange($oldStatus, $newStatus, $reason = null, $notes = null)
    {
        return $this->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'notes' => $notes,
            'admin_id' => Auth::guard('admin')->id()
        ]);
    }
}
