export type BmiBandColor =
    'gray' | 'green' | 'yellow' | 'dark-yellow' | 'orange' | 'red';

export type BmiBand = {
    label: string;
    color: BmiBandColor;
};

export type ChartSeries = {
    labels: string[];
    values: (number | null)[];
};

export type Measurement = {
    id: number;
    date: string;
    weight: string;
    fat_perc: string;
    muscle_perc: string;
    bmi_value: string;
    bedtime: string | null;
    sleep_minutes: number | null;
    notes: string | null;
};
