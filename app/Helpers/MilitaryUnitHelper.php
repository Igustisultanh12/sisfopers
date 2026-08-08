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
                    'Kodam I/Bukit Barisan' => [
                        'Kodim 0201/Medan', 'Kodim 0202/Tapanuli Utara', 'Kodim 0203/Langkat', 'Kodim 0204/Deli Serdang',
                        'Kodim 0205/Tanah Karo', 'Kodim 0206/Dairi', 'Kodim 0207/Simalungun', 'Kodim 0208/Asahan',
                        'Kodim 0209/Labuhanbatu', 'Kodim 0210/Tapanuli Utara', 'Kodim 0211/Tapanuli Tengah', 'Kodim 0212/Tapanuli Selatan',
                        'Kodim 0213/Nias', 'Kodim 0304/Agam', 'Kodim 0305/Pasaman', 'Kodim 0306/50 Kota', 'Kodim 0307/Tanah Datar',
                        'Kodim 0308/Pariaman', 'Kodim 0309/Solok', 'Kodim 0311/Pesisir Selatan', 'Kodim 0319/Mentawai',
                        'Kodim 0301/Pekanbaru', 'Kodim 0302/Inhu', 'Kodim 0303/Bengkalis', 'Kodim 0313/Kampar',
                        'Kodim 0314/Inhil', 'Kodim 0320/Dumai', 'Kodim 0321/Rokan Hilir', 'Kodim 0315/Tanjung Pinang',
                        'Kodim 0316/Batam', 'Kodim 0317/Tanjung Balai Karimun', 'Kodim 0318/Natuna'
                    ],
                    'Kodam II/Sriwijaya' => [
                        'Kodim 0401/Muba', 'Kodim 0402/OKI', 'Kodim 0403/OKU', 'Kodim 0404/Muara Enim', 'Kodim 0405/Lahat',
                        'Kodim 0406/Lubuklinggau', 'Kodim 0418/Palembang', 'Kodim 0430/Banyuasin', 'Kodim 0407/Kota Bengkulu',
                        'Kodim 0408/Bengkulu Selatan', 'Kodim 0409/Rejang Lebong', 'Kodim 0423/Bengkulu Utara', 'Kodim 0425/Seluma',
                        'Kodim 0428/Mukomuko', 'Kodim 0415/Jambi', 'Kodim 0417/Kerinci', 'Kodim 0419/Tanjab', 'Kodim 0420/Sarko',
                        'Kodim 0411/Kota Metro', 'Kodim 0412/Lampung Utara', 'Kodim 0421/Lampung Selatan', 'Kodim 0422/Lampung Barat',
                        'Kodim 0424/Tanggamus', 'Kodim 0426/Tulang Bawang', 'Kodim 0427/Way Kanan', 'Kodim 0429/Lampung Timur',
                        'Kodim 0413/Bangka', 'Kodim 0414/Belitung', 'Kodim 0431/Bangka Barat'
                    ],
                    'Kodam III/Siliwangi' => [
                        'Kodim 0601/Pandeglang', 'Kodim 0602/Serang', 'Kodim 0603/Lebak', 'Kodim 0623/Cilegon',
                        'Kodim 0604/Karawang', 'Kodim 0605/Subang', 'Kodim 0606/Kota Bogor', 'Kodim 0607/Kota Sukabumi',
                        'Kodim 0608/Cianjur', 'Kodim 0609/Cimahi', 'Kodim 0610/Sumedang', 'Kodim 0611/Garut',
                        'Kodim 0612/Tasikmalaya', 'Kodim 0613/Ciamis', 'Kodim 0614/Kota Cirebon', 'Kodim 0615/Kuningan',
                        'Kodim 0616/Indramayu', 'Kodim 0617/Majalengka', 'Kodim 0618/Kota Bandung', 'Kodim 0620/Kabupaten Cirebon',
                        'Kodim 0621/Kabupaten Bogor', 'Kodim 0622/Kabupaten Sukabumi', 'Kodim 0624/Kabupaten Bandung', 'Kodim 0625/Pangandaran'
                    ],
                    'Kodam IV/Diponegoro' => [
                        'Kodim 0701/Banyumas', 'Kodim 0702/Purbalingga', 'Kodim 0703/Cilacap', 'Kodim 0704/Banjarnegara',
                        'Kodim 0705/Magelang', 'Kodim 0706/Temanggung', 'Kodim 0707/Wonosobo', 'Kodim 0708/Purworejo',
                        'Kodim 0709/Kebumen', 'Kodim 0710/Pekalongan', 'Kodim 0711/Pemalang', 'Kodim 0712/Tegal',
                        'Kodim 0713/Brebes', 'Kodim 0714/Salatiga', 'Kodim 0715/Kendal', 'Kodim 0716/Demak',
                        'Kodim 0717/Grobogan', 'Kodim 0718/Pati', 'Kodim 0719/Jepara', 'Kodim 0720/Rembang',
                        'Kodim 0721/Blora', 'Kodim 0722/Kudus', 'Kodim 0723/Klaten', 'Kodim 0724/Boyolali',
                        'Kodim 0725/Sragen', 'Kodim 0726/Sukoharjo', 'Kodim 0727/Karanganyar', 'Kodim 0728/Wonogiri',
                        'Kodim 0733/Kota Semarang', 'Kodim 0734/Kota Yogyakarta', 'Kodim 0730/Gunungkidul', 'Kodim 0731/Kulon Progo', 'Kodim 0732/Sleman'
                    ],
                    'Kodam V/Brawijaya' => [
                        'Kodim 0801/Pacitan', 'Kodim 0802/Ponorogo', 'Kodim 0803/Madiun', 'Kodim 0804/Magetan',
                        'Kodim 0805/Ngawi', 'Kodim 0806/Trenggalek', 'Kodim 0807/Tulungagung', 'Kodim 0808/Blitar',
                        'Kodim 0809/Kediri', 'Kodim 0810/Nganjuk', 'Kodim 0811/Tuban', 'Kodim 0812/Lamongan',
                        'Kodim 0813/Bojonegoro', 'Kodim 0814/Jombang', 'Kodim 0815/Mojokerto', 'Kodim 0816/Sidoarjo',
                        'Kodim 0817/Gresik', 'Kodim 0818/Kabupaten Malang', 'Kodim 0819/Pasuruan', 'Kodim 0820/Probolinggo',
                        'Kodim 0821/Lumajang', 'Kodim 0822/Bondowoso', 'Kodim 0823/Situbondo', 'Kodim 0824/Jember',
                        'Kodim 0825/Banyuwangi', 'Kodim 0826/Pamekasan', 'Kodim 0827/Sumenep', 'Kodim 0828/Sampang',
                        'Kodim 0829/Bangkalan', 'Kodim 0830/Surabaya Utara', 'Kodim 0831/Surabaya Timur', 'Kodim 0832/Surabaya Selatan', 'Kodim 0833/Kota Malang'
                    ],
                    'Kodam VI/Mulawarman' => [
                        'Kodim 0901/Samarinda', 'Kodim 0902/Berau', 'Kodim 0904/Paser', 'Kodim 0905/Balikpapan',
                        'Kodim 0906/Kutai Kartanegara', 'Kodim 0907/Tarakan', 'Kodim 0908/Bontang', 'Kodim 0909/Kutai Timur',
                        'Kodim 0911/Nunukan', 'Kodim 0912/Kutai Barat', 'Kodim 0913/Penajam Paser Utara', 'Kodim 0914/Tana Tidung',
                        'Kodim 1001/Hulu Sungai Utara', 'Kodim 1002/Hulu Sungai Tengah', 'Kodim 1003/Hulu Sungai Selatan',
                        'Kodim 1004/Kotabaru', 'Kodim 1005/Barito Kuala', 'Kodim 1006/Banjar', 'Kodim 1007/Banjarmasin',
                        'Kodim 1008/Tabalong', 'Kodim 1009/Tanah Laut', 'Kodim 1010/Tapin', 'Kodim 1022/Tanah Bumbu'
                    ],
                    'Kodam IX/Udayana' => [
                        'Kodim 1609/Buleleng', 'Kodim 1611/Badung', 'Kodim 1612/Manggarai', 'Kodim 1606/Mataram',
                        'Kodim 1607/Sumbawa', 'Kodim 1608/Bima', 'Kodim 1614/Dompu', 'Kodim 1615/Lombok Timur',
                        'Kodim 1620/Lombok Tengah', 'Kodim 1601/Sumba Timur', 'Kodim 1602/Ende', 'Kodim 1603/Sikka',
                        'Kodim 1604/Kupang', 'Kodim 1605/Belu', 'Kodim 1613/Sumba Barat', 'Kodim 1618/TTU',
                        'Kodim 1621/TTS', 'Kodim 1622/Alor', 'Kodim 1624/Flores Timur', 'Kodim 1625/Ngada',
                        'Kodim 1627/Rote Ndao', 'Kodim 1629/Sumba Barat Daya'
                    ],
                    'Kodam XII/Tanjungpura' => [
                        'Kodim 1201/Mempawah', 'Kodim 1202/Singkawang', 'Kodim 1203/Ketapang', 'Kodim 1204/Sanggau',
                        'Kodim 1205/Sintang', 'Kodim 1206/Putussibau', 'Kodim 1207/Pontianak', 'Kodim 1208/Sambas',
                        'Kodim 1209/Bengkayang', 'Kodim 1011/Kuala Kapuas', 'Kodim 1012/Buntok', 'Kodim 1013/Muara Teweh',
                        'Kodim 1014/Pangkalan Bun', 'Kodim 1015/Sampit', 'Kodim 1016/Palangka Raya', 'Kodim 1017/Lamandau', 'Kodim 1019/Katingan'
                    ],
                    'Kodam XIII/Merdeka' => [
                        'Kodim 1301/Sangihe', 'Kodim 1302/Minahasa', 'Kodim 1303/Bolaang Mongondow', 'Kodim 1309/Manado',
                        'Kodim 1310/Bitung', 'Kodim 1312/Talaud', 'Kodim 1313/Pohuwato', 'Kodim 1314/Gorontalo Utara',
                        'Kodim 1315/Kabupaten Gorontalo', 'Kodim 1304/Gorontalo', 'Kodim 1305/Buol Tolitoli', 'Kodim 1306/Kota Palu',
                        'Kodim 1307/Poso', 'Kodim 1308/Luwuk Banggai', 'Kodim 1311/Morowali'
                    ],
                    'Kodam XIV/Hasanuddin' => [
                        'Kodim 1401/Majene', 'Kodim 1402/Polman', 'Kodim 1403/Palopo', 'Kodim 1404/Pinrang',
                        'Kodim 1405/Parepare', 'Kodim 1406/Wajo', 'Kodim 1407/Bone', 'Kodim 1408/Makassar',
                        'Kodim 1409/Gowa', 'Kodim 1410/Bantaeng', 'Kodim 1411/Bulukumba', 'Kodim 1412/Kolaka',
                        'Kodim 1413/Buton', 'Kodim 1414/Tana Toraja', 'Kodim 1415/Selayar', 'Kodim 1416/Muna',
                        'Kodim 1417/Kendari', 'Kodim 1418/Mamuju', 'Kodim 1419/Enrekang', 'Kodim 1420/Sidrap',
                        'Kodim 1421/Pangkep', 'Kodim 1422/Maros', 'Kodim 1423/Soppeng', 'Kodim 1424/Sinjai',
                        'Kodim 1425/Jeneponto', 'Kodim 1426/Takalar', 'Kodim 1427/Pasangkayu', 'Kodim 1428/Mamasa',
                        'Kodim 1429/Buton Utara', 'Kodim 1430/Konawe Utara', 'Kodim 1431/Bombana'
                    ],
                    'Kodam XV/Pattimura' => [
                        'Kodim 1501/Ternate', 'Kodim 1502/Masohi', 'Kodim 1503/Tual', 'Kodim 1504/Ambon',
                        'Kodim 1505/Tidore', 'Kodim 1506/Namlea', 'Kodim 1507/Saumlaki', 'Kodim 1508/Tobelo',
                        'Kodim 1509/Labuha', 'Kodim 1510/Sula', 'Kodim 1511/Pulau Moa', 'Kodim 1512/Weda',
                        'Kodim 1513/Seram Bagian Barat', 'Kodim 1514/Morotai'
                    ],
                    'Kodam XVII/Cenderawasih' => [
                        'Kodim 1701/Jayapura', 'Kodim 1702/Jayawijaya', 'Kodim 1703/Deiyai', 'Kodim 1705/Nabire',
                        'Kodim 1708/Biak Numfor', 'Kodim 1709/Yapen Waropen', 'Kodim 1710/Mimika', 'Kodim 1711/Boven Digoel',
                        'Kodim 1712/Sarmi', 'Kodim 1714/Puncak Jaya', 'Kodim 1715/Yahukimo', 'Kodim 1716/Mamberamo Raya'
                    ],
                    'Kodam XVIII/Kasuari' => [
                        'Kodim 1801/Manokwari', 'Kodim 1802/Sorong', 'Kodim 1803/Fakfak', 'Kodim 1804/Kaimana',
                        'Kodim 1805/Raja Ampat', 'Kodim 1806/Teluk Bintuni', 'Kodim 1807/South Sorong', 'Kodim 1808/Manokwari Selatan',
                        'Kodim 1809/Maybrat', 'Kodim 1810/Tambrauw', 'Kodim 1811/Teluk Wondama', 'Kodim 1812/Pegunungan Arfak'
                    ],
                    'Kodam Jaya' => [
                        'Kodim 0501/Jakarta Pusat', 'Kodim 0502/Jakarta Utara', 'Kodim 0503/Jakarta Barat',
                        'Kodim 0504/Jakarta Selatan', 'Kodim 0505/Jakarta Timur', 'Kodim 0506/Tangerang',
                        'Kodim 0507/Bekasi', 'Kodim 0508/Depok', 'Kodim 0509/Kabupaten Bekasi', 'Kodim 0510/Tigaraksa'
                    ],
                    'Kodam Iskandar Muda' => [
                        'Kodim 0101/Kota Banda Aceh', 'Kodim 0102/Pidie', 'Kodim 0103/Aceh Utara', 'Kodim 0104/Aceh Timur',
                        'Kodim 0105/Aceh Barat', 'Kodim 0106/Aceh Tengah', 'Kodim 0107/Aceh Selatan', 'Kodim 0108/Aceh Tenggara',
                        'Kodim 0109/Aceh Singkil', 'Kodim 0110/Aceh Barat Daya', 'Kodim 0111/Bireuen', 'Kodim 0112/Sabang',
                        'Kodim 0113/Gayo Lues', 'Kodim 0114/Aceh Jaya', 'Kodim 0115/Simeulue', 'Kodim 0116/Nagan Raya',
                        'Kodim 0117/Aceh Tamiang', 'Kodim 0118/Subulussalam', 'Kodim 0119/Bener Meriah'
                    ]
                ]
            ],
            'AL' => [
                'label_kotama' => 'Kodaeral (Komando Daerah Angkatan Laut)',
                'label_satuan' => 'Lanal (Pangkalan TNI Angkatan Laut)',
                'kotama' => [
                    'Kodaeral I (Belawan)' => ['Lanal Sabang', 'Lanal Lhokseumawe', 'Lanal Tanjung Balai Asahan', 'Lanal Simeulue', 'Lanal Dumai', 'Lanal Bintan'],
                    'Kodaeral II (Padang)' => ['Lanal Sibolga', 'Lanal Nias', 'Lanal Bengkulu', 'Lanal Melaboh'],
                    'Kodaeral III (Jakarta)' => ['Lanal Lampung', 'Lanal Palembang', 'Lanal Cirebon', 'Lanal Bandung', 'Lanal Banten', 'Lanal Pangandaran'],
                    'Kodaeral IV (Batam)' => ['Lanal Ranai', 'Lanal Tarempa', 'Lanal Dabo Singkep', 'Lanal Tanjung Balai Karimun'],
                    'Kodaeral V (Surabaya)' => ['Lanal Semarang', 'Lanal Yogyakarta', 'Lanal Cilacap', 'Lanal Malang', 'Lanal Banyuwangi', 'Lanal Denpasar', 'Lanal Batuporon'],
                    'Kodaeral VI (Makassar)' => ['Lanal Mamuju', 'Lanal Palu', 'Lanal Kendari', 'Lanal Fajar'],
                    'Kodaeral VII (Kupang)' => ['Lanal Mataram', 'Lanal Maumere', 'Lanal Rote', 'Lanal Waingapu', 'Lanal Labuan Bajo'],
                    'Kodaeral VIII (Manado)' => ['Lanal Gorontalo', 'Lanal Tahuna', 'Lanal Melonguane', 'Lanal Tolitoli'],
                    'Kodaeral IX (Ambon)' => ['Lanal Saumlaki', 'Lanal Aru', 'Lanal Bandanaira'],
                    'Kodaeral X (Jayapura)' => ['Lanal Biak', 'Lanal Sarmi', 'Lanal Nabire'],
                    'Kodaeral XI (Merauke)' => ['Lanal Timika', 'Lanal Arafuru', 'Lanal Agats'],
                    'Kodaeral XII (Pontianak)' => ['Lanal Sambas', 'Lanal Ketapang'],
                    'Kodaeral XIII (Tarakan)' => ['Lanal Nunukan', 'Lanal Sangatta', 'Lanal Balikpapan', 'Lanal Kotabaru'],
                    'Kodaeral XIV (Sorong)' => ['Lanal Morotai', 'Lanal Kaimana', 'Lanal Fakfak']
                ]
            ],
            'AU' => [
                'label_kotama' => 'Kodau (Komando Daerah Angkatan Udara)',
                'label_satuan' => 'Lanud (Pangkalan TNI Angkatan Udara)',
                'kotama' => [
                    'Kodau I (Koopsud I)' => [
                        'Lanud Halim Perdanakusuma (Jakarta)', 'Lanud Atang Sendjaja (Bogor)', 'Lanud Suryadarma (Subang)',
                        'Lanud Husein Sastranegara (Bandung)', 'Lanud Roesmin Nurjadin (Pekanbaru)', 'Lanud Soewondo (Medan)',
                        'Lanud Sultan Iskandar Muda (Banda Aceh)', 'Lanud Maimun Saleh (Sabang)', 'Lanud Sutan Sjahrir (Padang)',
                        'Lanud Sri Mulyono Herlambang (Palembang)', 'Lanud H.AS Hanandjoeddin (Belitung)', 'Lanud Raden Sadjad (Natuna)',
                        'Lanud Prince M. Bun Yamin (Lampung)', 'Lanud Sugiri Sukani (Majalengka)', 'Lanud Wiriadinata (Tasikmalaya)', 'Lanud Gading (Gunungkidul)'
                    ],
                    'Kodau II (Koopsud II)' => [
                        'Lanud Sultan Hasanuddin (Makassar)', 'Lanud Iswahjudi (Madiun)', 'Lanud Abdulrachman Saleh (Malang)',
                        'Lanud Muljono (Surabaya)', 'Lanud Sam Ratulangi (Manado)', 'Lanud Zainuddin Abdul Madjid / Zam (Lombok)',
                        'Lanud Syamsudin Noor (Banjarmasin)', 'Lanud Anang Busra (Tarakan)', 'Lanud I Gusti Ngurah Rai (Bali)',
                        'Lanud Hadizuddin (Pontianak)', 'Lanud Iskandar (Pangkalan Bun)'
                    ],
                    'Kodau III (Koopsud III)' => [
                        'Lanud Silas Papare (Jayapura)', 'Lanud Manuhua (Biak)', 'Lanud Johannes Abraham Dimara (Merauke)',
                        'Lanud Leo Wattimena (Morotai)', 'Lanud Pattimura (Ambon)', 'Lanud El Tari (Kupang)',
                        'Lanud Yohanis Kapiyau (Timika)', 'Lanud Dumatubun (Langgur)', 'Lanud Ignatius Dewanto (Saumlaki)'
                    ]
                ]
            ]
        ];

        if ($matra && isset($units[$matra])) {
            return $units[$matra];
        }

        return $units;
    }
}
