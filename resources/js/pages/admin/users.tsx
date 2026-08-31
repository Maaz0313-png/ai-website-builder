import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { useState } from 'react';
import { users as adminUsersIndex, updateUser } from '@/actions/App/Http/Controllers/Admin/AdminController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
];

interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: string;
    is_suspended: boolean;
    credits_balance: number;
    projects_count: number;
    generations_count: number;
    created_at: string;
}

interface Props {
    users: { data: AdminUser[]; links: unknown[] };
    filters: { q: string };
}

export default function AdminUsers({ users, filters }: Props) {
    const [search, setSearch] = useState(filters.q);
    const adjustForm = useForm<{ credits_delta: string }>({ credits_delta: '' });

    const apply = (userId: number, action: 'toggleSuspend' | 'adjustCredits', value?: string) => {
        const data =
            action === 'toggleSuspend' ? {} : { credits_delta: Number(value ?? 0) || 0 };
        router.put(updateUser(userId).url, data, {
            preserveScroll: true,
            onSuccess: () => adjustForm.setData('credits_delta', ''),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin — Users" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">User Management</h1>

                <form
                    className="flex max-w-md gap-2"
                    onSubmit={(e) => {
                        e.preventDefault();
                        router.get(adminUsersIndex().url, { q: search }, { preserveState: true });
                    }}
                >
                    <Input placeholder="Search name or email…" value={search} onChange={(e) => setSearch(e.target.value)} />
                    <Button type="submit" variant="outline">Search</Button>
                </form>

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="text-muted-foreground border-b text-left text-xs uppercase">
                            <tr>
                                <th className="p-3">User</th>
                                <th className="p-3">Role</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Credits</th>
                                <th className="p-3">Projects</th>
                                <th className="p-3">Generations</th>
                                <th className="p-3">Adjust credits</th>
                                <th className="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((u) => (
                                <tr key={u.id} className="border-b last:border-0">
                                    <td className="p-3">
                                        <div className="font-medium">{u.name}</div>
                                        <div className="text-muted-foreground text-xs">{u.email}</div>
                                    </td>
                                    <td className="p-3"><Badge variant={u.role === 'admin' ? 'default' : 'outline'}>{u.role}</Badge></td>
                                    <td className="p-3">
                                        {u.is_suspended ? <Badge variant="destructive">suspended</Badge> : <Badge variant="secondary">active</Badge>}
                                    </td>
                                    <td className="p-3 font-medium">{u.credits_balance}</td>
                                    <td className="p-3">{u.projects_count}</td>
                                    <td className="p-3">{u.generations_count}</td>
                                    <td className="p-3">
                                        <form
                                            className="flex gap-1"
                                            onSubmit={(e) => {
                                                e.preventDefault();
                                                apply(u.id, 'adjustCredits', adjustForm.data.credits_delta);
                                            }}
                                        >
                                            <Input
                                                className="h-8 w-24 text-xs"
                                                type="number"
                                                step="1"
                                                placeholder="+10 / -5"
                                                value={adjustForm.data.credits_delta}
                                                onChange={(e) => adjustForm.setData('credits_delta', e.target.value)}
                                            />
                                            <Button type="submit" size="sm" variant="outline">Apply</Button>
                                        </form>
                                    </td>
                                    <td className="p-3">
                                        <Button
                                            size="sm"
                                            variant={u.is_suspended ? 'default' : 'destructive'}
                                            onClick={() => apply(u.id, 'toggleSuspend')}
                                        >
                                            {u.is_suspended ? 'Unsuspend' : 'Suspend'}
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
