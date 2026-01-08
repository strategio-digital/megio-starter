import { megio } from 'megio-api';
import { computed, ref } from 'vue';
import IntlMessageFormat from 'intl-messageformat';
import {
	loadFromCache,
	saveToCache,
} from '@/assets/app-ui/Translations/TranslationCache';

type TranslationsResponse = {
	messages: Record<string, string>;
};

const translations = ref<Record<string, string>>({});
const currentPosix = ref<string>('');

/** POSIX locale (e.g., cs_CZ, en_US) */
const posix = computed(() => currentPosix.value);

/** BCP 47 locale for Intl APIs (e.g., cs-CZ, en-US) */
const bcp47 = computed(() => currentPosix.value.replace('_', '-'));

/** Short locale for URLs (e.g., cs, en) */
const shortCode = computed(() => currentPosix.value.substring(0, 2));

/**
 * Get fallback locales from HTML data attribute
 */
const getFallbackLocales = (): string[] => {
	const fallbackData = document.documentElement.dataset.posixFallback;
	return fallbackData ? JSON.parse(fallbackData) : [];
};

/**
 * Initialize current locale from HTML data attribute and load cached translations
 */
const initLocale = (): void => {
	if (currentPosix.value !== '') {
		return;
	}

	const posix = document.documentElement.dataset.posix;

	if (posix !== undefined && posix !== '') {
		currentPosix.value = posix;
	} else {
		const fallbackLocales = getFallbackLocales();
		currentPosix.value = fallbackLocales[0] ?? 'en_US';
	}

	// Immediately load from cache to prevent flickering
	const cached = loadFromCache(currentPosix.value);
	if (cached !== null) {
		translations.value = cached;
	}
};

export function useTranslation() {
	// Initialize locale on first use
	initLocale();

	/**
	 * Load translations from API for current locale (skips if cached)
	 */
	const load = async (): Promise<void> => {
		// Skip API call if we already have translations from cache
		if (Object.keys(translations.value).length > 0) {
			return;
		}

		const locale = currentPosix.value;
		const fallbackLocales = getFallbackLocales();
		const fallback = fallbackLocales[0] ?? 'en_US';

		const response = await megio.fetch<TranslationsResponse, null>(
			`megio/translation/fetch/${locale}`,
			{ method: 'GET' },
		);

		if (response.success) {
			translations.value = response.data.messages;
			saveToCache(locale, response.data.messages);
			return;
		}

		if (locale === fallback) {
			return;
		}

		const fallbackResponse = await megio.fetch<TranslationsResponse, null>(
			`megio/translation/fetch/${fallback}`,
			{ method: 'GET' },
		);

		if (fallbackResponse.success) {
			translations.value = fallbackResponse.data.messages;
			saveToCache(fallback, fallbackResponse.data.messages);
		}
	};

	/**
	 * Set locale and reload translations
	 */
	const setPosix = async (newPosix: string): Promise<void> => {
		currentPosix.value = newPosix;
		document.documentElement.dataset.posix = newPosix;
		document.documentElement.lang = newPosix.replace('_', '-');

		// Load from cache if available
		const cached = loadFromCache(newPosix);
		if (cached !== null) {
			translations.value = cached;
			return;
		}

		// Otherwise fetch from API
		translations.value = {};
		await load();
	};

	/**
	 * Translate a key with optional ICU MessageFormat parameters
	 */
	const t = (
		key: string,
		params?: Record<string, string | number>,
	): string => {
		const text = translations.value[key] ?? key;

		if (params === undefined) {
			return text;
		}

		const formatter = new IntlMessageFormat(text, bcp47.value);
		const result = formatter.format(params);

		if (typeof result === 'string') {
			return result;
		}

		if (typeof result === 'number') {
			return String(result);
		}

		return result.join('');
	};

	return { t, load, setPosix, posix, shortCode };
}
