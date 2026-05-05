<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        return response()->json($devices);
    }

    // public function indexByType(Request $request)
    // {
    //     $priorityOrder = [
    //         'Desktop',
    //         'Laptop',
    //         'Printer',
    //         'Scanner',
    //         'Monitor',
    //         'UPS',
    //         'Speaker',
    //         'Keyboard',
    //         'Mouse',
    //         'Other',
    //     ];

    //     $search = $request->search;

    //     $query = Device::with('employee');

    //     // 🔍 Apply search
    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //             ->orWhere('mr_employee_name', 'like', "%{$search}%")
    //             ->orWhere('user_employee_name', 'like', "%{$search}%");
    //             // ->orWhereHas('employee', function ($q2) use ($search) {
    //             //     $q2->where('firstname', 'like', "%{$search}%")
    //             //         ->orWhere('lastname', 'like', "%{$search}%");
    //             // });
    //         });
    //     }

    //     $devices = $query->get()
    //         ->groupBy('type')
    //         ->map(function ($items, $type) {
    //             return [
    //                 'type' => $type,
    //                 'devices' => $items->values()
    //             ];
    //         })
    //         ->sortBy(function ($item) use ($priorityOrder) {
    //             $index = array_search($item['type'], $priorityOrder);
    //             return $index === false ? 999 : $index;
    //         })
    //         ->values();

    //     return response()->json($devices);
    // }

    public function indexByType(Request $request)
    {
        $priorityOrder = [
            'Desktop',
            'Laptop',
            'Printer',
            'Scanner',
            'Monitor',
            'UPS',
            'Speaker',
            'Keyboard',
            'Mouse',
        ];

        $search = $request->search;

        $query = Device::with(['owner', 'user']);

        // 🔍 Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('mr_employee_name', 'like', "%{$search}%")
                ->orWhere('user_employee_name', 'like', "%{$search}%");
            });
        }

        $collection = $query->get();

        // 👇 Custom grouping logic
        $grouped = $collection->groupBy(function ($item) {
            if ($item->type === 'Other') {
                return $item->custom_type ?: 'Other'; // fallback if empty
            }
            return $item->type;
        });

        // ✅ Known types
        $known = collect($priorityOrder)
            ->filter(fn ($type) => $grouped->has($type))
            ->map(function ($type) use ($grouped) {
                return [
                    'type' => $type,
                    'devices' => $grouped[$type]->values(),
                ];
            });

        // ✅ Custom types (from "Other")
        $custom = $grouped
            ->reject(function ($items, $type) use ($priorityOrder) {
                return in_array($type, $priorityOrder);
            })
            ->sortKeys() // alphabetical
            ->map(function ($items, $type) {
                return [
                    'type' => $type,
                    'devices' => $items->values(),
                ];
            })
            ->values();

        $devices = $known->concat($custom)->values();

        return response()->json($devices);
    }

    // ✅ Store new device
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mr_employeeid' => 'nullable|integer',
            'mr_employee_name' => 'nullable|string',
            'user_employeeid' => 'nullable|integer',
            'user_employee_name' => 'nullable|string',
            'purchased_at' => 'nullable|string',
            'name' => 'nullable|string|unique:devices,name',
            'type' => 'required|string',
            'custom_type' => 'nullable|required_if:type,Other|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'status' => 'nullable|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',

            'processor' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'ram' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'system_type' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'operating_system' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'storage' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'mac_address' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
        ]);

        $user = $request->user();

        $device = Device::create([
            ...$validated,
            "created_by_employeeid" => $user->employeeid,
        ]);

        return response()->json([
            'message' => 'Device created successfully',
            'data' => $device
        ], 201);
    }

    // ✅ Show single device
    public function show($id)
    {
        $device = Device::with(['owner', 'user', 'histories'])->find($id);

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        return response()->json($device);
    }

    // ✅ Update device
    public function update(Request $request, $id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $validated = $request->validate([
            'mr_employeeid' => 'nullable|integer',
            'mr_employee_name' => 'nullable|string',
            'user_employeeid' => 'nullable|integer',
            'user_employee_name' => 'nullable|string',
            'purchased_at' => 'nullable|string',
            'name' => 'nullable|string|unique:devices,name,' . $id,
            'type' => 'required|string',
            'custom_type' => 'nullable|required_if:type,Other|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'status' => 'nullable|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',

            'processor' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'ram' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'system_type' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'operating_system' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'storage' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
            'mac_address' => 'required_if:type,Desktop|required_if:type,Laptop|nullable',
        ]);

        $user = $request->user();

        $device->update([
            ...$validated,
            "updated_by_employeeid" => $user->employeeid,
        ]);

        return response()->json([
            'message' => 'Device updated successfully',
            'data' => $device
        ]);
    }

    // ✅ Delete device
    public function destroy($id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $device->delete();

        return response()->json([
            'message' => 'Device deleted successfully'
        ]);
    }
}
