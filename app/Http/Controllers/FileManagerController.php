<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use App\Traits\NFManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JsonException;

class FileManagerController extends Controller
{
    use NFManager;

    /**
     * @return Factory|View|Application|\Illuminate\View\View
     * @throws JsonException
     */
    public function index()
    {
        $settings = $this->mixedOAC($this->settings());
        $path = public_path($settings->base);

        $contains = $this->get($path);
        $items = count($contains);

        return view('nh-file-manager.file-manager', compact('contains', 'settings', 'items'));
    }


    /**
     * @param Request $request
     * @return array|int
     */
    public function items(Request $request)
    {
        $path = $request->input('path');
        if ($this->pathCheck($path)) {
            return $this->get($path);
        }
        return 0;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function information(Request $request): array
    {
        return $this->getInfo($request->path);
    }

    /**
     * @param Request $request
     * @return Request
     * @throws JsonException
     */
    public function settingsUpdate(Request $request): Request
    {
        $jsonFile = public_path('assets/js/settings.json');
        $data = $this->settings();
        $data[$request->key] = $request->value;

        $newJsonData = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        file_put_contents($jsonFile, $newJsonData);
        return $request;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function add(Request $request): string
    {
        $folder = str_replace('storage\\', '', $request->path === 'storage' ? 'storage\\' : $request->path);
        $path = $folder . '/' . $request->name;
        $name = $path;

        if (Storage::disk('public')->exists($path)) {

            $exists = collect($this->get($request->path));

            $ext = pathinfo($request->name, PATHINFO_EXTENSION);
            $fi = pathinfo($request->name, PATHINFO_FILENAME);

            $same = $exists->map(function ($item) use ($request, $ext, $fi) {
                $fn = pathinfo($item['name'], PATHINFO_FILENAME);

                if ($item['type'] === $request->addOp && str_contains($fn, $fi . '_')) {
                    $s = [];
                    if ($request->addOp === 'file' && $item['ext'] === $ext) {
                        $s = explode('_', $fn);
                    }

                    if ($request->addOp === 'folder') {
                        $s = explode('_', $fn);
                    }

                    $sl = count($s);
                    return $sl > 0 ? $s[$sl - 1] : 0;
                }

                return null;
            })->filter();


            $fn = ($same->isNotEmpty() ? ($same->max() + 1) : 1);

            if ($request->addOp === 'file') {
                $name = $folder . '/' . $fi . '_' . $fn . '.' . $ext;
            }

            if ($request->addOp === 'folder') {
                $name = $path . '_' . $fn;
            }
        }


        if ($request->addOp === 'folder') {
            Storage::disk('public')->makeDirectory($name);
        }

        if ($request->addOp === 'file') {
            Storage::disk('public')->put($name, '');
        }

        return $name;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function rename(Request $request): int
    {
        $lt = $this->pathValidation($request->path, $request->type);
        $lf = str_replace($request->pn, $request->name, $lt);
        Storage::disk('public')->move($lt, $lf);
        return 1;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function rearrange(Request $request)
    {
        $cbf = [];
        $cbp = collect($request->clipboard['files'])->map(function ($item) use (&$cbf) {
            $cbf[] = [
                'path' => $item['path'],
                'type' => $item['type']
            ];
            return $item['path'];
        });

        $fod = collect($this->get($request->to));

        $exists = collect($fod->whereIn('path', $cbp)->map(function ($item) {
            return pathinfo($item['path'], PATHINFO_FILENAME);
        }));

        if (!$request->arrange && $exists->count()) {
            return 'conflict';
        }

        $df = $this->pathValidation($request->to, 'folder', true);

        $this->arrange($cbf, $df, $request->clipboard['type'], $request->arrange, $exists);
        return 1;
    }


    /**
     * @param Request $request
     * @return int
     * @throws JsonException
     */
    public function delete(Request $request): int
    {
        $paths = $request->query('path');
        $trash = $this->mixedOAC($this->settings())->trash;

        foreach ($paths as $path) {
            if ($trash === 'on') {
                Trash::create([
                    'file_name' => pathinfo($path['path'], PATHINFO_FILENAME),
                    'path' => 'trash/' . basename($path['path']),
                    'type' => $path['type'],
                    'original_path' => $path['path']
                ]);

                $cbf[] = [
                    'path' => $path['path'],
                    'type' => $path['type']
                ];

                if (!$this->pathCheck('trash')) {
                    Storage::disk('public')->makeDirectory('trash');
                }

                $df = $this->pathValidation('trash', 'folder', true);
                $this->arrange($cbf, $df, 'cut', 'new', collect([]));
            }

            $pv = $this->pathValidation($path['path'], $path['type']);

            if ($path['type'] === 'folder' && $trash !== 'on') {
                Storage::disk('public')->deleteDirectory($pv);
            } else {
                Storage::disk('public')->delete($pv);
            }
        }
        return 1;
    }

}
