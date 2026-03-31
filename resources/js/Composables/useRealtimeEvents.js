/**
 * WebSocket Real-time Events Composable
 * Handles Laravel Echo connection and event listening
 */

import { ref } from 'vue';
import Echo from 'laravel-echo';

let echoInstance = null;

export const useRealtimeEvents = () => {
    const isConnected = ref(false);
    const listeners = ref(new Map());

    /**
     * Initialize Laravel Echo with Reverb connection
     */
    const initializeEcho = () => {
        if (echoInstance) {
            return echoInstance;
        }

        try {
            echoInstance = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY || 'default-app-key',
                wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
                wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
                wssPort: import.meta.env.VITE_REVERB_PORT || 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
                enabledTransports: ['ws', 'wss'],
            });

            isConnected.value = true;
            console.log('🔌 WebSocket connected via Reverb');

            return echoInstance;
        } catch (error) {
            console.error('Failed to initialize Echo:', error);
            isConnected.value = false;
            return null;
        }
    };

    /**
     * Listen to a public channel event
     */
    const listenToApplications = (callback) => {
        const echo = initializeEcho();
        if (!echo) return;

        echo.channel('applications')
            .listen('application.submitted', (data) => {
                console.log('📨 Application submitted:', data);
                callback({
                    type: 'application.submitted',
                    data,
                });
            })
            .listen('application.status-changed', (data) => {
                console.log('📊 Application status changed:', data);
                callback({
                    type: 'application.status-changed',
                    data,
                });
            });

        listeners.value.set('applications', callback);
    };

    /**
     * Stop listening to applications channel
     */
    const stopListeningToApplications = () => {
        const echo = echoInstance;
        if (!echo) return;

        echo.leaveChannel('applications');
        listeners.value.delete('applications');
    };

    /**
     * Listen to private channel for specific user
     */
    const listenToUserNotifications = (userId, callback) => {
        const echo = initializeEcho();
        if (!echo) return;

        const channelName = `private.user.${userId}`;

        echo.private(channelName)
            .listen('application.status-changed', (data) => {
                console.log('📌 Personal notification:', data);
                callback({
                    type: 'personal.status-changed',
                    data,
                });
            });

        listeners.value.set(channelName, callback);
    };

    /**
     * Disconnect from WebSocket
     */
    const disconnect = () => {
        if (echoInstance) {
            echoInstance.disconnect();
            echoInstance = null;
            isConnected.value = false;
            listeners.value.clear();
            console.log('❌ WebSocket disconnected');
        }
    };

    /**
     * Get Echo instance
     */
    const getEcho = () => {
        return echoInstance || initializeEcho();
    };

    return {
        isConnected,
        initializeEcho,
        listenToApplications,
        stopListeningToApplications,
        listenToUserNotifications,
        disconnect,
        getEcho,
    };
};
