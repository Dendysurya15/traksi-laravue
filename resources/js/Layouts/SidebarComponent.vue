<script>
import { defineComponent, defineEmits } from "vue";
import {
    ChevronDoubleDownIcon,
    ChevronDownIcon,
    HomeIcon,
} from "@heroicons/vue/24/solid";
import { usePage, router } from "@inertiajs/vue3";

export default defineComponent({
    props: {
        listLink: Array,
        activeLink: String,
    },
    setup(props, { emit }) {
        const page = usePage();
        const currentUrl = page.url;

        const navigateTo = (nameList) => {
            router.visit("/" + nameList.toLowerCase()); // Navigate to the clicked link
            emit("clickedLink", nameList); // Emit event to parent component
        };

        const getIconComponent = (iconName) => {
            const icons = {
                ChevronDoubleDownIcon,
                ChevronDownIcon,
                HomeIcon,
            };
            return icons[iconName] || null;
        };

        return {
            currentUrl,
            navigateTo,
            getIconComponent,
        };
    },
});
</script>

<style scoped>
.active-link {
    background-color: #325266; /* Example background color for active link */
}
.active-link:hover {
    background-color: #3e647c; /* Slightly lighter shade for hover */
    transition: background-color 0.3s ease; /* Smooth transition effect */
}
</style>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center text-white justify-center p-2 mb-10 mt-3">
            <div
                class="h-11 w-12 rounded bg-white flex items-center justify-center"
            >
                <img
                    src="/loginImage/traksi_logo.png"
                    alt="Logo"
                    class="h-10"
                />
            </div>

            <div class="flex-col ml-2">
                <p class="font-bold text-lg">Fleet Management</p>
                <p
                    class="rounded-sm bg-green-800 text-sm text-gray-50 font-medium pl-3 pr-3 inline-block"
                >
                    v1.2
                </p>
            </div>
        </div>

        <!-- Menu -->

        <ul class="mt-3">
            <li
                v-for="(link, index) in listLink"
                :key="index"
                @click="navigateTo(link.name)"
                :class="[
                    'bg-[#002741] text-white rounded-xl p-3 m-1 ',
                    'hover:bg-[#3e647c] transition-colors duration-300 ease-in-out', // Add this line for hover effect
                    {
                        'active-link':
                            currentUrl === '/' + link.name.toLowerCase(),
                    },
                ]"
                class="bg-[#002741] text-white rounded-xl p-3 m-1 flex items-center pl-5 gap-3 cursor-pointer"
            >
                <component :is="getIconComponent(link.icon)" class="size-5" />
                {{ link.name }}
            </li>
        </ul>
    </div>
</template>
