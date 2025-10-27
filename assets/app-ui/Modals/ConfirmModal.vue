<script setup lang="ts">
import { ref, watch } from 'vue';

const {
	show,
	title,
	message,
	confirmText = 'Confirm',
	cancelText = 'Cancel',
} = defineProps<{
	show: boolean;
	title: string;
	message: string;
	confirmText?: string;
	cancelText?: string;
}>();

const emit = defineEmits<{
	confirm: [];
	cancel: [];
}>();

const isVisible = ref(show);

watch(
	() => show,
	(newValue) => {
		isVisible.value = newValue;
	},
);

const handleConfirm = () => {
	emit('confirm');
	isVisible.value = false;
};

const handleCancel = () => {
	emit('cancel');
	isVisible.value = false;
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="isVisible"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <!-- Background overlay -->
                <Transition name="fade">
                    <div
                        v-if="isVisible"
                        class="fixed inset-0 bg-gray-900/50 transition-opacity"
                        @click="handleCancel"
                    ></div>
                </Transition>

                <!-- Modal panel -->
                <Transition name="scale">
                    <div
                        v-if="isVisible"
                        class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-10"
                    >
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg
                                        class="h-6 w-6 text-red-600"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        {{ title }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            {{ message }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="button"
                                @click="handleConfirm"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer"
                            >
                                {{ confirmText }}
                            </button>
                            <button
                                type="button"
                                @click="handleCancel"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm cursor-pointer disabled:cursor-not-allowed"
                            >
                                {{ cancelText }}
                            </button>
                        </div>
                    </div>
                </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 200ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 200ms ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
    transition: all 200ms ease;
}

.scale-enter-from,
.scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
