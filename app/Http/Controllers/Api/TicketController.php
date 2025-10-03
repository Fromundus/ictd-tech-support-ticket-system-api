<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{

    public function index()
    {
        $tickets = Ticket::orderBy('created_at', 'desc')->get();
        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'topic'         => 'required|string|max:255',
            'description'   => 'required|string',
            'it_tech_name'  => 'nullable|string|max:255',
            'status'        => ['nullable', Rule::in(['New','Pending','In Progress','Resolved'])],
        ]);

        $ticket = Ticket::create([
            'employee_name' => $data['employee_name'],
            'topic'         => $data['topic'],
            'description'   => $data['description'],
            'it_tech_name'  => $data['it_tech_name'] ?? null,
            'status'        => $data['status'] ?? 'New',
        ]);

        return response()->json($ticket, 201);
    }

    public function updateDetails(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'employee_name' => 'sometimes|required|string|max:255',
            'date'          => 'sometimes|required|date',
            'topic'         => 'sometimes|required|string|max:255',
            'description'   => 'sometimes|required|string',
            'it_tech_name'  => 'nullable|string|max:255',
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
