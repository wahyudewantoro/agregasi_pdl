<?php

namespace App\Http\Middleware;

use App\UserService;
use App\LogService;
// use  \Carbon\Carbon;
use Closure;

class ServiceLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {

        $user = UserService::where('username', $_SERVER['PHP_AUTH_USER'])->where('password', $_SERVER['PHP_AUTH_PW'])->first();

        if ($request->method() == 'GET') {
            $log = $request->all();
        } else {
            $body = json_decode($request->getContent(), true);
            // if (isset($body['Tagihan'])) {
            //     $log['tagihan'] = json_encode($body['Tagihan']);
            // }

            $log['nop'] = $body["Nop"] ?? null;
            $log['masa'] = $body["Masa"] ?? null;
            $log['tahun'] = $body["Tahun"] ?? null;
            $log['pokok'] = $body["Pokok"] ?? null;
            $log['denda'] = $body["Denda"] ?? null;
            $log['potongan'] = $body["Potongan"] ?? null;
            $log['total'] = $body["Total"] ?? null;
            $log['datetime'] = $body["DateTime"] ?? null;
            $log['kodeinstitusi'] = $body["KodeInstitusi"] ?? null;
            $log['referensi'] = $body["Referensi"] ?? null;
            $log['nohp'] = $body["NoHp"] ?? null;
            $log['email'] = $body["Email"] ?? null;
        }




        $log['url'] = $request->url();
        $log['response_code'] = $response->getStatusCode();
        $log['method'] = $request->method();
        $log['body'] = $request->getContent();
        $log['username'] = $user->username;
        $log['kode_bank'] = $user->kode_bank;
        $log['ip'] = $request->ip();
        $log['result'] = $response->getContent() ?? null;
        LogService::create($log);
    }
}
