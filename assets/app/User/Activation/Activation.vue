<script setup lang="ts">
import { megio } from 'megio-api';
import { ref } from 'vue';
import Spinner from '@/assets/app-ui/Loading/Spinner.vue';
import ErrorIcon from '@/assets/app-ui/Icons/ErrorIcon.vue';
import Button from '@/assets/app-ui/Buttons/Button.vue';
import Logo from '@/assets/app/Logo/Logo.vue';

const { token } = defineProps<{
	token: string;
}>();

const isLoading = ref<boolean>(true);
const error = ref<string>('');

const activateUser = async () => {
	isLoading.value = true;
	error.value = '';

	const response = await megio.fetch('api/v1/user/activate', {
		method: 'POST',
		body: JSON.stringify({ token }),
	});

	isLoading.value = false;

	if (response.status === 200) {
		window.toast.asleep();
		window.toast.add(
			'success',
			'Your account has been successfully activated. You can now log in.',
			20 * 1000,
		);
		window.location.replace('/user/login');
		return;
	}

	const err = response.errors as {
		token?: string;
		general?: string;
	};
	error.value =
		err.token ??
		err.general ??
		'An error occurred during activation. Please try again.';
};

activateUser();
</script>

<template>
    <div class="min-h-screen bg-gray-50 relative px-4 sm:px-6 lg:px-8">
        <div class="absolute top-8 left-1/2 transform -translate-x-1/2 md:left-4 md:transform-none md:translate-x-0 lg:left-8">
            <Logo linkTo="/" size="sm" />
        </div>
        <div class="min-h-screen flex items-center justify-center">
            <div class="max-w-md w-full">
                <!-- Loading State -->
                <div v-if="isLoading" class="text-center">
                    <Spinner class="mx-auto h-12 w-12 text-blue-600" />
                    <p class="mt-4 text-gray-600">Activating your account...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="space-y-6">
                    <div class="text-center">
                        <h2 class="text-3xl font-extrabold text-gray-900">Activation Failed</h2>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0 text-red-800">
                                <ErrorIcon class="w-5 h-5" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    {{ error }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <Button
                        @click="activateUser"
                        variant="primary"
                        size="lg"
                        class="w-full"
                    >
                        Try Again
                    </Button>

                    <div class="text-center">
                        <a href="/user/login" class="text-sm text-gray-500 hover:text-gray-700">
                            ← Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
