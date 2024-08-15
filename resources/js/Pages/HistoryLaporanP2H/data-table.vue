<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef } from "@tanstack/vue-table";
import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
    SortingState,
    getSortedRowModel,
    VisibilityState,
    ColumnFiltersState,
    getFilteredRowModel,
    getPaginationRowModel,
} from "@tanstack/vue-table";
import { ref, watch, computed, onMounted } from "vue";
import { valueUpdater } from "@/lib/utils";
import { Button } from "@/Components/ui/button";
import ExportHistoryP2H from "@/Components/ExportHistoryP2H.vue";
import ToastNotification from "@/Components/ToastNotification.vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from "@/Components/ui/select";
import {
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    ArrowsUpDownIcon,
    ViewColumnsIcon,
} from "@heroicons/vue/24/solid";
import { isActionTriggered } from "@/lib/actionStateTable";
import { sharedActionType, sharedActionData } from "@/lib/sharedDataState";
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";

const showToast = ref(false);
const toastTitle = ref("");
const toastColor = ref("");
const toastDescription = ref("");
const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    perPage: number;
}>();
const handleShowToast = async ({ title, description, color, id }) => {
    showToast.value = true;
    toastTitle.value = title;
    toastDescription.value = description;
    const colorNow = color === "green" ? "bg-green-500" : "bg-red-500";
    toastColor.value = colorNow;
};
const emit = defineEmits([
    "fetch-data",
    "action-clicked",
    "shared-action-type-changed",
    "shared-action-data-changed",
]);

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },

    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
    },
    getPaginationRowModel: getPaginationRowModel(),
});

const pageSize = ref(`${props.perPage}`);

const availablePageSizes = [5, 10, 20, 30, 50];

const filteredPageSizes = computed(() => {
    const total = props.pagination.total;
    return availablePageSizes.filter(
        (size) =>
            size <= total ||
            size === Math.min(...availablePageSizes.filter((s) => s > total))
    );
});

watch(pageSize, (newPageSize) => {
    table.setPageSize(parseInt(newPageSize));
    emit("fetch-data", 1, newPageSize);
});

onMounted(() => {
    table.setPageSize(parseInt(pageSize.value));
});

watch(
    () => props.data,
    () => {
        table.setPageSize(parseInt(pageSize.value));
        table.reset();
    },
    { deep: true }
);

const nextPage = () => {
    if (props.pagination.current_page < props.pagination.last_page) {
        emit("fetch-data", props.pagination.current_page + 1, pageSize.value);
    }
};

const previousPage = () => {
    if (props.pagination.current_page > 1) {
        emit("fetch-data", props.pagination.current_page - 1, pageSize.value);
    }
};

const firstPage = () => {
    emit("fetch-data", 1);
};

const lastPage = () => {
    emit("fetch-data", props.pagination.last_page);
};

const globalFilter = ref("");

watch(isActionTriggered, (newValue) => {
    emit("action-clicked");
    emit("shared-action-data-changed", sharedActionData);
    emit("shared-action-type-changed", sharedActionType);
});

const visibleColumnsCount = computed(() => {
    return table
        .getAllColumns()
        .filter(
            (column) =>
                column.getIsVisible() &&
                !["actions", "select"].includes(column.id)
        ).length;
});

watch(globalFilter, (newValue) => {
    table.setGlobalFilter(newValue || "");
});
</script>

<template>
    <div class="p-3">
        <div class="flex items-center justify-between pb-4">
            <div class="flex items-center">
                <Input
                    class="max-w-sm mr-1"
                    placeholder="Filter Tanggal Upload ..."
                    :model-value="table.getColumn('tanggal_upload')?.getFilterValue() as string"
                    @update:model-value="
                        table
                            .getColumn('tanggal_upload')
                            ?.setFilterValue($event)
                    "
                />
                <Input
                    class="max-w-sm"
                    placeholder="Search ..."
                    v-model="globalFilter"
                />
            </div>
            <div class="flex justify-center gap-2">
                <ExportHistoryP2H
                    :title="'LHP ALAT BERAT'"
                    :data="data"
                    @sendToast="handleShowToast"
                />
                <!-- <ExportHistoryP2H
                    :title="'LHP UNIT'"
                    :data="data"
                    @sendToast="handleShowToast"
                /> -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="ml-auto">
                            <ViewColumnsIcon
                                class="w-5 h-5 text-green-600 mr-2"
                            />
                            Kolom
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 ms-2 text-xs font-semibold text-green-900 bg-green-200 rounded-full"
                            >
                                {{ visibleColumnsCount }}
                            </span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuCheckboxItem
                            v-for="column in table
                                .getAllColumns()
                                .filter((column) => column.getCanHide())"
                            :key="column.id"
                            class="capitalize"
                            :checked="column.getIsVisible()"
                            @update:checked="
                                (value) => {
                                    column.toggleVisibility(!!value);
                                }
                            "
                        >
                            {{ column.id }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
        <div class="border rounded-md">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="
                                row.getIsSelected() ? 'bg-blue-200' : undefined
                            "
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="columns.length"
                                class="h-24 text-center"
                            >
                                Data tidak ditemukan!
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-between p-4">
            <div class="flex-1 text-sm text-muted-foreground">
                {{ table.getFilteredSelectedRowModel().rows.length }} of
                {{ table.getFilteredRowModel().rows.length }} row(s) selected.
            </div>
            <div class="flex items-center space-x-6 lg:space-x-8">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">Rows per page</p>

                    <Select
                        :model-value="pageSize"
                        @update:model-value="(val) => (pageSize = val)"
                    >
                        <SelectTrigger class="h-8 w-[70px]">
                            <SelectValue :placeholder="pageSize" />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem
                                v-for="size in filteredPageSizes"
                                :key="size"
                                :value="`${size}`"
                            >
                                {{ size }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div
                    class="flex w-[100px] items-center justify-center text-sm font-medium"
                >
                    Page {{ props.pagination.current_page }} of

                    {{ props.pagination.last_page }}

                    <!-- Page {{ table.getState().pagination.pageIndex + 1 }} of
                {{ table.getPageCount() }} -->
                </div>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="outline"
                        class="hidden w-8 h-8 p-0 lg:flex"
                        :disabled="props.pagination.current_page === 1"
                        @click="firstPage"
                    >
                        <span class="sr-only">Go to first page</span>
                        <ChevronDoubleLeftIcon class="w-4 h-4" />
                    </Button>
                    <Button
                        variant="outline"
                        class="w-8 h-8 p-0"
                        :disabled="props.pagination.current_page === 1"
                        @click="previousPage"
                    >
                        <span class="sr-only">Go to previous page</span>
                        <ChevronLeftIcon class="w-4 h-4" />
                    </Button>
                    <Button
                        variant="outline"
                        class="w-8 h-8 p-0"
                        :disabled="
                            props.pagination.current_page ===
                            props.pagination.last_page
                        "
                        @click="nextPage"
                    >
                        <span class="sr-only">Go to next page</span>
                        <ChevronRightIcon class="w-4 h-4" />
                    </Button>
                    <Button
                        variant="outline"
                        class="hidden w-8 h-8 p-0 lg:flex"
                        :disabled="
                            props.pagination.current_page ===
                            props.pagination.last_page
                        "
                        @click="lastPage"
                    >
                        <span class="sr-only">Go to last page</span>
                        <ChevronDoubleRightIcon class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>

    <ToastNotification
        :showToast="showToast"
        :title="toastTitle"
        :description="toastDescription"
        :color="toastColor"
        :duration="5000"
        @close-toast="showToast = false"
    />
</template>
