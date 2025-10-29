<?php

namespace App\Http\Controllers;

use App\Models\Tanque;
use Illuminate\Http\Request;

class TanqueController extends Controller{
    function index(){
        return Tanque::all();
    }
}
