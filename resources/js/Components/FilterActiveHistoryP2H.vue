<template>
    <div
        v-if="activeFilterType"
        class="bg-[#fafafa] mx-5 rounded-sm mt-4 px-5 py-2 mb-3 ring-1 ring-gray-300 p-1 mt-2 flex items-center justify-between"
    >
        <div class="flex">
            <span class="text-gray-800 mr-2">Filter Aktif:</span>

            <span
                v-if="activeFilterType === 'option'"
                v-for="(filter, index) in activeFilter"
                :key="index"
                class="bg-green-100 text-green-500 font-medium ring-2 ring-green-200 px-2 rounded-md flex items-center mr-2"
            >
                {{ filter }}
                <button
                    type="button"
                    class="ml-2 text-white hover:text-gray-200 focus:outline-none"
                    @click="() => handleClearActiveFilter(filter, index)"
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
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    activeFilterType: String,
    activeFilter: Array,
    date: Object,
    formatDateRange: Function,
    clearActiveFilter: Function,
    clearDateFilter: Function,
    clearAllFilters: Function,
});

const emit = defineEmits(["filter-cleared"]);

const handleClearActiveFilter = (filter, index) => {
    emit("filter-cleared", { filter, index });
};
</script>
