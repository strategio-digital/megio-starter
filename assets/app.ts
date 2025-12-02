// Files
import '@/assets/img/strategio.svg';
import '@/assets/img/favicon.svg';
import '@/assets/img/favicon.png';
import '@/assets/img/mail-logo.png';
import '@/assets/img/social.png';

// Styles
import '@/assets/css/tailwind.css';

// Plugins
import MegioApi from '@/assets/ts/Plugins/MegioApi.ts';
MegioApi();

// Translations
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';
const { load } = useTranslation();
load();

import { megio } from 'megio-api';
import { createApp } from 'vue';

// Toast system
import {
	addToast,
	asleep,
	awake,
	success,
	error,
	info,
	warning,
} from '@/assets/app-ui/Toast/Toast.ts';
import ToastContainer from '@/assets/app-ui/Toast/ToastContainer.vue';

// Initialize toast container
const toastContainerEl = document.getElementById('vue-toast-container');
if (toastContainerEl) {
	createApp(ToastContainer).mount(toastContainerEl);
}

// Register global toast interface
window.toast = {
	add: addToast,
	success,
	error,
	info,
	warning,
	asleep,
	awake,
};

const userLoginEl = document.getElementById('vue-user-login-form');
if (userLoginEl) {
	const LoginForm = await import('@/assets/app/User/LoginForm/LoginForm.vue');
	createApp(LoginForm.default).mount(userLoginEl);
}

const userRegisterEl = document.getElementById('vue-user-register-form');
if (userRegisterEl) {
	const RegisterForm = await import(
		'@/assets/app/User/RegisterForm/RegisterForm.vue'
	);
	createApp(RegisterForm.default).mount(userRegisterEl);
}

const userActivationEl = document.getElementById('vue-user-activation');
if (userActivationEl) {
	const UserActivation = await import(
		'@/assets/app/User/Activation/Activation.vue'
	);
	const token = String(userActivationEl.getAttribute('data-token'));
	createApp(UserActivation.default, { token }).mount(userActivationEl);
}

const userForgotPasswordEl = document.getElementById(
	'vue-user-forgot-password-form',
);
if (userForgotPasswordEl) {
	const ForgotPasswordForm = await import(
		'@/assets/app/User/ForgotPasswordForm/ForgotPasswordForm.vue'
	);
	createApp(ForgotPasswordForm.default).mount(userForgotPasswordEl);
}

const userResetPasswordEl = document.getElementById(
	'vue-user-reset-password-form',
);
if (userResetPasswordEl) {
	const ResetPasswordForm = await import(
		'@/assets/app/User/ResetPasswordForm/ResetPasswordForm.vue'
	);
	const token = String(userResetPasswordEl.getAttribute('data-token'));
	createApp(ResetPasswordForm.default, { token }).mount(userResetPasswordEl);
}

// Only authenticated users
if (megio.auth.user.hasRole('user')) {
	const dashboardEl = document.getElementById('vue-dashboard');
	if (dashboardEl) {
		const Dashboard = await import('@/assets/app/Dashboard/Dashboard.vue');
		createApp(Dashboard.default).mount(dashboardEl);
	}
}
