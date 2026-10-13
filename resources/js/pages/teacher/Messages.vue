<script setup lang="ts">
import { computed, ref } from 'vue';
import TeacherConversationList from '@/components/teacher/TeacherConversationList.vue';
import StudentCard from '@/components/student/StudentCard.vue';
import MessageThread from '@/components/messages/MessageThread.vue';
import {
    authUserId,
    messagesByStudent,
    students,
} from '@/hardcoded-data/messages-data';
import MessageComposer from '@/components/messages/MessageComposer.vue';

const selectedId = ref<number | null>(null);
const selected = computed(() =>
    students.find((s) => s.id === selectedId.value),
);
</script>

<template>
    <div class="flex min-h-0 flex-1 gap-4">
        <TeacherConversationList
            class="w-72 shrink-0"
            :students="students"
            :selected-student-id="selectedId"
            @select="selectedId = $event"
        />

        <template v-if="selected">
            <div class="flex min-w-0 flex-1 flex-col gap-3">
                <MessageThread
                    class="min-h-0 flex-1 rounded-xl bg-card"
                    :key="selected.id"
                    :auth-user-id="authUserId"
                    :messages="messagesByStudent[selected.id] ?? []"
                />
                <MessageComposer class="shrink-0 min-w-full" />
            </div>

            <aside class="w-80 shrink-0 overflow-y-auto">
                <StudentCard :student="selected" />
            </aside>
        </template>
    </div>
</template>
