<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PlansController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::query()
            ->withCount('subscriptions')
            ->orderBy('sort_order')->orderBy('devices')->orderBy('days')
            ->get()
            ->map(fn (Plan $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'devices' => (int) $p->devices,
                'days' => (int) $p->days,
                'price' => (int) $p->price,
                'discount' => (int) ($p->discount ?? 0),
                'trafficGb' => (int) ($p->traffic_gb ?? 0),
                'isPopular' => (bool) $p->is_popular,
                'isActive' => (bool) $p->is_active,
                'sortOrder' => (int) $p->sort_order,
                'subscriptionsCount' => $p->subscriptions_count,
            ]);

        return Inertia::render('Admin/Plans/Index', ['plans' => $plans]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Plan::create($data);

        return back()->with('success', "Тариф «{$data['name']}» создан.");
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $this->validateData($request, $plan->id);
        $plan->update($data);

        return back()->with('success', "Тариф «{$plan->name}» обновлён.");
    }

    public function toggle(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('success', "Тариф «{$plan->name}» ".($plan->is_active ? 'включён' : 'выключен').'.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists() || $plan->orders()->exists()) {
            return back()->with('error', 'Нельзя удалить тариф с привязанными подписками/заказами — выключите его вместо удаления.');
        }

        $name = $plan->name;
        $plan->delete();

        return back()->with('success', "Тариф «{$name}» удалён.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => ['required', 'string', 'max:100', Rule::unique('plans', 'slug')->ignore($ignoreId)],
            'devices' => 'required|integer|min:1|max:100',
            'days' => 'required|integer|min:1|max:3650',
            'price' => 'required|integer|min:0|max:1000000',
            'discount' => 'nullable|integer|min:0|max:100',
            'traffic_gb' => 'nullable|integer|min:0|max:100000',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['discount'] = (int) ($validated['discount'] ?? 0);
        $validated['traffic_gb'] = (int) ($validated['traffic_gb'] ?? 0);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_popular'] = (bool) ($validated['is_popular'] ?? false);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return $validated;
    }
}
