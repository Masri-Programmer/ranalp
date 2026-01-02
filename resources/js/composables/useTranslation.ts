import { usePage } from '@inertiajs/vue3';

export function useTranslation() {
    const page = usePage();

    /**
     * Translate a given data object based on the current locale.
     * Use this for database content that has multiple translations (e.g. {"en": "...", "es": "..."}).
     *
     * Priority:
     * 1. Current App Locale
     * 2. English ('en')
     * 3. First available key
     *
     * @param data The object containing translations (e.g. { en: 'Name', es: 'Nombre' })
     * @param fallbackLocale The fallback locale to use if current is missing (default: 'en')
     */
    const translate = (
        data: Record<string, string> | null | undefined,
        fallbackLocale: string = 'en',
    ): string => {
        if (!data || typeof data !== 'object') {
            return '';
        }

        const currentLocale = page.props.locale as string;

        // 1. Try current locale
        if (data[currentLocale]) {
            return data[currentLocale];
        }

        // 2. Try fallback locale (English)
        if (data[fallbackLocale]) {
            return data[fallbackLocale];
        }

        // 3. Fallback to the first available key
        const keys = Object.keys(data);
        if (keys.length > 0) {
            return data[keys[0]];
        }

        return '';
    };

    return {
        translate,
    };
}
