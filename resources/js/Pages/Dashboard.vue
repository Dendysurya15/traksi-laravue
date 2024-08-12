<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";
import { Skeleton } from "@/Components/ui/skeleton";
import { useAxios } from "@vueuse/integrations/useAxios";
import { columns } from "@/Pages/HistoryLaporanP2H/column";
import DataTable from "@/Pages/HistoryLaporanP2H/data-table.vue";
import { setActionTriggered } from "@/lib/actionStateTable";
import { PaginationMeta } from "@/types/pagination";
import { LaporanP2H } from "@/types/laporanP2h";
import DetailLaporanP2H from "@/Pages/HistoryLaporanP2H/detail-laporan.vue";
import { useDateFormat, useNow } from "@vueuse/core";
import { Badge } from "@/Components/ui/badge";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import FilterSelectOptionHistoryP2H from "@/Components/FilterSelectOptionHistoryP2H.vue";
import { format } from "date-fns";
import { id } from "date-fns/locale";
import { ArrowPathIcon, SignalSlashIcon } from "@heroicons/vue/24/solid";
import ChartHistoryP2H from "@/Components/ChartHistoryP2H.vue";
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/Components/ui/accordion";

const defaultValue = "item-1";

const accordionItems = [
    {
        value: "item-2",
        title: "*Catatan",
        content:
            "Yes. It's unstyled by default, giving you freedom over the look and feel.",
    },
];
const activeFilterType = ref(null);
const date = ref();
const formatVueDatePicker = (date) => {
    return "";
};
const optionsDefault = [
    { value: "Minggu", label: "Per Minggu" },
    { value: "Bulan", label: "Per Bulan" },
    { value: "Tahun", label: "Per Tahun" },
];

const optionsTahun = [{ value: "2024", label: "2024" }];
const props = defineProps<{ data: LaporanP2H[]; jenisUnit }>();
const jenisUnit = ref(props.jenisUnit);
const data = ref<LaporanP2H[]>(props.data);

const optionsJenisUnit = jenisUnit.value.map((unit: any) => ({
    value: unit, // Replace with the correct property name if different
    label: unit, // Replace with the correct property name if different
}));

// console.log(jj);
const live_tanggal = useDateFormat(useNow(), "dddd, D MMM YYYY  HH:mm:ss", {
    locales: "id-ID",
});
const typeContentData = ref("");
const detailContentData = ref(null);
const isDetailContent = ref(false);

const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
});

const handleDateRangeSelection = (selectedRange) => {
    if (selectedRange === null) {
        clearDateFilter();
        return;
    }

    // Update the date variable with the selected range
    date.value = selectedRange;
    activeFilterType.value = "date"; // Set the active filter type to 'date'

    // Reset the select option filter
    activeFilter.value = [];
    selectOptionFilterArr.value = [];

    // Reset all filter select components

    child.value.forEach((ref) => {
        ref.resetSelectedOption(); // Ensure the method exists in your component
    });

    console.log(test.value);
    fetchChartData();
};

const clearDateFilter = () => {
    date.value = null;
    activeFilterType.value = null;
};

const clearActiveFilter = (filter) => {
    if (activeFilterType.value === "option") {
        // Remove the filter from the activeFilter array
        activeFilter.value = activeFilter.value.filter(
            (item) => item !== filter
        );
        selectOptionFilterArr.value = selectOptionFilterArr.value.filter(
            (item) => item.value !== filter
        );

        if (activeFilter.value.length === 0) {
            activeFilterType.value = null;
        }

        if (filter in test.value && child.value[test.value[filter]]) {
            child.value[test.value[filter]].resetSelectedOption();
        }

        if (filter in test.value) {
            delete test.value[filter];
        }
    } else if (activeFilterType.value === "date") {
        // Clear the date filter
        activeFilter.value = [];
        activeFilterType.value = null;
        date.value = null;

        if (datePickerRef.value) {
            datePickerRef.value.resetSelectedOption();
        }
    }

    fetchChartData();
};

const clearAllFilters = () => {
    // Clear all active filters and reset states
    activeFilter.value = [];
    activeFilterType.value = null;
    date.value = null;
    // Reset selectOptionFilterArr
    selectOptionFilterArr.value = [];
    isResetAllOptionFilter.value = true;

    child.value.forEach((ref) => {
        ref.resetSelectedOption(); // Ensure the method exists in your component
    });
};

const activeFilter = ref(null);
const isFetchingData = ref(true);
const errorFetching = ref<string | null>(null);

const formatDateRange = (dateRange) => {
    if (!dateRange || dateRange.length !== 2) {
        return "Date Range";
    }
    const [startDate, endDate] = dateRange;
    const formattedStartDate = format(startDate, "dd MMMM yyyy", {
        locale: id,
    });
    const formattedEndDate = endDate
        ? format(endDate, "dd MMMM yyyy", { locale: id })
        : "";

    return `${formattedStartDate} - ${formattedEndDate}`;
};
const child = ref(null);
const test = ref([]);
const testChild = (index) => {
    // child.value.forEach((ref) => {
    //     ref.resetSelectedOption(); // Ensure the method exists in your component
    // });
    // child.value[test.value[index]].resetSelectedOption();
};

const updatedSeries = ref([]);
const updatedXAxisCategories = ref([]);
const isChartDataLoading = ref(false);
const filterComponents = [
    {
        type: "timeline",
        defaultSelect: "Minggu",
        options: optionsDefault,
        placeholder: "Filter Chart",
    },
    {
        type: "jenis_unit",
        defaultSelect: "",
        options: optionsJenisUnit,
        placeholder: "Filter Jenis Unit",
    },
];

const filterSelectRefs = ref([]);
const selectOptionFilterArr = ref([]);

const isResetAllOptionFilter = ref(false);
async function fetchChartData() {
    try {
        isChartDataLoading.value = true; // Set loading state to true

        let params = {};

        // Check if there's an active filter
        if (activeFilterType.value === "option") {
            // Include the selected options as parameters
            selectOptionFilterArr.value.forEach((filter) => {
                params[filter.type] = filter.value;
            });
        } else if (activeFilterType.value === "date") {
            // Include the selected date range as parameters
            const [startDate, endDate] = date.value;
            params.startDate = startDate ? formatDate(startDate) : null;
            params.endDate = endDate ? formatDate(endDate) : null;
        }

        // await new Promise((resolve) => setTimeout(resolve, 1000));

        const { data: chartData, error: isError } = await useAxios(
            "/fetch-chart-history-p2h",
            {
                params,
            },
            axios
        );

        if (chartData.value) {
            // Update the chart data with the fetched data
            updatedSeries.value = chartData.value.series;
            updatedXAxisCategories.value = chartData.value.xAxis;
        }

        if (isError.value) {
            console.error("Error fetching chart data:", isError.value);
        }
    } catch (error) {
        console.error("Error fetching chart data:", error);
    } finally {
        isChartDataLoading.value = false; // Set loading state to false
    }
}

const series = ref(updatedSeries.value);

watch([updatedSeries, updatedXAxisCategories], ([newSeries]) => {
    if (newSeries !== series.value) {
        series.value = newSeries;
    }
});

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}
async function getData(page = 1, paginate = 10): Promise<void> {
    try {
        isFetchingData.value = true;
        errorFetching.value = null;

        const { data: fetchedData, error: isError } = useAxios(
            "/fetch-data-table-p2h",
            {
                params: {
                    paginate: paginate,
                    page: page,
                },
            },
            axios
        );

        watch(fetchedData, (newData) => {
            if (newData) {
                data.value = newData.data;

                pagination.value = {
                    current_page: newData.current_page,
                    last_page: newData.last_page,
                    per_page: paginate,
                    total: newData.total,
                };
                isFetchingData.value = false;
            }
        });

        watch(isError, (newError) => {
            if (newError) {
                errorFetching.value =
                    "Tidak dapat mengakses data, Pastikan Koneksi Internet Stabil!";
                isFetchingData.value = false;
            }
        });
    } catch (error) {
        console.error("Error fetching data:", error);
        errorFetching.value = "Error fetching data.";
        isFetchingData.value = false;
    }
}

const selectOption = (index, option, filterType) => {
    // Find the index of the existing filterType in the array
    const existingFilterIndex = selectOptionFilterArr.value.findIndex(
        (item) => item.type === filterType
    );

    if (existingFilterIndex !== -1) {
        // If the filterType exists, update its value
        selectOptionFilterArr.value[existingFilterIndex].value = option;
    } else {
        // If the filterType does not exist, add a new entry
        selectOptionFilterArr.value.push({ value: option, type: filterType });
    }

    // Update the activeFilterType and activeFilter based on the selected options
    if (selectOptionFilterArr.value.length > 0) {
        activeFilterType.value = "option";
        activeFilter.value = selectOptionFilterArr.value.map(
            (item) => item.value
        );

        test.value[option] = index;
        console.log(test.value);
    } else {
        activeFilterType.value = null;
        activeFilter.value = [];
    }

    fetchChartData();
};

onMounted(async () => {
    await getData();
    // const startDate = new Date();
    // const endDate = new Date(new Date().setDate(startDate.getDate() + 7));
    // date.value = [startDate, null];
});

function actionStateTableTrigger() {
    isDetailContent.value = true;
    setActionTriggered(false);
}

function handleSharedActionTypeChanged(newType) {
    typeContentData.value = newType;
}

function handleSharedActionDataChanged(newData) {
    detailContentData.value = newData;
}

function checkBackToTable() {
    isDetailContent.value = false;
}

function refreshData() {
    getData(pagination.value.current_page, pagination.value.per_page);
}
</script>
<style>
.dp__theme_light {
    --dp-primary-color: #002741;
}
</style>
<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div>
            <div class="mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden rounded-sm shadow-xl sm:rounded-lg"
                >
                    <div>
                        <div>
                            <p
                                class="px-5 pt-3 font-semibold text-2xl justify-start items-center flex"
                            >
                                Rekap Laporan P2H
                            </p>

                            <div
                                class="flex px-5 gap-2 mb-3 justify-start mt-3 items-center"
                            >
                                <!-- <FilterSelectOptionHistoryP2H
                                    @option-selected="
                                        selectOption($event, 'date')
                                    "
                                    defaultSelect="week"
                                    :options="optionsDefault"
                                    placeholder="Filter Chart"
                                    ref="filterSelectOptionDateRef"
                                />

                                <FilterSelectOptionHistoryP2H
                                    @option-selected="
                                        selectOption($event, 'jenis_unit')
                                    "
                                    defaultSelect="week"
                                    :options="optionsJenisUnit"
                                    placeholder="Filter Jenis Unit"
                                    ref="filterSelectOptionJenisUnitRef"
                                /> -->
                                <FilterSelectOptionHistoryP2H
                                    v-for="(filter, index) in filterComponents"
                                    :key="index"
                                    @option-selected="
                                        selectOption(index, $event, filter.type)
                                    "
                                    :defaultSelect="filter.defaultSelect"
                                    :options="filter.options"
                                    :placeholder="filter.placeholder"
                                    ref="child"
                                    :resetAll="isResetAllOptionFilter"
                                />
                                <div>
                                    <VueDatePicker
                                        v-model="date"
                                        range
                                        position="left"
                                        ref="dp"
                                        :enable-time-picker="false"
                                        :preview-format="formatVueDatePicker"
                                        @update:model-value="
                                            handleDateRangeSelection
                                        "
                                    >
                                        <!-- <template #action-buttons>
                                            <p
                                                @click="selectDate"
                                                class="bg-green-500 text-white px-2 py-1 rounded cursor-pointer hover:bg-green-600"
                                            >
                                                Submit
                                            </p>
                                        </template> -->
                                    </VueDatePicker>
                                </div>
                            </div>

                            <div
                                v-if="activeFilterType"
                                class="bg-[#fafafa] mx-5 rounded-sm mt-4 px-5 py-2 mb-3 ring-1 ring-gray-300 p-1 mt-2 flex items-center justify-between"
                            >
                                <div class="flex">
                                    <span class="text-gray-800 mr-2"
                                        >Filter Aktif:</span
                                    >

                                    <span
                                        v-if="activeFilterType === 'option'"
                                        v-for="(filter, index) in activeFilter"
                                        :key="filter"
                                        class="bg-green-100 text-green-500 font-medium ring-2 ring-green-200 px-2 rounded-md flex items-center mr-2"
                                    >
                                        {{ filter }}
                                        <button
                                            type="button"
                                            class="ml-2 text-white hover:text-gray-200 focus:outline-none"
                                            @click="clearActiveFilter(filter)"
                                        >
                                            <svg
                                                class="h-4 w-4 text-green-300 hover:text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </span>

                                    <span
                                        v-else-if="activeFilterType === 'date'"
                                        class="bg-green-100 text-green-500 font-medium ring-2 ring-green-200 px-2 rounded-md flex items-center"
                                    >
                                        {{ formatDateRange(date) }}
                                        <button
                                            type="button"
                                            class="ml-2 text-white hover:text-gray-200 focus:outline-none"
                                            @click="clearDateFilter"
                                        >
                                            <svg
                                                class="h-4 w-4 text-green-300 hover:text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                                <div>
                                    <svg
                                        @click="clearAllFilters"
                                        class="h-4 w-4 cursor-pointer hover:text-gray-800"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <!-- <div class="px-5">
                                <Accordion
                                    type="single"
                                    class="w-full"
                                    collapsible
                                    :default-value="defaultValue"
                                >
                                    <AccordionItem
                                        v-for="item in accordionItems"
                                        :key="item.value"
                                        :value="item.value"
                                    >
                                        <AccordionTrigger>{{
                                            item.title
                                        }}</AccordionTrigger>
                                        <AccordionContent>
                                            {{ item.content }}
                                        </AccordionContent>
                                    </AccordionItem>
                                </Accordion>
                            </div> -->
                            <div class="px-5">
                                <div
                                    v-if="isChartDataLoading"
                                    class="flex justify-center items-center h-96"
                                >
                                    <div
                                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"
                                    ></div>
                                </div>
                                <ChartHistoryP2H
                                    v-else
                                    :series="updatedSeries"
                                    :xAxisCategories="updatedXAxisCategories"
                                ></ChartHistoryP2H>
                            </div>
                        </div>
                        <div
                            class="px-5 pt-3 font-semibold text-xl justify-start items-center flex"
                        >
                            History Laporan P2H
                            <!-- - {{ live_tanggal }} -->
                            <!-- <div class="cursor-pointer ml-3 text-sm">
                                <Badge
                                    class="bg-gray-200 text-black gap-2 hover:bg-gray-300"
                                    @click="refreshData"
                                >
                                    Refresh
                                    <ArrowPathIcon
                                        class="w-5 h-5 text-green-600"
                                    />
                                </Badge>
                            </div> -->
                        </div>
                        <div v-if="isFetchingData" class="p-4">
                            <div class="space-y-2">
                                <Skeleton class="h-8 w-full bg-gray-200" />
                            </div>
                            <div class="mt-4 space-y-2">
                                <Skeleton
                                    v-for="i in 10"
                                    :key="i"
                                    class="h-12 w-full bg-gray-200"
                                />
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <Skeleton class="h-8 w-[100px] bg-gray-200" />
                                <div class="flex space-x-2">
                                    <Skeleton
                                        v-for="i in 3"
                                        :key="i"
                                        class="h-8 w-8 bg-gray-200"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="errorFetching"
                            class="flex justify-center gap-3 h-96 items-center font-bold text-xl"
                        >
                            <SignalSlashIcon class="w-5 h-5 text-green-600" />
                            <p>{{ errorFetching }}</p>
                        </div>
                        <div v-else>
                            <DataTable
                                v-if="!isDetailContent && !isFetchingData"
                                :columns="columns"
                                :data="data"
                                :pagination="pagination"
                                :per-page="pagination.per_page"
                                @fetch-data="getData"
                                @action-clicked="actionStateTableTrigger"
                                @shared-action-type-changed="
                                    handleSharedActionTypeChanged
                                "
                                @shared-action-data-changed="
                                    handleSharedActionDataChanged
                                "
                            />
                            <div v-else class="p-5">
                                <DetailLaporanP2H
                                    :data="detailContentData"
                                    :typeContent="typeContentData"
                                    @is-back-to-table="checkBackToTable"
                                    @refresh-data-table="refreshData"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
