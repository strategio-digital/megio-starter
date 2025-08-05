// Minimal setup
import 'megio-panel/styles';
import { createMegioPanel } from 'megio-panel';

createMegioPanel({
	baseUrl: window.location.host.includes('localhost')
		? 'http://localhost:8090/'
		: '/',
});

// Advanced setup
// https://megio.dev/docs/megio-panel
