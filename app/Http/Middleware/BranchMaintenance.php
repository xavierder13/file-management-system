<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class BranchMaintenance
{

    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $url = 'api/branch';

        //Brand Record
        if($request->is($url . '/index')){
            if($user->can('branch-list')){
                return $next($request); 
            }
        }

        //Brand Create
        if($request->is($url . '/create') || $request->is($url . '/store')){
            if($user->can('branch-create')){
                return $next($request); 
            }
        }

        //Brand Edit
        if($request->is($url . '/edit/*') || $request->is($url . '/update/*')){
            if($user->can('branch-edit')){
                return $next($request); 
            }
        }

        //Brand Delete
        if($request->is($url . '/delete')){
            if($user->can('branch-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
