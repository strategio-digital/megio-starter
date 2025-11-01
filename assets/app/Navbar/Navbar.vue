<script setup lang="ts">
import { megio } from 'megio-api';
import Button from '@/assets/app-ui/Buttons/Button.vue';
import Logo from '@/assets/app/Logo/Logo.vue';

type Props = {
	title?: string;
	showBackButton?: boolean;
	backUrl?: string;
};

const { showBackButton = false, backUrl = '/dashboard' } = defineProps<Props>();

const handleLogout = async () => {
	megio.auth.logout();
	window.toast.asleep();
	window.toast.add('info', 'You have been logged out.');
	window.location.replace('/user/login');
};

const handleBack = () => {
	window.location.href = backUrl;
};
</script>

<template>
  <div class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center">
          <Logo linkTo="/dashboard" size="sm" />
        </div>
        <Button
            v-if="showBackButton"
            variant="secondary"
            size="sm"
            @click="handleBack"
        >
          ← Back
        </Button>
        <Button
            v-else
            variant="secondary"
            size="sm"
            @click="handleLogout"
        >
          Log out
        </Button>
      </div>
    </div>
  </div>
</template>