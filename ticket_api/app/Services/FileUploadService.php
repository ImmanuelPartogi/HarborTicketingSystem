<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload an image file and optionally resize it
     *
     * @param UploadedFile $file The uploaded file
     * @param string $folder The folder to store the file in
     * @return string The URL of the uploaded file
     */
    public function uploadImage(UploadedFile $file, string $folder = 'uploads'): string
    {
        try {
            // Generate a unique filename
            $filename = $this->generateUniqueFilename($file);

            // Store the file in the specified folder within public storage
            $path = $file->storeAs("public/{$folder}", $filename);

            // Return the URL
            return Storage::url($path);
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a unique filename for an uploaded file
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $random = Str::random(5);

        // Clean the filename to remove invalid characters
        $cleanName = preg_replace('/[^a-z0-9]+/', '-', strtolower($originalName));
        $cleanName = trim($cleanName, '-');

        return "{$cleanName}-{$timestamp}-{$random}.{$extension}";
    }

    /**
     * Delete a file by its URL
     *
     * @param string $url The URL of the file to delete
     * @return bool True if the file was deleted, false otherwise
     */
    public function deleteFile(string $url): bool
    {
        try {
            // Extract the path from the URL
            $path = parse_url($url, PHP_URL_PATH);
            $relativePath = str_replace('/storage/', 'public/', $path);

            // Check if the file exists
            if (Storage::exists($relativePath)) {
                return Storage::delete($relativePath);
            }

            return false;
        } catch (Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage());
            return false;
        }
    }
}
