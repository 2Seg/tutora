import { describe, expect, it } from 'vitest';
import { resolveLayout } from '@/layouts/resolveLayout';
import TeacherLayout from '@/layouts/TeacherLayout.vue';

describe('resolveLayout', () => {
    it('does not match an undefined name', () => {
        expect(resolveLayout('undefinedPrefix/')).toBeUndefined();
    });

    it('does not match a wrongly prefixed name', () => {
        expect(resolveLayout('wronglyPrefixed/teacher')).toBeUndefined();
    });

    it('resolves TeacherLayout for teacher pages ', () => {
        expect(resolveLayout('teacher/ExamplePage')).toBe(TeacherLayout);
    });
});
