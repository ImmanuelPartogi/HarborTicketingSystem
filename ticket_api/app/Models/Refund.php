<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'payment_id',
        'amount',
        'reason',
        'status',
        'refunded_by',
        'transaction_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the booking that owns the refund.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the payment that owns the refund.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the admin that processed the refund.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'refunded_by');
    }

    /**
     * Check if the refund is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if the refund is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check if the refund is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === 'REJECTED';
    }

    /**
     * Check if the refund is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'COMPLETED';
    }

    /**
     * Get the formatted amount.
     *
     * @return string
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Approve the refund.
     *
     * @param int|null $adminId
     * @return $this
     */
    public function approve(?int $adminId = null)
    {
        $this->status = 'APPROVED';

        if ($adminId) {
            $this->refunded_by = $adminId;
        }

        $this->save();

        return $this;
    }

    /**
     * Reject the refund.
     *
     * @param int|null $adminId
     * @return $this
     */
    public function reject(?int $adminId = null)
    {
        $this->status = 'REJECTED';

        if ($adminId) {
            $this->refunded_by = $adminId;
        }

        $this->save();

        return $this;
    }

    /**
     * Complete the refund.
     *
     * @param string|null $transactionId
     * @return $this
     */
    public function complete(?string $transactionId = null)
    {
        $this->status = 'COMPLETED';

        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }

        $this->save();

        return $this;
    }
}
