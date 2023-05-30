<?php

namespace App\Http\Controllers;

use App\Models\Statusflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatusflowController extends Controller
{
    /**
     * * Displaying next status from status mapping
     */
    public function getNextStatus(Request $request, $current_status)
    {
        $model = new Statusflow();

        $result = $model->getNextStatus('DELIVERY', $current_status);
    
        return response()->json([
            'message' => 'Success',
            'data' => $result
        ]);
    }

    /**
     * * Check is allowed change status by status mapping
     */
    public function checkIsAllowedChangeStatus(Request $request)
    {
        /**
         * * Validation Request
         */
        $validate = Validator::make($request->all(), [
            'current_status' => 'nullable|string|max:100',
            'next_status' => 'nullable|string|max:100',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => "Error: unprocessable entity, validation error!",
                'errors' => $validate->errors(),
            ], 422);
        }

        $model = new Statusflow();

        $result = $model->isAllowedChangeStatus('DELIVERY', $request->current_status, $request->next_status);

        return response()->json([
            'message' => 'Success',
            'data' => $result
        ]);
    }
}
