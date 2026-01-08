import { ref, type Ref } from 'vue';
import { megio } from 'megio-api';
import type { AuthUser } from 'megio-api/types/auth';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';
import { REFRESH_INTERVAL_MS } from '@/assets/app-ui/SessionKeeper/constants/REFRESH_INTERVAL_MS';
import { ACTIVITY_EVENTS } from '@/assets/app-ui/SessionKeeper/constants/ACTIVITY_EVENTS';
import type { RefreshTokenError } from '@/assets/app-ui/SessionKeeper/types/RefreshTokenError';
import type { UseSessionKeeperReturn } from '@/assets/app-ui/SessionKeeper/types/UseSessionKeeperReturn';

// Module-level state (shared across all uses)
const isRunning: Ref<boolean> = ref<boolean>(false);
const lastActivityTime: Ref<number> = ref<number>(Date.now());
let timeoutId: ReturnType<typeof setTimeout> | null = null;

const handleActivity = (): void => {
	lastActivityTime.value = Date.now();
};

const addActivityListeners = (): void => {
	for (const event of ACTIVITY_EVENTS) {
		window.addEventListener(event, handleActivity, { passive: true });
	}
};

const removeActivityListeners = (): void => {
	for (const event of ACTIVITY_EVENTS) {
		window.removeEventListener(event, handleActivity);
	}
};

const refreshToken = async (): Promise<void> => {
	const { posix } = useTranslation();

	const resp = await megio.fetch<AuthUser, RefreshTokenError>(
		`api/v1/${posix.value}/user/refresh-token`,
		{ method: 'POST' },
	);

	if (resp.success === true) {
		localStorage.setItem('megio_user', JSON.stringify(resp.data));
		console.log('[SessionKeeper] Token refreshed successfully');
	}
};

const scheduleRefresh = (): void => {
	timeoutId = setTimeout(async (): Promise<void> => {
		const timeSinceActivity: number = Date.now() - lastActivityTime.value;

		if (timeSinceActivity < REFRESH_INTERVAL_MS) {
			await refreshToken();
		}

		if (isRunning.value === true) {
			scheduleRefresh();
		}
	}, REFRESH_INTERVAL_MS);
};

export function useSessionKeeper(): UseSessionKeeperReturn {
	const start = (): void => {
		if (isRunning.value === true) {
			return;
		}

		isRunning.value = true;
		lastActivityTime.value = Date.now();
		addActivityListeners();
		scheduleRefresh();
	};

	const stop = (): void => {
		isRunning.value = false;
		removeActivityListeners();

		if (timeoutId !== null) {
			clearTimeout(timeoutId);
			timeoutId = null;
		}
	};

	return { start, stop, isRunning };
}
