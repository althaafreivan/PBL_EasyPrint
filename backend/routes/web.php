<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-health-check', function () {
    try{
        DB::connection()->getPdo();
        $dbStatus = 'Connected';
    }catch(\Exception $e){
        $dbStatus = 'Disconnected' . $e->getMessage();
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'Backend API Easyprint ready to use.',
        'database' => $dbStatus,
        'timestamp' => now(),
    ], 200);
});
