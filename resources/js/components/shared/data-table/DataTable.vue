<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, useTable } from '@tanstack/vue-table';
import { Search, ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { computed } from 'vue';

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
        <div class="relative w-full max-w-sm items-center">
            <Input
                id="search"
                class="max-w-sm pl-10"
                :placeholder="searchPlaceholder ?? 'Search...'"
                :model-value="table.atoms.globalFilter.get() ?? ''"
                @update:model-value="table.setGlobalFilter($event)"
            />
            <span
                class="absolute inset-s-0 inset-y-0 flex items-center justify-center px-2"
            >
                <Search class="size-5 text-muted-foreground" />
            </span>
        </div>

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
                            class="group border-0 hover:bg-transparent"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="px-3 py-2 first:rounded-l-xl last:rounded-r-xl group-hover:bg-accent"
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
