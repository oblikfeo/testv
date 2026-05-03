<?php

namespace App\Http\Controllers;

use App\Models\KeyOrder;
use App\Models\SaleKey;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(
        protected SupportNotifier $notifier
    ) {}

    public function index(Request $request): View
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('cabinet.support.index', [
            'activeRoute' => 'support',
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
        $saleKeyId = null;
        if ($activeSubscription) {
            $saleKeyId = SaleKey::query()
                ->where('subscription_id', $activeSubscription->id)
                ->value('id');
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
            'meta' => [
                'subscription_id' => $activeSubscription?->id,
                'sale_key_id' => $saleKeyId,
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

    public function show(Request $request, SupportTicket $ticket): View
    {
        $this->authorizeOwn($request, $ticket);

        $ticket->load(['messages.authorUser:id,name,email']);

        return view('cabinet.support.show', [
            'activeRoute' => 'support',
            'ticket' => $ticket,
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
