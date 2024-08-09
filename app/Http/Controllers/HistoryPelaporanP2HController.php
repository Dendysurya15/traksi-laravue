<?php

namespace App\Http\Controllers;

use App\Models\LaporanP2H;
use App\Models\ListPertanyaan;
use App\Models\Pengguna;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        return Inertia::render('Dashboard', [
            'data' => $query,
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
        $isOption = $request->has('date');

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        // Parse and format the start date
        $formattedStartDate = $startDate ? Carbon::parse($startDate)->format('Y-m-d') : null;

        // Parse and format the end date
        $formattedEndDate = $endDate ? Carbon::parse($endDate)->format('Y-m-d') : null;

        $query = '';
        $xAxis = [];
        $yAxis = [];
        $groupOfXaxis = [];
        if ($isOption) {
            $selectOption = $request->query('date');
            switch ($selectOption) {
                case 'week':

                    $startOfWeek = Carbon::now()->startOfWeek();
                    $endOfWeek = Carbon::now()->endOfWeek()->addDay();

                    $query = LaporanP2H::whereBetween('tanggal_upload', [
                        $startOfWeek->format('Y-m-d'),
                        $endOfWeek->format('Y-m-d'),
                    ]);

                    for ($i = 0; $i < 7; $i++) {
                        $xAxis[] = $startOfWeek->locale('id')->isoFormat('D MMM');
                        $groupOfXaxis[] = $startOfWeek->format('Y-m-d');
                        $startOfWeek->addDay();
                    }
                    break;
                case 'month':

                    $startOfMonth = Carbon::now()->startOfMonth();
                    $endOfMonth = Carbon::now()->endOfMonth();

                    $query = LaporanP2H::whereMonth('tanggal_upload', Carbon::now()->month)
                        ->whereYear('tanggal_upload', Carbon::now()->year);

                    while ($startOfMonth->lte($endOfMonth)) {
                        $xAxis[] = $startOfMonth->locale('id')->isoFormat('D MMM');
                        $groupOfXaxis[] = $startOfMonth->format('Y-m-d');
                        $startOfMonth->addDay();
                    }
                    break;
                case 'year':
                    // Filter data for the current year

                    for ($month = 1; $month <= 12; $month++) {
                        $date = Carbon::create()->month($month);
                        $groupOfXaxis[] = $date->format('m');
                        $xAxis[] = $date->locale('id')->isoFormat('MMM'); // 'F' for full month name
                    }

                    $query = LaporanP2H::whereYear('tanggal_upload', Carbon::now()->year);
                    break;
            }
        } else {
            // If no option is provided, apply the date range filter
            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');

            if ($startDate && $endDate) {
                $formattedStartDate = Carbon::parse($startDate)->format('Y-m-d');
                $formattedEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');

                $query = LaporanP2H::whereBetween('tanggal_upload', [
                    $formattedStartDate,
                    $formattedEndDate,
                ]);
            } else {
                $query = LaporanP2H::get();
            }
        }


        //if jenis_unit filtered
        $query = $query->when($request->has('jenis_unit'), function ($query) use ($request) {
            $jenisUnit = strtolower($request->input('jenis_unit'));
            return $query->whereRaw('LOWER(jenis_unit) LIKE ?', ["%{$jenisUnit}%"]);
        });


        $groupedData = $query->get();
        if ($selectOption === 'year') {
            $groupedDataExist = $groupedData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_upload)->format('m'); // Group by month
            })->map(function ($group) {
                return $group->count();
            });
        } else {
            $groupedDataExist = $groupedData->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_upload)->format('Y-m-d');
            })->map(function ($group) {
                return $group->count();
            });
        }

        $groupedDataExist = $groupedDataExist->toArray();

        foreach ($groupOfXaxis as $key => $value) {

            if (array_key_exists($value, $groupedDataExist)) {
                $yAxis[] = $groupedDataExist[$value];
            } else {
                $yAxis[] = 0;
            }
        }





        return response()->json([
            'series' => [
                [
                    'name' => 'Laporan P2H',
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
