<script lang="ts" setup>
import { UserPlus2 } from '@lucide/vue';
import { ref } from 'vue';
import { Form, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { store } from '@/routes/teacher/students';

defineProps<{ timezones: string[] }>();

const open = ref(false);
const teacherTimezone = usePage().props.auth.user.timezone;

const textFields = [
    { name: 'first_name', type: 'text', autocomplete: 'given-name' },
    { name: 'last_name', type: 'text', autocomplete: 'family-name' },
    { name: 'email', type: 'email', autocomplete: 'email' },
    { name: 'phone', type: 'tel', autocomplete: 'tel' },
] as const;

function onCreated() {
    toast.success(trans('students.create.toast.created'));
    open.value = false;
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button><UserPlus2 /> {{ trans('students.create.trigger') }}</Button>
        </PopoverTrigger>

        <PopoverContent :collision-padding="16" align="end" class="w-80" @click.stop>
            <Form #default="{ errors, processing }" :action="store()" class="flex flex-col gap-3" @success="onCreated">
                <h2>{{ trans('students.create.title') }}</h2>

                <div v-for="field in textFields" :key="field.name" class="flex flex-col gap-1">
                    <Label :for="`student-${field.name}`">{{ trans(`students.create.fields.${field.name}`) }}</Label>
                    <Input
                        :id="`student-${field.name}`"
                        :aria-invalid="!!errors[field.name]"
                        :autocomplete="field.autocomplete"
                        :name="field.name"
                        :placeholder="trans(`students.create.placeholders.${field.name}`)"
                        :type="field.type"
                    />
                    <p v-if="errors[field.name]" class="text-sm text-destructive">{{ errors[field.name] }}</p>
                </div>

                <div class="flex flex-col gap-1">
                    <Label for="student-timezone">{{ trans('students.create.fields.timezone') }}</Label>
                    <Select :default-value="teacherTimezone" name="timezone">
                        <SelectTrigger id="student-timezone" :aria-invalid="!!errors.timezone" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="timezone in timezones" :key="timezone" :value="timezone">
                                    {{ timezone }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <p v-if="errors.timezone" class="text-sm text-destructive">{{ errors.timezone }}</p>
                </div>

                <div class="flex justify-end gap-2">
                    <Button size="sm" type="button" variant="ghost" @click="open = false">
                        {{ trans('students.create.cancel') }}
                    </Button>
                    <Button :disabled="processing" size="sm">{{ trans('students.create.save') }}</Button>
                </div>
            </Form>
        </PopoverContent>
    </Popover>
</template>
