<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('admin/dashboard', [
            'stats' => [
                'users' => User::count(),
                'projects' => \App\Models\Project::count(),
                'generations' => Generation::count(),
                'generations_failed' => Generation::where('status', Generation::STATUS_FAILED)->count(),
                'credits_granted' => (int) CreditTransaction::where('amount', '>', 0)->sum('amount'),
                'credits_spent' => (int) abs(CreditTransaction::where('amount', '<', 0)->sum('amount')),
            ],
            'recentGenerations' => Generation::query()
                ->latest()
                ->take(10)
                ->with('user:id,email')
                ->get(['id', 'user_id', 'status', 'credits_spent', 'provider', 'model', 'created_at'])
                ->toArray(),
        ]);
    }

    public function users(Request $request): Response
    {
        return Inertia::render('admin/users', [
            'users' => User::query()
                ->when($request->input('q'), fn ($q, $search) => $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->orderByDesc('created_at')
                ->paginate(20)
                ->through(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'is_suspended' => $u->is_suspended,
                    'credits_balance' => $u->credits_balance,
                    'projects_count' => $u->projects_count ?? $u->projects()->count(),
                    'generations_count' => $u->generations()->count(),
                    'created_at' => $u->created_at?->toIso8601String(),
                ]),
            'filters' => ['q' => (string) $request->input('q', '')],
        ]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['sometimes', 'in:user,admin'],
            'is_suspended' => ['sometimes', 'boolean'],
            'credits_delta' => ['sometimes', 'integer', 'min:-100000', 'max:100000'],
        ]);

        if (isset($validated['role'])) {
            if ($request->user()->id === $user->id && $validated['role'] !== User::ROLE_ADMIN) {
                return back()->with('error', 'You cannot demote yourself.');
            }
            $user->role = $validated['role'];
        }

        if (isset($validated['is_suspended'])) {
            if ($request->user()->id === $user->id) {
                return back()->with('error', 'You cannot suspend yourself.');
            }
            $user->is_suspended = $validated['is_suspended'];
        }

        if (isset($validated['credits_delta']) && $validated['credits_delta'] !== 0) {
            $delta = $validated['credits_delta'];
            $service = app(\App\Services\CreditService::class);
            try {
                $delta > 0
                    ? $service->grant($user, $delta, 'admin_adjustment', 'Adjusted by admin #'.$request->user()->id)
                    : $service->deduct($user, abs($delta), 'admin_adjustment', 'Adjusted by admin #'.$request->user()->id);
            } catch (\App\Services\InsufficientCreditsException) {
                return back()->with('error', 'Adjustment would make balance negative.');
            }
        } else {
            $user->save();
        }

        return back()->with('success', 'User updated.');
    }

    public function plans(): Response
    {
        return Inertia::render('admin/plans', [
            'plans' => Plan::orderBy('sort_order')->get()->toArray(),
        ]);
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:50'],
            'monthly_credits' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'price_cents' => ['sometimes', 'integer', 'min:0'],
            'stripe_price_id' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $plan->update($validated);

        return back()->with('success', 'Plan updated.');
    }
}
