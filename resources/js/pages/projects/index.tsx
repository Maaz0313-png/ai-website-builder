import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Trash2 } from 'lucide-react';
import { FormEventHandler, useState, useEffect } from 'react';
import { show as projectShow, store as projectStore, destroy as projectDestroy } from '@/actions/App/Http/Controllers/ProjectController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Projects', href: '/projects' },
];

interface Project {
    id: number;
    name: string;
    prompt: string;
    status: string;
    created_at: string;
}

interface Model {
    id: string;
    name: string;
    context_length: number | null;
}

interface Props {
    projects: Project[];
    creditsBalance: number;
}

export default function Projects({ projects, creditsBalance }: Props) {
    const form = useForm<{ prompt: string; model: string }>({ prompt: '', model: '' });
    const deleteForm = useForm({ _method: 'delete' });
    const [deletingId, setDeletingId] = useState<number | null>(null);
    const [models, setModels] = useState<Model[]>([]);
    const [modelsLoading, setModelsLoading] = useState(true);

    useEffect(() => {
        fetch('/projects/models', { credentials: 'same-origin' })
            .then((res) => res.json())
            .then((data) => {
                setModels(data);
                setModelsLoading(false);
            })
            .catch(() => setModelsLoading(false));
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(projectStore().url, {
            onSuccess: () => form.reset('prompt'),
        });
    };

    const confirmDelete = (id: number) => {
        if (window.confirm('Are you sure you want to delete this project? This cannot be undone.')) {
            setDeletingId(id);
            deleteForm.delete(projectDestroy(id).url, {
                onSuccess: () => setDeletingId(null),
                onError: () => setDeletingId(null),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Projects" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Your Websites</h1>
                    <Badge variant="secondary">{creditsBalance} credits</Badge>
                </div>

                <Card>
                    <CardContent className="pt-6">
                        <form onSubmit={submit} className="space-y-4">
                            <Textarea
                                value={form.data.prompt}
                                onChange={(e) => form.setData('prompt', e.target.value)}
                                placeholder="Describe the website you want… e.g. “A landing page for a cozy neighborhood coffee shop with a menu and testimonials”"
                                rows={4}
                                required
                                minLength={10}
                                maxLength={2000}
                            />
                            {form.errors.prompt && (
                                <p className="text-sm text-red-500">{form.errors.prompt}</p>
                            )}
                            <div>
                                <label className="mb-1 block text-sm font-medium">Model</label>
                                <Select
                                    value={form.data.model}
                                    onValueChange={(value) => form.setData('model', value)}
                                    disabled={modelsLoading}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder={modelsLoading ? 'Loading models…' : 'Select a model (default: GLM 5.2)'} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {models.map((model) => (
                                            <SelectItem key={model.id} value={model.id}>
                                                {model.name}
                                                {model.context_length && (
                                                    <span className="ml-2 text-xs text-muted-foreground">
                                                        ({Math.round(model.context_length / 1000)}k context)
                                                    </span>
                                                )}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? 'Starting generation…' : 'Generate Website'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <div className="grid gap-3">
                    {projects.length === 0 && (
                        <p className="text-muted-foreground text-sm">No websites yet — describe one above to get started.</p>
                    )}
                    {projects.map((project) => (
                        <Card key={project.id} className="transition-colors hover:border-primary/50">
                            <CardContent className="flex items-center justify-between py-4">
                                <Link href={projectShow(project.id).url} className="flex-1 min-w-0">
                                    <p className="truncate font-medium">{project.name || project.prompt}</p>
                                    <p className="text-muted-foreground truncate text-xs">{project.prompt}</p>
                                </Link>
                                <div className="flex items-center gap-2">
                                    <Badge variant="outline">{project.status}</Badge>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={() => confirmDelete(project.id)}
                                        disabled={deletingId === project.id}
                                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                        title="Delete project"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
