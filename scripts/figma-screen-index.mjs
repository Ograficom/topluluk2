import { createHash } from 'node:crypto';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';

const fileKey = process.env.FIGMA_FILE_KEY?.trim();
const token = process.env.FIGMA_ACCESS_TOKEN?.trim();
const outputFile = process.env.GITHUB_OUTPUT;

if (!fileKey) throw new Error('FIGMA_FILE_KEY is required.');
if (!token) throw new Error('FIGMA_ACCESS_TOKEN is required.');

const mapPath = resolve('design/figma-map.json');
const screenIndexPath = resolve('.figma-sync/screen-index.json');
const designMap = JSON.parse(await readFile(mapPath, 'utf8'));
const mappedScreens = (designMap.screens ?? []).filter((screen) => screen?.nodeId);

async function figmaFetch(pathname) {
    const response = await fetch(`https://api.figma.com/v1${pathname}`, {
        headers: {
            'X-Figma-Token': token,
            Accept: 'application/json',
        },
    });

    if (!response.ok) {
        const body = await response.text();
        throw new Error(`Figma API ${response.status}: ${body.slice(0, 1000)}`);
    }

    return response.json();
}

function round(value, digits = 2) {
    if (typeof value !== 'number' || !Number.isFinite(value)) return null;
    const factor = 10 ** digits;
    return Math.round(value * factor) / factor;
}

function compactPaint(paint) {
    if (!paint || paint.visible === false) return null;
    if (paint.type !== 'SOLID') return { type: paint.type, opacity: round(paint.opacity ?? 1, 3) };
    const c = paint.color ?? {};
    return {
        type: 'SOLID',
        r: round(c.r, 4),
        g: round(c.g, 4),
        b: round(c.b, 4),
        a: round(paint.opacity ?? c.a ?? 1, 4),
    };
}

function compactNode(node) {
    if (!node) return null;

    const bounds = node.absoluteBoundingBox ?? {};
    const style = node.style ?? {};
    const result = {
        id: node.id,
        name: node.name,
        type: node.type,
        visible: node.visible !== false,
        width: round(bounds.width),
        height: round(bounds.height),
        layoutMode: node.layoutMode ?? null,
        layoutWrap: node.layoutWrap ?? null,
        primaryAxisAlignItems: node.primaryAxisAlignItems ?? null,
        counterAxisAlignItems: node.counterAxisAlignItems ?? null,
        itemSpacing: round(node.itemSpacing),
        padding: [
            round(node.paddingTop),
            round(node.paddingRight),
            round(node.paddingBottom),
            round(node.paddingLeft),
        ],
        cornerRadius: round(typeof node.cornerRadius === 'number' ? node.cornerRadius : null),
        strokesIncludedInLayout: node.strokesIncludedInLayout ?? null,
        fills: Array.isArray(node.fills) ? node.fills.map(compactPaint).filter(Boolean) : [],
        strokes: Array.isArray(node.strokes) ? node.strokes.map(compactPaint).filter(Boolean) : [],
        strokeWeight: round(node.strokeWeight),
    };

    if (node.type === 'TEXT') {
        result.text = String(node.characters ?? '').slice(0, 500);
        result.textStyle = {
            fontFamily: style.fontFamily ?? null,
            fontWeight: round(style.fontWeight, 0),
            fontSize: round(style.fontSize),
            lineHeightPx: round(style.lineHeightPx),
            textAlignHorizontal: style.textAlignHorizontal ?? null,
        };
    }

    if (node.type === 'INSTANCE' || node.type === 'COMPONENT') {
        result.componentId = node.componentId ?? null;
    }

    if (Array.isArray(node.children)) {
        result.children = node.children.map(compactNode);
    }

    return result;
}

const nodesById = {};
const screenIds = mappedScreens.map((screen) => screen.nodeId);
for (let offset = 0; offset < screenIds.length; offset += 80) {
    const ids = screenIds.slice(offset, offset + 80);
    const query = new URLSearchParams({ ids: ids.join(',') });
    const payload = await figmaFetch(`/files/${encodeURIComponent(fileKey)}/nodes?${query.toString()}`);
    Object.assign(nodesById, payload.nodes ?? {});
}

let previous = null;
try {
    previous = JSON.parse(await readFile(screenIndexPath, 'utf8'));
} catch (error) {
    if (error?.code !== 'ENOENT') throw error;
}

const previousById = new Map((previous?.screens ?? []).map((screen) => [screen.nodeId, screen]));
const screens = mappedScreens.map((mapping) => {
    const document = nodesById[mapping.nodeId]?.document ?? null;
    const compact = compactNode(document);
    const hash = createHash('sha256').update(JSON.stringify(compact)).digest('hex');
    const bounds = document?.absoluteBoundingBox ?? {};

    return {
        ...mapping,
        found: Boolean(document),
        width: round(bounds.width),
        height: round(bounds.height),
        hash,
    };
});

const changedScreens = screens.filter((screen) => previousById.get(screen.nodeId)?.hash !== screen.hash);
const removedScreens = (previous?.screens ?? []).filter((screen) => !screens.some((current) => current.nodeId === screen.nodeId));
const summary = changedScreens
    .filter((screen) => !screen.name.startsWith('iOS /'))
    .map((screen) => screen.name)
    .slice(0, 30);

const index = {
    fileKey,
    generatedAt: new Date().toISOString(),
    screenCount: screens.length,
    changedScreenCount: changedScreens.length + removedScreens.length,
    screens,
};

await mkdir(dirname(screenIndexPath), { recursive: true });
await writeFile(screenIndexPath, `${JSON.stringify(index, null, 2)}\n`, 'utf8');

if (outputFile) {
    const safeSummary = summary.join(', ').replace(/[\r\n]/g, ' ').slice(0, 3500);
    await writeFile(
        outputFile,
        `screen_changed=${changedScreens.length + removedScreens.length > 0}\nchanged_screen_count=${changedScreens.length + removedScreens.length}\nchanged_screens=${safeSummary}\n`,
        { encoding: 'utf8', flag: 'a' },
    );
}

console.log(
    changedScreens.length + removedScreens.length > 0
        ? `Mapped screen changes: ${changedScreens.length + removedScreens.length}. ${summary.join(', ')}`
        : `No mapped screen structure changes across ${screens.length} screens.`,
);
