<script setup lang="ts">
import { megio } from 'megio-api';
import { reactive, ref } from 'vue';
import Button from '@/assets/app-ui/Buttons/Button.vue';
import Input from '@/assets/app-ui/Inputs/Input.vue';
import Spinner from '@/assets/app-ui/Loading/Spinner.vue';
import ErrorIcon from '@/assets/app-ui/Icons/ErrorIcon.vue';
import SuccessIcon from '@/assets/app-ui/Icons/SuccessIcon.vue';
import Logo from '@/assets/app/Logo/Logo.vue';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

const { t, posix, shortCode } = useTranslation();

type RegisterForm = {
	email: string;
	password: string;
};

type RegisterErrors = {
	general?: string;
} & Partial<Record<keyof RegisterForm, string>>;

const form = reactive<RegisterForm>({
	email: '',
	password: '',
});

const errors = ref<RegisterErrors>({});
const isLoading = ref<boolean>(false);
const isRegistrationSuccessful = ref<boolean>(false);

const clearFieldError = (field: keyof RegisterErrors) => {
	errors.value[field] = undefined;
};

const handleSubmit = async () => {
	isLoading.value = true;
	errors.value = {};

	const response = await megio.fetch<null, RegisterErrors>(
		`api/v1/${posix.value}/user/register`,
		{
			method: 'POST',
			body: JSON.stringify(form),
		},
	);

	if (response.success) {
		isRegistrationSuccessful.value = true;
		isLoading.value = false;
		return;
	}

	isLoading.value = false;
	errors.value = response.data;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 relative px-4 sm:px-6 lg:px-8">
        <div class="absolute top-8 left-1/2 transform -translate-x-1/2 md:left-4 md:transform-none md:translate-x-0 lg:left-8">
            <Logo :linkTo="`/${shortCode}`" size="sm" />
        </div>
        <div class="min-h-screen flex items-center justify-center pt-24">
            <div class="max-w-md w-full space-y-8">
                <!-- Success message -->
                <div v-if="isRegistrationSuccessful" class="text-center">
                    <div class="flex justify-center mb-6">
                        <div class="bg-green-100 rounded-full p-4">
                            <SuccessIcon class="w-10 h-10 text-green-600" />
                        </div>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                        {{ t('user.message.register_success_title') }}
                    </h2>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <p class="text-base text-gray-700">
                            {{ t('user.message.register_success_message') }}
                        </p>
                    </div>
                    <a :href="`/${shortCode}`" class="text-sm text-gray-500 hover:text-gray-700">
                        {{ t('app.button.back_to_home') }}
                    </a>
                </div>

                <!-- Registration form -->
                <div v-else>
                    <div class="text-center">
                        <h2 class="text-3xl font-extrabold text-gray-900">
                            {{ t('user.page.register.title') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ t('user.subtitle.register') }}
                        </p>
                    </div>

                    <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                        <div class="space-y-4">
                            <Input
                                v-model="form.email"
                                name="email"
                                type="email"
                                :label="t('user.field.email')"
                                :placeholder="t('user.field.email_placeholder')"
                                :error="errors.email"
                                required
                                :disabled="isLoading"
                                @update:modelValue="() => clearFieldError('email')"
                            />

                            <Input
                                v-model="form.password"
                                name="password"
                                type="password"
                                :label="t('user.field.password')"
                                :placeholder="t('user.field.password_placeholder')"
                                :error="errors.password"
                                required
                                :disabled="isLoading"
                                @update:modelValue="() => clearFieldError('password')"
                            />
                        </div>

                        <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0 text-red-800">
                                    <ErrorIcon class="w-5 h-5" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        {{ errors.general }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <Button
                            type="submit"
                            variant="primary"
                            size="lg"
                            :loading="isLoading"
                            :disabled="isLoading"
                            class="w-full"
                        >
                            <span v-if="!isLoading">{{ t('user.button.sign_up') }}</span>
                            <span v-else class="flex items-center">
                                <Spinner size="sm" color="white" class="mr-2" />
                                {{ t('user.message.registering') }}
                            </span>
                        </Button>

                        <div class="text-center mt-4">
                            <p class="text-sm text-gray-600">
                                {{ t('user.link.have_account') }}
                                <a :href="`/${shortCode}/user/login`" class="font-medium text-blue-600 hover:text-blue-500">
                                    {{ t('user.button.sign_in') }}
                                </a>
                            </p>
                        </div>

                        <div class="text-center my-6">
                            <a :href="`/${shortCode}`" class="text-sm text-gray-500 hover:text-gray-700">
                                {{ t('app.button.back_to_home') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
