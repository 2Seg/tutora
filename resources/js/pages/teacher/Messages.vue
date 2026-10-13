<script setup lang="ts">
import { computed, ref } from 'vue';
import TeacherConversationList from '@/components/teacher/TeacherConversationList.vue';
import StudentCard from '@/components/student/StudentCard.vue';
import MessageThread from '@/components/messages/MessageThread.vue';
import { authUserId, messagesByStudent, students } from '@/hardcoded-data/messages-data';

const selectedId = ref<number | null>(null);
const selected = computed(() => students.find((s) => s.id === selectedId.value));
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
            <MessageThread
                class="min-w-0 flex-1"
                :key="selected.id"
                :auth-user-id="authUserId"
                :messages="messagesByStudent[selected.id] ?? []"
            />
            <aside class="w-80 shrink-0 overflow-y-auto">
                <StudentCard :student="selected" />
            </aside>
        </template>
    </div>
</template>
