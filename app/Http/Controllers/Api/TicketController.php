<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_id'   => 'required',
            'uid' => 'nullable|string',
            'topic'         => 'required|string|max:255',
            'description'   => 'required|string',
            'it_tech_name'  => 'nullable|string|max:255',
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
            'status'        => $data['status'] ?? 'New',
            'date'          => $data["date"],
        ]);

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

        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json([
            'message' => 'Ticket deleted successfully.'
        ], 200);
    }


}
