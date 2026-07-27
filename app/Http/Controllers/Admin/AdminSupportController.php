<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSupportController extends Controller
{
    public function __construct(
        protected SupportNotifier $notifier
    ) {}

    public function index(Request $request): Response
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
            ->withQueryString()
            ->through(fn (SupportTicket $t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'category' => $t->categoryLabel(),
                'status' => $t->status,
                'statusLabel' => $t->statusLabel(),
                'messagesCount' => $t->messages_count,
                'user' => $t->user ? [
                    'id' => $t->user->id,
                    'label' => $t->user->name ?: $t->user->email,
                ] : null,
                'lastMessageAt' => $t->last_message_at?->format('d.m.Y H:i'),
            ]);

        $counters = [
            'open' => SupportTicket::query()->where('status', SupportTicket::STATUS_OPEN)->count(),
            'pending_user' => SupportTicket::query()->where('status', SupportTicket::STATUS_PENDING_USER)->count(),
            'closed' => SupportTicket::query()->where('status', SupportTicket::STATUS_CLOSED)->count(),
        ];

        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets,
            'filter' => $filter,
            'counters' => $counters,
        ]);
    }

    public function show(SupportTicket $ticket): Response
    {
        $ticket->load(['user', 'messages.authorUser:id,email,name']);

        return Inertia::render('Admin/Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'category' => $ticket->categoryLabel(),
                'status' => $ticket->status,
                'statusLabel' => $ticket->statusLabel(),
                'isClosed' => $ticket->status === SupportTicket::STATUS_CLOSED,
                'createdAt' => $ticket->created_at?->format('d.m.Y H:i'),
                'user' => $ticket->user ? [
                    'id' => $ticket->user->id,
                    'label' => $ticket->user->name ?: $ticket->user->email,
                    'email' => $ticket->user->email,
                ] : null,
            ],
            'messages' => $ticket->messages->map(fn ($m) => [
                'id' => $m->id,
                'isAdmin' => $m->isAdmin(),
                'author' => $m->isAdmin() ? 'Поддержка' : ($m->authorUser?->name ?: 'Пользователь'),
                'body' => $m->body,
                'createdAt' => $m->created_at?->format('d.m.Y H:i'),
            ]),
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
