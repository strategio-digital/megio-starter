import type { Ref } from 'vue';

export type UseSessionKeeperReturn = {
	start: () => void;
	stop: () => void;
	isRunning: Ref<boolean>;
};
