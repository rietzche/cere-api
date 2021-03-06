<?php

namespace App\Http\Controllers\Services\Cereouts;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Carbon\Carbon;

class TryoutService extends Controller
{
    public function __construct()
    {
        $this->newTryout = new Tryout;
    }

    public function browse()
    {
        $today =  Carbon::now()->todatestring();
        return $this->newTryout->where('end_date','>=',$today)->get();
    }

    public function create(Array $req)
    {
        return $this->newTryout->create($req);
    }

    public function find($id)
    {
        return $this->newTryout->find($id);
    }

    public function update($id, Array $req)
    {
        $this->find($id)->update($req);
    }

    public function destroy($id)
    {
        $this->find($id)->delete();
    }
}
