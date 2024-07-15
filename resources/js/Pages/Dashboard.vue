<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";
import { useAxios } from "@vueuse/integrations/useAxios";
import { columns } from "@/Pages/HistoryLaporanP2H/column"; // Import columns from the separate file
import DataTable from "@/Pages/HistoryLaporanP2H/data-table.vue";
import { setActionTriggered } from "@/lib/actionStateTable";
import { watch } from "vue";
import { PaginationMeta } from "@/types/pagination";
import { LaporanP2H } from "@/types/laporanP2h";

import DetailLaporanP2H from "@/Pages/HistoryLaporanP2H/detail-laporan.vue";
import { useDateFormat, useNow } from "@vueuse/core";
import { Badge } from "@/Components/ui/badge";
import { ArrowPathIcon } from "@heroicons/vue/24/solid";

const props = defineProps<{ data: LaporanP2H[] }>();
const data = ref<LaporanP2H[]>(props.data);

const live_tanggal = useDateFormat(useNow(), "dddd, M MMM YYYY  HH:mm:ss", {
    locales: "id-ID",
});
const typeContentData = ref(""); // Reactive variable to store sharedActionType
const detailContentData = ref(null);
const isDetailContent = ref(false);

const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
});

const isFetchingData = ref(true);

async function getData(page = 1, paginate = 10): Promise<void> {
    try {
        const {
            data: fetchedData,
            error: isError,
            isLoading: fetchingData,
        } = useAxios(
            "/fetch-data",
            {
                params: {
                    paginate: paginate,
                    page: page,
                },
            },
            axios
        );

        // isFetchingData.value = true;

        watch(fetchedData, (newData) => {
            if (newData) {
                data.value = newData.data;

                pagination.value = {
                    current_page: newData.current_page,
                    last_page: newData.last_page,
                    per_page: newData.per_page,
                    total: newData.total,
                };
                isFetchingData.value = false;
            }
        });

        isFetchingData.value = true;
    } catch (error) {
        console.error("Error fetching data:", error);
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

<style>
.fixed-width-date {
    display: inline-block;
    width: 400px; /* Adjust the width as needed */
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div>
                        <div
                            class="px-5 pt-3 font-semibold text-2xl justify-start items-center flex"
                        >
                            History Laporan P2H -

                            {{ live_tanggal }}

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
                        <div v-if="isFetchingData" class="text-center">
                            <p>Loading data...</p>
                        </div>
                        <div v-else>
                            <DataTable
                                v-if="!isDetailContent && !isFetchingData"
                                :columns="columns"
                                :data="data"
                                :pagination="pagination"
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
