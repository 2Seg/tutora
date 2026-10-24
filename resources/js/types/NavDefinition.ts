import type { Component } from 'vue';
import type { Method } from '@inertiajs/core';

export interface NavDefinition {
    key: string;
    href: string;
    icon: Component;
    exact?: boolean;
    method?: Method;
}
