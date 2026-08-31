import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { useEffect, useState } from 'react';
import { edit as projectEditor } from '@/actions/App/Http/Controllers/SiteEditorController';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Projects', href: '/projects' }];

interface Project {
    id: number;
    name: string;
    prompt: string;
    status: string;
}

interface Generation {
    id: number;
    status: string;
    progress: number;
    error?: string | null;
    meta?: {
        step?: string;
        provider?: string;
        model?: string;
    };
}

interface Props {
    project: Project;
    generation?: Generation | null;
    creditsBalance: number;
}

export default function ProjectShow({ project, generation, creditsBalance }: Props) {
    const [status, setStatus] = useState(generation?.status ?? null);
    const [progress, setProgress] = useState(generation?.progress ?? 0);
    const [step, setStep] = useState(generation?.meta?.step ?? '');
    const [model, setModel] = useState(generation?.meta?.model ?? generation?.model ?? '');
    const userId = usePage().props.auth?.user?.id;

    const isActive = status === 'queued' || status === 'processing';

    useEffect(() => {
        setStatus(generation?.status ?? null);
        setProgress(generation?.progress ?? 0);
        setStep(generation?.meta?.step ?? '');
        setModel(generation?.meta?.model ?? generation?.model ?? '');
    }, [generation?.status, generation?.progress, generation?.meta, generation?.model]);

    useEffect(() => {
        if (!isActive || !userId || !window.Echo) return;

        // Live updates via Reverb…
        const channel = window.Echo.private(`generations.${userId}`).listen('.generation.progress', (payload: { status?: string; progress?: number; error?: string; meta?: { step?: string; model?: string } }) => {
            if (payload.status) setStatus(payload.status);
            if (typeof payload.progress === 'number') setProgress(payload.progress);
            if (payload.meta?.step) setStep(payload.meta.step);
            if (payload.meta?.model) setModel(payload.meta.model);
        });

        return () => {
            window.Echo.leave(`generations.${userId}`);
        };
    }, [isActive, userId]);

    useEffect(() => {
        if (!isActive) return;

        // Fallback polling in case Reverb misses updates.
        // Stop after 5 minutes to avoid infinite reloads if queue is down.
        const startTime = Date.now();
        const timer = setInterval(() => {
            if (Date.now() - startTime > 5 * 60 * 1000) {
                clearInterval(timer);
                return;
            }
            router.reload({
                only: ['project', 'generation', 'creditsBalance'],
                onSuccess: () => {},
            });
        }, 5000);
        return () => clearInterval(timer);
    }, [isActive, router]);

    const done = status === 'completed' && project.status === 'generated' && project.spec;

    const getStatusLabel = () => {
        switch (status) {
            case 'queued': return 'Queued';
            case 'processing': return 'Processing';
            case 'completed': return 'Completed';
            case 'failed': return 'Failed';
            default: return status;
        }
    };

    return (
        <AppLayout breadcrumbs={[...breadcrumbs, { title: project.name || 'Generating…', href: `/projects/${project.id}` }]}>
            <Head title={project.name || 'Project'} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">{project.name || 'Generating your website…'}</h1>
                    <Badge variant="secondary">{creditsBalance} credits</Badge>
                </div>

                <p className="text-muted-foreground max-w-3xl text-sm">{project.prompt}</p>

                {isActive && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium">Generation in progress — {progress}%</p>
                                {step && <p className="text-sm text-muted-foreground">{step}</p>}
                                {model && <p className="text-xs text-muted-foreground">Model: {model}</p>}
                            </div>
                            <Badge variant="secondary">{getStatusLabel()}</Badge>
                        </div>
                        <Progress value={progress} className="h-3" />
                    </div>
                )}

                {status === 'failed' && (
                    <p className="text-sm text-red-500">
                        Generation failed{generation?.error ? `: ${generation.error}` : '.'} Your credit was refunded.
                    </p>
                )}

                {done && (
                    <div className="flex flex-col gap-3">
                        <Button className="w-fit" onClick={() => router.visit(projectEditor({ project: project.id }).url)}>
                            Open editor & preview
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
