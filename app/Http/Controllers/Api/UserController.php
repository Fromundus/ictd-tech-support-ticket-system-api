<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Laravel\Firebase\Facades\Firebase;

class UserController extends Controller
{
    public function all(){
        $users = User::all();

        return response()->json($users);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $role = $request->query('role');
        $status = $request->query('status'); // "active" or "inactive"

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive')->orWhereNull('status');
            }
        }

        $users = $query->where("role", "bns")->orderBy('id', 'desc')->paginate($perPage);

        $roleCounts = [
            'total'      => User::count(),
            'superadmin' => User::where('role', 'superadmin')->count(),
            'admin'      => User::where('role', 'admin')->count(),
            'driver'       => User::where('role', 'driver')->count(),
        ];

        return response()->json([
            'users' => $users,
            'counts' => $roleCounts,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:11', 'min:11'],
            'area' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'area' => $request->area,
            'notes' => $request->notes,
            'password' => Hash::make(1234),
            'email' => $request->email,
            'email_verified_at' => Carbon::now(),
            'role' => $request->role,
        ]);

        return response()->noContent();
    }

    public function show($id)
    {
        return User::with("activityLogs")->findOrFail($id);
    }

    public function updateRole(Request $request){
        $validated = $request->validate([
            'ids' => 'required|array',
            'role' => 'required|string',
        ]);

        DB::table('users')
            ->whereIn('id', $validated['ids'])
            ->update(['role' => $validated['role']]);

        $users = User::whereIn('id', $validated['ids'])->get();

        // foreach($users as $user){            
        //     ActivityLogger::log('update', 'account', "Updated account: #" . $user->id . " " . $user->name . " (changed role to " . $request->role . ")");
        // }

        return response()->json(['message' => 'Roles updated successfully']);
    }

    public function updateStatus(Request $request){
        $validated = $request->validate([
            'ids' => 'required|array',
            'status' => 'required|string',
        ]);

        DB::table('users')
            ->whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        $users = User::whereIn('id', $validated['ids'])->get();

        // foreach($users as $user){            
        //     ActivityLogger::log('update', 'account', "Updated account: #" . $user->id . " " . $user->name . " (changed role to " . $request->role . ")");
        // }

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function changePassword(Request $request, $id){
        $user = User::where("id", $id)->first();

        if($user){
            $validator = Validator::make($request->all(), [
                "password" => "required|confirmed|string|min:6"
            ]);

            if($validator->fails()){
                return response()->json([
                    "status" => "422",
                    "message" => $validator->errors()
                ], 422);
            } else {
                $user->update([
                    "password" => Hash::make($request->password)
                ]);

                if($user){                    
                    return response()->json([
                        "status" => "200",
                        "message" => "Password Updated Successfully"
                    ], 200);
                } else {
                    return response()->json([
                        "status" => "500",
                        "message" => "Something Went Wrong"
                    ]);
                }
            }
        } else {
            return response()->json([
                "status" => "404",
                "message" => "User Not Found"
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::where("id", $id)->first();

        if ($user) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|min:3|max:50|unique:users,name,' . $user->id ,
                'name' => 'required|string',
                'role' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => $validator->errors()
                ], 422);
            } else {
                $user->update([
                    "username" => $request->username,
                    "name" => $request->name,
                    "role" => $request->role,
                    "email" => $request->email,
                ]);

                return response()->json([
                    "status" => "200",
                    "message" => "Account Updated Successfully",
                    // "user" => $user,
                ], 200);
            }
        } else {
            return response()->json([
                "status" => "404",
                "message" => "User not found"
            ], 404);
        }
    }

    public function resetPasswordDefault(Request $request){
        $request->validate([
            'id' => 'required',
        ]);

        $user = User::findOrFail($request->id);

        $user->update([
            "password" => Hash::make(1234),
        ]);

        // ActivityLogger::log('reset', 'auth', "Reset the password for account: #" . $user->id . " " . $user->name);

        return response()->json(["message" => "Password Reset Success"], 200);
    }

    public function delete(Request $request){
        $validated = $request->validate([
            'ids' => 'required|array',
        ]);

        $users = User::whereIn('id', $validated['ids'])->get();

        User::whereIn('id', $validated['ids'])->delete();

        // foreach($users as $user){
        //     ActivityLogger::log('delete', 'account', "Deleted account: #" . $user->id . " " . $user->name);
        // }

        return response()->json(['message' => 'Users deleted successfully']);
    }

    // public function fcmToken(Request $request){
    //     $user = $request->user();

    //     $user->update([
    //         'fcm_token' => $request->token
    //     ]);

    //     // Subscribe only if role is user
    //     if ($user->role === 'user') {

    //     }

    //     return response()->json(['success' => true]);
    // }

    // public function fcmToken(Request $request)
    // {
    //     $user = $request->user();

    //     // Save the token
    //     $user->update([
    //         'fcm_token' => $request->token
    //     ]);

    //     // Subscribe to topic 'users' if role is 'user'
    //     if ($user->role === 'user' && $user->fcm_token) {
    //         $factory = (new Factory)
    //             ->withServiceAccount(storage_path('app/firebase.json'));
    //         $messaging = $factory->createMessaging();

    //         try {
    //             $messaging->subscribeToTopic('users', [$user->fcm_token]);
    //         } catch (\Kreait\Firebase\Exception\MessagingException $e) {
    //             Log::error('FCM subscribe failed: '.$e->getMessage());
    //         }
    //     }

    //     return response()->json(['success' => true]);
    // }

    public function fcmToken(Request $request)
    {
        $user = $request->user();

        // Update or save the token
        $token = $request->token;
        $user->update(['fcm_token' => $token]);

        if ($user->role === 'user' && $token) {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase.json'));
            $messaging = $factory->createMessaging();

            try {
                // Subscribe user token to 'users' topic
                $messaging->subscribeToTopic('users', [$token]);
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                Log::error('FCM subscribe failed: ' . $e->getMessage());
            } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
                Log::error('Firebase error: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }
}
