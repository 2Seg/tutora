<script lang="ts" setup>
import { Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { update } from '@/routes/teacher/calendar/availabilities';
import type { Availability } from '@/types/calendar';

type Slot = Omit<Availability, 'id'>;

const props = defineProps<{ availabilities: Availability[] }>();

const isoWeekDays = [1, 2, 3, 4, 5, 6, 7];
const weekdayFormatter = new Intl.DateTimeFormat(undefined, { weekday: 'long', timeZone: 'UTC' });
const mondayOfJanuaryFirst2024 = Date.UTC(2024, 0, 1);
const millisecondsPerDay = 24 * 60 * 60 * 1000;
const weekdayName = (isoDay: number) =>
    weekdayFormatter.format(new Date(mondayOfJanuaryFirst2024 + (isoDay - 1) * millisecondsPerDay));

const open = ref(false);
const slots = ref<Slot[]>([]);

const slotsFor = (day: number) =>
    slots.value.map((slot, index) => ({ slot, index })).filter(({ slot }) => slot.dayOfWeek === day);
const addSlot = (day: number) => slots.value.push({ dayOfWeek: day, startTime: '09:00', endTime: '17:00' });
const removeSlot = (index: number) => slots.value.splice(index, 1);
const fieldName = (index: number, field: string) => `availabilities[${index}][${field}]`;

const onOpenChange = (isOpen: boolean) => {
    if (isOpen) {
        slots.value = props.availabilities.map(({ dayOfWeek, startTime, endTime }) => ({
            dayOfWeek,
            startTime,
            endTime,
        }));
    }
    open.value = isOpen;
};

const sendEmptyListWhenNoSlot = (data: Record<string, unknown>) => ({ availabilities: [], ...data });
</script>

<template>
    <Popover :open="open" @update:open="onOpenChange">
        <PopoverTrigger as-child>
            <Button size="sm" variant="outline">{{ trans('calendar.aside.update_availabilities') }}</Button>
        </PopoverTrigger>

        <PopoverContent
            :collision-padding="16"
            align="start"
            class="flex max-h-(--reka-popover-content-available-height) w-96 flex-col gap-3"
            side="right"
        >
            <Form
                #default="{ errors, processing }"
                :action="update()"
                :transform="sendEmptyListWhenNoSlot"
                class="flex min-h-0 flex-col gap-3"
                @success="open = false"
            >
                <h2>{{ trans('calendar.availability.popover.title') }}</h2>

                <div class="-mr-2 flex min-h-0 flex-col gap-3 overflow-y-auto pr-2">
                    <div v-for="day in isoWeekDays" :key="day" class="flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm capitalize">{{ weekdayName(day) }}</span>
                            <Button
                                :aria-label="trans('calendar.availability.popover.add')"
                                size="icon-sm"
                                type="button"
                                variant="ghost"
                                @click="addSlot(day)"
                            >
                                <Plus />
                            </Button>
                        </div>

                        <p v-if="slotsFor(day).length === 0" class="text-xs text-muted-foreground">
                            {{ trans('calendar.availability.unavailable') }}
                        </p>

                        <div v-for="{ slot, index } in slotsFor(day)" :key="index" class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <input :name="fieldName(index, 'day_of_week')" :value="slot.dayOfWeek" type="hidden" />
                                <Input
                                    v-model="slot.startTime"
                                    :aria-invalid="!!errors[`availabilities.${index}.start_time`]"
                                    :name="fieldName(index, 'start_time')"
                                    step="600"
                                    type="time"
                                />
                                <span class="text-muted-foreground">–</span>
                                <Input
                                    v-model="slot.endTime"
                                    :aria-invalid="!!errors[`availabilities.${index}.end_time`]"
                                    :name="fieldName(index, 'end_time')"
                                    step="600"
                                    type="time"
                                />
                                <Button
                                    :aria-label="trans('calendar.availability.popover.remove')"
                                    size="icon-sm"
                                    type="button"
                                    variant="ghost"
                                    @click="removeSlot(index)"
                                >
                                    <Trash2 />
                                </Button>
                            </div>
                            <p v-if="errors[`availabilities.${index}.start_time`]" class="text-xs text-destructive">
                                {{ errors[`availabilities.${index}.start_time`] }}
                            </p>
                            <p v-if="errors[`availabilities.${index}.end_time`]" class="text-xs text-destructive">
                                {{ errors[`availabilities.${index}.end_time`] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Button size="sm" type="button" variant="ghost" @click="open = false">
                        {{ trans('calendar.availability.popover.cancel') }}
                    </Button>
                    <Button :disabled="processing" size="sm">{{ trans('calendar.availability.popover.save') }}</Button>
                </div>
            </Form>
        </PopoverContent>
    </Popover>
</template>
