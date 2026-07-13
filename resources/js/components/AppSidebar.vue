<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    PhLayout,
    PhUsers,
    PhTruck,
    PhAddressBook,
    PhFileText,
    PhCalendar,
    PhShoppingCart,
    PhWrench,
    PhCurrencyEur,
    PhArchive,
    PhShieldCheck,
    PhGear,
} from '@phosphor-icons/vue';
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
import { dashboard } from '@/routes';
import { index as usersIndex } from '@/routes/users';
import { index as rolesIndex } from '@/routes/roles';
import { index as clientsIndex } from '@/routes/clients';
import { index as suppliersIndex } from '@/routes/suppliers';
import { index as proposalsIndex } from '@/routes/proposals';
import { index as calendarEventsIndex } from '@/routes/calendar-events';
import { index as countriesIndex } from '@/routes/countries';
import { index as contactsIndex } from '@/routes/contacts';
import { index as contactFunctionsIndex } from '@/routes/contact-functions';
import { index as calendarTypesIndex } from '@/routes/calendar-types';
import { index as calendarActionsIndex } from '@/routes/calendar-actions';
import { index as articlesIndex } from '@/routes/articles';
import { index as vatRatesIndex } from '@/routes/vat-rates';
import { index as digitalFilesIndex } from '@/routes/digital-files';
import { index as documentCategoriesIndex } from '@/routes/document-categories';
import { edit as companyEdit } from '@/routes/company';
import type { NavItem } from '@/types';
import { toUrl } from '@/lib/utils';

// Obter a rota de clientes caso ela já tenha sido gerada
const getRoute = (fn: any) => {
    return fn ? toUrl(fn) : '#';
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: getRoute(dashboard),
        icon: PhLayout,
    },
    {
        title: 'Clientes',
        href: getRoute(clientsIndex),
        icon: PhUsers,
    },
    {
        title: 'Fornecedores',
        href: getRoute(suppliersIndex),
        icon: PhTruck,
    },
    {
        title: 'Contactos',
        href: getRoute(contactsIndex),
        icon: PhAddressBook,
    },
    {
        title: 'Propostas',
        href: getRoute(proposalsIndex),
        icon: PhFileText,
    },
    {
        title: 'Calendário',
        href: getRoute(calendarEventsIndex),
        icon: PhCalendar,
    },
    {
        title: 'Encomendas',
        icon: PhShoppingCart,
        items: [
            { title: 'Clientes', href: '#' },
            { title: 'Fornecedores', href: '#' },
        ],
    },
    {
        title: 'Ordens de Trabalho',
        href: '#',
        icon: PhWrench,
    },
    {
        title: 'Financeiro',
        icon: PhCurrencyEur,
        items: [
            { title: 'Contas Bancárias', href: '#' },
            { title: 'Conta Corrente Clientes', href: '#' },
            { title: 'Faturas Fornecedores', href: '#' },
        ],
    },
    {
        title: 'Arquivo Digital',
        href: getRoute(digitalFilesIndex),
        icon: PhArchive,
    },
    {
        title: 'Gestão de Acessos',
        icon: PhShieldCheck,
        items: [
            { title: 'Utilizadores', href: getRoute(usersIndex) },
            { title: 'Permissões', href: getRoute(rolesIndex) },
        ],
    },
    {
        title: 'Configurações',
        icon: PhGear,
        items: [
            { title: 'Entidades - Países', href: getRoute(countriesIndex) },
            { title: 'Contactos - Funções', href: getRoute(contactFunctionsIndex) },
            { title: 'Calendário - Tipos', href: getRoute(calendarTypesIndex) },
            { title: 'Calendário - Acções', href: getRoute(calendarActionsIndex) },
            { title: 'Artigos', href: getRoute(articlesIndex) },
            { title: 'Financeiro - IVA', href: getRoute(vatRatesIndex) },
            { title: 'Categorias de Documentos', href: getRoute(documentCategoriesIndex) },
            { title: 'Logs', href: '#' },
            { title: 'Empresa', href: getRoute(companyEdit) },
        ],
    },
];

const footerNavItems: NavItem[] = [
    // Remover ou ajustar caso pretenda ter links de rodapé
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="getRoute(dashboard)">
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
            <NavFooter v-if="footerNavItems.length" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
