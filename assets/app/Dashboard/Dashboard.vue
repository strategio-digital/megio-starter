<script setup lang="ts">
import { megio } from 'megio-api';
import { ref } from 'vue';
import Navbar from '@/assets/app/Navbar/Navbar.vue';
import Spinner from '@/assets/app-ui/Loading/Spinner.vue';
import ErrorIcon from '@/assets/app-ui/Icons/ErrorIcon.vue';
import UserInfo from '@/assets/app/Dashboard/components/UserInfo.vue';
import type { User } from '@/assets/ts/Entities/User';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

const { t, posix } = useTranslation();

type DashboardData = {
	user: User;
};

const dashboardData = ref<DashboardData | null>(null);
const isLoading = ref<boolean>(true);
const error = ref<string>('');

const loadDashboardData = async () => {
	isLoading.value = true;
	error.value = '';

	const response = await megio.fetch(`api/v1/${posix.value}/dashboard/data`, {
		method: 'GET',
	});

	isLoading.value = false;

	if (response.status === 200) {
		dashboardData.value = response.data as DashboardData;
		return;
	}
};

loadDashboardData();
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar :user="dashboardData?.user" :title="t('dashboard.page.title')" />

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div v-if="isLoading" class="flex justify-center items-center py-12">
                    <Spinner size="lg" />
                    <span class="ml-3 text-gray-600">{{ t('app.message.loading') }}</span>
                </div>

                <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
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

                <div v-else-if="dashboardData" class="space-y-6">
                    <UserInfo :user="dashboardData.user" />
                </div>
            </div>
        </main>
    </div>
</template>
