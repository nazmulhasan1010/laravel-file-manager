<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

trait GetPathItems
{
    /**
     * @param $path
     * @return array
     */
    public function get($path): array
    {
        $fileArray = [];
        $folders = File::directories($path);
        $files = File::files($path);


        foreach ($folders as $folder) {
            $allFiles = File::allFiles($folder);
            $allFolders = File::directories($folder);

            $totalItems = count($allFiles) + count($allFolders);

            $totalSize = 0;
            foreach ($allFiles as $file) {
                $totalSize += File::size($file);
            }
            $size = $this->formatSizeUnits($totalSize);

            $path = preg_replace('/^.*public\\\\/', '', $folder);

            $fileArray[] = [
                'path' => $path,
                'paths' => str_replace('\\', '/', $path),
                'type' => 'folder',
                'items' => $totalItems,
                'size' => $size,
                'name' => File::basename($folder)
            ];
        }

        foreach ($files as $file) {
            $fileSize = File::size($file);
            $lastModified = File::lastModified($file);
            $mimeType = File::mimeType($file);
            $baseName = File::basename($file);
            $extension = File::extension($file);

            $fileArray[] = [
                'type' => 'file',
                'name' => $baseName,
                'size' => $this->formatSizeUnits($fileSize),
                'mime' => $mimeType,
                'modified' => $lastModified,
                'ext' => $extension,
                'path' => 'storage/' . preg_replace('/^.*public\\\\/', '', realpath($file))
            ];
        }
        return $fileArray;
    }

    /**
     * @return Collection
     * @throws JsonException
     */
    public function settings(): Collection
    {
        $jsonFile = public_path('assets/js/settings.json');
        $jsonData = file_get_contents($jsonFile);
        return collect(json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @param $bytes
     * @return string
     */
    public function formatSizeUnits($bytes): string
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } else {
            $bytes .= ' bytes';
        }

        return $bytes;
    }

    /**
     * @param $path
     * @param $type
     * @return array|string|string[]
     */
    public function pathValidation($path, $type = null): array|string
    {
        $pd = $type === 'folder' ? str_replace('storage\\', '', $path) : str_replace('storage/', '', $path);
        return str_replace('\\', '/', $pd);
    }
}
