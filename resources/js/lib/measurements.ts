import type { BmiBand, BmiBandColor } from '@/types';

/**
 * Today's date in the user's local timezone, formatted as YYYY-MM-DD.
 */
export function todayIsoDate(): string {
    return new Intl.DateTimeFormat('en-CA').format(new Date());
}

/**
 * Format a decimal value with a fixed number of digits ("80.5" -> "80.5").
 */
export function formatDecimal(
    value: string | number,
    decimals: number = 1,
): string {
    return Number(value).toFixed(decimals);
}

/**
 * Format an ISO date (or datetime) string as a DD/MM/YYYY date.
 */
export function formatDate(date: string): string {
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(new Date(date));
}

/**
 * Convert minutes to an HH:mm duration string (450 -> "07:30").
 */
export function minutesToDuration(minutes: number | null): string {
    if (minutes === null) {
        return '';
    }

    const hours = String(Math.floor(minutes / 60)).padStart(2, '0');
    const remainder = String(minutes % 60).padStart(2, '0');

    return `${hours}:${remainder}`;
}

/**
 * Convert an HH:mm duration string to total minutes ("07:30" -> 450).
 */
export function durationToMinutes(duration: string): number | null {
    const match = /^(\d{1,2}):(\d{2})$/.exec(duration);

    if (match === null) {
        return null;
    }

    return Number(match[1]) * 60 + Number(match[2]);
}

/**
 * Progress score derived client-side from weight, mirroring the backend
 * formula: (85 - weight) * 1.5 + 70, rounded to one decimal.
 */
export function progressFor(weight: number): number {
    return Math.round(((85 - weight) * 1.5 + 70) * 10) / 10;
}

/**
 * BMI color band computed client-side, mirroring Measurement::bmiBand().
 */
export function bmiBandFor(bmi: number): BmiBand {
    switch (true) {
        case bmi < 18:
            return { label: 'Underweight', color: 'gray' };
        case bmi < 23:
            return { label: 'Normal', color: 'green' };
        case bmi < 25:
            return { label: 'Normal', color: 'yellow' };
        case bmi < 29:
            return { label: 'Overweight', color: 'dark-yellow' };
        case bmi < 30:
            return { label: 'Overweight', color: 'orange' };
        default:
            return { label: 'Obese', color: 'red' };
    }
}

/**
 * Tailwind classes (dark mode aware) for each BMI band color;
 * "dark-yellow" maps to the amber-600 scale.
 */
export const bmiBandBadgeClasses: Record<BmiBandColor, string> = {
    gray: 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-300',
    green: 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300',
    'dark-yellow':
        'bg-amber-600/15 text-amber-700 dark:bg-amber-600/25 dark:text-amber-500',
    orange: 'bg-orange-100 text-orange-800 dark:bg-orange-500/20 dark:text-orange-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-300',
};
