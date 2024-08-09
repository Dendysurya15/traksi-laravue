<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watch } from "vue";
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

const date = ref();

const selectDate = () => {
    console.log(date.value);
};

const formatVueDatePicker = (date) => {
    return "";
};
import type { DateValue } from "@internationalized/date";
import {
    RangeCalendarCell,
    RangeCalendarCellTrigger,
    RangeCalendarGrid,
    RangeCalendarGridBody,
    RangeCalendarGridHead,
    RangeCalendarGridRow,
    RangeCalendarHeadCell,
    RangeCalendarHeader,
    RangeCalendarHeading,
    RangeCalendarNext,
    RangeCalendarPrev,
    RangeCalendarRoot,
} from "radix-vue";

import {
    ArrowPathIcon,
    SignalSlashIcon,
    WifiIcon,
    CalendarIcon,
} from "@heroicons/vue/24/solid";
import ChartHistoryP2H from "@/Components/ChartHistoryP2H.vue";

const props = defineProps<{ data: LaporanP2H[] }>();
const data = ref<LaporanP2H[]>(props.data);

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
    console.log("Selected Date Range:", selectedRange);
    // You can perform additional operations with the selected date range here
};
const isFetchingData = ref(true);
const errorFetching = ref<string | null>(null);

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
const activeFilter = ref(null);
const selectedOption = ref(null);
const showOptions = ref(false);
const options = [
    { value: "Minggu", label: "Minggu" },
    { value: "Bulan", label: "Bulan" },
    { value: "Tahun", label: "Tahun" },
];

const selectOption = (value) => {
    selectedOption.value = options.find(
        (option) => option.value === value
    ).label;

    showOptions.value = false;
    activeFilter.value = value;
};

const resetSelectedOption = () => {
    selectedOption.value = null;
    activeFilter.value = false;
};

const toggleOptions = () => {
    showOptions.value = !showOptions.value;
};

onMounted(async () => {
    await getData();
    const startDate = new Date();
    // const endDate = new Date(new Date().setDate(startDate.getDate() + 7));
    date.value = [startDate, null];
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
const series = [
    {
        name: "Laporan P2H",
        data: [31, 40, 28, 51, 42, 109, 100],
    },
];

const chartOptions = {
    chart: {
        height: 350,
        type: "bar",
        zoom: {
            enabled: false,
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: "55%",
            endingShape: "rounded",
            fill: {
                colors: ["#6366f1"], // Change this to your desired color
            },
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        show: true,
        width: 2,
        colors: ["transparent"],
    },
    // title: {
    //     text: "Grafik Laporan P2H",
    //     align: "left",
    // },
    grid: {
        row: {
            colors: ["#f3f3f3"], // takes an array which will be repeated on columns
            opacity: 0.5,
        },
    },
    xaxis: {
        categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
    },
};
</script>
<style>
/* .custom-select {
    cursor: pointer;
    color: var(--vp-c-text-2);
    background-color: aqua;

    margin: 0;
    display: inline-block; */
/* } */
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
                                <div class="relative">
                                    <div
                                        class="w-[180px] border border-gray-300 rounded-md px-3 py-1.5 cursor-pointer flex items-center justify-between"
                                        @click="toggleOptions"
                                    >
                                        <span
                                            v-if="selectedOption"
                                            class="text-gray-800"
                                            >{{ selectedOption }}</span
                                        >
                                        <span
                                            v-else
                                            class="text-gray-400 flex items-center"
                                        >
                                            <CalendarIcon
                                                class="mr-2 h-4 w-4 opacity-70"
                                            />
                                            Filter Chart</span
                                        >
                                        <svg
                                            class="w-4 h-4 ml-2 inline-block transition-transform"
                                            :class="{
                                                'rotate-180': showOptions,
                                            }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div
                                        v-show="showOptions"
                                        class="absolute z-10 w-[180px] mt-2 bg-white border border-gray-300 rounded-md shadow-lg"
                                    >
                                        <div class="py-1">
                                            <div
                                                v-for="option in options"
                                                :key="option.value"
                                                class="px-3 py-2 cursor-pointer hover:bg-gray-100"
                                                @click="
                                                    selectOption(option.value)
                                                "
                                            >
                                                {{ option.label }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                v-if="activeFilter"
                                class="bg-[#fafafa] mx-5 rounded-sm mt-4 px-5 py-2 mb-3 ring-1 ring-gray-300 p-1 mt-2 flex items-center justify-between"
                            >
                                <div class="flex">
                                    <span class="text-gray-800 mr-2"
                                        >Filter Aktif :</span
                                    >
                                    <span
                                        class="bg-yellow-100 text-yellow-500 font-medium ring-2 ring-yellow-200 px-2 rounded-md flex items-center"
                                    >
                                        {{ activeFilter }}
                                        <button
                                            type="button"
                                            @click="resetSelectedOption()"
                                            class="ml-2 text-white hover:text-gray-200 focus:outline-none"
                                        >
                                            <svg
                                                class="h-4 w-4 text-yellow-300 hover:text-yellow-600"
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
                                        @click="resetSelectedOption()"
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

                            <div class="px-5">
                                <ChartHistoryP2H
                                    :chartOptions="chartOptions"
                                    :series="series"
                                ></ChartHistoryP2H>
                            </div>
                        </div>
                        <div
                            class="px-5 pt-3 font-semibold text-xl justify-start items-center flex"
                        >
                            History Laporan P2H - {{ live_tanggal }}
                            <div class="cursor-pointer ml-3 text-sm">
                                <Badge
                                    class="bg-gray-200 text-black gap-2 hover:bg-gray-300"
                                    @click="refreshData"
                                >
                                    Refresh
                                    <ArrowPathIcon
                                        class="w-5 h-5 text-green-600"
                                    />
                                </Badge>
                            </div>
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
