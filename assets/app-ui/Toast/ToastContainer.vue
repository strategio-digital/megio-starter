<script setup lang="ts">
import { removeToast, toasts } from './Toast';
import Toast from './Toast.vue';
</script>

<template>
	<TransitionGroup
		name="fade-toast"
		tag="div"
		class="fixed end-0 bottom-0 p-5 z-50 flex flex-col gap-3 justify-end max-w-[350px] w-full"
		:class="{
			'p-0': toasts.length === 0,
		}"
	>
		<Toast
			v-for="toast in toasts"
			:key="toast.id"
			:type="toast.type"
			@close="removeToast(toast)"
		>
			<div v-html="toast.message"></div>
		</Toast>
	</TransitionGroup>
</template>

<style scoped>
.fade-toast-enter-active,
.fade-toast-leave-active {
	transition: all 0.4s ease;
	transform: translateY(20px);
}
.fade-toast-enter-from,
.fade-toast-leave-to {
	opacity: 0;
}
.fade-toast-enter-to,
.fade-toast-leave-from {
	opacity: 1;
	transform: translateY(0);
}
</style>