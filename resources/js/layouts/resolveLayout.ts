import { Component } from 'vue';
import TeacherLayout from '@/layouts/TeacherLayout.vue';

const layouts: Record<string, Component> = {
    'teacher/': TeacherLayout,
};

export function resolveLayout(name: string): Component | undefined {
    const prefix = Object.keys(layouts)
        .find((p) => name.startsWith(p));

    return prefix ? layouts[prefix] : undefined;
}
