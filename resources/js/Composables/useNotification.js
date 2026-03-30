import { ref, computed } from 'vue';

const notifications = ref([]);
let notificationId = 0;

export function useNotification() {
    /**
     * Add a notification
     * @param {Object} options
     * @param {String} options.type - 'success', 'error', 'warning', 'info'
     * @param {String} options.title - Notification title
     * @param {String} options.message - Notification message
     * @param {Number} options.duration - Auto-dismiss time in ms (0 = no auto-dismiss)
     */
    const addNotification = (options = {}) => {
        const {
            type = 'info',
            title = '',
            message = '',
            duration = 5000
        } = options;

        const id = notificationId++;
        const notification = {
            id,
            type,
            title,
            message,
            startTime: Date.now(),
            duration
        };

        notifications.value.push(notification);

        // Auto remove notification
        if (duration > 0) {
            setTimeout(() => {
                removeNotification(id);
            }, duration);
        }

        return id;
    };

    const removeNotification = (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    };

    const clearAll = () => {
        notifications.value = [];
    };

    const success = (title, message = '', duration = 5000) => {
        return addNotification({ type: 'success', title, message, duration });
    };

    const error = (title, message = '', duration = 7000) => {
        return addNotification({ type: 'error', title, message, duration });
    };

    const warning = (title, message = '', duration = 6000) => {
        return addNotification({ type: 'warning', title, message, duration });
    };

    const info = (title, message = '', duration = 5000) => {
        return addNotification({ type: 'info', title, message, duration });
    };

    return {
        notifications: computed(() => notifications.value),
        addNotification,
        removeNotification,
        clearAll,
        success,
        error,
        warning,
        info
    };
}
