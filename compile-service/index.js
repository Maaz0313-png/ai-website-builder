import { createServer } from 'node:http';
import { promises as fs } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import crypto from 'node:crypto';

const PORT = Number(process.env.PORT || 5178);
// Sandboxed workspace root — the service will only read/write inside this tree.
// Resolved relative to this file so it always lands in <project>/storage/app/sites.
const WORKSPACE_ROOT = path.resolve(
    path.dirname(fileURLToPath(import.meta.url)),
    process.env.WORKSPACE_ROOT || '../storage/app/sites',
);

function safeResolve(...segments) {
    const target = path.resolve(WORKSPACE_ROOT, ...segments);
    const normalizedRoot = path.resolve(WORKSPACE_ROOT) + path.sep;
    if (target !== path.resolve(WORKSPACE_ROOT) && !target.startsWith(normalizedRoot)) {
        throw new Error('Path escapes the sandboxed workspace');
    }
    return target;
}

function minifyHtml(html) {
    return html
        .replace(/<!--(?!\[if)[\s\S]*?-->/g, '')
        .replace(/\n\s+/g, '\n')
        .replace(/>\s+</g, '><')
        .trim();
}

async function compile({ project_id, version_id }) {
    const srcDir = safeResolve(String(project_id), 'src', String(version_id));
    const outDir = safeResolve(String(project_id), 'build', String(version_id));

    await fs.mkdir(outDir, { recursive: true });

    const files = [];
    async function walk(dir) {
        const entries = await fs.readdir(dir, { withFileTypes: true });
        for (const entry of entries) {
            const full = path.join(dir, entry.name);
            if (entry.isDirectory()) {
                await walk(full);
            } else {
                files.push(full);
            }
        }
    }
    await walk(srcDir);

    if (files.length === 0) {
        throw new Error('Source directory is empty');
    }

    let count = 0;
    for (const file of files) {
        const rel = path.relative(srcDir, file).split(path.sep).join('/');
        // Defense-in-depth: reject traversal in stored file names.
        if (rel.includes('..')) continue;
        const dest = path.join(outDir, rel);
        await fs.mkdir(path.dirname(dest), { recursive: true });

        if (rel.toLowerCase().endsWith('.html')) {
            const html = await fs.readFile(file, 'utf8');
            await fs.writeFile(dest, minifyHtml(html));
        } else {
            await fs.copyFile(file, dest);
        }
        count++;
    }

    return {
        ok: true,
        project_id,
        version_id,
        files_compiled: count,
        output_dir: `sites/${project_id}/build/${version_id}`,
        checksum: crypto.createHash('sha256').update(`${project_id}:${version_id}:${count}`).digest('hex'),
    };
}

const server = createServer(async (req, res) => {
    if (req.method === 'GET' && req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true }));
        return;
    }

    if (req.method === 'POST' && req.url === '/compile') {
        // Internal-only: require a shared secret from Laravel.
        if (process.env.COMPILE_TOKEN && req.headers['x-compile-token'] !== process.env.COMPILE_TOKEN) {
            res.writeHead(401, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: 'Unauthorized' }));
            return;
        }

        let body = '';
        let size = 0;
        req.on('data', (chunk) => {
            size += chunk.length;
            if (size > 1024 * 1024) {
                req.destroy();
            }
            body += chunk;
        });

        req.on('end', async () => {
            try {
                const payload = JSON.parse(body || '{}');
                const result = await compile(payload);
                console.log(`[compile] project=${result.project_id} version=${result.version_id} files=${result.files_compiled}`);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify(result));
            } catch (err) {
                console.error('[compile] failed:', err.message);
                res.writeHead(422, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ ok: false, error: err.message }));
            }
        });
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Not found' }));
});

server.listen(PORT, '127.0.0.1', () => {
    console.log(`Compile service listening on http://127.0.0.1:${PORT} (workspace: ${path.resolve(WORKSPACE_ROOT)})`);
});
