import './bootstrap';

// Load the editor only when a composer is opened, using Vite's local assets.
window.ografiLoadEditorTools = () => import('./editor-tools.js');
