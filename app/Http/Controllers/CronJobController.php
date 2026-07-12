<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class CronJobController extends Controller
{
    public function run(Request $request)
    {
        ini_set('max_execution_time', 0);

        $cronJobKey = config('alma.cronjob.key');

        if ($cronJobKey == '') {
            return response()->json([
                'status' => 'error',
                'message' => __('Cron Job Key is not set'),
            ], 403);
        }

        if (isset($cronJobKey)) {
            $validator = Validator::make($request->all(), [
                'key' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $error,
                    ], 403);
                }
            }

            if ($cronJobKey != $request->key) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('Invalid Cron Job Key'),
                ], 403);
            }
        }

        Artisan::call('schedule:run');

        Config::write('alma.cronjob.last_execution', Carbon::now()->toDateTimeString());

        return response()->json([
            'status' => 'success',
            'message' => __('Cron Job executed successfully'),
        ], 200);
    }
}
