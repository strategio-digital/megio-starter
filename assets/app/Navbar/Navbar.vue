<script setup lang="ts">
import { computed } from 'vue';
import { megio } from 'megio-api';
import Button from '@/assets/app-ui/Buttons/Button.vue';
import Logo from '@/assets/app/Logo/Logo.vue';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

const { t, shortCode } = useTranslation();

type Props = {
	title?: string;
	showBackButton?: boolean;
	backUrl?: string;
};

const props = defineProps<Props>();

const showBackButton = computed(() => props.showBackButton ?? false);
const backUrl = computed(
	() => props.backUrl ?? `/${shortCode.value}/dashboard`,
);

const handleLogout = async () => {
	megio.auth.logout();
	window.toast.asleep();
	window.toast.add('info', t('user.message.logged_out'));
	window.location.replace(`/${shortCode.value}/user/login`);
};

const handleBack = () => {
	window.location.href = backUrl.value;
};
</script>

<template>
  <div class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center">
          <Logo :linkTo="`/${shortCode}/dashboard`" size="sm" />
        </div>
        <Button
            v-if="showBackButton"
            variant="secondary"
            size="sm"
            @click="handleBack"
        >
          {{ t('app.button.back') }}
        </Button>
        <Button
            v-else
            variant="secondary"
            size="sm"
            @click="handleLogout"
        >
          {{ t('user.button.logout') }}
        </Button>
      </div>
    </div>
  </div>
</template>
