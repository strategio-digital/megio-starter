import { megio, setup } from 'megio-api';

export default () => {
	const developMode = window.location.hostname === 'localhost';
	const baseUrl = developMode ? 'http://localhost:8090/' : '/';

	setup(baseUrl, errorHandler);

	function errorHandler(response: Response, errors: string[]) {
		console.error(response.status, errors);
		if (response.headers.has('X-Auth-Reject-Reason')) {
			megio.auth.logout();
			window.location.href = '/login';
		}
	}
};
