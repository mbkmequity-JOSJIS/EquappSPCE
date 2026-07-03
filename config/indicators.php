<?php

declare(strict_types=1);

return [
    /**
     * Threshold untuk setiap indikator
     * Status: 'normal', 'waspada', 'bahaya'
     */
    'aqua_viska' => [
        'temperature' => [
            'label' => 'Suhu Air',
            'unit' => '°C',
            'description' => 'Temperatur air permukaan yang diukur di lokasi.',
            'thresholds' => [
                'normal' => ['min' => 25, 'max' => 30],
                'waspada' => [
                    ['min' => 23, 'max' => 24],
                    ['min' => 31, 'max' => 33],
                ],
                'bahaya' => [
                    ['min' => null, 'max' => 22.99],
                    ['min' => 33.01, 'max' => null],
                ],
            ],
        ],
        'ph' => [
            'label' => 'pH',
            'unit' => 'skala 0–14',
            'description' => 'Tingkat keasaman atau kebasaan air tanpa satuan.',
            'thresholds' => [
                'normal' => ['min' => 6.5, 'max' => 8.5],
                'waspada' => [
                    ['min' => 5.5, 'max' => 6.4],
                    ['min' => 8.6, 'max' => 9.0],
                ],
                'bahaya' => [
                    ['min' => null, 'max' => 5.49],
                    ['min' => 9.01, 'max' => null],
                ],
            ],
        ],
        'do' => [
            'label' => 'Dissolved Oxygen (DO)',
            'unit' => 'mg/L',
            'description' => 'Jumlah oksigen terlarut yang tersedia di dalam air.',
            'thresholds' => [
                'normal' => ['min' => 4.0, 'max' => null],
                'waspada' => ['min' => 3.0, 'max' => 3.99],
                'bahaya' => ['min' => null, 'max' => 2.99],
            ],
        ],
        'turbidity' => [
            'label' => 'Kekeruhan (Turbidity)',
            'unit' => 'NTU',
            'description' => 'Seberapa keruh air; nilai lebih tinggi berarti air lebih keruh.',
            'thresholds' => [
                'normal' => ['min' => null, 'max' => 49.99],
                'waspada' => ['min' => 50, 'max' => 100],
                'bahaya' => ['min' => 100.01, 'max' => null],
            ],
        ],
        'tds' => [
            'label' => 'Total Dissolved Solids (TDS)',
            'unit' => 'ppm',
            'description' => 'Kadar mineral dan zat terlarut dalam air.',
            'thresholds' => [
                'normal' => ['min' => null, 'max' => 499.99],
                'waspada' => ['min' => 500, 'max' => 1000],
                'bahaya' => ['min' => 1000.01, 'max' => null],
            ],
        ],
    ],
    
    /**
     * Threshold untuk IoT CLIMATE sensors
     */
    'iot_climate' => [
        'suhu_udara' => [
            'label' => 'Suhu Udara',
            'unit' => '°C',
            'description' => 'Temperatur udara di sekitar lokasi sensor.',
            'thresholds' => [
                'normal' => ['min' => 20, 'max' => 30],
                'waspada' => [
                    ['min' => 16, 'max' => 19.99],
                    ['min' => 30.01, 'max' => 34],
                ],
                'bahaya' => [
                    ['min' => null, 'max' => 15.99],
                    ['min' => 34.01, 'max' => null],
                ],
            ],
        ],
        'kelembaban' => [
            'label' => 'Kelembapan',
            'unit' => '% RH',
            'description' => 'Persentase uap air di udara.',
            'thresholds' => [
                'normal' => ['min' => 40, 'max' => 60],
                'waspada' => [
                    ['min' => 30, 'max' => 39.99],
                    ['min' => 60.01, 'max' => 80],
                ],
                'bahaya' => [
                    ['min' => null, 'max' => 29.99],
                    ['min' => 80.01, 'max' => null],
                ],
            ],
        ],
        'co2' => [
            'label' => 'CO₂',
            'unit' => 'ppm',
            'description' => 'Kadar karbon dioksida di udara.',
            'thresholds' => [
                'normal' => ['min' => 400, 'max' => 1000],
                'waspada' => ['min' => 1001, 'max' => 2000],
                'bahaya' => ['min' => 2000.01, 'max' => null],
            ],
        ],
        'tvoc' => [
            'label' => 'TVOC',
            'unit' => 'ppb',
            'description' => 'Kadar senyawa organik volatil di udara.',
            'thresholds' => [
                'normal' => ['min' => 0, 'max' => 220],
                'waspada' => ['min' => 221, 'max' => 660],
                'bahaya' => ['min' => 660.01, 'max' => null],
            ],
        ],
        'uv_index' => [
            'label' => 'UV Index',
            'unit' => 'skala',
            'description' => 'Intensitas sinar ultraviolet yang mencapai permukaan.',
            'thresholds' => [
                'normal' => ['min' => 0, 'max' => 5],
                'waspada' => ['min' => 6, 'max' => 7],
                'bahaya' => ['min' => 7.01, 'max' => null],
            ],
        ],
        'kecepatan_angin' => [
            'label' => 'Kecepatan Angin',
            'unit' => 'km/jam',
            'description' => 'Kecepatan angin di sekitar area sensor.',
            'thresholds' => [
                'normal' => ['min' => 0, 'max' => 20],
                'waspada' => ['min' => 21, 'max' => 40],
                'bahaya' => ['min' => 40.01, 'max' => null],
            ],
        ],
        'curah_hujan' => [
            'label' => 'Curah Hujan',
            'unit' => 'mm/jam',
            'description' => 'Jumlah hujan yang tercatat dalam periode tertentu.',
            'thresholds' => [
                'normal' => ['min' => 0, 'max' => 4.99],
                'waspada' => ['min' => 5, 'max' => 20],
                'bahaya' => ['min' => 20.01, 'max' => null],
            ],
        ],
    ],
];
