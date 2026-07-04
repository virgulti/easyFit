import type { Ref } from 'vue';
import { onMounted, ref } from 'vue';

export type DecimalSeparator = 'dot' | 'comma';

export type UseNumberFormatReturn = {
    decimalSeparator: Ref<DecimalSeparator>;
    updateDecimalSeparator: (value: DecimalSeparator) => void;
};

const STORAGE_KEY = 'decimal-separator';

const getStoredDecimalSeparator = (): DecimalSeparator | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem(STORAGE_KEY) as DecimalSeparator | null;
};

/**
 * Non-reactive getter for use outside components (e.g. from lib/measurements.ts, which isn't a
 * composable and can't call onMounted/ref).
 */
export function getDecimalSeparator(): DecimalSeparator {
    return getStoredDecimalSeparator() ?? 'dot';
}

const decimalSeparator = ref<DecimalSeparator>('dot');

export function useNumberFormat(): UseNumberFormatReturn {
    onMounted(() => {
        const saved = getStoredDecimalSeparator();

        if (saved) {
            decimalSeparator.value = saved;
        }
    });

    function updateDecimalSeparator(value: DecimalSeparator): void {
        decimalSeparator.value = value;

        if (typeof window !== 'undefined') {
            localStorage.setItem(STORAGE_KEY, value);
        }
    }

    return {
        decimalSeparator,
        updateDecimalSeparator,
    };
}
