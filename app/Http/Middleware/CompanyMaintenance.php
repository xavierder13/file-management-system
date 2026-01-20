<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CompanyMaintenance
{

    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $url = 'api/company';
        //Company Record
        if($request->is($url . '/index')){
            if($user->can('company-list')){
                return $next($request); 
            }
        }

        //Company Create
        if($request->is($url . '/create') || $request->is($url . '/store')){
            if($user->can('company-create')){
                return $next($request); 
            }
        }

        //Company Edit
        if($request->is($url . '/edit/*') || $request->is($url . '/update/*')){
            if($user->can('company-edit')){
                return $next($request); 
            }
        }

        //Company Delete
        if($request->is($url . '/delete')){
            if($user->can('company-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
