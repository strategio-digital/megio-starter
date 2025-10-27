<script setup lang="ts">
import AppLogo from '@/assets/img/favicon.svg';

type Props = {
	linkTo?: string;
	size?: 'sm' | 'md' | 'lg';
};

const { linkTo = '/', size = 'md' } = defineProps<Props>();

const sizeClasses = {
	sm: {
		icon: 'w-6 h-6',
		text: 'text-lg',
	},
	md: {
		icon: 'w-8 h-8',
		text: 'text-xl',
	},
	lg: {
		icon: 'w-10 h-10',
		text: 'text-2xl',
	},
};
</script>

<template>
    <a :href="linkTo" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
        <img
            :class="`${sizeClasses[size].icon}`"
            :src="AppLogo"
            alt="WebAppName"
        />
        <h2 :class="`${sizeClasses[size].text} font-bold text-gray-900`">WebAppName</h2>
    </a>
</template>