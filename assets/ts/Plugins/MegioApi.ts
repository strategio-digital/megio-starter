import { megio, setup } from 'megio-api';

export default () => {
	const developMode = window.location.hostname === 'localhost';
	const baseUrl = developMode ? 'http://localhost:8090/' : '/';

	setup(baseUrl, errorHandler);

	function errorHandler(response: Response, errorData: unknown) {
		console.error(response.status, errorData);
		if (response.headers.has('X-Auth-Reject-Reason')) {
			megio.auth.logout();
			window.location.href = '/user/login';
		}
	}
};
