type CachedTranslations = {
	messages: Record<string, string>;
	timestamp: number;
};

const CACHE_KEY_PREFIX = 'megio_translations_';
const CACHE_TTL_MS = 60 * 60 * 1000; // 1 hour

/**
 * Get cache key for given locale
 */
const getCacheKey = (locale: string): string => {
	return `${CACHE_KEY_PREFIX}${locale}`;
};

/**
 * Load translations from localStorage cache (returns null if expired or missing)
 */
export const loadFromCache = (
	locale: string,
): Record<string, string> | null => {
	try {
		const cached = localStorage.getItem(getCacheKey(locale));
		if (cached === null) {
			return null;
		}

		const data: CachedTranslations = JSON.parse(cached);
		const now = Date.now();

		if (
			data.messages !== undefined &&
			data.timestamp !== undefined &&
			now - data.timestamp < CACHE_TTL_MS
		) {
			return data.messages;
		}

		return null;
	} catch {
		return null;
	}
};

/**
 * Save translations to localStorage cache
 */
export const saveToCache = (
	locale: string,
	messages: Record<string, string>,
): void => {
	try {
		const data: CachedTranslations = {
			messages,
			timestamp: Date.now(),
		};
		localStorage.setItem(getCacheKey(locale), JSON.stringify(data));
	} catch {
		// localStorage might be full or disabled, silently ignore
	}
};
