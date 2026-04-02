<template>
    <div class="galaxy-container">
        <!-- Stars background -->
        <svg class="galaxy-canvas" ref="galaxyCanvas" :viewBox="viewBox">
            <!-- Distant nebula glow -->
            <defs>
                <radialGradient id="nebula-light" cx="40%" cy="40%">
                    <stop offset="0%" :style="{ stopColor: isDarkMode ? '#6366f1' : '#a78bfa', stopOpacity: '0.15' }" />
                    <stop offset="100%" :style="{ stopColor: isDarkMode ? '#4c1d95' : '#ddd6fe', stopOpacity: '0' }" />
                </radialGradient>
                
                <radialGradient id="nebula-dark" cx="60%" cy="40%">
                    <stop offset="0%" :style="{ stopColor: isDarkMode ? '#f59e0b' : '#dbeafe', stopOpacity: '0.1' }" />
                    <stop offset="100%" :style="{ stopColor: isDarkMode ? '#7c2d12' : '#e0f2fe', stopOpacity: '0' }" />
                </radialGradient>

                <filter id="glow-light">
                    <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                    <feMerge>
                        <feMergeNode in="coloredBlur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>

                <filter id="glow-strong">
                    <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                    <feMerge>
                        <feMergeNode in="coloredBlur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>

            <!-- Nebula backgrounds -->
            <circle cx="30%" cy="30%" r="45%" fill="url(#nebula-light)" />
            <circle cx="70%" cy="60%" r="50%" fill="url(#nebula-dark)" />

            <!-- Animated orbiting objects -->
            <g :style="{ transform: `rotate(${orbit1Rotation}deg)`, transformOrigin: '50% 50%', transition: 'none' }">
                <circle cx="50%" cy="15%" r="2" :fill="isDarkMode ? '#fbbf24' : '#fcd34d'" filter="url(#glow-light)" opacity="0.8" />
            </g>

            <g :style="{ transform: `rotate(${orbit2Rotation}deg)`, transformOrigin: '50% 50%', transition: 'none' }">
                <circle cx="85%" cy="50%" r="1.5" :fill="isDarkMode ? '#c084fc' : '#d8b4fe'" filter="url(#glow-light)" opacity="0.7" />
            </g>

            <g :style="{ transform: `rotate(${orbit3Rotation}deg)`, transformOrigin: '50% 50%', transition: 'none' }">
                <circle cx="50%" cy="85%" r="1.8" :fill="isDarkMode ? '#06b6d4' : '#06b6d4'" filter="url(#glow-light)" opacity="0.6" />
            </g>

            <!-- Twinkling stars -->
            <g v-for="(star, index) in stars" :key="`star-${index}`">
                <circle 
                    :cx="`${star.x}%`" 
                    :cy="`${star.y}%`" 
                    :r="star.size"
                    :fill="getStarColor(star.color)"
                    :style="{ 
                        opacity: star.opacity,
                        animation: `twinkle ${star.duration}s ease-in-out ${star.delay}s infinite`
                    }"
                />
            </g>

            <!-- Planets/large objects -->
            <g v-for="(planet, index) in planets" :key="`planet-${index}`">
                <!-- Planet glow -->
                <circle 
                    :cx="`${planet.x}%`" 
                    :cy="`${planet.y}%`" 
                    :r="planet.size * 1.5"
                    :fill="getStarColor(planet.color)"
                    :style="{ opacity: 0.2 }"
                />
                <!-- Planet body -->
                <circle 
                    :cx="`${planet.x}%`" 
                    :cy="`${planet.y}%`" 
                    :r="planet.size"
                    :fill="getStarColor(planet.color)"
                    filter="url(#glow-strong)"
                    :style="{ 
                        opacity: 0.9,
                        animation: `float ${planet.duration}s ease-in-out ${planet.delay}s infinite`
                    }"
                />
            </g>

            <!-- Shooting stars / meteors -->
            <g v-for="(meteor, index) in meteors" :key="`meteor-${index}`">
                <line 
                    :x1="`${meteor.x1}%`"
                    :y1="`${meteor.y1}%`"
                    :x2="`${meteor.x2}%`"
                    :y2="`${meteor.y2}%`"
                    :stroke="isDarkMode ? '#fbbf24' : '#fcd34d'"
                    stroke-width="1"
                    filter="url(#glow-light)"
                    :style="{ 
                        opacity: 0,
                        animation: `meteor ${meteor.duration}s ease-in ${meteor.delay}s infinite`
                    }"
                />
            </g>
        </svg>

        <!-- Floating particles overlay -->
        <div class="galaxy-particles">
            <div 
                v-for="(particle, index) in particles"
                :key="`particle-${index}`"
                class="particle"
                :style="particleStyle(particle)"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDarkMode } = useDarkMode();
const galaxyCanvas = ref(null);

const viewBox = '0 0 100 100';

// Orbital rotation states
const orbit1Rotation = ref(0);
const orbit2Rotation = ref(0);
const orbit3Rotation = ref(0);

// Animation loop
let animationFrameId = null;
const updateOrbit = () => {
    orbit1Rotation.value = (orbit1Rotation.value + 0.3) % 360;
    orbit2Rotation.value = (orbit2Rotation.value + 0.5) % 360;
    orbit3Rotation.value = (orbit3Rotation.value + 0.2) % 360;
    animationFrameId = requestAnimationFrame(updateOrbit);
};

onMounted(() => {
    updateOrbit();
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
});

// Generate random stars
const generateStars = (count = 80) => {
    return Array.from({ length: count }).map(() => ({
        x: Math.random() * 100,
        y: Math.random() * 100,
        size: Math.random() * 0.3 + 0.1,
        color: ['white', 'blue', 'cyan', 'purple', 'yellow', 'pink'][Math.floor(Math.random() * 6)],
        opacity: Math.random() * 0.6 + 0.2,
        duration: Math.random() * 3 + 2,
        delay: Math.random() * 5
    }));
};

// Generate planets
const generatePlanets = (count = 5) => {
    return Array.from({ length: count }).map(() => ({
        x: Math.random() * 80 + 10,
        y: Math.random() * 80 + 10,
        size: Math.random() * 1.5 + 0.5,
        color: ['blue', 'cyan', 'purple', 'yellow', 'pink'][Math.floor(Math.random() * 5)],
        duration: Math.random() * 8 + 8,
        delay: Math.random() * 3
    }));
};

// Generate meteors
const generateMeteors = (count = 3) => {
    return Array.from({ length: count }).map(() => {
        const x1 = Math.random() * 100;
        const y1 = Math.random() * 100;
        const angle = Math.random() * Math.PI * 2;
        const distance = 30;
        return {
            x1,
            y1,
            x2: x1 + Math.cos(angle) * distance,
            y2: y1 + Math.sin(angle) * distance,
            duration: Math.random() * 2 + 2,
            delay: Math.random() * 8
        };
    });
};

// Generate floating particles
const generateParticles = (count = 20) => {
    return Array.from({ length: count }).map(() => ({
        x: Math.random() * 100,
        y: Math.random() * 100,
        size: Math.random() * 1.5 + 0.5,
        duration: Math.random() * 20 + 15,
        delay: Math.random() * 5,
        color: ['purple', 'blue', 'cyan', 'pink'][Math.floor(Math.random() * 4)]
    }));
};

const stars = ref(generateStars());
const planets = ref(generatePlanets());
const meteors = ref(generateMeteors());
const particles = ref(generateParticles());

// Star color function
const getStarColor = (color) => {
    if (!isDarkMode.value) {
        // Light mode colors (softer)
        const lightColors = {
            white: '#e5e7eb',
            blue: '#bfdbfe',
            cyan: '#a5f3fc',
            purple: '#d8b4fe',
            yellow: '#fef3c7',
            pink: '#fbcfe8'
        };
        return lightColors[color] || '#e5e7eb';
    } else {
        // Dark mode colors (brighter)
        const darkColors = {
            white: '#f3f4f6',
            blue: '#3b82f6',
            cyan: '#06b6d4',
            purple: '#d946ef',
            yellow: '#f59e0b',
            pink: '#ec4899'
        };
        return darkColors[color] || '#f3f4f6';
    }
};

// Particle style
const particleStyle = (particle) => {
    const baseColor = isDarkMode.value 
        ? 'rgba(168, 85, 247, 0.3)' 
        : 'rgba(168, 85, 247, 0.15)';
    
    return {
        left: `${particle.x}%`,
        top: `${particle.y}%`,
        width: `${particle.size}px`,
        height: `${particle.size}px`,
        backgroundColor: baseColor,
        animation: `float-particle ${particle.duration}s ease-in-out ${particle.delay}s infinite`
    };
};
</script>

<style scoped>
.galaxy-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
    z-index: 0;
}

.galaxy-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.galaxy-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
}

.particle {
    position: absolute;
    border-radius: 50%;
    filter: blur(0.5px);
}

/* Animations */
@keyframes twinkle {
    0%, 100% {
        opacity: 0.2;
    }
    50% {
        opacity: 0.8;
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) translateX(0px);
    }
    25% {
        transform: translateY(-5px) translateX(3px);
    }
    50% {
        transform: translateY(0px) translateX(6px);
    }
    75% {
        transform: translateY(5px) translateX(2px);
    }
}

@keyframes meteor {
    0% {
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}

@keyframes float-particle {
    0%, 100% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0;
    }
    10% {
        opacity: 0.4;
    }
    50% {
        transform: translateY(-30vh) translateX(20vw) scale(0.5);
        opacity: 0.6;
    }
    90% {
        opacity: 0.2;
    }
}
</style>
