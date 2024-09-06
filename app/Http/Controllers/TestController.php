<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Psy\TabCompletion\Matcher\FunctionDefaultParametersMatcher;
use App\Models\Test;

class TestController extends Controller
{
    public function index() 
    {
        $values = Test::all();
        $count = Test::count();


        return view('tests.test', compact('values'));
    }
}
