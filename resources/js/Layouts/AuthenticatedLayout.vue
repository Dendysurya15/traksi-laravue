<script>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import SidebarComponent from "./SidebarComponent.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { ChevronDownIcon, HomeIcon } from "@heroicons/vue/24/solid";
import ActionProfileNavbar from "@/Components/actionProfileNavbar.vue";

export default {
    components: {
        SidebarComponent,
        Link,
        ChevronDownIcon,
        ActionProfileNavbar,
    },
    props: {
        activeLink: String,
        children: Array,
    },
    data() {
        return {
            activeLink: "Dashboard",
            links: [{ name: "Dashboard", icon: "HomeIcon" }],
        };
    },
    methods: {
        navigateTo(link) {
            this.activeLink = link;
        },
        handleLogout() {
            router.post("/logout", {}, { method: "post" });
        },
    },
    setup() {
        const isSidebarOpen = ref(false); // Default to false
        const isSmallScreen = ref(true); // Default to true
        const page = usePage();
        const routeNow =
            page.url.replace("/", "").charAt(0).toUpperCase() +
            page.url.slice(2);
        const user = page.props.auth.user;
        const linksDropdown = [
            { href: "/profile", label: "Profile" },
            {
                href: "/logout",
                label: "Log out",
                method: "post",
                class: "text-red-500",
            },
        ];
        const isMenuOpen = ref(false);
        const isLoaded = ref(false);

        const updateIsSmallScreen = () => {
            isSmallScreen.value = window.innerWidth < 1279;
            isSidebarOpen.value = !isSmallScreen.value;
        };

        onMounted(() => {
            nextTick(() => {
                updateIsSmallScreen();
                window.addEventListener("resize", updateIsSmallScreen);
                isLoaded.value = true;
            });
        });

        onUnmounted(() => {
            window.removeEventListener("resize", updateIsSmallScreen);
        });

        const toggleSidebar = () => {
            isSidebarOpen.value = !isSidebarOpen.value;
        };

        const closeSidebar = () => {
            isSidebarOpen.value = false;
        };

        const toggleMenu = () => {
            isMenuOpen.value = !isMenuOpen.value;
        };

        const closeMenu = () => {
            isMenuOpen.value = false;
        };

        return {
            isSidebarOpen,
            routeNow,
            linksDropdown,
            isSmallScreen,
            toggleSidebar,
            user,
            closeSidebar,
            isMenuOpen,
            toggleMenu,
            closeMenu,
            isLoaded,
        };
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
    opacity: 0;
}
.svg-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    background-color: #f0f0f0;
    border-radius: 50%;
}
.svg-icon {
    width: 23px;
    height: 23px;
}
</style>

<template>
    <div class="relative">
        <div
            class="h-screen grid grid-cols-5"
            :style="{ background: '#003151' }"
        >
            <!-- Sidebar -->
            <aside
                v-show="isLoaded && !isSmallScreen"
                class="col-span-1 border-r-2 border-gray-200 shadow-2xl"
            >
                <div>
                    <SidebarComponent
                        :listLink="links"
                        :activeLink="activeLink"
                        @clickedLink="navigateTo"
                    />
                </div>
            </aside>

            <!-- Content -->
            <main
                class="overflow-y-auto"
                :class="[
                    'col-span-4',
                    'md:col-span-full',
                    'sm:col-span-full',
                    'xs:col-span-full',
                    'rs:col-span-full',
                    { 'xl:col-span-full lg:col-span-full': !isSidebarOpen },
                ]"
                :style="{ backgroundColor: '#eff0f3' }"
            >
                <div class="bg-white">
                    <div
                        class="w-full p-2 flex justify-between items-center border-b-2 border-gray-200 shadow-md shadow-gray-200"
                    >
                        <div>
                            <button
                                class="hover:bg-gray-100 text-black font-bold py-1 px-3 rounded"
                                @click="toggleSidebar"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="size-8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div
                            class="flex justify-center content-center items-center"
                        >
                            <p class="mr-2 text-gray-400">|</p>
                            <ActionProfileNavbar
                                :userAuth="user.nama_lengkap"
                                class="mr-2"
                            />
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white shadow-sm text-md text-blue-950 font-bold shadow-gray-300 p-4"
                >
                    {{ routeNow }}
                </div>
                <div class="p-5 overflow-y-auto">
                    <slot />
                </div>
            </main>

            <!-- Sidebar for small screens -->
            <transition name="fade-leave-active">
                <div v-show="isSidebarOpen && isSmallScreen">
                    <div
                        class="fixed inset-0 z-50 md:w-2/5 sm:w-2/5 xs:w-2/5 rs:w-3/5 h-full"
                        :style="{ backgroundColor: '#003151' }"
                    >
                        <SidebarComponent
                            :listLink="links"
                            :activeLink="activeLink"
                            @clickedLink="navigateTo"
                        />
                    </div>
                    <div
                        class="fixed inset-0 bg-black bg-opacity-25"
                        @click="closeSidebar"
                    ></div>
                </div>
            </transition>
        </div>
    </div>
</template>
