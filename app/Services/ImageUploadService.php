<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Services;
use RuntimeException;
use Throwable;

class ImageUploadService
{
    public function store(UploadedFile $file, string $collection): string
    {
        if (! $file->isValid() || $file->hasMoved()) {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        $mime = $file->getMimeType();
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            default      => throw new RuntimeException('Only genuine JPG and PNG images are accepted.'),
        };

        $directory = FCPATH . 'uploads/' . trim($collection, '/');
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('The image storage directory is unavailable.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . DIRECTORY_SEPARATOR . $filename;

        try {
            Services::image()
                ->withFile($file->getTempName())
                ->fit(800, 800, 'center')
                ->save($destination, 88);
        } catch (Throwable $exception) {
            if (is_file($destination)) {
                unlink($destination);
            }

            throw new RuntimeException('The image could not be prepared for display.', 0, $exception);
        }

        if (! is_file($destination) || filesize($destination) === 0) {
            throw new RuntimeException('The prepared image was not saved.');
        }

        return $filename;
    }

    public function delete(?string $filename, string $collection): void
    {
        if (! $filename || basename($filename) !== $filename) {
            return;
        }

        $path = FCPATH . 'uploads/' . trim($collection, '/') . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
