<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SpaController extends Controller
{
    public function index()
    {
        return file_get_contents(public_path() . '/index.html');
    }
}
