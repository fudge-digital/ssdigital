<?php
return [
    // kunci promo_type => harga per siswa
    'prices' => [
        'none'     => 325000,
        'sibling'  => 300000,
        'sponsor'  => 300000,       // contoh promo lain
        'beasiswa' => 0,
    ],

    // label / warna untuk UI
    'labels' => [
        'none'     => ['label' => 'Tidak Ada', 'class' => 'bg-gray-400 text-white'],
        'sibling'  => ['label' => 'Sibling',  'class' => 'bg-blue-600 text-white'],
        'sponsor'  => ['label' => 'Sponsor',  'class' => 'bg-green-600 text-white'],
        'beasiswa' => ['label' => 'Beasiswa', 'class' => 'bg-purple-600 text-white'],
    ],
];
