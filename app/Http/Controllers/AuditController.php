<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index()
    {
        return view('audit.index', [
            'logs' => AuditLog::latest('created_at')->limit(500)->get(),
        ]);
    }
}
