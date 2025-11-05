<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnfermeroController extends Controller
{
    public function index()
{
    return view('enfermeria.dashboard'); // 👈 debe decir "enfermeria", NO "enfermero"
}

}
