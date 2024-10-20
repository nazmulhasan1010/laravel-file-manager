<?php

namespace App\Http\Controllers;

use App\Traits\GetPathItems;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JsonException;

class FileManagerController extends Controller
{
    use GetPathItems;

    /**
     * @return Factory|View|Application|\Illuminate\View\View
     * @throws JsonException
     */
    public function index()
    {
        $settings = $this->settings();
        $path = public_path($settings['base']);

        $contains = $this->get($path);
        $items = count($contains);

        return view('nh-file-manager.file-manager', compact('contains', 'settings', 'items'));
    }


    /**
     * @param Request $request
     * @return array
     */
    public function items(Request $request): array
    {
        $path = $request->input('path');
        return $this->get($path);
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

        $df = $this->pathValidation($request->to, 'folder');

        foreach ($cbf as $cb) {
            $lt = $this->pathValidation($cb['path'], $cb['type']);
            $fi = pathinfo($lt, PATHINFO_FILENAME);
            $exp = pathinfo($lt, PATHINFO_DIRNAME);

            $tn = $df . '/' . $fi;
            if ($request->arrange === 'new' && in_array($fi, $exists->toArray(), true)) {
                $tn .= ' -copy';
            }

            if ($cb['type'] === 'file') {
                $ext = pathinfo($lt, PATHINFO_EXTENSION);
                $tn .= '.' . $ext;

                if ($request->clipboard['type'] === 'copy') {
                    Storage::disk('public')->copy($lt, $tn);
                } elseif ($request->clipboard['type'] === 'cut') {
                    Storage::disk('public')->move($lt, $tn);
                }
            }

            if ($cb['type'] === 'folder') {
                $fip = collect($this->getAllFiles($cb['path']));
                $this->mkDir($tn);
                foreach ($fip as $fp) {
                    $d = $df . '/' . str_replace($exp . '/', '', $this->pathValidation($fp['dir'], 'folder'));
                    $l = $this->pathValidation($fp['path']);
                    $f = $d . '/' . $fp['name'];
                    $this->mkDir($d);
                    Storage::disk('public')->copy($l, $f);
                }

                if ($request->clipboard['type'] === 'cut'){
                    Storage::disk('public')->deleteDirectory($lt);
                }
            }

        }
        return 1;
    }


    /**
     * @param Request $request
     * @return int
     */
    public function delete(Request $request): int
    {
        $paths = $request->query('path');

        foreach ($paths as $path) {
            $pv = $this->pathValidation($path['path'], $path['type']);

            if ($path['type'] === 'folder') {
                Storage::disk('public')->deleteDirectory($pv);
            } else {
                Storage::disk('public')->delete($pv);
            }
        }
        return 1;
    }

}
