import { megio, setup } from 'megio-api';

export default () => {
	const developMode = window.location.hostname === 'localhost';
	const baseUrl = developMode ? 'http://localhost:8090/' : '/';

	setup<{ errors: string[] }>(baseUrl, errorHandler);

	function errorHandler(response: Response) {
		if (response.headers.has('X-Auth-Reject-Reason')) {
			megio.auth.logout();
			window.location.href = '/user/login';
		}
	}
};
