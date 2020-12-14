<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Payment;
use Illuminate\Http\Request;
use Validator;


class InquiryController extends Controller
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
            "DateTime" => 'required|date_format:Y-m-d H:i:s',
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
            $error = "False";
            $nop = $request->Nop;
            $tahun = $request->Tahun;
            // cek data
            $tagihan = getTagihan($nop, $tahun);
            if (!empty($tagihan)) {
                // $cekpay = Payment::find($nop);
                if (($tagihan[0]->status_bayar == null || $tagihan[0]->status_bayar == 0) ) {
                    $data = array_map(function ($value) {
                        return (array)$value;
                    }, $tagihan);
                    $data = [
                        "Nop" => $data[0]['nop'],
                        "Nama" => $data[0]['nama'],
                        "Alamat" => $data[0]['alamat'],
                        "Masa" => $data[0]['masa'],
                        "Tahun" => $data[0]['tahun'],
                        "NoSk" => $data[0]['nosk'],
                        "JatuhTempo" => $data[0]['jatuhtempo'],
                        "KodeRek" => $data[0]['koderek'],
                        "Pokok" => (int)$data[0]['pokok'],
                        "Denda" => (int)$data[0]['denda'],
                        // "Potongan" => (int)$data[0]['potongan'],
                        "Total" => (int)$data[0]['total'],
                        "KodeInstitusi" => $request->KodeInstitusi,
                        "NoHp" => $request->NoHp,
                        "Email" => $request->Email
                    ];

                    $msg = "sukses";
                    $kode = '00';
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
