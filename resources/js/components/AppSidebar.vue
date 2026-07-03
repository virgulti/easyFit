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
    UtensilsCrossed,
    ClipboardList,
} from '@lucide/vue';
import MealLogController from '@/actions/App/Http/Controllers/MealLogController';
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
        title: 'New measurement',
        href: MeasurementController.create(),
        icon: CirclePlus,
    },
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'History',
        href: MeasurementController.index(),
        icon: History,
    },
    {
        title: 'Meals',
        href: MealLogController.index(),
        icon: UtensilsCrossed,
    },
    {
        title: 'Manage meals',
        href: MealLogController.manage(),
        icon: ClipboardList,
    },
];

// Read-only chart pages: grouped in the footer, alongside the BMI guide,
// since the user doesn't interact with them like the items above.
const footerNavItems: NavItem[] = [
    {
        title: 'Progress history',
        href: progressHistory(),
        icon: TrendingUp,
    },
    {
        title: 'Weight history',
        href: weightHistory(),
        icon: Weight,
    },
    {
        title: 'Fat weight history',
        href: fatWeightHistory(),
        icon: Flame,
    },
    {
        title: 'Muscle weight history',
        href: muscleWeightHistory(),
        icon: Dumbbell,
    },
    {
        title: 'BMI progress history',
        href: bmiProgressHistory(),
        icon: Gauge,
    },
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
