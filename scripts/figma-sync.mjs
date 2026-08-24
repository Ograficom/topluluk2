import { createHash } from 'node:crypto';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';

const fileKey = process.env.FIGMA_FILE_KEY?.trim();
const token = process.env.FIGMA_ACCESS_TOKEN?.trim();
const outputFile = process.env.GITHUB_OUTPUT;

if (!fileKey) {
    throw new Error('FIGMA_FILE_KEY is required.');
}

if (!token) {
    throw new Error('FIGMA_ACCESS_TOKEN is required.');
}

const statePath = resolve('.figma-sync/state.json');
const indexPath = resolve('.figma-sync/design-index.json');

const response = await fetch(`https://api.figma.com/v1/files/${encodeURIComponent(fileKey)}?depth=2`, {
    headers: {
        'X-Figma-Token': token,
        Accept: 'application/json',
    },
});

if (!response.ok) {
    const body = await response.text();
    throw new Error(`Figma API ${response.status}: ${body.slice(0, 1000)}`);
}

const file = await response.json();

function summarizeNode(node) {
    return {
        id: node.id,
        name: node.name,
        type: node.type,
        visible: node.visible !== false,
        childCount: Array.isArray(node.children) ? node.children.length : 0,
        children: Array.isArray(node.children)
            ? node.children.map((child) => ({
                id: child.id,
                name: child.name,
                type: child.type,
                visible: child.visible !== false,
                childCount: Array.isArray(child.children) ? child.children.length : 0,
            }))
            : [],
    };
}

const pages = Array.isArray(file.document?.children)
    ? file.document.children.map(summarizeNode)
    : [];

const snapshot = {
    fileKey,
    name: file.name ?? null,
    version: file.version ?? null,
    lastModified: file.lastModified ?? null,
    components: Object.keys(file.components ?? {}).sort(),
    componentSets: Object.keys(file.componentSets ?? {}).sort(),
    styles: Object.keys(file.styles ?? {}).sort(),
    pages,
};

const fingerprint = createHash('sha256')
    .update(JSON.stringify(snapshot))
    .digest('hex');

let previousState = null;
try {
    previousState = JSON.parse(await readFile(statePath, 'utf8'));
} catch (error) {
    if (error?.code !== 'ENOENT') {
        throw error;
    }
}

const changed = previousState?.fingerprint !== fingerprint;

await mkdir(dirname(statePath), { recursive: true });

await writeFile(indexPath, `${JSON.stringify(snapshot, null, 2)}\n`, 'utf8');
await writeFile(statePath, `${JSON.stringify({
    fileKey,
    fileName: file.name ?? null,
    version: file.version ?? null,
    lastModified: file.lastModified ?? null,
    fingerprint,
    syncedAt: new Date().toISOString(),
}, null, 2)}\n`, 'utf8');

if (outputFile) {
    await writeFile(outputFile, `changed=${changed}\nfingerprint=${fingerprint}\nversion=${file.version ?? ''}\n`, {
        encoding: 'utf8',
        flag: 'a',
    });
}

console.log(changed
    ? `Figma change detected: ${file.name ?? fileKey} (${fingerprint.slice(0, 12)})`
    : `No Figma change: ${file.name ?? fileKey}`);
