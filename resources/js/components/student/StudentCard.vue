<script lang="ts" setup>
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Student } from '@/types/Student';
import UserAvatar from '@/components/user/UserAvatar.vue';
import { User } from '@/types/User';
import { Item, ItemActions, ItemContent, ItemDescription, ItemTitle } from '@/components/ui/item';
import { Button } from '@/components/ui/button';
import ItemHeader from '@/components/ui/item/ItemHeader.vue';
import ItemMedia from '@/components/ui/item/ItemMedia.vue';

import { Link, usePage } from '@inertiajs/vue3';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { formatEventSchedule } from '@/lib/calendar-event';
import { toDate, toWallClock } from '@/lib/calendar-utils';
import { show } from '@/routes/teacher/calendar';
import StatusBadge from '@/components/shared/calendar/StatusBadge.vue';

const props = defineProps<{ student: Student }>();
const timezone = usePage().props.auth.user.timezone;
const nextLesson = computed(() => props.student.nextLesson ?? null);

function formatNextLessonStart(options: Intl.DateTimeFormatOptions): string {
    if (!nextLesson.value) return '';
    const locale = getActiveLanguage().replace('_', '-');
    return new Intl.DateTimeFormat(locale, { ...options, timeZone: timezone }).format(
        toDate(nextLesson.value.startsAt),
    );
}

const month = computed(() => formatNextLessonStart({ month: 'short' }));
const dayOfMonth = computed(() => formatNextLessonStart({ day: 'numeric' }));
const weekday = computed(() => formatNextLessonStart({ weekday: 'long' }));
const timeRange = computed(() => nextLesson.value && formatEventSchedule(nextLesson.value, timezone).timeRange);
const calendarUrl = computed(
    () =>
        nextLesson.value &&
        show({
            query: {
                week: toWallClock(toDate(nextLesson.value.startsAt), timezone).toISOString().slice(0, 10),
                event: nextLesson.value.id,
            },
        }).url,
);
</script>

<template>
    <Card class="gap-4 border-0 bg-secondary shadow-none min-h-full">
        <CardHeader class="flex flex-row items-center gap-4">
            <UserAvatar :user="student as User" class="size-16" />
            <h1>{{ student.fullName }}</h1>
        </CardHeader>

        <CardContent class="flex flex-col gap-4">
            <Item class="flex flex-col items-start bg-primary-foreground">
                <ItemContent class="gap-4">
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">
                            {{ $t('students.card.email') }}
                        </p>
                        <p class="text-sm">{{ student.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">
                            {{ $t('students.card.phone') }}
                        </p>
                        <p class="text-sm">{{ student.phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-muted-foreground">
                            {{ $t('students.card.timezone') }}
                        </p>
                        <p class="text-sm">{{ student.timezone }}</p>
                    </div>
                </ItemContent>
            </Item>

            <Item class="rounded-2xl border-0 bg-primary-foreground">
                <ItemHeader>
                    <h4 class="font-serif text-lg">
                        {{ $t('students.card.next_lesson') }}
                    </h4>
                </ItemHeader>

                <template v-if="nextLesson">
                    <div class="flex flex-row items-center gap-4">
                        <ItemMedia class="size-14 flex-col gap-0 rounded-xl bg-secondary text-primary">
                            <span class="text-[10px] font-semibold uppercase">{{ month }}</span>
                            <span class="font-serif text-xl leading-none">{{ dayOfMonth }}</span>
                        </ItemMedia>

                        <ItemContent>
                            <ItemTitle>
                                <p>{{ nextLesson.title }}</p>
                                <StatusBadge :variant="nextLesson.status" />
                            </ItemTitle>
                            <ItemDescription> {{ weekday }} · {{ timeRange }} </ItemDescription>
                        </ItemContent>
                    </div>

                    <ItemActions>
                        <Button as-child class="font-semibold text-primary cursor-pointer" size="sm" variant="ghost">
                            <Link :href="calendarUrl!">
                                {{ $t('students.card.view_calendar') }}
                            </Link>
                        </Button>
                    </ItemActions>
                </template>

                <ItemContent v-else>
                    <ItemDescription>{{ $t('students.card.no_next_lesson') }}</ItemDescription>
                </ItemContent>
            </Item>
        </CardContent>
    </Card>
</template>
