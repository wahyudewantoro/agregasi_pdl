<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getkode')) {
    function getkode()
    {
        $panjang = 55;
        $karakter       = 'kodingin.com4543534-039849kldsam][].';
        $panjangKata = strlen($karakter);
        $kode = '';
        for ($i = 0; $i < $panjang; $i++) {
            $kode .= $karakter[rand(0, $panjangKata - 1)];
        }
        return $kode;
    }
}

if (!function_exists('getTagihan')) {
    function getTagihan($kobil = null, $tahun = null)
    {

        $tahun = $tahun ?? date('Y');

        return DB::select("SELECT ID_BILLING NOP, NAMA_WP NAMA,ALAMAT_WP ALAMAT,SUBSTR(ID_BILLING,10,2) MASA,'20'||SUBSTR(ID_BILLING,8,2) TAHUN, NULL NOSK,TO_CHAR(JATUH_TEMPO, 'ddmmyyyy')  JATUHTEMPO,
        KODE_REK KodeRek,BPHTB POKOK,DENDA ,POTONGAN,TOTAL,CASE WHEN STATUS_BAYAR = 1 THEN '1' ELSE '0' END STATUS_BAYAR
        FROM BPHTB_DEV.TAGIHAN_SSPD
        WHERE  ID_BILLING='$kobil' and '20'||SUBSTR(ID_BILLING,8,2)='$tahun' 
        UNION ALL
        SELECT KODE_BILING NOP, NAMA_WP NAMA,ALAMAT_WP ALAMAT,SUBSTR(KODE_BILING,10,2) MASA,'20'||SUBSTR(KODE_BILING,8,2) TAHUN,NO_SK NOSK,TO_CHAR(JATUH_TEMPO, 'ddmmyyyy')  JATUHTEMPO,
        KODE_REK KODEREK,CEIL(TAGIHAN-POTONGAN) POKOK,DENDA ,POTONGAN, CEIL(TAGIHAN-POTONGAN+DENDA) TOTAL,CASE WHEN STATUS = 1 THEN '1' ELSE '0' END STATUS_BAYAR 
        FROM PDRD2_DEV.TAGIHAN
        WHERE  KODE_BILING='$kobil' and '20'||SUBSTR(KODE_BILING,8,2)='$tahun' 
        ");
    }
}

if (!function_exists('KdPengesahan')) {
    function KdPengesahan($digits = 12)
    {
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while ($i < $digits) {
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return '3507' . $pin;
    }
}
