import { megio, setup } from 'megio-api';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

export default () => {
	const developMode = window.location.hostname === 'localhost';
	const baseUrl = developMode ? 'http://localhost:8090/' : '/';

	setup<{ errors: string[] }>(baseUrl, errorHandler);

	function errorHandler(response: Response) {
		if (response.headers.has('X-Auth-Reject-Reason')) {
			const { shortCode } = useTranslation();
			megio.auth.logout();
			window.location.href = `/${shortCode.value}/user/login`;
		}
	}
};
