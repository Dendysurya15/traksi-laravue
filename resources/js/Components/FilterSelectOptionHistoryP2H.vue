<template>
    <div class="relative" ref="dropdown">
        <div
            class="border border-gray-300 rounded-md px-3 py-1.5 cursor-pointer flex items-center justify-between"
            @click="toggleOptions"
        >
            <span v-if="selectedOption" class="text-gray-800">
                {{ selectedOption.label }}
            </span>
            <span v-else class="text-gray-400 flex items-center">
                <CalendarIcon class="mr-2 h-4 w-4 opacity-70" />
                {{ placeholder }}
            </span>
            <svg
                class="w-4 h-4 ml-2 inline-block transition-transform"
                :class="{ 'rotate-180': showOptions }"
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
            class="absolute z-10 w-[200px] rs:w-[150px] mt-1 bg-white border border-gray-300 rounded-md shadow-lg custom-scrollbar left-1/2 transform -translate-x-1/2 top-full"
        >
            <div class="py-1">
                <div
                    v-for="option in options"
                    :key="option.value"
                    class="px-3 py-2 cursor-pointer hover:bg-gray-100"
                    @click="selectOption(option)"
                >
                    {{ option.label }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted, defineComponent } from "vue";
import { CalendarIcon } from "@heroicons/vue/24/solid";

export default defineComponent({
    components: {
        CalendarIcon,
    },
    props: {
        defaultSelect: {
            type: String,
            required: true,
        },
        placeholder: {
            type: String,
            required: true,
        },
        options: {
            type: Array,
            required: true,
        },
    },
    setup(props, { emit }) {
        const selectedOption = ref(null);
        const showOptions = ref(false);
        const dropdown = ref(null); // Reference to the dropdown container

        const toggleOptions = () => {
            showOptions.value = !showOptions.value;
        };

        const selectOption = (option) => {
            selectedOption.value = option;
            showOptions.value = false;
            emit("option-selected", option.value);
        };
        const resetSelectedOption = () => {
            selectedOption.value = null;
        };

        const handleClickOutside = (event) => {
            if (dropdown.value && !dropdown.value.contains(event.target)) {
                showOptions.value = false;
            }
        };

        onMounted(() => {
            document.addEventListener("click", handleClickOutside);
        });

        onUnmounted(() => {
            document.removeEventListener("click", handleClickOutside);
        });

        return {
            selectedOption,
            showOptions,
            options: props.options,
            toggleOptions,
            selectOption,
            resetSelectedOption,
            placeholder: props.placeholder,
            dropdown,
        };
    },
});
</script>

<style scoped>
.custom-scrollbar {
    max-height: 240px; /* Adjust as needed */
    overflow-y: auto;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 8px; /* Width of the scrollbar */
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: lightgray; /* Color of the scrollbar thumb */
    border-radius: 4px; /* Rounds the corners of the scrollbar thumb */
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #555; /* Darker color when hovered */
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; /* Background of the track */
    border-radius: 4px; /* Rounds the corners of the track */
}
</style>
