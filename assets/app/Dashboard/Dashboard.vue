<script setup lang="ts">
import { megio } from 'megio-api';
import { ref } from 'vue';
import Navbar from '@/assets/app/Navbar/Navbar.vue';
import Spinner from '@/assets/app-ui/Loading/Spinner.vue';
import ErrorIcon from '@/assets/app-ui/Icons/ErrorIcon.vue';
import type { User } from '@/assets/ts/Entities/User';

type DashboardData = {
	user: User;
};

const dashboardData = ref<DashboardData | null>(null);
const isLoading = ref<boolean>(true);
const error = ref<string>('');

const loadDashboardData = async () => {
	isLoading.value = true;
	error.value = '';

	const response = await megio.fetch('api/dashboard/data', {
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
    <Navbar :user="dashboardData?.user" title="Dashboard" />

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div v-if="isLoading" class="flex justify-center items-center py-12">
          <Spinner size="lg" />
          <span class="ml-3 text-gray-600">Načítám data...</span>
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
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Uživatel
              </h3>
              <div class="mt-5 border-t border-gray-200">
                <dl class="divide-y divide-gray-200">
                  <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                      {{ dashboardData.user.email }}
                    </dd>
                  </div>
                  <div v-if="dashboardData.user.lastLogin" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Poslední přihlášení</dt>
                    <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                      {{ dashboardData.user.lastLogin }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>