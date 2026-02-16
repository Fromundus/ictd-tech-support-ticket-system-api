<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Services\BroadcastEventService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tickets = [];
        $users = [];

        if ($user->role === "admin") {
            // If admin, allow filtering by user_id (optional)
            $users = User::where('role', 'user')->get();
            $query = Ticket::query()->orderBy('created_at', 'desc');

            if ($request->has('user_id') && !empty($request->user_id) && $request->user_id != "all") {
                $query->where('user_id', $request->user_id);
            }

            $tickets = $query->get();
        } 
        // else if ($user->role === "user") {
        //     // Normal user only sees their own tickets
        //     $tickets = Ticket::where('user_id', $user->id)
        //         ->orderBy('created_at', 'desc')
        //         ->get();
        // }

        else if ($user->role === "user") {
            $tickets = Ticket::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($q) {
                        $q->whereNull('user_id')
                            ->whereNull('tech_employeeid')
                            ->whereNull('it_tech_name');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        }

        return response()->json([
            "tickets" => $tickets,
            "users" => $users,
        ]);
    }

    public function show(Request $request, $uid)
    {
        $tickets = Ticket::where('uid', $uid)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            "tickets" => $tickets,
        ]);
    }

    public function count(Request $request)
    {
        $new = Ticket::where('status', 'New')->whereDate('created_at', now())->count();
        $pending = Ticket::where('status', 'Pending')->whereDate('created_at', now())->count();
        $inProgress = Ticket::where('status', 'In Progress')->whereDate('created_at', now())->count();
        $resolved = Ticket::where('status', 'Resolved')->whereDate('created_at', now())->count();

        return response()->json([
            "new" => $new,
            "pending" => $pending,
            "inProgress" => $inProgress,
            "resolved" => $resolved,
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_id'   => 'required',
            'uid' => 'nullable|string',
            'topic'         => 'required|string|max:255',
            'description'   => 'required|string',
            // 'it_tech_name'  => 'nullable|string|max:255',
            'status'        => ['nullable', Rule::in(['New','Pending','In Progress','Resolved'])],
            'date'          => 'required',
        ]);

        $user = $request->user();

        $ticket = Ticket::create([
            'user_id' => $user->id ?? null,
            'employee_name' => $data['employee_name'],
            'requested_by_employeeid' => $data['employee_id'] ?? null,
            'uid' => $data['uid'] ?? null,
            'topic'         => $data['topic'],
            'description'   => $data['description'],
            'it_tech_name'  => $user->name ?? null,
            'tech_employeeid'  => $user->employeeid ?? null,
            'status'        => $data['status'] ?? 'New',
            'date'          => $data["date"],
        ]);

        $factory = (new Factory)
        ->withServiceAccount(storage_path('app/firebase.json'));
        $messaging = $factory->createMessaging();

        // Send notification to all active tokens
        $userTokens = User::where('role', 'user')
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        Log::info($userTokens);

        foreach ($userTokens as $key => $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create(
                        'New Ticket Created',
                        'A new support ticket was submitted.'
                    ));

                $messaging->send($message);
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                // If token is invalid, remove it from DB
                if (str_contains($e->getMessage(), 'NotRegistered') || str_contains($e->getMessage(), 'InvalidArgument')) {
                    User::where('fcm_token', $token)->update(['fcm_token' => null]);
                    unset($userTokens[$key]);
                }
                Log::error('FCM send failed: '.$e->getMessage());
            } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
                Log::error('Firebase error: '.$e->getMessage());
            }
        }


        BroadcastEventService::signal('tickets');

        return response()->json($ticket, 201);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_id'   => 'required',
            'uid' => 'nullable|string',
            'topic'         => 'required|string|max:255',
            'description'   => 'required|string',
            // 'it_tech_name'  => 'nullable|string|max:255',
            'status'        => ['nullable', Rule::in(['New','Pending','In Progress','Resolved'])],
            'date'          => 'required',
        ]);

        $user = $request->user();

        $ticket = Ticket::create([
            'user_id' => $user->id ?? null,
            'employee_name' => $data['employee_name'],
            'requested_by_employeeid' => $data['employee_id'] ?? null,
            'uid' => $data['uid'] ?? null,
            'topic'         => $data['topic'],
            'description'   => $data['description'],
            'it_tech_name'  => $user->name ?? null,
            'tech_employeeid'  => $user->employeeid ?? null,
            'status'        => $data['status'] ?? 'New',
            'date'          => $data["date"],
        ]);

        $factory = (new Factory)
        ->withServiceAccount(storage_path('app/firebase.json'));
        $messaging = $factory->createMessaging();

        // Send notification to all active tokens
        $userTokens = User::where('role', 'user')
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        Log::info($userTokens);

        foreach ($userTokens as $key => $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create(
                        'New Ticket Created',
                        'A new support ticket was submitted.'
                    ));

                $messaging->send($message);
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                // If token is invalid, remove it from DB
                if (str_contains($e->getMessage(), 'NotRegistered') || str_contains($e->getMessage(), 'InvalidArgument')) {
                    User::where('fcm_token', $token)->update(['fcm_token' => null]);
                    unset($userTokens[$key]);
                }
                Log::error('FCM send failed: '.$e->getMessage());
            } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
                Log::error('Firebase error: '.$e->getMessage());
            }
        }


        BroadcastEventService::signal('tickets');

        return response()->json($ticket, 201);
    }

    public function updateDetails(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            // 'employee_name' => 'sometimes|required|string|max:255',
            // 'date'          => 'sometimes|required|date',
            // 'topic'         => 'sometimes|required|string|max:255',
            // 'description'   => 'sometimes|required|string',
            'it_tech_name'  => 'nullable|string|max:255',
            'tech_employeeid'  => 'nullable',
            'user_id'  => 'nullable',
        ]);

        $ticket->update($data);

        BroadcastEventService::signal('tickets');

        return response()->json($ticket);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['New','Pending','In Progress','Resolved'])],
        ]);

        $status = $data['status'];

        $ticket->update([
            'status' => $status,
            'date_resolved' => $status === 'Resolved' ? now() : null,
        ]);

        BroadcastEventService::signal('tickets');

        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        BroadcastEventService::signal('tickets');

        return response()->json([
            'message' => 'Ticket deleted successfully.'
        ], 200);
    }
}
