<?php

namespace App\Http\Controllers;

use App\Models\KeyOrder;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function __construct(
        protected SupportNotifier $notifier
    ) {}

    public function index(Request $request): Response
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->through(fn (SupportTicket $ticket) => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'categoryLabel' => $ticket->categoryLabel(),
                'statusLabel' => $ticket->statusLabel(),
                'status' => $ticket->status,
                'lastMessageAt' => optional($ticket->last_message_at ?? $ticket->created_at)->format('d.m.Y H:i'),
            ])
            ->withQueryString();

        return Inertia::render('Support/Index', [
            'tickets' => $tickets,
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'category' => 'required|string|in:'.implode(',', array_keys(SupportTicket::CATEGORIES)),
            'body' => 'required|string|max:5000',
        ]);

        $user = $request->user();

        $activeSubscription = $user->activeSubscriptions()->first();
        $lastOrder = $user->keyOrders()->latest()->first();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
            'meta' => [
                'subscription_id' => $activeSubscription?->id,
                'last_order_id' => $lastOrder?->id,
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            ],
        ]);

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'author_type' => SupportMessage::AUTHOR_USER,
            'author_user_id' => $user->id,
            'body' => $data['body'],
        ]);

        $this->notifier->notifyAdminNewTicket($ticket, $message);

        return redirect()->route('cabinet.support.show', $ticket)
            ->with('success', 'Обращение отправлено. Мы ответим как можно скорее.');
    }

    public function show(Request $request, SupportTicket $ticket): Response
    {
        $this->authorizeOwn($request, $ticket);

        $ticket->load(['messages.authorUser:id,name,email']);

        return Inertia::render('Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'categoryLabel' => $ticket->categoryLabel(),
                'statusLabel' => $ticket->statusLabel(),
                'status' => $ticket->status,
                'isOpen' => $ticket->isOpen(),
                'createdAt' => $ticket->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i'),
                'messages' => $ticket->messages->map(fn (SupportMessage $message) => [
                    'id' => $message->id,
                    'isAdmin' => $message->isAdmin(),
                    'body' => $message->body,
                    'createdAt' => $message->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i'),
                ])->values(),
            ],
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorizeOwn($request, $ticket);

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            return back()->withErrors(['body' => 'Тикет закрыт. Создайте новое обращение.']);
        }

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'author_type' => SupportMessage::AUTHOR_USER,
            'author_user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $ticket->update([
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $this->notifier->notifyAdminUserReply($ticket, $message);

        return redirect()->route('cabinet.support.show', $ticket);
    }

    public function close(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorizeOwn($request, $ticket);

        $ticket->update(['status' => SupportTicket::STATUS_CLOSED]);

        return redirect()->route('cabinet.support.show', $ticket)
            ->with('success', 'Тикет закрыт. Спасибо за обращение!');
    }

    protected function authorizeOwn(Request $request, SupportTicket $ticket): void
    {
        abort_if($ticket->user_id !== $request->user()->id, 404);
    }
}
