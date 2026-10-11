<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, useTable } from '@tanstack/vue-table';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import SearchInput from '@/components/shared/SearchInput.vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    features,
    type DataTableFeatures,
} from '@/components/shared/data-table/features';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        columns: ColumnDef<DataTableFeatures, any, any>[];
        data: any[];
        searchPlaceholder?: string;
        itemLabel?: string;
        pageSize?: number;
    }>(),
    { pageSize: 6 },
);

const emit = defineEmits<{
    'row-select': [row: any | null];
}>();
const selectedRowId = ref<string | null>(null);

function select(row: { id: string; original: unknown }) {
    selectedRowId.value = row.id;
    emit('row-select', row.original);
}

function clear() {
    selectedRowId.value = null;
    emit('row-select', null);
}

onMounted(() => document.addEventListener('click', clear));
onBeforeUnmount(() => document.removeEventListener('click', clear));

const table = useTable({
    features,
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    globalFilterFn: 'includesString',
    initialState: {
        pagination: { pageIndex: 0, pageSize: props.pageSize },
    },
});

const pagination = computed(() => table.atoms.pagination.get());
const total = computed(() => table.getFilteredRowModel().rows.length);
const from = computed(() =>
    total.value === 0
        ? 0
        : pagination.value.pageIndex * pagination.value.pageSize + 1,
);
const to = computed(() =>
    Math.min(
        (pagination.value.pageIndex + 1) * pagination.value.pageSize,
        total.value,
    ),
);
</script>

<template>
    <div class="space-y-4">
        <SearchInput
            class="mb-4"
            :placeholder="searchPlaceholder"
            :model-value="table.atoms.globalFilter.get() ?? ''"
            @update:model-value="table.setGlobalFilter($event)"
        />

        <div class="rounded-2xl bg-primary-foreground p-4">
            <Table>
                <TableHeader class="[&_tr]:border-0">
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                        class="border-0 hover:bg-transparent"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="h-auto px-3 pb-4 text-xs font-medium uppercase tracking-wider text-muted-foreground"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :header="header"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="group border-0 hover:bg-transparent cursor-pointer"
                            @click.stop="select(row)"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="px-3 py-2 gap-1 first:rounded-l-xl last:rounded-r-xl group-hover:bg-accent"
                                :class="{ 'bg-accent/50': selectedRowId === row.id }"
                            >
                                <FlexRender :cell="cell" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else class="border-0 hover:bg-transparent">
                        <TableCell
                            :colspan="columns.length"
                            class="h-24 text-center"
                        >
                            No results.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="mt-4 flex items-center justify-between px-3">
                <p class="text-sm text-primary">
                    Showing {{ from }}–{{ to }} of {{ total }}
                    {{ itemLabel ?? 'results' }}
                </p>

                <div class="flex items-center gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-8 text-primary"
                        :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()"
                    >
                        <ChevronLeft class="size-4" />
                    </Button>

                    <Button
                        v-for="page in table.getPageCount()"
                        :key="page"
                        variant="ghost"
                        size="icon"
                        class="size-8 text-sm"
                        :class="
                            page - 1 === pagination.pageIndex
                                ? 'bg-accent font-semibold text-primary'
                                : 'text-muted-foreground'
                        "
                        @click="table.setPageIndex(page - 1)"
                    >
                        {{ page }}
                    </Button>

                    <Button
                        variant="ghost"
                        size="icon"
                        class="size-8 text-primary"
                        :disabled="!table.getCanNextPage()"
                        @click="table.nextPage()"
                    >
                        <ChevronRight class="size-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
