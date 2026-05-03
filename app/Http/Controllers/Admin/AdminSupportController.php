<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function __construct(
        protected SupportNotifier $notifier
    ) {}

    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'active');

        $query = SupportTicket::query()
            ->with(['user:id,email,name,telegram_username'])
            ->withCount('messages');

        if ($filter === 'open') {
            $query->where('status', SupportTicket::STATUS_OPEN);
        } elseif ($filter === 'pending_user') {
            $query->where('status', SupportTicket::STATUS_PENDING_USER);
        } elseif ($filter === 'closed') {
            $query->where('status', SupportTicket::STATUS_CLOSED);
        } else {
            // active = open + pending_user
            $query->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_PENDING_USER]);
            $filter = 'active';
        }

        $tickets = $query
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'pending_user' THEN 1 ELSE 2 END")
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $counters = [
            'open' => SupportTicket::query()->where('status', SupportTicket::STATUS_OPEN)->count(),
            'pending_user' => SupportTicket::query()->where('status', SupportTicket::STATUS_PENDING_USER)->count(),
            'closed' => SupportTicket::query()->where('status', SupportTicket::STATUS_CLOSED)->count(),
        ];

        return view('admin.support.index', [
            'tickets' => $tickets,
            'filter' => $filter,
            'counters' => $counters,
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'messages.authorUser:id,email,name']);

        return view('admin.support.show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'author_type' => SupportMessage::AUTHOR_ADMIN,
            'author_user_id' => null,
            'body' => $data['body'],
        ]);

        $ticket->update([
            'status' => SupportTicket::STATUS_PENDING_USER,
            'last_message_at' => now(),
        ]);

        $this->notifier->notifyUserAdminReply($ticket, $message);

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Ответ отправлен.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        $ticket->update(['status' => SupportTicket::STATUS_CLOSED]);

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Тикет закрыт.');
    }

    public function reopen(SupportTicket $ticket): RedirectResponse
    {
        $ticket->update(['status' => SupportTicket::STATUS_OPEN]);

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Тикет переоткрыт.');
    }
}
