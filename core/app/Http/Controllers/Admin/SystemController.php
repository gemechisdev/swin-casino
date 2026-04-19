<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateLog;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function systemInfo()
    {
        $laravelVersion = app()->version();
        $timeZone       = config('app.timezone');
        $pageTitle      = 'Application Information';
        return view('admin.system.info', compact('pageTitle', 'laravelVersion', 'timeZone'));
    }

    public function optimize()
    {
        $pageTitle = 'Clear System Cache';
        return view('admin.system.optimize', compact('pageTitle'));
    }

    public function optimizeClear()
    {
        Artisan::call('optimize:clear');
        $notify[] = ['success', 'Cache cleared successfully'];
        return back()->withNotify($notify);
    }

    public function systemServerInfo()
    {
        $currentPHP    = phpversion();
        $pageTitle     = 'Server Information';
        $serverDetails = $_SERVER;
        return view('admin.system.server', compact('pageTitle', 'currentPHP', 'serverDetails'));
    }

    public function systemUpdate()
    {
        $pageTitle = 'System Updates';
        return view('admin.system.update', compact('pageTitle'));
    }

    public function systemUpdateProcess()
    {
        return response()->json([
            'status'  => 'info',
            'message' => [
                'Automatic updates are disabled in this build. Please apply updates manually.',
            ],
        ]);
    }

    public function systemUpdateLog()
    {
        $pageTitle = 'System Update Log';
        $updates   = UpdateLog::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.system.update_log', compact('pageTitle', 'updates'));
    }
}
