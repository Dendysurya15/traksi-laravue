<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

import { columns } from "@/Pages/HistoryLaporanP2H/column"; // Import columns from the separate file
import DataTable from "@/Pages/HistoryLaporanP2H/data-table.vue";
import { setActionTriggered } from "@/lib/actionStateTable";

import { PaginationMeta } from "@/types/pagination";
import { LaporanP2H } from "@/types/laporanP2h";

import DetailLaporanP2H from "@/Pages/HistoryLaporanP2H/detail-laporan.vue";

const props = defineProps<{ data: LaporanP2H[] }>();
const data = ref<LaporanP2H[]>(props.data);

console.log(data);
const typeContentData = ref(""); // Reactive variable to store sharedActionType
const detailContentData = ref(null);
const isDetailContent = ref(false);

const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
});

async function getData(page = 1, paginate = 10): Promise<void> {
    try {
        const response = await axios.get("/fetch-data", {
            params: { paginate: paginate, page },
        });
        data.value = response.data.data;

        // console.log(response);
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
        };
    } catch (error) {
        console.error("Error fetching data:", error);
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

function gas() {
    isDetailContent.value = false;
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
                        <DataTable
                            v-if="!isDetailContent"
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
                            <!-- </Button> -->
                            <DetailLaporanP2H
                                :data="detailContentData"
                                :typeContent="typeContentData"
                                @is-back-to-table="gas"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
