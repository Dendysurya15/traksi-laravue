<?php

use App\Models\KodeUnit;
use App\Models\LaporanP2H;
use App\Models\Regional;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


if (!function_exists('get_reg_wil_est')) {
    function get_reg_wil_est()
    {
        //     $query = Regional::with(['wilayah' => function ($query) {
        //         $query->where('nama', '=', 'Wilayah 1');
        //     }, 'wilayah.estate' => function ($query) {
        //         $query->where(DB::raw('LOWER(nama)'), 'not like', '%mill%')
        //             ->where(DB::raw('LOWER(nama)'), 'not like', '%srs%')
        //             ->where(DB::raw('LOWER(nama)'), 'not like', '%plasma%')
        //             ->where(DB::raw('LOWER(nama)'), 'not like', '%training%')
        //             ->where(DB::raw('LOWER(nama)'), 'not like', '%regional i%');
        //     }])

        $data = Regional::with(['wilayah', 'wilayah.estate' => function ($query) {
            $query->where(DB::raw('LOWER(nama)'), 'not like', '%mill%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%research%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%workshop%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%ranch%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%sre%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%lde%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%ske%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%plasma%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%training%')
                ->where(DB::raw('LOWER(nama)'), 'not like', '%regional i%');
        }])
            ->where('nama', '=', 'Regional I')
            ->get()
            ->toArray();

        // Add new collection to the last element of 'wilayah'

        // Add new item to 'wilayah'
        foreach ($data as &$regional) {
            if (isset($regional['wilayah'])) {
                // Create the new item to be pushed
                $newItem = [
                    'nama' => 'CWS',
                    'estate' => [
                        [
                            'est' => 'BSJ'
                        ]
                    ]
                ];

                // Push the new item to the 'wilayah' array
                $regional['wilayah'][] = $newItem;
            }
        }

        return $data;
    }
}

if (!function_exists('get_lhp_unit')) {
    function get_lhp_unit()
    {


        $query_reg_wil_est = get_reg_wil_est();
        $query_list_unit = get_list_unit();
        // $per_date = generate_dates();

        // $query_all_data = data_all_until_now();

        // $integrated_data = integrate_data_into_dates($per_date, $query_all_data);

        $data = [];


        foreach ($query_reg_wil_est as &$region) {
            foreach ($region['wilayah'] as &$wilayah) {
                foreach ($wilayah['estate'] as &$estate) {
                    if (isset($query_list_unit[$estate['est']])) {
                        $estate['data'] = $query_list_unit[$estate['est']];
                    } else {
                        $estate['data'] = []; // or any default value you want
                    }
                }
            }
        }

        $finalData = get_all_data_each_unit($query_reg_wil_est);

        return $finalData;
    }
}
if (!function_exists('get_all_data_each_unit')) {
    function get_all_data_each_unit($query_reg_wil_est)
    {

        $query = LaporanP2H::get()->toArray();

        // Transform the data
        $transformed = Collection::make($query)->map(function ($item) {
            // Check if the 'jenis_unit' contains parentheses
            if (preg_match('/\((.*?)\)/', $item['jenis_unit'], $matches)) {
                // Extract the value inside parentheses and convert to uppercase
                $item['jenis_unit_group'] = strtoupper($matches[1]);
            } else {
                // Convert the whole 'jenis_unit' value to uppercase
                $item['jenis_unit_group'] = strtoupper($item['jenis_unit']);
            }

            // Remove letters from 'kode_unit', keep only numbers
            $item['kode_unit_modified'] = preg_replace('/\D/', '', $item['kode_unit']);

            // Format 'tanggal_upload' to 'Y-m-d'
            $item['tanggal_upload_formatted'] = Carbon::parse($item['tanggal_upload'])->format('Y-m-d');

            return $item;
        });

        // Filter out items with null or empty 'aset_unit'
        $filtered = $transformed->filter(function ($item) {
            return !empty($item['aset_unit']);
        });

        // Group the data by 'jenis_unit_group', then 'aset_unit', then 'kode_unit_modified', and finally 'tanggal_upload_formatted'
        $grouped = $filtered->groupBy('jenis_unit_group')->map(function ($jenisUnitGroup) {
            return $jenisUnitGroup->groupBy('aset_unit')->map(function ($asetGroup) {
                return $asetGroup->groupBy('kode_unit_modified')->map(function ($kodeUnitGroup) {
                    return $kodeUnitGroup->groupBy('tanggal_upload_formatted')->map(function ($dateGroup) {
                        // Flatten the dateGroup to a simple array of data items

                        return $dateGroup[0];
                    });
                });
            });
        });

        // Optional: Convert the grouped data to array if needed
        $groupedArray = $grouped->toArray();

        foreach ($query_reg_wil_est as &$region) {
            foreach ($region['wilayah'] as &$wilayah) {
                foreach ($wilayah['estate'] as &$estate) {
                    $total = 0;
                    foreach ($estate['data'] as $key => &$per_unit) {

                        foreach ($per_unit as $key => &$value) {
                            $no_unit = preg_replace('/\D/', '', $value['no_unit']);

                            if (isset($groupedArray[$value['kode']][$value['est']][$no_unit])) {
                                // if ($value['est'] == 'SLE' && $value['kode'] == 'DT') {
                                //     $no_unit = preg_replace('/\D/', '', $value['no_unit']);
                                //     dd($no_unit);
                                // }

                                $value['data'] = $groupedArray[$value['kode']][$value['est']][$no_unit];
                                foreach ($value['data'] as $key => $datas) {
                                    // Decode the JSON string in 'kerusakan_unit'
                                    $kerusakanUnit = json_decode($datas['kerusakan_unit'], true);
                                    // Check if 'kerusakan_unit' is not empty
                                    if (!empty($kerusakanUnit)) {
                                        // Count the number of items in the 'kerusakan_unit' array
                                        $total += count($kerusakanUnit);
                                    }
                                }
                            } else {
                                $value['data'] = []; // or any default value you want
                            }
                        }
                    }
                    $estate['total'] = $total;
                }
            }
        }

        return $query_reg_wil_est;
    }
}

if (!function_exists('integrate_data_into_dates')) {
    function integrate_data_into_dates($per_date, $query_all_data)
    {

        $formatted_dates = [];

        // Loop through each month
        foreach ($per_date as $month => $dates) {
            $formatted_dates[$month] = [];

            // Loop through each date in the current month
            foreach ($dates as $date) {
                // Initialize an empty array for the current date
                $formatted_dates[$month][$date] = [];

                // Check if the date exists in $query_all_data
                if (isset($query_all_data[$date])) {
                    // Add the data to the formatted result for this date
                    $formatted_dates[$month][$date] = $query_all_data[$date];
                }
            }
        }

        // Debugging output
        return $formatted_dates;
    }
}
if (!function_exists('get_list_unit')) {
    function get_list_unit()
    {

        // Fetch the data
        $raw_query = KodeUnit::get()->toArray();

        // Normalize 'est' and 'kode' values, then group by 'est' and 'kode'
        $normalized_query = collect($raw_query)
            ->map(function ($item) {
                // Trim spaces from 'est' and 'kode' values
                $item['est'] = trim($item['est']);
                $item['kode'] = trim($item['kode']);
                $item['type'] = trim($item['type']);
                $item['tahun'] = trim($item['tahun']);
                $item['no_unit'] = trim($item['no_unit']);
                return $item;
            })
            ->groupBy('est')
            ->map(function ($estGroup) {
                // Within each 'est' group, group by 'kode'
                $groupedByKode = $estGroup->groupBy('kode')->toArray();
                // Sort the groups by 'kode'
                ksort($groupedByKode);
                return $groupedByKode;
            })
            ->sortKeys() // Optionally, sort groups by 'est' if needed
            ->toArray();

        // Output or use the $normalized_query as needed


        // Output or use the $normalized_query as needed

        return $normalized_query;
    }
}

if (!function_exists('generate_dates')) {
    function generate_dates()
    {
        $dates = [];
        $currentYear = date('Y'); // Get current year
        $currentMonth = date('m'); // Get current month in numeric format (01-12)

        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'Mei' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Agu' => '08',
            'Sep' => '09',
            'Okt' => '10',
            'Nov' => '11',
            'Des' => '12'
        ];

        foreach ($months as $shortMonth => $monthNumber) {
            // Only include months up to the current month
            if ($monthNumber > $currentMonth) {
                continue;
            }

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$monthNumber, $currentYear);

            $dates[$shortMonth] = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                // Format day to ensure two digits (e.g., '01', '02')
                $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
                $dateString = "{$currentYear}-{$monthNumber}-{$dayFormatted}";
                $dates[$shortMonth][$day] = $dateString; // Set index to start from 1
            }
        }

        return $dates;
    }
}

if (!function_exists('data_all_until_now')) {
    function data_all_until_now()
    {
        $query = LaporanP2H::all();

        // Group by formatted tanggal_upload
        $grouped = $query->groupBy(function ($item) {
            // Convert tanggal_upload to Carbon instance and format as Y-m-d
            return Carbon::parse($item->tanggal_upload)->format('Y-m-d');
        });

        $grouped = $grouped->toArray();

        return $grouped;
    }
}