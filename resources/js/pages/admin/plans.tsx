import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { updatePlan } from '@/actions/App/Http/Controllers/Admin/AdminController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Plans', href: '/admin/plans' },
];

interface Plan {
    id: number;
    name: string;
    slug: string;
    price_cents: number;
    monthly_credits: number;
    stripe_price_id: string | null;
    is_active: boolean;
}

export default function AdminPlans({ plans }: { plans: Plan[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin — Plans" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">Plan & Credit Management</h1>

                <div className="grid gap-4 md:grid-cols-3">
                    {plans.map((plan) => (
                        <PlanCard key={plan.id} plan={plan} />
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}

function PlanCard({ plan }: { plan: Plan }) {
    const form = useForm({
        monthly_credits: String(plan.monthly_credits),
        price_cents: String(plan.price_cents),
        stripe_price_id: plan.stripe_price_id ?? '',
    });

    return (
        <form
            className="space-y-3 rounded-lg border p-4"
            onSubmit={(e) => {
                e.preventDefault();
                router.put(updatePlan(plan.id).url, form.data, { preserveScroll: true });
            }}
        >
            <div className="flex items-center justify-between">
                <h2 className="font-semibold">{plan.name}</h2>
                <Badge variant={plan.is_active ? 'secondary' : 'outline'}>{plan.is_active ? 'active' : 'inactive'}</Badge>
            </div>
            <p className="text-muted-foreground font-mono text-xs">{plan.slug}</p>
            <label className="block text-xs">
                Monthly credits
                <Input className="mt-1 h-8 text-sm" type="number" min={0} value={form.data.monthly_credits} onChange={(e) => form.setData('monthly_credits', e.target.value)} />
            </label>
            <label className="block text-xs">
                Price (cents)
                <Input className="mt-1 h-8 text-sm" type="number" min={0} value={form.data.price_cents} onChange={(e) => form.setData('price_cents', e.target.value)} />
            </label>
            <label className="block text-xs">
                Stripe price ID
                <Input className="mt-1 h-8 text-sm font-mono" value={form.data.stripe_price_id} onChange={(e) => form.setData('stripe_price_id', e.target.value)} />
            </label>
            <Button type="submit" size="sm" disabled={form.processing}>Save</Button>
        </form>
    );
}
