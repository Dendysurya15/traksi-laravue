<?php

namespace App\Http\Controllers;

use App\Models\LaporanP2H;
use App\Models\ListPertanyaan;
use App\Models\Pengguna;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class HistoryPelaporanP2HController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = LaporanP2H::paginate($request->input('paginate', 10));

        return Inertia::render('Dashboard', [
            'data' => $query,
        ]);
    }

    public function fetchDataTableP2H(Request $request)
    {
        $perPage = $request->input('paginate', 5);
        $query = LaporanP2H::paginate($perPage);

        // Decode kerusakan_unit and get listPertanyaan
        $listPertanyaan = ListPertanyaan::get()->keyBy('id')->toArray();

        foreach ($query as $laporan) {
            if (!empty($laporan->kerusakan_unit)) {
                $kerusakanUnit = json_decode($laporan->kerusakan_unit, true);

                // Initialize an array to hold the nama_pertanyaan
                $newKerusakanUnit = [];

                // Iterate over the decoded kerusakan_unit
                foreach ($kerusakanUnit as $key => $value) {
                    if (array_key_exists($key, $listPertanyaan)) {
                        // Get the nama_pertanyaan and append to the new array
                        $newKerusakanUnit[] = $listPertanyaan[$key]['nama_pertanyaan'];
                    }
                }

                // Convert the new array back to JSON
                $laporan->kerusakan_unit_part = json_encode($newKerusakanUnit);
            } else {
                $laporan->kerusakan_unit_part = '';
            }
        }

        // Return the paginated result with modified data
        return response()->json($query);
    }


    public function getListKerusakan($id)
    {

        $kerusakanUnit = LaporanP2H::find($id)->kerusakan_unit;

        $listPertanyaan = ListPertanyaan::get()->toArray();

        $jsonKerusakanUnit = json_decode($kerusakanUnit, true);


        $newResponse = [];

        // dd($jsonKerusakanUnit);
        $inc = 0;
        foreach ($jsonKerusakanUnit as $key => $value) {
            if (array_key_exists($key, $listPertanyaan)) {
                $newResponse[$inc]['title_kerusakan'] = $listPertanyaan[$key]['nama_pertanyaan'];
                $newResponse[$inc]['komentar'] = $value['komentar'];
                $newResponse[$inc]['foto'] = $value['foto'];
            }
            $inc++;
        }

        if (!$newResponse) {
            return response()->json(['error' => 'Error Fetching data'], 404);
        }

        return response()->json($newResponse);
    }
}
