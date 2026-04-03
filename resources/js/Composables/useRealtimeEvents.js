/**
 * WebSocket Real-time Events Composable
 * Handles Laravel Echo connection and event listening
 */

import { ref } from 'vue';

let echoInstance = null;
let echoInitialized = false;
let echoError = null;

export const useRealtimeEvents = () => {
    const isConnected = ref(false);
    const listeners = ref(new Map());

    /**
     * Initialize Laravel Echo with Reverb connection
     */
    const initializeEcho = () => {
        // Return early if we've already attempted to initialize and failed
        if (echoInitialized) {
            if (echoError) {
                console.warn('⚠️ Real-time events unavailable:', echoError);
                return null;
            }
            return echoInstance;
        }

        echoInitialized = true;

        try {
            // Use window.Echo if available (initialized in bootstrap.js)
            if (window.Echo) {
                echoInstance = window.Echo;
                isConnected.value = true;
                console.log('🔌 WebSocket connected via Reverb');
                return echoInstance;
            }

            // Fallback: Attempt to create a new Echo instance
            const Echo = window.Echo || (typeof window !== 'undefined' && window.Echo);
            
            if (!Echo) {
                throw new Error('Laravel Echo library not available - real-time updates will be disabled');
            }

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
            echoError = error.message;
            console.warn('⚠️ Real-time WebSocket unavailable - App will continue without real-time updates:', error.message);
            isConnected.value = false;
            return null;
        }
    };

    /**
     * Listen to a public channel event
     */
    const listenToApplications = (callback) => {
        try {
            const echo = initializeEcho();
            if (!echo) {
                console.warn('⚠️ Real-time events disabled. Application will work with polling only.');
                return;
            }

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
        } catch (error) {
            console.warn('⚠️ Failed to listen to applications channel:', error.message);
        }
    };

    /**
     * Stop listening to applications channel
     */
    const stopListeningToApplications = () => {
        try {
            if (!echoInstance) return;

            echoInstance.leaveChannel('applications');
            listeners.value.delete('applications');
        } catch (error) {
            console.warn('⚠️ Failed to stop listening:', error.message);
        }
    };

    /**
     * Listen to private channel for specific user
     */
    const listenToUserNotifications = (userId, callback) => {
        try {
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
        } catch (error) {
            console.warn('⚠️ Failed to listen to user notifications:', error.message);
        }
    };

    /**
     * Disconnect from WebSocket
     */
    const disconnect = () => {
        try {
            if (echoInstance) {
                echoInstance.disconnect();
                echoInstance = null;
                isConnected.value = false;
                listeners.value.clear();
                console.log('❌ WebSocket disconnected');
            }
        } catch (error) {
            console.warn('⚠️ Failed to disconnect:', error.message);
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
