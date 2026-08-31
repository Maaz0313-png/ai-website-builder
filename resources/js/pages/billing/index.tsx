import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { subscribe as subscribeAction } from '@/actions/App/Http/Controllers/BillingController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing', href: '/billing' },
];

interface Plan {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_cents: number;
    billing_period: string;
    monthly_credits: number;
    features: string[] | null;
}

interface Props {
    plans: Plan[];
    currentPlan?: string | null;
    creditsBalance: number;
    stripeEnabled: boolean;
}

export default function Billing({ plans, currentPlan, creditsBalance, stripeEnabled }: Props) {
    const subscribe = (slug: string) => {
        router.post(subscribeAction().url, { plan: slug });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Billing & Credits</h1>
                        <p className="text-muted-foreground text-sm">Manage your plan and credit balance.</p>
                    </div>
                    <div className="rounded-lg border px-4 py-2 text-sm">
                        <span className="font-medium">Credits:</span>{' '}
                        <Badge variant="secondary" className="ml-1">{creditsBalance}</Badge>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    {plans.map((plan) => {
                        const isCurrent = currentPlan === plan.slug || (plan.slug === 'free' && !currentPlan);
                        return (
                            <Card key={plan.id} className={isCurrent ? 'border-primary' : ''}>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle>{plan.name}</CardTitle>
                                        {isCurrent && <Badge>Current</Badge>}
                                    </div>
                                    <CardDescription>{plan.description}</CardDescription>
                                </CardHeader>
                                <CardContent className="flex-1">
                                    <div className="mb-4 text-3xl font-bold">
                                        ${(plan.price_cents / 100).toFixed(0)}
                                        <span className="text-muted-foreground text-sm font-normal">/mo</span>
                                    </div>
                                    <ul className="space-y-2 text-sm text-muted-foreground">
                                        {(plan.features ?? []).map((feature) => (
                                            <li key={feature} className="flex items-center gap-2">
                                                <span className="text-primary">✓</span> {feature}
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                                <CardFooter>
                                    <Button
                                        className="w-full"
                                        variant={isCurrent ? 'outline' : 'default'}
                                        disabled={isCurrent}
                                        onClick={() => subscribe(plan.slug)}
                                    >
                                        {plan.price_cents === 0
                                            ? (isCurrent ? 'Current plan' : 'Switch to Free')
                                            : `Subscribe — $${(plan.price_cents / 100).toFixed(0)}/mo`}
                                    </Button>
                                </CardFooter>
                            </Card>
                        );
                    })}
                </div>

                {!stripeEnabled && (
                    <p className="text-muted-foreground text-xs">
                        Stripe is not configured in this environment; paid subscriptions are disabled.
                    </p>
                )}
            </div>
        </AppLayout>
    );
}
