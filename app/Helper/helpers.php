<?php

use App\Models\KodeUnit;
use App\Models\Regional;
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

        $data = [];
        foreach ($query_reg_wil_est as &$region) {
            foreach ($region['wilayah'] as &$wilayah) {
                foreach ($wilayah['estate'] as &$estate) {
                    // Normalize the 'est' value from the estate

                    // Check if the 'est' from $estate exists in the normalized query list
                    if (isset($query_list_unit[$estate['est']])) {
                        // Append the 'data' key with the value from $normalized_query
                        $estate['data'] = $query_list_unit[$estate['est']];
                    } else {
                        // Optionally handle cases where the 'est' value is not found
                        $estate['data'] = []; // or any default value you want
                    }
                }
            }
        }


        return $query_reg_wil_est;
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
