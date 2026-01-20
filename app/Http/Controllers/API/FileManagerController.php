<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        $path = '/home/xavierder/Downloads';

        if (!File::exists($path) || !is_readable($path)) {
            return response()->json(['files' => []]);
        }

        $files = collect(File::files($path))->map(function ($file) {
            return [
                'name'     => $file->getFilename(),
                'type'     => 'file',
                'size'     => round($file->getSize() / 1024, 2) . ' KB',
                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        });

        return response()->json([
            'files' => $files->values()
        ]);
    }
}
