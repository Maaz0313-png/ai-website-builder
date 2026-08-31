import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Dashboard', href: '/admin' },
];

interface Props {
    stats: Record<string, number>;
    recentGenerations: {
        id: number;
        status: string;
        credits_spent: number;
        provider: string | null;
        model: string | null;
        created_at: string;
        user?: { email: string } | null;
    }[];
}

export default function AdminDashboard({ stats, recentGenerations }: Props) {
    const cards = [
        ['Users', stats.users],
        ['Projects', stats.projects],
        ['Generations', stats.generations],
        ['Failed generations', stats.generations_failed],
        ['Credits granted', stats.credits_granted],
        ['Credits spent', stats.credits_spent],
    ] as const;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <h1 className="text-2xl font-semibold">Platform Overview</h1>

                <div className="grid gap-4 md:grid-cols-3">
                    {cards.map(([label, value]) => (
                        <Card key={label}>
                            <CardContent className="pt-6">
                                <p className="text-muted-foreground text-xs uppercase">{label}</p>
                                <p className="text-3xl font-bold">{value}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="rounded-lg border">
                    <div className="border-b p-4 text-sm font-medium">Recent generations</div>
                    <table className="w-full text-sm">
                        <thead className="text-muted-foreground border-b text-left text-xs uppercase">
                            <tr>
                                <th className="p-3">User</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Credits</th>
                                <th className="p-3">Provider</th>
                                <th className="p-3">Model</th>
                                <th className="p-3">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recentGenerations.map((g) => (
                                <tr key={g.id} className="border-b last:border-0">
                                    <td className="p-3">{g.user?.email ?? '—'}</td>
                                    <td className="p-3"><Badge variant={g.status === 'completed' ? 'secondary' : g.status === 'failed' ? 'destructive' : 'outline'}>{g.status}</Badge></td>
                                    <td className="p-3">{g.credits_spent}</td>
                                    <td className="p-3">{g.provider ?? '—'}</td>
                                    <td className="max-w-[220px] truncate p-3 font-mono text-xs">{g.model ?? '—'}</td>
                                    <td className="p-3">{new Date(g.created_at).toLocaleString()}</td>
                                </tr>
                            ))}
                            {recentGenerations.length === 0 && (
                                <tr><td colSpan={6} className="text-muted-foreground p-6 text-center">No generations yet.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
