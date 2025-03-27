<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tickets - {{ $booking->booking_number }}</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                margin: 1.6cm;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-4">
        <!-- Print Header Buttons - Only visible on screen -->
        <div class="mb-6 flex justify-between items-center no-print">
            <h1 class="text-2xl font-bold text-gray-800">Print Tickets</h1>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('admin.bookings.tickets', $booking->id) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-600 transition-colors inline-block">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>

        <!-- Company Header (will appear on print) -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-700">Ferry Ticket System</h1>
            <p class="text-gray-600">E-Ticket for Ferry Transportation</p>
        </div>

        <!-- Booking Information -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Booking Details</h2>
                    <p class="text-gray-600">Booking Number: <span class="font-semibold">{{ $booking->booking_number }}</span></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-600">Travel Date: <span class="font-semibold">{{ $booking->travel_date ? $booking->travel_date->format('d F Y') : 'N/A' }}</span></p>
                    <p class="text-gray-600">Status:
                        @if($booking->status == 'PENDING')
                        <span class="text-yellow-600 font-semibold">Pending</span>
                        @elseif($booking->status == 'CONFIRMED')
                        <span class="text-blue-600 font-semibold">Confirmed</span>
                        @elseif($booking->status == 'COMPLETED')
                        <span class="text-green-600 font-semibold">Completed</span>
                        @elseif($booking->status == 'CANCELLED')
                        <span class="text-red-600 font-semibold">Cancelled</span>
                        @else
                        <span class="font-semibold">{{ $booking->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Route Information</h3>
                    <p class="font-bold text-lg">{{ $booking->schedule->route->origin ?? 'N/A' }} → {{ $booking->schedule->route->destination ?? 'N/A' }}</p>
                    <p class="text-gray-600">Departure: {{ $booking->schedule->departure_time ?? 'N/A' }}</p>
                    <p class="text-gray-600">Ferry: {{ $booking->schedule->ferry->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Customer Information</h3>
                    <p class="font-bold">{{ $booking->user->name ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $booking->user->email ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $booking->user->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Tickets -->
        @foreach($booking->tickets as $ticket)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 relative border-t-8 border-indigo-600 {{ !$loop->last ? 'page-break' : '' }}">
            <!-- QR Code Placeholder (Top Right) -->
            <div class="absolute top-6 right-6 w-24 h-24 bg-gray-200 flex items-center justify-center">
                <div class="text-gray-400 text-xs text-center">
                    <i class="fas fa-qrcode text-4xl mb-1"></i>
                    <div>{{ substr($ticket->ticket_number, -8) }}</div>
                </div>
            </div>

            <div class="mb-4">
                <h2 class="text-2xl font-bold text-indigo-700">Ticket #{{ $loop->iteration }}</h2>
                <p class="text-gray-600">Ticket Number: {{ $ticket->ticket_number }}</p>
                <div class="inline-block px-3 py-1 mt-2 bg-indigo-100 text-indigo-800 rounded-full text-sm">
                    @if($ticket->status == 'ACTIVE')
                    <i class="fas fa-check-circle mr-1"></i> Active
                    @elseif($ticket->status == 'USED')
                    <i class="fas fa-check-double mr-1"></i> Used
                    @elseif($ticket->status == 'CANCELLED')
                    <i class="fas fa-times-circle mr-1"></i> Cancelled
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6 pr-28">
                <!-- Travel Information -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Route</h3>
                    <div class="mb-4">
                        <div class="flex items-center">
                            <div class="bg-indigo-100 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                                <i class="fas fa-ship text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="font-bold">{{ $booking->schedule->route->origin ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">Origin</p>
                            </div>
                        </div>
                        <div class="border-l-2 border-dashed border-gray-300 h-6 ml-5"></div>
                        <div class="flex items-center">
                            <div class="bg-indigo-100 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                                <i class="fas fa-map-marker-alt text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="font-bold">{{ $booking->schedule->route->destination ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">Destination</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Date</h3>
                            <p>{{ $booking->travel_date ? $booking->travel_date->format('d F Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Time</h3>
                            <p>{{ $booking->schedule->departure_time ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Passenger Information -->
                @if($ticket->passenger)
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Passenger</h3>
                    <p class="font-bold text-lg">{{ $ticket->passenger->name }}</p>
                    <div class="grid grid-cols-2 gap-4 mt-2">
                        <div>
                            <p class="text-xs text-gray-500">ID Type</p>
                            <p>{{ $ticket->passenger->id_type }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">ID Number</p>
                            <p>{{ $ticket->passenger->id_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Passenger Type</p>
                            <p>
                                @if($ticket->passenger->type == 'ADULT')
                                Adult
                                @elseif($ticket->passenger->type == 'CHILD')
                                Child
                                @elseif($ticket->passenger->type == 'INFANT')
                                Infant
                                @else
                                {{ $ticket->passenger->type }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Vehicle Information (if exists) -->
            @if($ticket->vehicle)
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Vehicle</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Vehicle Type</p>
                        <p class="font-medium">{{ $ticket->vehicle->vehicle_type->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">License Plate</p>
                        <p class="font-medium">{{ $ticket->vehicle->license_plate }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Brand/Model</p>
                        <p class="font-medium">{{ $ticket->vehicle->brand_model }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Footer with Terms & Conditions -->
            <div class="border-t border-gray-200 mt-6 pt-4 text-xs text-gray-500">
                <p class="font-semibold mb-1">Terms & Conditions:</p>
                <ol class="list-decimal pl-4 space-y-1">
                    <li>Please arrive at least 30 minutes before departure time.</li>
                    <li>This e-ticket must be presented along with valid ID at check-in.</li>
                    <li>For vehicle tickets, please have your vehicle registration documents ready.</li>
                    <li>Cancellation and refund policies apply as per terms of service.</li>
                    <li>The company reserves the right to change the schedule due to weather or operational reasons.</li>
                </ol>
            </div>

            <!-- Ticket Tear Line -->
            <div class="border-t-2 border-dashed border-gray-300 my-6 relative">
                <div class="absolute -left-6 -top-4 w-8 h-8 rounded-full bg-gray-100"></div>
                <div class="absolute -right-6 -top-4 w-8 h-8 rounded-full bg-gray-100"></div>
            </div>

            <!-- Boarding Pass Section -->
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-bold">BOARDING PASS</h3>
                    <p class="text-sm">{{ $booking->schedule->route->origin ?? 'N/A' }} → {{ $booking->schedule->route->destination ?? 'N/A' }}</p>
                    <p class="text-sm">{{ $booking->travel_date ? $booking->travel_date->format('d/m/Y') : 'N/A' }} | {{ $booking->schedule->departure_time ?? 'N/A' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm">Passenger: <span class="font-medium">{{ $ticket->passenger->name ?? 'N/A' }}</span></p>
                    <p class="text-sm">Ticket: <span class="font-medium">{{ substr($ticket->ticket_number, -8) }}</span></p>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Print Footer - Only visible on print -->
        <div class="text-center text-gray-500 text-sm mt-8">
            <p>This is an official e-ticket. Printed on {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>For inquiries, please contact support@ferryticketsystem.com</p>
        </div>
    </div>

    <script>
        // Auto-print when the page loads (optional)
        window.onload = function() {
            // Uncomment if you want to auto-print
            // window.print();
        }
    </script>
</body>
</html>
