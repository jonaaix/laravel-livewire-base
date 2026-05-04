import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const hmrHost = env.VITE_HMR_HOST || 'localhost';
    const hmrProtocol = env.VITE_HMR_PROTOCOL || 'ws';
    const hmrClientPort = parseInt(env.VITE_HMR_CLIENT_PORT || '5178');
    const publicOrigin = hmrProtocol === 'wss'
        ? `https://${hmrHost}`
        : `http://${hmrHost}:${hmrClientPort}`;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: 5178,
            cors: true,
            origin: publicOrigin,
            allowedHosts: [hmrHost, 'localhost'],
            hmr: {
                host: hmrHost,
                protocol: hmrProtocol,
                clientPort: hmrClientPort,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
