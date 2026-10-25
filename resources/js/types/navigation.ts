import type { Component } from 'vue';
import type { Method } from '@inertiajs/core';
import { InertiaLinkProps } from '@inertiajs/vue3';

export interface NavDefinition {
    key: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: Component;
    exact?: boolean;
    method?: Method;
}
