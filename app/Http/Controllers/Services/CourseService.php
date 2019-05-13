<?php

namespace App\Http\Controllers\Services;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Course;

class CourseService extends Controller
{
    public function __construct()
    {
        $this->newCourse = new Course;
    }

    public function browse()
    {
        return $this->newCourse->all();
    }

    public function create(Array $req)
    {
        return $this->newCourse->create($req);
    }

    public function find($id)
    {
        return $this->newCourse->find($id);
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
