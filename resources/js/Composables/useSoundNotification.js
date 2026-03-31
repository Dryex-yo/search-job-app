/**
 * Sound Notification Utility
 * Plays audio notification sounds in response to events
 */

export const useSoundNotification = () => {
    /**
     * Create an audio context and play a simple "ting" sound
     */
    const playTingSound = () => {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const now = audioContext.currentTime;

            // Create oscillator for the "ting" sound
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Ting sound: frequency sweep from high to medium
            oscillator.frequency.setValueAtTime(1200, now);
            oscillator.frequency.exponentialRampToValueAtTime(800, now + 0.1);

            // Volume envelope: quick attack, quick decay
            gainNode.gain.setValueAtTime(0.3, now);
            gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.1);

            oscillator.start(now);
            oscillator.stop(now + 0.1);
        } catch (error) {
            console.error('Error playing sound notification:', error);
        }
    };

    /**
     * Play notification sound with optional delay
     */
    const notify = (delayMs = 0) => {
        if (delayMs > 0) {
            setTimeout(() => playTingSound(), delayMs);
        } else {
            playTingSound();
        }
    };

    return {
        playTingSound,
        notify,
    };
};
