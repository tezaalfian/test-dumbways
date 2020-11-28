<?php

function tentukanOlahraga($kalori)
{
    $olahraga = "";
    $menit = 0;
    if ($kalori > 750) {
        $olahraga = "Lari";
    } elseif ($kalori > 500 && $kalori <= 750) {
        $olahraga = "Badminton";
    } else {
        $olahraga = "Renang";
    }
    $menit = floor(($kalori/20)*2);
    echo "Jumlah Kalori     : $kalori kalori\n";
    echo "Jenis Olahraga    : $olahraga\n";
    echo "Waktu Olahraga    : $menit Menit";
}

tentukanOlahraga(751);