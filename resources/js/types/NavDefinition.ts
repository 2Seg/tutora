import type { Component } from 'vue';

export interface NavDefinition {
    key: string;
    href: string;
    icon: Component;
    exact?: boolean;
}
