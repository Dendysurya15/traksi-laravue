<template>
    <div class="relative">
        <div
            class="border border-gray-300 rounded-md px-3 py-1.5 cursor-pointer flex items-center justify-between"
            @click="toggleOptions"
        >
            <span v-if="selectedOption" class="text-gray-800">{{
                selectedOption.label
            }}</span>
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
            class="absolute z-10 w-[180px] mt-2 bg-white border border-gray-300 rounded-md shadow-lg"
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
import { ref, watch, defineComponent } from "vue";
import { CalendarIcon } from "@heroicons/vue/24/solid";

export default defineComponent({
    components: {
        CalendarIcon,
    },
    props: {
        // Define your props here
        defaultSelect: {
            type: String, // Specify the type of the prop
            required: true, // Make it required or not
        },
        placeholder: {
            type: String, // Specify the type of the prop
            required: true, // Make it required or not
        },
        options: {
            type: Array,
            required: true,
        },
    },
    setup(props, { emit }) {
        const selectedOption = ref(null);
        const showOptions = ref(false);
        const placeholder = props.placeholder;
        const options = props.options;
        const resetSelectedOption = () => {
            selectedOption.value = null;
        };

        const toggleOptions = () => {
            showOptions.value = !showOptions.value;
        };

        const selectOption = (option) => {
            selectedOption.value = option;
            showOptions.value = false;
            emit("option-selected", option.value);
        };

        return {
            selectedOption,
            showOptions,
            options,
            toggleOptions,
            selectOption,
            resetSelectedOption,
            placeholder,
        };
    },
});
</script>
