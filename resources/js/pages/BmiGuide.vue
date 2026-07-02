<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { BookOpen, Scale, ArrowLeft } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Guida BMI',
                href: '/bmi-guide',
            },
        ],
    },
});

const bmiBands = [
    {
        range: '< 18,0',
        label: 'Sottopeso (Underweight)',
        color: 'gray',
        description: 'Il peso corporeo è inferiore alla norma. Potrebbe essere opportuno consultare un nutrizionista.',
        classes: 'border-slate-200 bg-slate-50/50 text-slate-700 dark:border-slate-800 dark:bg-slate-900/30 dark:text-slate-300',
        dotClass: 'bg-slate-400',
    },
    {
        range: '18,0 - 22,9',
        label: 'Normopeso ottimale (Normal)',
        color: 'green',
        description: 'La fascia di peso ideale per la salute cardiovascolare e metabolica complessiva.',
        classes: 'border-emerald-200 bg-emerald-50/40 text-emerald-800 dark:border-emerald-900/30 dark:bg-emerald-950/20 dark:text-emerald-300',
        dotClass: 'bg-emerald-500',
    },
    {
        range: '23,0 - 24,9',
        label: 'Normopeso limite (Normal)',
        color: 'yellow',
        description: 'Peso nella norma ma al limite superiore. Ottimo per mantenere stabilità fisica.',
        classes: 'border-yellow-200 bg-yellow-50/40 text-yellow-800 dark:border-yellow-900/30 dark:bg-yellow-950/20 dark:text-yellow-300',
        dotClass: 'bg-yellow-500',
    },
    {
        range: '25,0 - 28,9',
        label: 'Sovrappeso moderato (Overweight)',
        color: 'dark-yellow',
        description: 'Fascia di sovrappeso iniziale. Monitorare alimentazione e attività fisica.',
        classes: 'border-amber-200 bg-amber-50/40 text-amber-800 dark:border-amber-900/30 dark:bg-amber-950/20 dark:text-amber-300',
        dotClass: 'bg-amber-500',
    },
    {
        range: '29,0 - 29,9',
        label: 'Sovrappeso elevato (Overweight)',
        color: 'orange',
        description: 'Fascia vicina all\'obesità. Si consiglia di adottare uno stile di vita più attivo.',
        classes: 'border-orange-200 bg-orange-50/40 text-orange-800 dark:border-orange-900/30 dark:bg-orange-950/20 dark:text-orange-300',
        dotClass: 'bg-orange-500',
    },
    {
        range: '≥ 30,0',
        label: 'Obesità (Obese)',
        color: 'red',
        description: 'Peso corporeo significativamente superiore ai limiti di salute consigliati. Si consiglia un parere medico.',
        classes: 'border-red-200 bg-red-50/40 text-red-800 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-300',
        dotClass: 'bg-red-500',
    },
];
</script>

<template>
    <Head title="Guida BMI" />

    <div class="mx-auto w-full max-w-4xl flex-1 space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Guida BMI"
                description="Legenda dei limiti dell'Indice di Massa Corporea (BMI)"
            />

            <Button variant="outline" as-child>
                <Link :href="dashboard()">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Torna alla dashboard
                </Link>
            </Button>
        </div>

        <!-- Info Card -->
        <div class="rounded-xl border border-sidebar-border bg-sidebar p-6 shadow-xs">
            <div class="flex items-start gap-4">
                <div class="rounded-lg bg-primary/10 p-2 text-primary">
                    <Scale class="h-6 w-6" />
                </div>
                <div class="space-y-1">
                    <h3 class="font-semibold text-lg">Cos'è il BMI?</h3>
                    <p class="text-sm text-muted-foreground leading-relaxed">
                        L'Indice di Massa Corporea (IMC o BMI, dall'inglese <em>Body Mass Index</em>) è un valore biometrico calcolato come rapporto tra il peso (in kg) e il quadrato dell'altezza (in metri).
                        Rappresenta un indicatore rapido dello stato di peso forma ed è diviso in fasce di colore all'interno dell'applicazione per aiutarti a visualizzare i progressi nel tempo.
                    </p>
                </div>
            </div>
        </div>

        <!-- Cards grid -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div
                v-for="band in bmiBands"
                :key="band.range"
                class="flex flex-col justify-between rounded-xl border p-5 shadow-xs transition-all duration-300 hover:scale-[1.01] hover:shadow-md"
                :class="band.classes"
            >
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold tracking-wide uppercase opacity-90">
                            {{ band.label }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full" :class="band.dotClass"></span>
                            <span class="text-xs font-mono font-semibold">{{ band.range }}</span>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed opacity-95 dark:opacity-90">
                        {{ band.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional notes -->
        <div class="rounded-xl border border-sidebar-border p-6 text-center text-xs text-muted-foreground">
            <BookOpen class="mx-auto mb-2 h-5 w-5 opacity-50" />
            <p>
                Nota: Il BMI è una stima indicativa e non tiene conto direttamente della distribuzione della massa muscolare rispetto a quella grassa.
                Per un'analisi approfondita della composizione corporea, monitora anche le percentuali di grasso e muscolo presenti nelle tabelle e nei grafici storici.
            </p>
        </div>
    </div>
</template>
