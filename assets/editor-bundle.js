import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Collaboration from '@tiptap/extension-collaboration';
import CollaborationCursor from '@tiptap/extension-collaboration-cursor';
import * as Y from 'yjs';
import { WebsocketProvider } from 'y-websocket';

document.addEventListener('DOMContentLoaded', () => {
    if (typeof wpEditorConfig === 'undefined') return;

    const postId = wpEditorConfig.postId;
    const authToken = wpEditorConfig.userToken;
    const currentUserName = wpEditorConfig.currentUserName;
    const currentUserColor = wpEditorConfig.currentUserColor || '#0073aa';

    const wsProtocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';

    // 1. Initialize a clean Yjs Document state
    const ydoc = new Y.Doc();
    const xmlFragment = ydoc.getXmlFragment('default');

    // 2. Connect the provider via the Apache proxy route
    const provider = new WebsocketProvider(
        // `${wsProtocol}${window.location.host}/dmbc-editor`,
        'ws://localhost:1234',
        postId.toString(),
        ydoc,
        { params: { token: authToken } }
    );

    // 3. Initialize the Tiptap Editor instance immediately
    const editor = new Editor({
        element: document.getElementById('dmbc-tiptap-container'),
        extensions: [
            StarterKit.configure({
                history: false // Disable internal tracking so Yjs handles undo/redo stacks
            }),
            Collaboration.configure({
                document: ydoc
            }),
            CollaborationCursor.configure({
                provider: provider,
                awareness: provider.awareness,
                user: { name: currentUserName, color: currentUserColor },
            }),
        ],
    });

    // 4. Handle initial content seeding SAFELY after synchronization completes
    let dynamicSeedGuard = false;

    provider.on('synced', () => {
        if (dynamicSeedGuard) return;
        dynamicSeedGuard = true;

        // Check if the synchronized server document is brand new and empty
        if (xmlFragment.length === 0) {
            console.log("Empty collaboration workspace confirmed. Injecting WordPress database content...");
            const initialContentEl = document.getElementById('dmbc-editor-initial-content');

            if (initialContentEl && initialContentEl.innerHTML.trim() !== '') {
                // Use Tiptap's native command sequence to parse the HTML string using its own schema
                editor.commands.setContent(initialContentEl.innerHTML, false);
            }
        }
    });

    // Connection status bar tracking monitors
    provider.on('status', event => {
        const statusEl = document.getElementById('dmbc-active-users');
        if (!statusEl) return;
        if (event.status === 'connected') {
            statusEl.innerHTML = `🟢 Connected | Active Users: <strong>${provider.awareness.getStates().size}</strong>`;
        } else if (event.status === 'connecting') {
            statusEl.textContent = '⏳ Connecting to collaboration room...';
        } else {
            statusEl.textContent = '🔴 Disconnected from server';
        }
    });

    provider.awareness.on('change', () => {
        const statusEl = document.getElementById('dmbc-active-users');
        if (statusEl && provider.shouldConnect) {
            statusEl.innerHTML = `🟢 Connected | Active Users: <strong>${provider.awareness.getStates().size}</strong>`;
        }
    });
});
