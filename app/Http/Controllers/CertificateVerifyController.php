<?php

namespace App\Http\Controllers;

use App\Models\CrmEmployeeTraining;
use Illuminate\Http\Request;

class CertificateVerifyController extends Controller
{
    public function show(CrmEmployeeTraining $training)
    {
        $training->load('client');

        return view('certificate_verify', compact('training'));
    }
}
