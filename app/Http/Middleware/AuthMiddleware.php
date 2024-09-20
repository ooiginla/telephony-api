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
    const METHOD = 'aes-256-ctr';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authUser = request()->header('auth-user');
        $authKey = request()->header('auth-key');

        /*
            $profile = Profile::where('auth_user', 'continulink')->first();    
            $request->merge(['profile' => $profile]);
            return $next($request);
        */

        $profile = Profile::where('auth_user', $authUser)->first();

        if(empty($profile)){
            return response()->json([
                'status' => false,
                'message' => 'You are not authenticated',
                'data' => null
            ]);
        }
        
        if($profile->exists())
        {
            if($profile->auth_key ===  $authKey){
                $request->merge(['profile' => $profile]);
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

    /**
     * Encrypts (but does not authenticate) a message
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded 
     * @return string (raw binary)
     */
    public function encrypt($message, $key, $encode = false)
    {
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize);
        
        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        
        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($encode) {
            return base64_encode($nonce.$ciphertext);
        }
        return $nonce.$ciphertext;
    }
    
    /**
     * Decrypts (but does not verify) a message
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    public  function decrypt($message, $key, $encoded = false)
    {
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');
        
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        
        return $plaintext;
    }
}
