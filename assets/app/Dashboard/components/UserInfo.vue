<script setup lang="ts">
import type { User } from '@/assets/ts/Entities/User';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

const { t } = useTranslation();

const { user } = defineProps<{
	user: User;
}>();
</script>

<template>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ t('dashboard.section.user') }}</h3>
            <div class="mt-5 border-t border-gray-200">
                <dl class="divide-y divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ t('user.field.email') }}</dt>
                        <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ user.email }}
                        </dd>
                    </div>
                    <div v-if="user.lastLogin" class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ t('user.field.last_login') }}</dt>
                        <dd class="mt-1 flex text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ user.lastLogin }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
