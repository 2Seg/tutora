<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Card, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLogo from '@/components/shared/AppLogo.vue';
import { trans } from 'laravel-vue-i18n';
import { dashboard } from '@/routes/teacher';

const props = defineProps<{ statusCode: number }>();
const translatedErrorDescription = computed(() => {
    const translationKey = 'error.' + props.statusCode;
    const translatedText = trans(translationKey);

    return translatedText === translationKey ? trans('error.default.description') : translatedText;
});

const hasPreviousPage = ref(false);

onMounted(() => {
    hasPreviousPage.value = window.history.length > 1;
});

function goBack(): void {
    window.history.back();
}
</script>

<template>
    <main class="min-h-screen flex flex-col items-center justify-center gap-2">
        <Card class="bg-card w-full max-w-sm">
            <CardHeader class="flex items-center justify-center">
                <AppLogo />
            </CardHeader>
            <CardHeader>
                <CardTitle>
                    <h1>{{ $t('error.default.title') }}</h1>
                </CardTitle>
                <CardDescription class="text-primary">
                    {{ translatedErrorDescription }}
                </CardDescription>
            </CardHeader>
            <CardFooter class="flex items-center justify-center gap-2">
                <Button v-if="hasPreviousPage" variant="outline" @click="goBack">{{ $t('error.actions.back') }}</Button>
                <Button as-child>
                    <Link :href="dashboard()">{{ $t('error.actions.home') }}</Link>
                </Button>
            </CardFooter>
        </Card>
    </main>
</template>
