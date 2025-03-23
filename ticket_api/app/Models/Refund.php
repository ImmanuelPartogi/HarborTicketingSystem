<?php
// Refund.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    /**
     * Refund status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_COMPLETED = 'COMPLETED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
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
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the payment that owns the refund.
     *
     * @return BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the admin that processed the refund.
     *
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'refunded_by');
    }

    /**
     * Check if the refund is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the refund is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the refund is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if the refund is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get the formatted amount.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Approve the refund and update related models
     *
     * @param int|null $adminId
     * @return $this
     */
    public function approve(?int $adminId = null): self
    {
        $this->status = self::STATUS_APPROVED;

        if ($adminId) {
            $this->refunded_by = $adminId;
        }

        $this->save();

        // Update the payment with the refund amount if not already set
        $payment = $this->payment;
        if ($payment && $payment->refund_amount === null) {
            $payment->refund_amount = $this->amount;
            $payment->save();
        }

        // Notify the user
        if ($this->booking->user) {
            Notification::createPaymentNotification(
                $this->booking->user,
                'Refund Disetujui',
                'Permintaan refund untuk booking ' . $this->booking->booking_code . ' telah disetujui.',
                [
                    'booking_id' => $this->booking->id,
                    'refund_id' => $this->id,
                    'amount' => (float) $this->amount,
                ]
            );
        }

        return $this;
    }

    /**
     * Reject the refund.
     *
     * @param int|null $adminId
     * @param string|null $rejectionReason
     * @return $this
     */
    public function reject(?int $adminId = null, ?string $rejectionReason = null): self
    {
        $this->status = self::STATUS_REJECTED;

        if ($adminId) {
            $this->refunded_by = $adminId;
        }

        if ($rejectionReason) {
            $this->reason = $rejectionReason;
        }

        $this->save();

        // Notify the user
        if ($this->booking->user) {
            Notification::createPaymentNotification(
                $this->booking->user,
                'Refund Ditolak',
                'Permintaan refund untuk booking ' . $this->booking->booking_code . ' telah ditolak.' .
                ($rejectionReason ? ' Alasan: ' . $rejectionReason : ''),
                [
                    'booking_id' => $this->booking->id,
                    'refund_id' => $this->id,
                ]
            );
        }

        return $this;
    }

    /**
     * Complete the refund and update related models
     *
     * @param string|null $transactionId
     * @return $this
     */
    public function complete(?string $transactionId = null): self
    {
        $this->status = self::STATUS_COMPLETED;

        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }

        $this->save();

        // Update the payment status
        $payment = $this->payment;
        if ($payment) {
            // Check if this is a full or partial refund
            $totalRefunded = $payment->refunds()
                ->where('status', self::STATUS_COMPLETED)
                ->sum('amount');

            if ($totalRefunded >= $payment->amount) {
                $payment->status = Payment::STATUS_REFUNDED;
            } else {
                $payment->status = Payment::STATUS_PARTIAL_REFUND;
            }

            $payment->refund_date = now();
            $payment->save();

            // Update the booking status if completely refunded
            if ($payment->status === Payment::STATUS_REFUNDED) {
                $this->booking->changeStatus(
                    Booking::STATUS_REFUNDED,
                    BookingLog::CHANGED_BY_ADMIN,
                    $this->refunded_by,
                    'Full refund completed'
                );
            }
        }

        // Notify the user
        if ($this->booking->user) {
            Notification::createPaymentNotification(
                $this->booking->user,
                'Refund Selesai',
                'Refund untuk booking ' . $this->booking->booking_code . ' telah selesai diproses.',
                [
                    'booking_id' => $this->booking->id,
                    'refund_id' => $this->id,
                    'amount' => (float) $this->amount,
                    'transaction_id' => $this->transaction_id,
                ]
            );
        }

        return $this;
    }
}
