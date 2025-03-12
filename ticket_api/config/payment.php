<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for payment gateways used in the
    | ferry ticket booking system.
    |
    */

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'sanitize' => true,
        'payment_methods' => [
            'virtual_account' => [
                'bca' => true,
                'bni' => true,
                'bri' => true,
                'mandiri' => true,
            ],
            'e_wallet' => [
                'gopay' => true,
                'shopeepay' => true,
                'dana' => false, // Not directly supported by Midtrans
                'ovo' => false,  // Not directly supported by Midtrans
            ],
        ],
    ],

    'bank_transfer' => [
        'enabled' => true,
        'accounts' => [
            [
                'bank' => 'BCA',
                'account_number' => env('BANK_BCA_ACCOUNT', '1234567890'),
                'account_name' => env('BANK_BCA_NAME', 'PT Ferry Ticket'),
            ],
            [
                'bank' => 'BNI',
                'account_number' => env('BANK_BNI_ACCOUNT', '0987654321'),
                'account_name' => env('BANK_BNI_NAME', 'PT Ferry Ticket'),
            ],
            [
                'bank' => 'BRI',
                'account_number' => env('BANK_BRI_ACCOUNT', '9876543210'),
                'account_name' => env('BANK_BRI_NAME', 'PT Ferry Ticket'),
            ],
            [
                'bank' => 'Mandiri',
                'account_number' => env('BANK_MANDIRI_ACCOUNT', '0123456789'),
                'account_name' => env('BANK_MANDIRI_NAME', 'PT Ferry Ticket'),
            ],
        ],
    ],

    'payment_expiry' => [
        'bank_transfer' => env('PAYMENT_EXPIRY_BANK_TRANSFER', 24), // hours
        'virtual_account' => env('PAYMENT_EXPIRY_VIRTUAL_ACCOUNT', 24), // hours
        'e_wallet' => env('PAYMENT_EXPIRY_E_WALLET', 1), // hours
    ],

    'cancellation_policy' => [
        'full_refund_hours_before' => env('FULL_REFUND_HOURS_BEFORE', 48),
        'partial_refund_hours_before' => env('PARTIAL_REFUND_HOURS_BEFORE', 24),
        'partial_refund_percentage' => env('PARTIAL_REFUND_PERCENTAGE', 50),
    ],

];
