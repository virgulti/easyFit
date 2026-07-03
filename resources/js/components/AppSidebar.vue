<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    History,
    CirclePlus,
    Scale,
    TrendingUp,
    Weight,
    Flame,
    Dumbbell,
    Gauge,
} from '@lucide/vue';
import MeasurementController from '@/actions/App/Http/Controllers/MeasurementController';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    bmiGuide,
    bmiProgressHistory,
    dashboard,
    fatWeightHistory,
    muscleWeightHistory,
    progressHistory,
    weightHistory,
} from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Nuova misura',
        href: MeasurementController.create(),
        icon: CirclePlus,
    },
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Storico',
        href: MeasurementController.index(),
        icon: History,
    },
    {
        title: 'Progress storico',
        href: progressHistory(),
        icon: TrendingUp,
    },
    {
        title: 'Peso storico',
        href: weightHistory(),
        icon: Weight,
    },
    {
        title: 'Massa grassa storico',
        href: fatWeightHistory(),
        icon: Flame,
    },
    {
        title: 'Massa muscolare storico',
        href: muscleWeightHistory(),
        icon: Dumbbell,
    },
    {
        title: 'Progress BMI storico',
        href: bmiProgressHistory(),
        icon: Gauge,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'BMI guide',
        href: bmiGuide(),
        icon: Scale,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
