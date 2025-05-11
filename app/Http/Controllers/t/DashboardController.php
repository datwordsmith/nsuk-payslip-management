<?php

namespace App\Http\Controllers\t;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        return view('trackr.dashboard');
    }
}
