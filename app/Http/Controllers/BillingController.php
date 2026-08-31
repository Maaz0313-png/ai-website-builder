<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('billing/index', [
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'description', 'price_cents', 'billing_period', 'monthly_credits', 'features'])
                ->toArray(),
            'currentPlan' => $user->subscriptions->firstWhere('stripe_status', 'active')?->type,
            'creditsBalance' => $user->credits_balance,
            'stripeEnabled' => config('cashier.key') !== null,
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', 'exists:plans,slug'],
        ]);

        $plan = Plan::query()->where('slug', $validated['plan'])->where('is_active', true)->firstOrFail();
        $user = $request->user();

        if ($plan->price_cents === 0) {
            return back()->with('success', "You are on the {$plan->name} plan.");
        }

        if (! config('cashier.key')) {
            return back()->with('error', 'Billing is not configured yet. Please contact support.');
        }

        return $user->newSubscription('default', $plan->stripe_price_id)
            ->checkout([
                'success_url' => route('billing').'?subscribed=1',
                'cancel_url' => route('billing'),
            ])->redirect();
    }

    public function portal(Request $request): RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(route('billing'));
    }
}
