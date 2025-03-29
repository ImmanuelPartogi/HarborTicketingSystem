<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Ferry Ticket App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background-color: #28a745;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 50px;
        }
        .booking-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }
        .detail-value {
            font-weight: 600;
            text-align: right;
        }
        .btn-primary {
            background-color: #0066cc;
            border-color: #0066cc;
        }
        .btn-outline-secondary {
            color: #0066cc;
            border-color: #0066cc;
        }
        .divider {
            border-top: 1px solid #dee2e6;
            margin: 20px 0;
        }
        .mt-4 {
            margin-top: 1.5rem !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="bi bi-check-lg">✓</i>
            </div>

            <div class="text-center">
                <h2 class="mb-3">Payment Successful!</h2>
                <p class="lead mb-4">Your booking has been confirmed and is ready for your journey.</p>
            </div>

            <div class="booking-details">
                <h5 class="mb-3">Booking Details</h5>

                <div class="detail-row">
                    <span class="detail-label">Booking Number:</span>
                    <span class="detail-value">{{ $booking->booking_code }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Route:</span>
                    <span class="detail-value">{{ $booking->schedule->route->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Departure:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->booking_date)->format('D, d M Y') }} {{ $booking->schedule->departure_time }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Passengers:</span>
                    <span class="detail-value">{{ $booking->passengers->count() }}</span>
                </div>

                @if($booking->vehicles->count() > 0)
                <div class="detail-row">
                    <span class="detail-label">Vehicles:</span>
                    <span class="detail-value">{{ $booking->vehicles->count() }}</span>
                </div>
                @endif

                <div class="divider"></div>

                <div class="detail-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value" style="color: #28a745; font-size: 1.1em;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ $payment->payment_method }} ({{ $payment->payment_channel }})</span>
                </div>
            </div>

            <div class="mt-4 text-center">
                <p>Tickets for your booking have been generated. You can access them through the app.</p>

                <div class="mt-4">
                    <a href="{{ url('/') }}" class="btn btn-primary me-2">Return to Home</a>
                    <a href="{{ url('/bookings/'.$booking->booking_code) }}" class="btn btn-outline-secondary">View Booking Details</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
