<?php
function kirimNotifWhatsApp($kode, $namaPenerima, $noHp, $alamat, $catatan, $metode, $total, $items) {
    $itemList = '';
    foreach ($items as $item) {
        $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
        $sub = number_format($h * $item['jumlah'], 0, ',', '.');
        $itemList .= "- {$item['nama']} x{$item['jumlah']} = Rp {$sub}\n";
    }
    $totalFmt = number_format($total, 0, ',', '.');
    $catatan  = $catatan ?: '-';

    $pesan = "🥬 *PESANAN BARU MASUK!*\n\n"
           . "📋 *Kode:* $kode\n"
           . "👤 *Nama:* $namaPenerima\n"
           . "📱 *HP:* $noHp\n"
           . "📍 *Alamat:* $alamat\n"
           . "📝 *Catatan:* $catatan\n"
           . "💳 *Metode:* $metode\n\n"
           . "*Detail Produk:*\n$itemList\n"
           . "💰 *Total: Rp $totalFmt*";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'target'  => '085693425118',
            'message' => $pesan,
        ],
        CURLOPT_HTTPHEADER => [
            'Authorization: b8f7tB65wtAnPfiyGGFv',
        ],
    ]);
    curl_exec($curl);
    curl_close($curl);
}
