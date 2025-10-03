/// <reference types="vite/client" />

interface Window {
	toast: {
		add: (
			type: 'success' | 'error' | 'info' | 'warning',
			message: string,
			duration?: number | null,
		) => void;
		success: (message: string, duration?: number | null) => void;
		error: (message: string, duration?: number | null) => void;
		info: (message: string, duration?: number | null) => void;
		warning: (message: string, duration?: number | null) => void;
		asleep: () => void;
		awake: () => void;
	};
}
