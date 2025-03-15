<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\TicketAction;
use App\Http\Controllers\Controller;
use App\Models\UserSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function list()
    {
        $user = auth()->user();

        $items = $user->isAdmin() ? UserSupport::all() : $user->supportRequests;

        return view('panel.support.list', compact('items'));
    }

    public function newTicket()
    {
        return view('panel.support.new');
    }

    // public function newTicketSend(Request $request): void
    // {
    //     dd($request->all());
        
    //     if (! $user = Auth::user()) {
    //         return;
    //     }

    //     $support = $user->supportRequests()->create([
    //         'ticket_id' => Str::upper(Str::random(10)),
    //         'priority'  => $request->priority,
    //         'category'  => $request->category,
    //         'subject'   => $request->subject,
    //     ]);

    //     TicketAction::ticket($support)
    //         ->fromUser()
    //         ->new($request->message)
    //         ->send();
    // }
    
    public function newTicketSend(Request $request)
{
    // Debugging: Dump request data to verify file input
    // dd($request->all());

    if (! $user = Auth::user()) {
        return;
    }

    // Validate request including file attachment
    // $request->validate([
    //     'priority'  => 'required|string|in:Low,Normal,High,Critical',
    //     'category'  => 'required|string|in:General Inquiry,Technical Issue,Improvement Idea,Feedback,Other',
    //     'subject'   => 'required|string|max:255',
    //     'message'   => 'required|string',
    //     'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Adjust file types and size as needed
    // ]);

    // Create support ticket
    $support = $user->supportRequests()->create([
        'ticket_id' => Str::upper(Str::random(10)),
        'priority'  => $request->priority,
        'category'  => $request->category,
        'subject'   => $request->subject,
    ]);

    // Handle file upload
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filePath = $file->store('support_attachments', 'public'); // Save in 'storage/app/public/support_attachments'

        // Save attachment path to the database
        $support->update(['attachment' => $filePath]);
    }

    // Create ticket action
    TicketAction::ticket($support)
        ->fromUser()
        ->new($request->message)
        ->send();

    // Return response (if needed)
    // return response()->json([
    //     'message' => 'Support ticket created successfully.',
    //     'ticket_id' => $support->ticket_id,
    //     'attachment' => $support->attachment ?? null,
    // ]);
}


    public function viewTicket($ticket_id)
    {
        $ticket = UserSupport::where('ticket_id', $ticket_id)->firstOrFail();

        if ($ticket->user_id == Auth::id() or Auth::user()->isAdmin()) {
            return view('panel.support.view', compact('ticket'));
        } else {
            return back()->with(['message' => __('Unauthorized'), 'type' => 'error']);
        }
    }

    public function viewTicketSendMessage(Request $request): void
    {
        if (! $user = Auth::user()) {
            return;
        }

        TicketAction::ticket($request->input('ticket_id'))
            ->fromAdminIfTrue($user->isAdmin())
            ->answer($request->input('message'))
            ->send();
    }
}
