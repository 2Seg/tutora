<script lang="ts" setup>
import { computed, ref } from 'vue';
import UserConversationList from '@/components/user/UserConversationList.vue';
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
        <UserConversationList
            :selected-student-id="selectedId"
            :students="students"
            class="w-72 shrink-0"
            @select="selectedId = $event"
        />

        <template v-if="selected">
            <div class="flex min-w-0 flex-1 flex-col gap-3">
                <MessageThread
                    :key="selected.id"
                    :auth-user-id="authUserId"
                    :messages="messagesByStudent[selected.id] ?? []"
                    class="min-h-0 flex-1 rounded-xl bg-card"
                />
                <MessageComposer class="shrink-0 min-w-full" />
            </div>

            <aside class="w-80 shrink-0 overflow-y-auto">
                <StudentCard :student="selected" />
            </aside>
        </template>
    </div>
</template>
