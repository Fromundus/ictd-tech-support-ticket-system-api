<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceHistory;
use Illuminate\Http\Request;

class DeviceHistoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|integer',
            'date_requested' => 'required|string',
            'issue' => 'required|string',
            'action' => 'required|string',
            'date_performed' => 'required|string',
            'performed_by_employeeid' => 'required|integer',
            'performed_by_name' => 'required|string',
            'remarks' => 'required|string',
        ]);

        $device = DeviceHistory::create($validated);

        return response()->json([
            'message' => 'Device history created successfully',
            'data' => $device
        ], 201);
    }

    // ✅ Update device
    public function update(Request $request, $id)
    {
        $device = DeviceHistory::find($id);

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $validated = $request->validate([
            'device_id' => 'required|integer',
            'date_requested' => 'required|string',
            'issue' => 'required|string',
            'action' => 'required|string',
            'date_performed' => 'required|string',
            'performed_by_employeeid' => 'required|integer',
            'performed_by_name' => 'required|string',
            'remarks' => 'required|string',
        ]);

        $device->update($validated);

        return response()->json([
            'message' => 'Device history updated successfully',
            'data' => $device
        ]);
    }
}
