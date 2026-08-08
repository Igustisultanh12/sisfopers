<?php

namespace App\Helpers;

class MilitaryUnitHelper
{
    public static function getUnits(?string $matra = null): array
    {
        $units = [
            'AD' => [
                'label_kotama' => 'Kodam (Komando Daerah Militer)',
                'label_satuan' => 'Kodim (Komando Distrik Militer)',
                'kotama' => [
                    'Kodam I/Bukit Barisan' => ['Kodim 0201/Medan', 'Kodim 0202/Tapanuli Utara', 'Kodim 0203/Langkat', 'Kodim 0204/Deli Serdang', 'Kodim 0209/Labuhanbatu'],
                    'Kodam II/Sriwijaya' => ['Kodim 0401/Muba', 'Kodim 0402/OKI', 'Kodim 0403/OKU', 'Kodim 0418/Palembang', 'Kodim 0407/Kota Bengkulu'],
                    'Kodam III/Siliwangi' => ['Kodim 0601/Pandeglang', 'Kodim 0602/Serang', 'Kodim 0606/Kota Bogor', 'Kodim 0618/Kota Bandung', 'Kodim 0609/Cimahi'],
                    'Kodam IV/Diponegoro' => ['Kodim 0701/Banyumas', 'Kodim 0733/Kota Semarang', 'Kodim 0734/Kota Yogyakarta', 'Kodim 0709/Kebumen'],
                    'Kodam V/Brawijaya' => ['Kodim 0801/Pacitan', 'Kodim 0830/Surabaya Utara', 'Kodim 0831/Surabaya Timur', 'Kodim 0832/Surabaya Selatan', 'Kodim 0833/Kota Malang'],
                    'Kodam VI/Mulawarman' => ['Kodim 0901/Samarinda', 'Kodim 0905/Balikpapan', 'Kodim 0907/Tarakan', 'Kodim 0908/Bontang'],
                    'Kodam IX/Udayana' => ['Kodim 1609/Buleleng', 'Kodim 1611/Badung', 'Kodim 1612/Manggarai', 'Kodim 1606/Mataram', 'Kodim 1604/Kupang'],
                    'Kodam XII/Tanjungpura' => ['Kodim 1207/Pontianak', 'Kodim 1201/Mempawah', 'Kodim 1202/Singkawang'],
                    'Kodam XIII/Merdeka' => ['Kodim 1309/Manado', 'Kodim 1310/Bitung', 'Kodim 1306/Kota Palu'],
                    'Kodam XIV/Hasanuddin' => ['Kodim 1408/Makassar', 'Kodim 1409/Gowa', 'Kodim 1418/Mamuju'],
                    'Kodam XVI/Pattimura' => ['Kodim 1504/Ambon', 'Kodim 1508/Tobelo', 'Kodim 1501/Ternate'],
                    'Kodam XVII/Cenderawasih' => ['Kodim 1701/Jayapura', 'Kodim 1702/Jayawijaya', 'Kodim 1705/Nabire'],
                    'Kodam XVIII/Kasuari' => ['Kodim 1801/Manokwari', 'Kodim 1802/Sorong', 'Kodim 1803/Fakfak'],
                    'Kodam Jaya' => ['Kodim 0501/Jakarta Pusat', 'Kodim 0502/Jakarta Utara', 'Kodim 0503/Jakarta Barat', 'Kodim 0504/Jakarta Selatan', 'Kodim 0505/Jakarta Timur', 'Kodim 0506/Tangerang', 'Kodim 0507/Bekasi', 'Kodim 0508/Depok'],
                ]
            ],
            'AL' => [
                'label_kotama' => 'Kodaeral (Komando Daerah Angkatan Laut)',
                'label_satuan' => 'Lanal (Pangkalan TNI Angkatan Laut)',
                'kotama' => [
                    'Kodaeral I (Belawan)' => ['Lanal Sabang', 'Lanal Lhokseumawe', 'Lanal Tanjung Balai Asahan', 'Lanal Simeulue'],
                    'Kodaeral II (Padang)' => ['Lanal Sibolga', 'Lanal Nias', 'Lanal Bengkulu'],
                    'Kodaeral III (Jakarta)' => ['Lanal Lampung', 'Lanal Palembang', 'Lanal Cirebon', 'Lanal Bandung', 'Lanal Banten'],
                    'Kodaeral IV (Batam)' => ['Lanal Ranai', 'Lanal Tarempa', 'Lanal Dabo Singkep'],
                    'Kodaeral V (Surabaya)' => ['Lanal Semarang', 'Lanal Yogyakarta', 'Lanal Cilacap', 'Lanal Malang', 'Lanal Banyuwangi', 'Lanal Denpasar'],
                    'Kodaeral VI (Makassar)' => ['Lanal Mamuju', 'Lanal Palu', 'Lanal Kendari'],
                    'Kodaeral VII (Kupang)' => ['Lanal Mataram', 'Lanal Maumere', 'Lanal Rote', 'Lanal Waingapu'],
                    'Kodaeral VIII (Manado)' => ['Lanal Gorontalo', 'Lanal Tahuna', 'Lanal Melonguane'],
                    'Kodaeral IX (Ambon)' => ['Lanal Saumlaki', 'Lanal Aru'],
                    'Kodaeral X (Jayapura)' => ['Lanal Biak', 'Lanal Sarmi'],
                    'Kodaeral XI (Merauke)' => ['Lanal Timika', 'Lanal Arafuru'],
                    'Kodaeral XII (Pontianak)' => ['Lanal Sambas', 'Lanal Ketapang'],
                    'Kodaeral XIII (Tarakan)' => ['Lanal Nunukan', 'Lanal Sangatta', 'Lanal Balikpapan'],
                    'Kodaeral XIV (Sorong)' => ['Lanal Morotai', 'Lanal Kaimana'],
                ]
            ],
            'AU' => [
                'label_kotama' => 'Kodau (Komando Daerah Angkatan Udara)',
                'label_satuan' => 'Lanud (Pangkalan TNI Angkatan Udara)',
                'kotama' => [
                    'Kodau I (Koopsud I)' => ['Lanud Halim Perdanakusuma', 'Lanud Atang Sendjaja', 'Lanud Suryadarma', 'Lanud Husein Sastranegara', 'Lanud Roesmin Nurjadin', 'Lanud Soewondo', 'Lanud Sultan Iskandar Muda', 'Lanud Maimun Saleh', 'Lanud Sutan Sjahrir', 'Lanud Sri Mulyono Herlambang', 'Lanud H.AS Hanandjoeddin', 'Lanud Raden Sadjad', 'Lanud Prince M. Bun Yamin'],
                    'Kodau II (Koopsud II)' => ['Lanud Sultan Hasanuddin', 'Lanud Iswahjudi', 'Lanud Abdulrachman Saleh', 'Lanud Muljono', 'Lanud DAA', 'Lanud Sam Ratulangi', 'Lanud Dumatubun', 'Lanud Zam', 'Lanud Syamsudin Noor', 'Lanud Anang Busra'],
                    'Kodau III (Koopsud III)' => ['Lanud Silas Papare', 'Lanud Manuhua', 'Lanud Johannes Abraham Dimara', 'Lanud Leo Wattimena', 'Lanud Pattimura', 'Lanud El Tari'],
                ]
            ]
        ];

        if ($matra && isset($units[$matra])) {
            return $units[$matra];
        }

        return $units;
    }
}
