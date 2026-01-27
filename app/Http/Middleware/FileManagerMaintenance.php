<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class FileManagerMaintenance
{

    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        //File Record/File Download
        if($request->is('api/file-manager/index') || $request->is('api/file-manager/file-download/*')){
            if($user->can('file-list')){
                return $next($request); 
            }
        }

        //File Upload
        if($request->is('api/file-manager/file-upload')){
            if($user->can('file-create')){
                return $next($request); 
            }
        }

        //File Delete
        if($request->is('api/file-manager/file-delete')){
            if($user->can('file-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
