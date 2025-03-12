<?php

namespace App\Helpers;

use App\Models\Booking;
use App\Models\Passenger;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class WatermarkGenerator
{
    /**
     * Generate watermark data for a ticket.
     *
     * @param Booking $booking
     * @param Passenger $passenger
     * @param string $ticketCode
     * @return array
     */
    public function generateWatermarkData(Booking $booking, Passenger $passenger, string $ticketCode)
    {
        $schedule = $booking->schedule;
        $route = $schedule->route;
        $departureDate = $booking->booking_date->format('d/m/Y');
        $departureTime = $schedule->departure_time->format('H:i');

        // Generate a unique pattern based on ticket code
        $pattern = $this->generateUniquePattern($ticketCode);

        // Generate a unique color based on the ticket code
        $color = $this->generateUniqueColor($ticketCode);

        // Generate watermark data
        $watermarkData = [
            'ticket_code' => $ticketCode,
            'passenger_name' => $passenger->name,
            'origin' => $route->origin,
            'destination' => $route->destination,
            'departure_date' => $departureDate,
            'departure_time' => $departureTime,
            'pattern' => $pattern,
            'color' => $color,
            'timestamp' => now()->timestamp,
        ];

        return $watermarkData;
    }

    /**
     * Generate a watermark image for a ticket.
     *
     * @param array $watermarkData
     * @param string $text
     * @return string
     */
    public function generateWatermarkImage(array $watermarkData, string $text)
    {
        // Create a canvas for the watermark
        $img = Image::canvas(600, 150, '#ffffff');

        // Add text to the watermark
        $img->text($text, 300, 75, function ($font) use ($watermarkData) {
            $font->file(public_path('fonts/arial.ttf'));
            $font->size(36);
            $font->color($watermarkData['color']);
            $font->align('center');
            $font->valign('middle');
            $font->angle(30);
        });

        // Add the pattern to the watermark
        $this->addPatternToImage($img, $watermarkData['pattern']);

        // Save the watermark image
        $fileName = 'watermark_' . $watermarkData['ticket_code'] . '.png';
        $path = 'tickets/watermarks/' . $fileName;

        Storage::disk('public')->put($path, $img->encode('png'));

        return $path;
    }

    /**
     * Generate a unique pattern based on a ticket code.
     *
     * @param string $ticketCode
     * @return array
     */
    private function generateUniquePattern(string $ticketCode)
    {
        $seed = crc32($ticketCode);
        mt_srand($seed);

        $pattern = [];

        // Generate a pattern of dots and lines
        for ($i = 0; $i < 20; $i++) {
            $pattern[] = [
                'type' => mt_rand(0, 1) ? 'dot' : 'line',
                'x' => mt_rand(10, 590),
                'y' => mt_rand(10, 140),
                'size' => mt_rand(3, 8),
                'angle' => mt_rand(0, 360),
                'length' => mt_rand(10, 50),
            ];
        }

        return $pattern;
    }

    /**
     * Generate a unique color based on a ticket code.
     *
     * @param string $ticketCode
     * @return string
     */
    private function generateUniqueColor(string $ticketCode)
    {
        $seed = crc32($ticketCode);
        mt_srand($seed);

        // Generate a blue-ish color
        $r = mt_rand(0, 100);
        $g = mt_rand(0, 100);
        $b = mt_rand(150, 255);

        return "rgba($r,$g,$b,0.7)";
    }

    /**
     * Add a pattern to an image.
     *
     * @param \Intervention\Image\Image $img
     * @param array $pattern
     * @return void
     */
    private function addPatternToImage($img, array $pattern)
    {
        foreach ($pattern as $element) {
            if ($element['type'] === 'dot') {
                $img->circle($element['size'], $element['x'], $element['y'], function ($draw) {
                    $draw->background('rgba(0, 0, 255, 0.2)');
                });
            } else {
                $x2 = $element['x'] + cos(deg2rad($element['angle'])) * $element['length'];
                $y2 = $element['y'] + sin(deg2rad($element['angle'])) * $element['length'];

                $img->line($element['x'], $element['y'], $x2, $y2, function ($draw) {
                    $draw->color('rgba(0, 0, 255, 0.2)');
                    $draw->width(2);
                });
            }
        }
    }
}
