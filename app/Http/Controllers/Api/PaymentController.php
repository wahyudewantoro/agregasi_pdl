<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\UserService;
use App\Payment;

class PaymentController extends Controller
{
    //
    public function index(Request $request)
    {
        $error = "False";
        $kode = '00';
        $messages = [
            'required' => ':attribute harus disertakan',
            'numeric' => ':attribute harus angka',
            'digits' => ':attribute harus :digits digits.',
            'date_format' => ':attribute tidak sesuai format, pastikan format :attribute adalah :format.',
        ];
        $validator = Validator::make($request->all(), [
            "Nop" => 'required|numeric|digits:18',
            "Masa" => 'required|numeric|digits:2',
            "Tahun" => 'required|numeric|digits:4',
            "Pokok" => 'required|numeric',
            "Denda" => 'required|numeric',
            // "Potongan" => 'required|numeric',
            "Total" => 'required|numeric',
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
            "Referensi" => 'required|digits:10',
            "KodeInstitusi" => 'required|digits:10',
            "NoHp" => 'nullable|numeric',
            "Email" => 'nullable|email',
        ], $messages);
        $data = [];
        $msg = "";
        if ($validator->fails()) {
            // jika tidak lengkap
            foreach ($validator->errors()->all() as $rk) {
                $msg .= $rk . ', ';
            }
            $msg = \substr($msg, '0', '-2');
            $error = "True";
            $kode = '99';
        } else {
            // lengkap
            
            $nop = $request->Nop;
            $tahun = $request->Tahun;
            // cek data
            $tagihan = getTagihan($nop, $tahun);
            if (!empty($tagihan)) {

                // ini hanya untuk mode dev & kalau production tolong di hilangkan
                // $cekpay=Payment::find($nop);

                if (($tagihan[0]->status_bayar == null || $tagihan[0]->status_bayar == 0)) {
                    if (
                        $request->Pokok == $tagihan[0]->pokok &&
                        $request->Denda == $tagihan[0]->denda &&
                        $request->Total == $tagihan[0]->total
                    ) {

                        DB::beginTransaction();
                        try {
                            $dt = array_map(function ($value) {
                                return (array)$value;
                            }, $tagihan);

                            $user = UserService::where('username', $_SERVER['PHP_AUTH_USER'])->where('password', $_SERVER['PHP_AUTH_PW'])->first();
                            $datainsert = [
                                "nop" => $dt[0]['nop'],
                                "masa" => $dt[0]['masa'],
                                "tahun" => $dt[0]['tahun'],
                                "jatuhtempo" => $dt[0]['jatuhtempo'],
                                "koderek" => $dt[0]['koderek'],
                                "pokok" => (int)$dt[0]['pokok'],
                                "denda" => (int)$dt[0]['denda'],
                                "total" => (int)$dt[0]['total'],
                                "pengesahan" => KdPengesahan(),
                                "referensi" => $request->Referensi,
                                "kodeinstitusi" => $request->KodeInstitusi,
                                "nohp" => $request->NoHp,
                                "email" => $request->Email,
                                "username" => $user->username,
                                "kode_bank" => $user->kode_bank,
                                "nama_bank" => $user->nama_bank,
                                "ip" => $request->ip()
                            ];
                            $res = Payment::create($datainsert);

                            if ($res->koderek == '4111301') {
                                // bphtb
                                $tgl=date('d/m/Y',strtotime($request->DateTime));
                                DB::statement("call bphtb_dev.bayar_tagihan('$res->nop',$res->total,TO_DATE('$tgl', 'DD/MM/YYYY'),'$res->kode_bank','$res->pengesahan','$res->kode_bank','$res->ip','berdasarkan kode biling, dan melalui service pembayaran')");
                            } else {
                                // pdrd
                                DB::statement("CALL pdrd2_dev.P_INSERT_SPTPD_PEMBAYARAN('$res->nop','$res->ip','$res->kode_bank','$res->total','$res->pengesahan','$res->kode_bank')");
                                $df = DB::select("select keterangan,kode_biling from pdrd2_dev.SPTPD_TAGIHAN where kode_biling='$nop'");
                                $upd = "update pdrd2_dev." . $df[0]->keterangan . " set tgl_bayar=sysdate ,status=1 where kode_biling='" . $res->nop . "'";
                                DB::statement($upd);
                            }


                            $data['Nop'] = $res->nop;
                            $data['Masa'] = $res->masa;
                            $data['Tahun'] = $res->tahun;
                            $data['JatuhTempo'] = $res->jatuhtempo;
                            $data['KodeRek'] = $res->koderek;
                            $data['Pokok'] = $res->pokok;
                            $data['Denda'] = $res->denda;
                            $data['Total'] = $res->total;
                            $data['Pengesahan'] = $res->pengesahan;
                            $data['Referensi'] = $res->referensi;
                            $data['KodeInstitusi'] = $res->kodeinstitusi;
                            $data['NoHp'] = $res->nohp;
                            $data['Email'] = $res->email;
                            $msg = "sukses";
                            $kode = '00';
                            $error = "False";
                            DB::commit();
                            // all good
                        } catch (\Exception $e) {
                            DB::rollback();
                            // something went wrong
                            $error = "True";
                            $msg = 'Message: ' . $e->getMessage();
                            $kode = '96';
                        }
                    } else {

                        $msg = "Jumlah nominal yang dibayarkan tidak sama dengan seharusnya dibayar";
                        $error = "True";
                        $kode = '16';
                    }
                } else {
                    $msg = "Tagihan sudah di bayar";
                    $error = "True";
                    $kode = '13';
                }
            } else {
                $msg = "Data tidak ditemukan";
                $error = "True";
                $kode = '10';
            }
        }

        $status = array(
            "Status" => [
                'IsError' => $error,
                'ResponseCode' => $kode,
                'ErrorDesc' => $msg
            ]
        );

        $response = \array_merge($data, $status);
        return response()->json($response);
    }
}
