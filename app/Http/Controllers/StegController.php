<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StegController extends Controller
{
    public function index(){
        return view('stegno.enc_message');
    }
    public function decrypt(){
        return view('stegno.dec_message');
    }
}
