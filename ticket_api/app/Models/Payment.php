<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the refunds for the payment.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Check if the payment is pending.
     */
    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if the payment is successful.
     */
    public function isSuccess()
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Check if the payment has failed.
     */
    public function isFailed()
    {
        return $this->status === 'FAILED';
    }

    /**
     * Check if the payment has expired.
     */
    public function isExpired()
    {
        return $this->status === 'EXPIRED';
    }

    /**
     * Check if the payment has been refunded.
     */
    public function isRefunded()
    {
        return $this->status === 'REFUNDED';
    }

    /**
     * Check if the payment has been partially refunded.
     */
    public function isPartiallyRefunded()
    {
        return $this->status === 'PARTIAL_REFUND';
    }

    /**
     * Get the payment method name.
     */
    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'BANK_TRANSFER' => 'Transfer Bank',
            'VIRTUAL_ACCOUNT' => 'Virtual Account',
            'E_WALLET' => 'E-Wallet',
            'CREDIT_CARD' => 'Kartu Kredit',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
