<script>
import { ref, computed, onMounted, onUnmounted } from "vue";
import SidebarComponent from "./SidebarComponent.vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import { ChevronDownIcon, HomeIcon } from "@heroicons/vue/24/solid";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
export default {
    components: {
        SidebarComponent,
        Link,
        ChevronDownIcon,
    },
    props: {
        activeLink: String, // Receive activeLink from SidebarComponent
        children: Array,
    },
    data() {
        return {
            activeLink: "Dashboard",
            links: [
                { name: "Dashboard", icon: "HomeIcon" },
                { name: "Profile", icon: "ChevronDownIcon" },
            ],
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
        const isSidebarOpen = ref(true);
        const isSmallScreen = ref(window.innerWidth < 1279);
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

        const updateIsSmallScreen = () => {
            isSmallScreen.value = window.innerWidth < 1279;
        };

        onMounted(() => {
            window.addEventListener("resize", updateIsSmallScreen);
            isSidebarOpen.value = window.innerWidth >= 1279;
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
        };
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
    opacity: 0;
}
.svg-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px; /* Adjust the size as needed */
    height: 35px; /* Adjust the size as needed */
    background-color: #f0f0f0; /* Background color */
    border-radius: 50%; /* Makes the background round */
}

.svg-icon {
    width: 23px; /* Adjust the size as needed */
    height: 23px; /* Adjust the size as needed */
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
                :class="[
                    'col-span-1 border-r-2 border-gray-200 shadow-2xl',
                    { hidden: !isSidebarOpen },
                    'md:hidden',
                    'xs:hidden',
                    'sm:hidden',
                    'rs:hidden',
                ]"
            >
                <!-- Sidebar content -->
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
                <!-- navbar content -->
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

                            <Menu
                                as="div"
                                class="relative inline-block text-left"
                            >
                                <div class="flex justify-center items-center">
                                    <MenuButton
                                        class="inline-flex w-full justify-center cursor-pointer items-center rounded-xl shadow-inner border bg-white px-3 py-1 text-sm text-black font-medium hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                                        @click="toggleMenu"
                                    >
                                        <Avatar class="flex items-center gap-3">
                                            <div class="svg-container">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                    class="svg-icon"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                    />
                                                </svg>
                                            </div>
                                            <AvatarFallback>{{
                                                user.nama_lengkap
                                            }}</AvatarFallback>
                                        </Avatar>

                                        <ChevronDownIcon class="size-5 ml-3" />
                                    </MenuButton>
                                </div>

                                <transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0"
                                >
                                    <MenuItems
                                        v-if="isMenuOpen"
                                        class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                                    >
                                        <div class="py-1">
                                            <MenuItem
                                                v-for="link in linksDropdown"
                                                :key="link.href"
                                            >
                                                <button
                                                    v-if="link.method"
                                                    @click="handleLogout"
                                                    :class="[
                                                        'group flex w-full items-center px-2 py-2 text-sm hover:bg-gray-100',
                                                        link.class,
                                                    ]"
                                                >
                                                    {{ link.label }}
                                                </button>
                                                <Link
                                                    v-else
                                                    :href="link.href"
                                                    :class="[
                                                        'group flex w-full items-center px-2 py-2 text-sm hover:bg-gray-100',
                                                        link.class,
                                                    ]"
                                                    @click="closeMenu"
                                                >
                                                    {{ link.label }}
                                                </Link>
                                            </MenuItem>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>
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
                <div v-if="isSidebarOpen && isSmallScreen">
                    <div
                        class="fixed inset-0 z-50 md:w-2/5 sm:w-2/5 xs:w-2/5 rs:w-3/5 h-full"
                        :style="{ backgroundColor: '#fefefe' }"
                    >
                        <!-- Sidebar content -->
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
