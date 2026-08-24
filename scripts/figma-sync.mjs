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
const mapPath = resolve('design/figma-map.json');
const generatedCssPath = resolve('public/css/figma.generated.css');

const designMap = JSON.parse(await readFile(mapPath, 'utf8'));
if (designMap.fileKey && designMap.fileKey !== fileKey) {
    throw new Error(`FIGMA_FILE_KEY does not match design/figma-map.json (${designMap.fileKey}).`);
}

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

const file = await figmaFetch(`/files/${encodeURIComponent(fileKey)}?depth=2`);

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

const foundationIds = Object.values(designMap.foundations ?? {})
    .flatMap((group) => Object.values(group ?? {}));
const componentIds = (designMap.components ?? []).map((component) => component.nodeId);
const screenIds = (designMap.screens ?? []).map((screen) => screen.nodeId);
const mappedNodeIds = [...new Set([...foundationIds, ...componentIds, ...screenIds].filter(Boolean))];

const mappedNodes = {};
for (let index = 0; index < mappedNodeIds.length; index += 80) {
    const ids = mappedNodeIds.slice(index, index + 80);
    const query = new URLSearchParams({ ids: ids.join(',') });
    const payload = await figmaFetch(`/files/${encodeURIComponent(fileKey)}/nodes?${query.toString()}`);
    Object.assign(mappedNodes, payload.nodes ?? {});
}

function documentFor(nodeId) {
    return mappedNodes[nodeId]?.document ?? null;
}

function cssNumber(value, fallback = null) {
    return typeof value === 'number' && Number.isFinite(value) ? value : fallback;
}

function round(value, digits = 2) {
    const factor = 10 ** digits;
    return Math.round(value * factor) / factor;
}

function colorToCss(color, opacity = 1) {
    if (!color || typeof color !== 'object') {
        return null;
    }

    const red = Math.round((color.r ?? 0) * 255);
    const green = Math.round((color.g ?? 0) * 255);
    const blue = Math.round((color.b ?? 0) * 255);
    const alpha = Math.max(0, Math.min(1, opacity ?? color.a ?? 1));

    if (alpha < 0.999) {
        return `rgba(${red}, ${green}, ${blue}, ${round(alpha, 3)})`;
    }

    return `#${[red, green, blue]
        .map((channel) => channel.toString(16).padStart(2, '0'))
        .join('')}`;
}

function firstSolidPaint(node, property = 'fills') {
    const paints = Array.isArray(node?.[property]) ? node[property] : [];
    return paints.find((paint) => paint?.type === 'SOLID' && paint.visible !== false) ?? null;
}

function paintColor(node, property = 'fills') {
    const paint = firstSolidPaint(node, property);
    return paint ? colorToCss(paint.color, paint.opacity ?? 1) : null;
}

function nodeRadius(node) {
    if (typeof node?.cornerRadius === 'number') {
        return node.cornerRadius;
    }

    const radii = Array.isArray(node?.rectangleCornerRadii) ? node.rectangleCornerRadii : [];
    if (radii.length && radii.every((value) => typeof value === 'number')) {
        return radii[0];
    }

    return null;
}

function firstTextNode(node) {
    if (!node) {
        return null;
    }

    if (node.type === 'TEXT') {
        return node;
    }

    for (const child of node.children ?? []) {
        const match = firstTextNode(child);
        if (match) {
            return match;
        }
    }

    return null;
}

function slugify(value) {
    return String(value ?? '')
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

const cssVariables = new Map();
const setCssVariable = (name, value) => {
    if (name && value !== null && value !== undefined && value !== '') {
        cssVariables.set(name, String(value));
    }
};

for (const [variableName, nodeId] of Object.entries(designMap.foundations?.colors ?? {})) {
    setCssVariable(variableName, paintColor(documentFor(nodeId)));
}

for (const [tokenName, nodeId] of Object.entries(designMap.foundations?.spacing ?? {})) {
    const match = String(documentFor(nodeId)?.characters ?? '').match(/-?\d+(?:\.\d+)?/);
    if (match) {
        setCssVariable(`--figma-space-${tokenName}`, `${match[0]}px`);
    }
}

for (const [tokenName, nodeId] of Object.entries(designMap.foundations?.radius ?? {})) {
    const radius = nodeRadius(documentFor(nodeId));
    if (radius !== null) {
        setCssVariable(`--figma-radius-${tokenName}`, `${round(radius)}px`);
    }
}

for (const [tokenName, nodeId] of Object.entries(designMap.foundations?.typography ?? {})) {
    const node = documentFor(nodeId);
    const style = node?.style ?? {};
    const prefix = `--figma-type-${slugify(tokenName)}`;

    if (cssNumber(style.fontSize) !== null) setCssVariable(`${prefix}-size`, `${round(style.fontSize)}px`);
    if (cssNumber(style.fontWeight) !== null) setCssVariable(`${prefix}-weight`, Math.round(style.fontWeight));
    if (cssNumber(style.lineHeightPx) !== null) setCssVariable(`${prefix}-line-height`, `${round(style.lineHeightPx)}px`);
    if (style.fontFamily) setCssVariable(`${prefix}-family`, JSON.stringify(style.fontFamily));
}

const componentIndex = [];
for (const component of designMap.components ?? []) {
    const node = documentFor(component.nodeId);
    if (!node) {
        componentIndex.push({ ...component, found: false });
        continue;
    }

    const prefix = `--figma-${slugify(component.name)}`;
    const bounds = node.absoluteBoundingBox ?? {};
    const textNode = firstTextNode(node);
    const textStyle = textNode?.style ?? {};
    const radius = nodeRadius(node);
    const background = paintColor(node);
    const border = paintColor(node, 'strokes');
    const textColor = paintColor(textNode);

    if (cssNumber(bounds.width) !== null) setCssVariable(`${prefix}-width`, `${round(bounds.width)}px`);
    if (cssNumber(bounds.height) !== null) setCssVariable(`${prefix}-height`, `${round(bounds.height)}px`);
    if (radius !== null) setCssVariable(`${prefix}-radius`, `${round(radius)}px`);
    if (cssNumber(node.itemSpacing) !== null) setCssVariable(`${prefix}-gap`, `${round(node.itemSpacing)}px`);
    if (cssNumber(node.paddingLeft) !== null) setCssVariable(`${prefix}-padding-left`, `${round(node.paddingLeft)}px`);
    if (cssNumber(node.paddingRight) !== null) setCssVariable(`${prefix}-padding-right`, `${round(node.paddingRight)}px`);
    if (cssNumber(node.paddingTop) !== null) setCssVariable(`${prefix}-padding-top`, `${round(node.paddingTop)}px`);
    if (cssNumber(node.paddingBottom) !== null) setCssVariable(`${prefix}-padding-bottom`, `${round(node.paddingBottom)}px`);
    if (cssNumber(node.strokeWeight) !== null) setCssVariable(`${prefix}-border-width`, `${round(node.strokeWeight)}px`);
    if (background) setCssVariable(`${prefix}-background`, background);
    if (border) setCssVariable(`${prefix}-border-color`, border);
    if (cssNumber(textStyle.fontSize) !== null) setCssVariable(`${prefix}-font-size`, `${round(textStyle.fontSize)}px`);
    if (cssNumber(textStyle.fontWeight) !== null) setCssVariable(`${prefix}-font-weight`, Math.round(textStyle.fontWeight));
    if (cssNumber(textStyle.lineHeightPx) !== null) setCssVariable(`${prefix}-line-height`, `${round(textStyle.lineHeightPx)}px`);
    if (textColor) setCssVariable(`${prefix}-text-color`, textColor);

    componentIndex.push({
        ...component,
        found: true,
        width: cssNumber(bounds.width),
        height: cssNumber(bounds.height),
        radius,
        background,
        border,
    });
}

const cssBody = [...cssVariables.entries()]
    .sort(([left], [right]) => left.localeCompare(right))
    .map(([name, value]) => `    ${name}: ${value};`)
    .join('\n');

const mappedCssRules = `
[data-figma-node="1:143"] {
    min-height: var(--figma-button-primary-height, 48px) !important;
    padding-left: var(--figma-button-primary-padding-left, 16px) !important;
    padding-right: var(--figma-button-primary-padding-right, 16px) !important;
    border-radius: var(--figma-button-primary-radius, 12px) !important;
    background-color: var(--figma-button-primary-background, var(--figma-color-action-primary, #0ea5e9)) !important;
    color: var(--figma-button-primary-text-color, #ffffff) !important;
    font-size: var(--figma-button-primary-font-size, 16px) !important;
    font-weight: var(--figma-button-primary-font-weight, 600) !important;
}

article.post-card[data-post-card-shell] {
    border-color: var(--figma-post-card-default-border-color, var(--figma-color-border-default, #e4e6ea)) !important;
    border-radius: var(--figma-post-card-default-radius, var(--figma-radius-lg, 16px)) !important;
    background-color: var(--figma-post-card-default-background, var(--figma-color-bg-base, #ffffff)) !important;
}
`;

const generatedCss = `/*\n * AUTO-GENERATED from Figma: ${designMap.fileName ?? file.name ?? fileKey}\n * Source: ${designMap.sourceUrl ?? fileKey}\n * Do not edit manually. Run scripts/figma-sync.mjs instead.\n */\n:root {\n${cssBody}\n}\n${mappedCssRules}`;

const snapshot = {
    fileKey,
    name: file.name ?? null,
    version: file.version ?? null,
    lastModified: file.lastModified ?? null,
    components: Object.keys(file.components ?? {}).sort(),
    componentSets: Object.keys(file.componentSets ?? {}).sort(),
    styles: Object.keys(file.styles ?? {}).sort(),
    pages,
    mappedNodeIds,
    mappedComponents: componentIndex,
};

const fingerprint = createHash('sha256')
    .update(JSON.stringify(snapshot))
    .update(generatedCss)
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
await mkdir(dirname(generatedCssPath), { recursive: true });

await writeFile(generatedCssPath, generatedCss, 'utf8');
await writeFile(indexPath, `${JSON.stringify(snapshot, null, 2)}\n`, 'utf8');
await writeFile(statePath, `${JSON.stringify({
    fileKey,
    fileName: file.name ?? null,
    version: file.version ?? null,
    lastModified: file.lastModified ?? null,
    fingerprint,
    mappedNodeCount: mappedNodeIds.length,
    syncedAt: new Date().toISOString(),
}, null, 2)}\n`, 'utf8');

if (outputFile) {
    await writeFile(outputFile, `changed=${changed}\nfingerprint=${fingerprint}\nversion=${file.version ?? ''}\n`, {
        encoding: 'utf8',
        flag: 'a',
    });
}

console.log(changed
    ? `Figma change detected: ${file.name ?? fileKey} (${fingerprint.slice(0, 12)}), ${mappedNodeIds.length} mapped nodes processed.`
    : `No Figma change: ${file.name ?? fileKey}`);
