<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authUser = request()->header('auth-user');
        $authKey = request()->header('auth-key');

        $profile = Profile::where('auth_user', $authUser)->first();    
        if(empty($profile)){
            return response()->json([
                'status' => false,
                'message' => 'You are not authenticated',
                'data' => null
            ]);
        }
         
        if($profile->exists()){
                 if($profile->auth_key ===  $authKey){
                    $request->merge(['profile_id' => $profile->id]);
                    return $next($request);
                 }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'You are not authenticated',
                        'data' => null
                    ]);
                }
                    
            
            
        }
        
    }
}
