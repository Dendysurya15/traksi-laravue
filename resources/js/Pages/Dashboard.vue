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
import {
    ArrowPathIcon,
    SignalSlashIcon,
    WifiIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps<{ data: LaporanP2H[] }>();
const data = ref<LaporanP2H[]>(props.data);

const live_tanggal = useDateFormat(useNow(), "dddd, M MMM YYYY  HH:mm:ss", {
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

const isFetchingData = ref(true);
const errorFetching = ref<string | null>(null);

async function getData(page = 1, paginate = 10): Promise<void> {
    try {
        isFetchingData.value = true;
        errorFetching.value = null;

        const { data: fetchedData, error: isError } = useAxios(
            "/fetch-data",
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

onMounted(async () => {
    await getData();
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div>
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
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
