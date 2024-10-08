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
        $items = [];
        $folders = File::directories($path);
        $files = File::files($path);

        foreach ($folders as $folder) {
            $fif = File::allFiles($folder);
            $fof = File::directories($folder);
            $ti = count($fif) + count($fof);
            $tsz = 0;

            foreach ($fif as $file) {
                $tsz += File::size($file);
            }

            $size = $this->formatSizeUnits($tsz);
            $path = preg_replace('/^.*public\\\\/', '', $folder);

            $items[] = [
                'path' => $path, 'paths' => str_replace('\\', '/', $path),
                'type' => 'folder', 'items' => $ti, 'size' => $size,
                'name' => File::basename($folder)
            ];
        }

        foreach ($files as $file) {
            $size = $this->formatSizeUnits(File::size($file));
            $lm = File::lastModified($file);
            $mt = File::mimeType($file);
            $name = File::basename($file);
            $ext = File::extension($file);

            $items[] = [
                'type' => 'file', 'name' => $name, 'size' => $size,
                'mime' => $mt, 'modified' => $lm, 'ext' => $ext,
                'path' => 'storage/' . preg_replace('/^.*public\\\\/', '', realpath($file))
            ];
        }
        return $items;
    }


    /**
     * @param $path
     * @return array
     */
    public function getAllFiles($path): array
    {
        $files = [];
        $fif = File::files($path);
        $folders = File::directories($path);

        foreach ($fif as $file) {
            $size = $this->formatSizeUnits(File::size($file));
            $lm = File::lastModified($file);
            $mt = File::mimeType($file);
            $name = File::basename($file);
            $ext = File::extension($file);

            $files[] = [
                'type' => 'file', 'name' => $name, 'size' => $size,
                'mime' => $mt, 'modified' => $lm, 'ext' => $ext, 'dir' => $path,
                'path' => 'storage/' . preg_replace('/^.*public\\\\/', '', realpath($file))
            ];
        }

        $merged = [];
        foreach ($folders as $dir) {
            $merged[] = $this->getAllFiles($dir);
        }

        return array_merge($files, ...$merged);
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
