<?php

namespace App\Http\Controllers;

use App\Models\JenisUnit;
use App\Models\LaporanP2H;
use App\Models\ListPertanyaan;
use App\Models\Pengguna;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HistoryPelaporanP2HController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = LaporanP2H::paginate($request->input('paginate', 10));
        $listPertanyaan = ListPertanyaan::get()->keyBy('id')->toArray();


        foreach ($query as $laporan) {
            if (!empty($laporan->kerusakan_unit)) {
                $kerusakanUnit = json_decode($laporan->kerusakan_unit, true);

                if (is_array($kerusakanUnit)) {
                    // Initialize an array to hold the nama_pertanyaan
                    $newKerusakanUnit = [];

                    // Iterate over the decoded kerusakan_unit
                    foreach ($kerusakanUnit as $key => $value) {
                        if (array_key_exists($key, $listPertanyaan)) {
                            $newKerusakanUnit[] = $listPertanyaan[$key]['nama_pertanyaan'];
                        }
                    }

                    // Convert the new array back to JSON
                    $laporan->kerusakan_unit_part = json_encode($newKerusakanUnit);
                } else {
                    $laporan->kerusakan_unit_part = '';
                }
            } else {
                $laporan->kerusakan_unit_part = '';
            }
        }

        $queryRegWilEst = get_lhp_unit();
        // Assuming you are working with the JenisUnit model
        $queryJenisUnit = JenisUnit::select(DB::raw("CONCAT(nama_unit, ' (', kode, ')') AS value"))
            ->pluck('value')
            ->map(function ($item) {
                return Str::title($item);
            });

        return Inertia::render('Dashboard', [
            'data' => $query,
            'jenisUnit' => $queryJenisUnit,
            'regWilEst' => $queryRegWilEst,
        ]);
    }

    public function fetchDataTableP2H(Request $request)
    {
        $perPage = $request->input('paginate', 10);
        $query = LaporanP2H::orderBy('tanggal_upload', 'desc')->paginate($perPage);
        // Decode kerusakan_unit and get listPertanyaan
        $listPertanyaan = ListPertanyaan::get()->keyBy('id')->toArray();


        foreach ($query as $laporan) {
            $kerusakanUnit = '';
            if (!empty($laporan->kerusakan_unit)) {

                $cleanedKerusakanUnit = str_replace(["\r\n", "\n", "\r"], '', $laporan->kerusakan_unit);

                $kerusakanUnit = json_decode($cleanedKerusakanUnit, true);

                if (is_array($kerusakanUnit)) {
                    // Initialize an array to hold the nama_pertanyaan

                    $newKerusakanUnit = [];
                    // Iterate over the decoded kerusakan_unit
                    foreach ($kerusakanUnit as $key => $value) {
                        if (array_key_exists($key, $listPertanyaan)) {
                            $newKerusakanUnit[] = $listPertanyaan[$key]['nama_pertanyaan'];
                        }
                    }

                    // Convert the new array back to JSON
                    $laporan->kerusakan_unit_part = json_encode($newKerusakanUnit);
                } else {
                    $laporan->kerusakan_unit_part = '';
                }
            } else {
                $laporan->kerusakan_unit_part = '';
            }
        }

        // Return the paginated result with modified data
        return response()->json($query);
    }


    public function fetchChartHistoryP2H(Request $request)
    {

        $query = LaporanP2H::query();

        $now = Carbon::now();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek()->addDay();


        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Apply date filter based on selected option
        $query->when($request->has('timeline'), function ($query) use ($request, $startOfWeek, $now, $startOfMonth, $endOfWeek, $endOfMonth) {
            $selectOption = $request->query('timeline');
            switch ($selectOption) {
                case 'Minggu':
                    $query->whereBetween('tanggal_upload', [
                        $startOfWeek->format('Y-m-d H:i:s'),
                        $endOfWeek->format('Y-m-d H:i:s'),
                    ]);

                    break;
                case 'Bulan':
                    $query->whereMonth('tanggal_upload', $now->month)
                        ->whereYear('tanggal_upload', $now->year);
                    break;
                case 'Tahun':
                    $query->whereYear('tanggal_upload', $now->year);
                    break;
            }
        });

        // Apply jenis_unit filter
        $query->when($request->has('jenis_unit'), function ($query) use ($request) {
            $input = strtolower(trim($request->input('jenis_unit')));

            preg_match('/\((\w{2})\)$/', $input, $matches);
            $jenisUnit = $matches[1] ?? '';

            $query->whereRaw('LOWER(jenis_unit) LIKE ?', ["%{$jenisUnit}%"]);
        });

        $query->when($request->has('startDate') && $request->has('endDate'), function ($query) use ($request) {
            $startDate = Carbon::parse($request->query('startDate'))->format('Y-m-d H:i:s');
            $endDate = Carbon::parse($request->query('endDate'))->addDay()->format('Y-m-d H:i:s');
            $query->whereBetween('tanggal_upload', [$startDate, $endDate]);
        });



        // Fetch grouped data
        $groupedData = $query->get();

        $groupOfXaxis = [];
        $xAxis = [];
        $yAxis = [];


        if ($request->query('timeline') === 'Tahun') {
            $groupedDataExist = $groupedData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_upload)->format('m'); // Group by month
            })->map(function ($group) {
                return $group->count();
            });

            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::create()->month($month);
                $groupOfXaxis[] = $date->format('m');
                $xAxis[] = $date->locale('id')->isoFormat('MMM');
            }
        } else if ($request->query('timeline') === 'Bulan') {
            $groupedDataExist = $groupedData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_upload)->format('Y-m-d');
            })->map(function ($group) {
                return $group->count();
            });
            while ($startOfMonth->lte($endOfMonth)) {
                $xAxis[] = $startOfMonth->locale('id')->isoFormat('D MMM');
                $groupOfXaxis[] = $startOfMonth->format('Y-m-d');
                $startOfMonth->addDay();
            }
        } else if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->query('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->query('endDate'))->endOfDay();

            $groupedDataExist = $groupedData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_upload)->format('Y-m-d');
            })->map(function ($group) {
                return $group->count();
            });

            while ($startDate->lte($endDate)) {
                $xAxis[] = $startDate->locale('id')->isoFormat('D MMM');
                $groupOfXaxis[] = $startDate->format('Y-m-d');
                $startDate->addDay();
            }
        } else {
            $groupedDataExist = $groupedData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_upload)->format('Y-m-d');
            })->map(function ($group) {
                return $group->count();
            });
            for ($i = 0; $i < 7; $i++) {
                $xAxis[] = $startOfWeek->locale('id')->isoFormat('D MMM');
                $groupOfXaxis[] = $startOfWeek->format('Y-m-d');
                $startOfWeek->addDay();
            }
        }

        $groupedDataExist = $groupedDataExist->toArray();

        // dd($groupOfXaxis, $groupedDataExist);
        foreach ($groupOfXaxis as $key => $value) {

            if (array_key_exists($value, $groupedDataExist)) {
                $yAxis[] = $groupedDataExist[$value];
            } else {
                $yAxis[] = 0;
            }
        }

        $title = $request->has('jenis_unit') ? 'Kerusakan ' . $request->query('jenis_unit') : 'Total Laporan Kerusakan';
        return response()->json([
            'series' => [
                [
                    'name' => $title,
                    'data' => $yAxis,
                ],
            ],
            'xAxis' => $xAxis,
        ]);
    }
    public function changeStatusFuKerusakan(Request $request)
    {
        $requestData = $request->all();


        // Find the LaporanP2H record by the idLaporan
        $laporan = LaporanP2H::find($requestData['idLaporan']);

        if ($laporan) {
            // Update the komentar field with the provided value

            $statusFollowUp = json_encode([
                'status' => $requestData['status'],
                'komentar' => $requestData['komentar'],
                'userFollowUp' => $requestData['userFollowUp'],
                'tanggal_submit' => $requestData['tanggal_submit'],
            ]);
            $laporan->status_follow_up = $statusFollowUp;
            $laporan->save();

            // Return a success response
            return response()->json([
                'message' => 'Laporan updated successfully',
                'data' => $laporan
            ], 200);
        } else {
            // Return an error response if the LaporanP2H record is not found
            return response()->json([
                'message' => 'Laporan not found'
            ], 404);
        }
    }


    public function getDetailLaporanP2h($id)
    {
        $laporan = LaporanP2H::find($id);
        return response()->json($laporan);
    }

    public function getListKerusakan($id)
    {

        $kerusakanUnit = LaporanP2H::find($id)->kerusakan_unit;

        $listPertanyaan = ListPertanyaan::get()->keyBy('id')->toArray();

        $jsonKerusakanUnit = json_decode($kerusakanUnit, true);


        $newResponse = [];

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
