import { createInertiaApp } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome' || name === 'FileManager':
                return null;
            case name.startsWith('auth/'):
                
        }
    },
    progress: {
        color: '#4B5563',
    },
});
