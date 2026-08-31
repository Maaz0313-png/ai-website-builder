import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { useState } from 'react';
import { preview as previewAction, updateCode as updateCodeAction, updateSpec as updateSpecAction } from '@/actions/App/Http/Controllers/SiteEditorController';

interface Section {
    type: string;
    heading: string;
    body: string;
    items?: { title?: string; body?: string }[] | null;
}

interface Page {
    title: string;
    slug: string;
    sections: Section[];
}

interface Spec {
    theme: { primary_color: string; accent_color: string; font: string };
    pages: Page[];
}

interface Props {
    project: { id: number; name: string; spec: Spec | null };
    code: string;
}

export default function ProjectEditor({ project, code }: Props) {
    const [tab, setTab] = useState<'visual' | 'code'>(project.spec ? 'visual' : 'code');
    const [previewKey, setPreviewKey] = useState(0);

    const specForm = useForm<{ name: string; spec: Spec }>({
        name: project.name,
        spec: project.spec ?? {
            theme: { primary_color: '#4f46e5', accent_color: '#06b6d4', font: 'sans' },
            pages: [{ title: 'Home', slug: 'home', sections: [] }],
        },
    });

    const codeForm = useForm<{ code: string }>({ code });

    const saveSpec = (e: React.FormEvent) => {
        e.preventDefault();
        specForm.put(updateSpecAction(project.id).url, {
            onSuccess: () => setPreviewKey((k) => k + 1),
        });
    };

    const saveCode = (e: React.FormEvent) => {
        e.preventDefault();
        codeForm.put(updateCodeAction(project.id).url, {
            onSuccess: () => setPreviewKey((k) => k + 1),
        });
    };

    const updatePage = (pi: number, patch: Partial<Page>) => {
        const pages = specForm.data.spec.pages.map((p, i) => (i === pi ? { ...p, ...patch } : p));
        specForm.setData('spec', { ...specForm.data.spec, pages });
    };

    const updateSection = (pi: number, si: number, patch: Partial<Section>) => {
        const pages = specForm.data.spec.pages.map((p, i) =>
            i === pi ? { ...p, sections: p.sections.map((s, j) => (j === si ? { ...s, ...patch } : s)) } : p,
        );
        specForm.setData('spec', { ...specForm.data.spec, pages });
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Projects', href: '/projects' },
            { title: project.name, href: `/projects/${project.id}` },
            { title: 'Editor', href: `/projects/${project.id}/editor` },
        ]}>
            <Head title={`Edit — ${project.name}`} />
            <div className="flex h-full flex-1 flex-col gap-3 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">{project.name}</h1>
                    <div className="flex gap-2">
                        <Button variant={tab === 'visual' ? 'default' : 'outline'} size="sm" onClick={() => setTab('visual')}>
                            Visual
                        </Button>
                        <Button variant={tab === 'code' ? 'default' : 'outline'} size="sm" onClick={() => setTab('code')}>
                            Code
                        </Button>
                        <a
                            href={previewAction({ project: project.id }).url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-9 px-3"
                        >
                            Open preview
                        </a>
                    </div>
                </div>

                <div className="grid flex-1 grid-cols-1 gap-4 lg:grid-cols-2">
                    <div className="min-w-0 overflow-auto rounded-lg border">
                        {tab === 'visual' && project.spec && (
                            <form onSubmit={saveSpec} className="space-y-4 p-4">
                                {(specForm.errors as Record<string, string>).spec && (
                                    <p className="text-sm text-red-500">{String(specForm.errors.spec)}</p>
                                )}
                                <div>
                                    <label className="mb-1 block text-sm font-medium">Site name</label>
                                    <Input value={specForm.data.name} onChange={(e) => specForm.setData('name', e.target.value)} />
                                </div>

                                <div className="grid grid-cols-3 gap-2">
                                    <label className="text-xs">
                                        Primary
                                        <Input
                                            type="color"
                                            className="mt-1 h-9"
                                            value={specForm.data.spec.theme.primary_color}
                                            onChange={(e) => specForm.setData('spec.theme.primary_color', e.target.value)}
                                        />
                                    </label>
                                    <label className="text-xs">
                                        Accent
                                        <Input
                                            type="color"
                                            className="mt-1 h-9"
                                            value={specForm.data.spec.theme.accent_color}
                                            onChange={(e) => specForm.setData('spec.theme.accent_color', e.target.value)}
                                        />
                                    </label>
                                    <label className="text-xs">
                                        Font
                                        <select
                                            className="border-input mt-1 block h-9 w-full rounded-md border bg-transparent px-2 text-sm"
                                            value={specForm.data.spec.theme.font}
                                            onChange={(e) => specForm.setData('spec.theme.font', e.target.value)}
                                        >
                                            <option value="sans">Sans</option>
                                            <option value="serif">Serif</option>
                                            <option value="mono">Mono</option>
                                        </select>
                                    </label>
                                </div>

                                {specForm.data.spec.pages.map((page, pi) => (
                                    <div key={pi} className="rounded-lg border p-3">
                                        <div className="mb-2 flex gap-2">
                                            <Input
                                                className="text-sm"
                                                value={page.title}
                                                onChange={(e) => updatePage(pi, { title: e.target.value })}
                                            />
                                            <Badge variant="outline" className="shrink-0 py-2">/{page.slug}</Badge>
                                        </div>
                                        {page.sections.map((section, si) => (
                                            <div key={si} className="mb-3 space-y-2 rounded-md bg-muted/40 p-3">
                                                <div className="flex items-center justify-between">
                                                    <Badge variant="secondary">{section.type}</Badge>
                                                </div>
                                                <Input
                                                    placeholder="Heading"
                                                    value={section.heading}
                                                    onChange={(e) => updateSection(pi, si, { heading: e.target.value })}
                                                />
                                                <Textarea
                                                    rows={2}
                                                    placeholder="Body text"
                                                    value={section.body}
                                                    onChange={(e) => updateSection(pi, si, { body: e.target.value })}
                                                />
                                                {(section.items ?? []).map((item, ii) => (
                                                    <div key={ii} className="flex gap-2">
                                                        <Input
                                                            className="text-xs"
                                                            placeholder="Item title"
                                                            value={item.title ?? ''}
                                                            onChange={(e) => {
                                                                const items = [...(section.items ?? [])];
                                                                items[ii] = { ...items[ii], title: e.target.value };
                                                                updateSection(pi, si, { items });
                                                            }}
                                                        />
                                                        <Input
                                                            className="text-xs"
                                                            placeholder="Item body"
                                                            value={item.body ?? ''}
                                                            onChange={(e) => {
                                                                const items = [...(section.items ?? [])];
                                                                items[ii] = { ...items[ii], body: e.target.value };
                                                                updateSection(pi, si, { items });
                                                            }}
                                                        />
                                                    </div>
                                                ))}
                                            </div>
                                        ))}
                                    </div>
                                ))}

                                <Button type="submit" disabled={specForm.processing}>
                                    {specForm.processing ? 'Saving…' : 'Save & Rebuild'}
                                </Button>
                            </form>
                        )}

                        {tab === 'code' && (
                            <form onSubmit={saveCode} className="flex h-full flex-col gap-3 p-4">
                                {codeForm.errors.code && <p className="text-sm text-red-500">{codeForm.errors.code}</p>}
                                <Textarea
                                    className="min-h-[400px] flex-1 font-mono text-xs"
                                    value={codeForm.data.code}
                                    onChange={(e) => codeForm.setData('code', e.target.value)}
                                    spellCheck={false}
                                />
                                <Button type="submit" disabled={codeForm.processing}>
                                    {codeForm.processing ? 'Saving…' : 'Save Code & Rebuild'}
                                </Button>
                                <p className="text-muted-foreground text-xs">
                                    Note: saving from the Visual tab regenerates code from the structured spec.
                                </p>
                            </form>
                        )}
                    </div>

                    <div className="min-h-[600px] overflow-hidden rounded-lg border">
                        <iframe
                            key={previewKey}
                            src={`/preview/${project.id}`}
                            className="h-full min-h-[600px] w-full"
                            title="Live preview"
                        />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
