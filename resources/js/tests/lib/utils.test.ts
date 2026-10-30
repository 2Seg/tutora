import { describe, expect, it } from 'vitest';
import { cn } from '@/lib/utils';

describe('cn', () => {
    describe('clsx behavior (conditional class composition)', () => {
        it('joins multiple string arguments', () => {
            expect(cn('px-4', 'py-2')).toBe('px-4 py-2');
        });

        it('returns an empty string when called with no arguments', () => {
            expect(cn()).toBe('');
        });

        it('ignores falsy values', () => {
            expect(cn('px-4', false, null, undefined, 0, '')).toBe('px-4');
        });

        it('supports conditional classes via object syntax', () => {
            expect(cn({ 'px-4': true, 'py-2': false, 'font-bold': true })).toBe('px-4 font-bold');
        });

        it('supports array inputs', () => {
            expect(cn(['px-4', 'py-2'])).toBe('px-4 py-2');
        });

        it('supports nested arrays and mixed input types', () => {
            expect(cn('a', ['b', { c: true, d: false }, ['e']])).toBe('a b c e');
        });

        it('supports inline conditional expressions', () => {
            const isActive = true;
            const isDisabled = false;
            expect(cn('btn', isActive && 'btn-active', isDisabled && 'btn-disabled')).toBe('btn btn-active');
        });
    });

    describe('tailwind-merge behavior (conflict resolution)', () => {
        it('keeps the last conflicting utility', () => {
            expect(cn('px-2', 'px-4')).toBe('px-4');
        });

        it('resolves conflicts between different color utilities', () => {
            expect(cn('text-red-500', 'text-blue-500')).toBe('text-blue-500');
        });

        it('resolves conflicts across arguments of different types', () => {
            expect(cn('p-2', ['p-4'], { 'p-6': true })).toBe('p-6');
        });

        it('lets a later conditional class override an earlier one', () => {
            const isLarge = true;
            expect(cn('text-sm', isLarge && 'text-lg')).toBe('text-lg');
        });

        it('does not drop the earlier class when the override is falsy', () => {
            const isLarge = false;
            expect(cn('text-sm', isLarge && 'text-lg')).toBe('text-sm');
        });

        it('keeps non-conflicting utilities', () => {
            expect(cn('px-4', 'py-2', 'text-red-500')).toBe('px-4 py-2 text-red-500');
        });

        it('lets a more specific utility coexist with a general one when order allows', () => {
            // px-2 after p-4 overrides only horizontal padding; both are kept
            expect(cn('p-4', 'px-2')).toBe('p-4 px-2');
        });

        it('removes a specific utility when a later general one overrides it', () => {
            expect(cn('px-2', 'p-4')).toBe('p-4');
        });

        it('treats variants as separate conflict groups', () => {
            expect(cn('hover:bg-red-500', 'bg-blue-500')).toBe('hover:bg-red-500 bg-blue-500');
        });

        it('resolves conflicts within the same variant', () => {
            expect(cn('hover:bg-red-500', 'hover:bg-blue-500')).toBe('hover:bg-blue-500');
        });

        it('handles arbitrary values', () => {
            expect(cn('w-[100px]', 'w-[200px]')).toBe('w-[200px]');
        });

        it('handles important modifiers', () => {
            expect(cn('!p-2', '!p-4')).toBe('!p-4');
        });
    });

    describe('edge cases', () => {
        it('preserves unknown (non-tailwind) classes', () => {
            expect(cn('custom-class', 'another-class')).toBe('custom-class another-class');
        });

        it('keeps duplicate non-tailwind classes untouched by conflict logic', () => {
            expect(cn('foo', 'foo')).toBe('foo foo');
        });

        it('normalizes extra whitespace', () => {
            expect(cn('  px-4   py-2  ')).toBe('px-4 py-2');
        });

        it('handles a typical component pattern with overrides from props', () => {
            const base = 'inline-flex items-center rounded-md px-4 py-2 bg-primary text-white';
            const className = 'bg-red-500 px-6';
            expect(cn(base, className)).toBe('inline-flex items-center rounded-md py-2 text-white bg-red-500 px-6');
        });
    });
});
