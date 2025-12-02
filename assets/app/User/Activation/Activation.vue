<script setup lang="ts">
import { megio } from 'megio-api';
import { ref } from 'vue';
import Spinner from '@/assets/app-ui/Loading/Spinner.vue';
import ErrorIcon from '@/assets/app-ui/Icons/ErrorIcon.vue';
import Button from '@/assets/app-ui/Buttons/Button.vue';
import Logo from '@/assets/app/Logo/Logo.vue';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

const { t, posix, shortCode } = useTranslation();

const { token } = defineProps<{
	token: string;
}>();

type ActivationErrors = {
	general?: string;
	token?: string;
};

const isLoading = ref<boolean>(true);
const error = ref<string>('');

const activateUser = async () => {
	isLoading.value = true;
	error.value = '';

	const response = await megio.fetch<null, ActivationErrors>(
		`api/v1/${posix.value}/user/activate`,
		{
			method: 'POST',
			body: JSON.stringify({ token }),
		},
	);

	isLoading.value = false;

	if (response.success) {
		window.toast.asleep();
		window.toast.add(
			'success',
			t('user.message.activation_success'),
			20 * 1000,
		);
		window.location.replace(`/${shortCode.value}/user/login`);
		return;
	}

	error.value =
		response.data.token ??
		response.data.general ??
		t('user.message.activation_error');
};

activateUser();
</script>

<template>
    <div class="min-h-screen bg-gray-50 relative px-4 sm:px-6 lg:px-8">
        <div class="absolute top-8 left-1/2 transform -translate-x-1/2 md:left-4 md:transform-none md:translate-x-0 lg:left-8">
            <Logo :linkTo="`/${shortCode}`" size="sm" />
        </div>
        <div class="min-h-screen flex items-center justify-center">
            <div class="max-w-md w-full">
                <!-- Loading State -->
                <div v-if="isLoading" class="text-center">
                    <Spinner class="mx-auto h-12 w-12 text-blue-600" />
                    <p class="mt-4 text-gray-600">{{ t('user.message.activating') }}</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="space-y-6">
                    <div class="text-center">
                        <h2 class="text-3xl font-extrabold text-gray-900">{{ t('user.message.activation_failed') }}</h2>
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
                        {{ t('app.button.try_again') }}
                    </Button>

                    <div class="text-center">
                        <a :href="`/${shortCode}/user/login`" class="text-sm text-gray-500 hover:text-gray-700">
                            {{ t('app.button.back_to_login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
