<?php
// Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    /**
     * Payment status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_REFUNDED = 'REFUNDED';
    const STATUS_PARTIAL_REFUND = 'PARTIAL_REFUND';

    /**
     * Payment method constants
     */
    const METHOD_BANK_TRANSFER = 'BANK_TRANSFER';
    const METHOD_VIRTUAL_ACCOUNT = 'VIRTUAL_ACCOUNT';
    const METHOD_E_WALLET = 'E_WALLET';
    const METHOD_CREDIT_CARD = 'CREDIT_CARD';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'payment_channel',
        'transaction_id',
        'status',
        'payment_date',
        'expiry_date',
        'refund_amount',
        'refund_date',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'expiry_date' => 'datetime',
        'refund_amount' => 'decimal:2',
        'refund_date' => 'datetime',
    ];

    /**
     * Get the booking that owns the payment.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the refunds for the payment.
     *
     * @return HasMany
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Check if the payment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the payment is successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if the payment has failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the payment has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if the payment has been refunded.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Check if the payment has been partially refunded.
     *
     * @return bool
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->status === self::STATUS_PARTIAL_REFUND;
    }

    /**
     * Update payment status and trigger related actions
     *
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $status): bool
    {
        $oldStatus = $this->status;
        $this->status = $status;

        if ($status === self::STATUS_SUCCESS && !$this->payment_date) {
            $this->payment_date = now();
        }

        if (($status === self::STATUS_REFUNDED || $status === self::STATUS_PARTIAL_REFUND) &&
            !$this->refund_date &&
            $this->refund_amount) {
            $this->refund_date = now();
        }

        $saved = $this->save();

        // If payment became successful, we should update the booking status
        if ($saved && $oldStatus !== self::STATUS_SUCCESS && $status === self::STATUS_SUCCESS) {
            $this->booking->changeStatus(
                Booking::STATUS_CONFIRMED,
                BookingLog::CHANGED_BY_SYSTEM,
                null,
                'Payment confirmed automatically'
            );

            // Create notification
            if ($this->booking->user) {
                Notification::createPaymentNotification(
                    $this->booking->user,
                    'Pembayaran Berhasil',
                    'Pembayaran untuk booking ' . $this->booking->booking_code . ' telah berhasil.',
                    [
                        'booking_id' => $this->booking->id,
                        'booking_code' => $this->booking->booking_code,
                        'amount' => (float) $this->amount,
                    ]
                );
            }
        }

        return $saved;
    }

    /**
     * Check if payment is about to expire
     *
     * @param int $thresholdMinutes
     * @return bool
     */
    public function isAboutToExpire(int $thresholdMinutes = 30): bool
    {
        if (!$this->expiry_date || !$this->isPending()) {
            return false;
        }

        return now()->diffInMinutes($this->expiry_date, false) <= $thresholdMinutes;
    }

    /**
     * Get the payment method name.
     *
     * @return string
     */
    public function getPaymentMethodNameAttribute(): string
    {
        $methods = [
            self::METHOD_BANK_TRANSFER => 'Transfer Bank',
            self::METHOD_VIRTUAL_ACCOUNT => 'Virtual Account',
            self::METHOD_E_WALLET => 'E-Wallet',
            self::METHOD_CREDIT_CARD => 'Kartu Kredit',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
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
     * Get the remaining time before expiry (for pending payments)
     *
     * @return string|null
     */
    public function getRemainingTimeAttribute(): ?string
    {
        if (!$this->expiry_date || !$this->isPending()) {
            return null;
        }

        $minutes = now()->diffInMinutes($this->expiry_date, false);

        if ($minutes <= 0) {
            return 'Expired';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return $hours . ' jam ' . $remainingMinutes . ' menit';
        }

        return $remainingMinutes . ' menit';
    }
}
