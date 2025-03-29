<?php

return [
    'midtrans' => [
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID', 'G815000693'),
        'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-8csuXJ7DmFhqmkMX'),
        'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-jv_rZEY1OoQzsxdhc0GVb-uW'),
        'sandbox_url' => env('MIDTRANS_SANDBOX_URL', 'https://api.sandbox.midtrans.com'),
        'production_url' => env('MIDTRANS_PRODUCTION_URL', 'https://api.midtrans.com'),

        // Define available payment methods
        'payment_methods' => [
            'virtual_account' => [
                'bca' => true,      // BCA Virtual Account
                'bni' => true,      // BNI Virtual Account
                'bri' => true,      // BRI Virtual Account
                'mandiri' => true,  // Mandiri Bill Payment
                'permata' => true,  // Permata Virtual Account
            ],
            'e_wallet' => [
                'gopay' => true,    // GoPay
                'shopeepay' => true, // ShopeePay
                'dana' => true,     // DANA
                'ovo' => true,      // OVO
            ],
            'credit_card' => true,  // Credit Card
        ],

        // Notification URL for Midtrans to send callbacks
        'notification_url' => env('MIDTRANS_NOTIFICATION_URL', '/api/v1/payments/notification'),

        // Default payment expiry time in hours
        'expiry_duration' => env('MIDTRANS_EXPIRY_DURATION', 24),
    ],

    // Bank transfer accounts for manual transfers
    'bank_transfer' => [
        'accounts' => [
            'BCA' => [
                'bank' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'PT Ferry Company',
            ],
            'BNI' => [
                'bank' => 'BNI',
                'account_number' => '0987654321',
                'account_name' => 'PT Ferry Company',
            ],
            'MANDIRI' => [
                'bank' => 'MANDIRI',
                'account_number' => '1122334455',
                'account_name' => 'PT Ferry Company',
            ],
        ],
    ],

    // Cancellation policy
    'cancellation_policy' => [
        'full_refund_hours_before' => 48,
        'partial_refund_hours_before' => 24,
        'partial_refund_percentage' => 50,
    ],
];
