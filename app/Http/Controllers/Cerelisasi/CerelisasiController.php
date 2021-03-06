<?php

namespace App\Http\Controllers\Cerelisasi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cerelisasi;
use App\Models\Department;
use App\Models\GeneralInformation;
use App\User;

class CerelisasiController extends Controller
{
    public function analysis(Request $req)
    {
        $price = GeneralInformation::first()->cerelisasi_price;
        $price_total = count($req->departments) * $price;

        if ($this->isUserHasCerelisasi($req)) {
            if($this->useBalance($req, $price_total)) {
                $this->clearAnalyticsData($req);
                $this->createUserInfo($req);

                return $this->analyticsResult($req);
            }
            else {
                return response()->json([
                    'status' => false,
                    'message' => 'Saldo tidak mencukupi',
                ]);
            }
        }
        else {
            if (count($req->departments) <= 3){
                $this->clearAnalyticsData($req);
                $this->createUserInfo($req);

                return $this->analyticsResult($req);
            }
            else {
                $first_price = (count($req->departments) - 3) * $price;
                if($this->useBalance($req, $first_price)) {
                    $this->clearAnalyticsData($req);
                    $this->createUserInfo($req);
    
                    return $this->analyticsResult($req);
                }
                else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Saldo tidak mencukupi',
                    ]);
                }
            }
        }
    }

    public function resetAnalytics(Request $req)
    {
        $this->clearAnalyticsData($req);

        return response()->json([
            'status' => true,
            'message' => 'Sukses reset data',
        ]);
    }

    public function analyticsData(Request $req)
    {
        $department_ranks = [];
        $countables = Cerelisasi::where('user_id', $req->user()->id)->get();
        $type_data = Cerelisasi::where('user_id', $req->user()->id)->first();
        $type = $type_data ? $type_data->type : -1;
        if ($type !== -1) {
            $national_max_value = Cerelisasi::where('type', $type)->max('total_point');
            foreach ($countables as $key => $countable) {
                $department = Department::find($countable->department_id);
                $passing_grade = $department->passing_grade;
                $average_point = Cerelisasi::where('department_id', $countable->department_id)->where('type', $type)->avg('total_point');
                $maximum_value = Cerelisasi::where('department_id', $countable->department_id)->where('type', $type)->max('total_point');
                $surveyor_count = Cerelisasi::where('department_id', $countable->department_id)->where('type', $type)->count();
                array_push($department_ranks, [
                        'department' => [
                            'id' => $department->id,
                            'name' => $department->name,
                            'interrested_num' => $department->interrested_num,
                            'capacity' => $department->capacity,
                            'passing_grade' => $passing_grade,
                            'average_point' => round(($average_point/$maximum_value)*100),
                            'maximum_value' => $maximum_value,
                            'tightness' => round($department->interrested_num/$department->capacity),
                        ],
                        'accuracy' => ($surveyor_count >= $department->interrested_num ? 90 : round($surveyor_count/$department->interrested_num)),
                        'ranks' => $this->getDepartmentRanking($req, $department->id),
                        'status' => $countable->status,
                        'my_department_point' => round(($countable->total_point/$maximum_value)*100)
                    ]);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'type' => $type,
                    'national_ranks' => $this->getNationalRanking($req),
                    'department_ranks' => $department_ranks,
                    'my_point' => $countables->first() ? round(($countables->first()->total_point/$national_max_value)*100) : 0,
                ]
            ]);
        }
        else {
            return response()->json([
                'status' => true,
                'data' => null
            ]);
        }
    }

    public function analyticsResult($req)
    {
        $department_ranks = [];
        $countables = Cerelisasi::where('user_id', $req->user()->id)->get();
        $type = Cerelisasi::where('user_id', $req->user()->id)->first()->type;
        $national_max_value = Cerelisasi::where('type', $type)->max('total_point');
        foreach ($countables as $key => $countable) {
            $department = Department::find($countable->department_id);
            $passing_grade = $department->passing_grade;
            $average_point = Cerelisasi::where('type', $type)->where('department_id', $countable->department_id)->avg('total_point');
            $maximum_value = Cerelisasi::where('type', $type)->where('department_id', $countable->department_id)->max('total_point');
            $surveyor_count = Cerelisasi::where('type', $type)->where('department_id', $countable->department_id)->count();
            if ($countable->total_point < $passing_grade) {
                $countable->update(['status' => 'rendah']);
            }
            else if ($countable->total_point > $passing_grade && $countable->total_point < $average_point) {
                $countable->update(['status' => 'sedang']);
            }
            else {
                $countable->update(['status' => 'tinggi']);
            }

            array_push($department_ranks, [
                    'department' => [
                        'id' => $department->id,
                        'name' => $department->name,
                        'interrested_num' => $department->interrested_num,
                        'capacity' => $department->capacity,
                        'passing_grade' => $passing_grade,
                        'average_point' => round($average_point),
                        'maximum_value' => $maximum_value,
                        'tightness' => round($department->interrested_num/$department->capacity),
                    ],
                    'accuracy' => ($surveyor_count >= $department->interrested_num ? 90 : round($surveyor_count/$department->interrested_num)),
                    'ranks' => $this->getDepartmentRanking($req, $department->id),
                    'status' => $countable->status,
                    'my_department_point' => round(($countable->total_point/$maximum_value)*100)
                ]);
        }
        return response()->json([
            'status' => true,
            'data' => [
                'type' => $type,
                'national_ranks' => $this->getNationalRanking($req),
                'department_ranks' => $department_ranks,
                'my_point' => $countables->first() ? round(($countables->first()->total_point/$national_max_value)*100) : 0,
            ]
        ]);
    }

    public function getNationalRanking($req)
    {
        $i = 1;
        $my_rank = 0;
        $other_ranks = [];
        $type = Cerelisasi::where('user_id', $req->user()->id)->first()->type;
        $rankings = Cerelisasi::where('type', $type)->groupBy('user_id')->orderBy('total_point', 'desc')->get();
        $maximum_value = Cerelisasi::where('type', $type)->max('total_point');

        foreach ($rankings as $key => $ranking) {
            if ($ranking->user_id == $req->user()->id) {
                $my_rank = $i;
                if ($my_rank > 5) {
                    $j = $my_rank-5;
                    $array_ranks = Cerelisasi::where('type', $type)->groupBy('user_id')->orderBy('total_point', 'desc')->skip($my_rank-6)->take(11)->get();
                    foreach ($array_ranks as $key => $array_rank) {
                        array_push($other_ranks, [
                            'rank' => $j,
                            'total_point' => $array_rank->total_point ? round(($array_rank->total_point/$maximum_value)*100) : 0
                        ]);
                        $j++;
                    }
                }
                else {
                    $j = 1;
                    $array_ranks = Cerelisasi::where('type', $type)->groupBy('user_id')->orderBy('total_point', 'desc')->take($my_rank+5)->get();
                    foreach ($array_ranks as $key => $array_rank) {
                        array_push($other_ranks, [
                            'rank' => $j,
                            'total_point' => $array_rank->total_point ? round(($array_rank->total_point/$maximum_value)*100) : 0
                        ]);
                        $j++;
                    }
                }
            }
            $i++;
        }

        return [
            'total_students' => $rankings->count(),
            'my_rank' => $my_rank,
            'other_ranks' => $other_ranks,
        ];
    }

    public function getDepartmentRanking($req, $department_id)
    {
        $i = 1;
        $my_rank = 0;
        $other_ranks = [];
        $graph = [];
        $type = Cerelisasi::where('user_id', $req->user()->id)->first()->type;
        $rankings = Cerelisasi::where('type', $type)->where('department_id', $department_id)->groupBy('user_id')->orderBy('total_point', 'desc')->get();
        $maximum_value = Cerelisasi::where('type', $type)->where('department_id', $department_id)->max('total_point');

        foreach ($rankings as $key => $ranking) {
            if ($ranking->user_id == $req->user()->id) {
                $my_rank = $i;
                if ($my_rank > 5) {
                    $j = $my_rank-5;
                    $array_ranks = Cerelisasi::where('type', $type)->where('department_id', $department_id)->groupBy('user_id')->orderBy('total_point', 'desc')->skip($my_rank-6)->take(11)->get();
                    foreach ($array_ranks as $key => $array_rank) {
                        array_push($other_ranks, [
                            'rank' => $j,
                            'total_point' => $array_rank->total_point ? round(($array_rank->total_point/$maximum_value)*100) : 0
                        ]);
                        $j++;
                    }
                }
                else {
                    $j = 1;
                    $array_ranks = Cerelisasi::where('department_id', $department_id)->groupBy('user_id')->orderBy('total_point', 'desc')->take($my_rank+5)->get();
                    foreach ($array_ranks as $key => $array_rank) {
                        array_push($other_ranks, [
                            'rank' => $j,
                            'total_point' => $array_rank->total_point ? round(($array_rank->total_point/$maximum_value)*100) : 0
                        ]);
                        $j++;
                    }
                }
            }
            if (isset($graph[$i-1]['point']) == round(($ranking->total_point/$maximum_value)*100)) {
                $graph[$i-1]['students_count'] += 1;
            }
            else {
                array_push($graph, [
                    'students_count' => 1,
                    'point' => round(($ranking->total_point/$maximum_value)*100),
                ]);
            }
            $i++;
        }

        return [
            'total_students' => $rankings->count(),
            'my_rank' => $my_rank,
            'other_ranks' => $other_ranks,
        ];
    }

    public function isFoundData($req)
    {
        if (!is_null(Cerelisasi::where('user_id', $req->user()->id)->get())) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isUserHasCerelisasi($req)
    {
        $res = User::find($req->user()->id);
        if ($res->cerelisasi_status == 1) {
            return true;
        }
        else {
            $res->cerelisasi_status = 1;
            $res->save();

            return false;
        }
    }

    public function useBalance($req, $price)
    {
        $res = User::find($req->user()->id);
        if ($res->balance < $price) {
            return false;
        }
        else {
            $res->update([
                'balance' => $res->balance - $price,
            ]);
    
            return true;
        }
    }

    public function clearAnalyticsData($req)
    {
        if ($this->isFoundData($req)) {
            $cerelisasi = Cerelisasi::where('user_id', $req->user()->id)->delete();
            return true;
        }
    }

    public function createUserInfo($req)
    {
        $total_point = array_sum($req->points);
        foreach ($req->departments as $key => $department) {
            Cerelisasi::create([
                'user_id' => $req->user()->id,
                'department_id' => $department,
                'total_point' => $total_point,
                'type' => $req->type,
            ]);
        }

        return true;
    }
}
