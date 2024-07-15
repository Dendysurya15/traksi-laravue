<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <!-- <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div> -->

    <div class="grid grid-cols-3 h-screen">
        <div
            class="relative col-span-2 bg-cover bg-center hidden md:block lg:block xl:block"
            :style="{ backgroundImage: `url('/loginImage/car_bg.jpg')` }"
        >
            <div
                class="absolute inset-0 bg-sky-950 bg-opacity-90 grid content-center pl-20"
            >
                <div class="flex items-center">
                    <div
                        class="h-16 w-16 bg-white rounded-full flex items-center justify-center"
                    >
                        <img
                            src="/loginImage/traksi_logo.png"
                            alt="Logo"
                            class="h-12"
                        />
                    </div>
                    <p
                        class="scroll-m-20 ml-3 text-white text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-3xl font-semibold tracking-tight"
                    >
                        Monitoring-Traksi
                    </p>
                </div>
                <div
                    class="pt-12 text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-3xl font-bold text-white"
                >
                    Web Hasil Pelaporan Kerusakan Unit P2H
                </div>
                <p
                    class="italic text-xs sm:text-sm md:text-base lg:text-lg xl:text-lg text-muted-foreground text-white pt-2"
                >
                    Portal Informasi terkait rekap history pelaporan kerusakan
                    Unit P2H
                </p>
            </div>
        </div>

        <!-- form -->
        <div
            class="col-span-full md:col-span-1 lg:col-span-1 xl:col-span-1 sm:hidden flex justify-center items-center bg-white"
        >
            <div class="p-14">
                <div>
                    <div class="flex items-center rs:flex xs:flex hidden">
                        <div
                            class="h-11 w-11 bg-gray-100 rounded-full flex items-center justify-center"
                        >
                            <img
                                src="/loginImage/traksi_logo.png"
                                alt="Logo"
                                class="h-10"
                            />
                        </div>
                        <p
                            class="scroll-m-20 ml-2 text-sky-800 text-md font-semibold tracking-tight"
                        >
                            Monitoring-Traksi
                        </p>
                    </div>
                </div>

                <p
                    class="scroll-m-20 text-2xl xl:text-4xl font-semibold tracking-tight first:mt-0"
                >
                    Autentikasi Sistem
                </p>
                <small class="text-sm xl:text-md font-medium leading-none">
                    Silakan masuk portal web dengan username dan password dengan
                    benar!
                </small>
                <div class="mt-5">
                    <slot />
                </div>
                <small
                    class="mt-14 flex justify-center text-sm xl:text-md font-medium leading-none"
                >
                    @2024. Digital Architect SRS
                </small>
            </div>
        </div>

        <!-- tablet mode only -->
        <div
            class="col-span-full xs:hidden rs:hidden lg:hidden md:hidden xl:hidden flex items-center justify-center border-2 content-center"
        >
            <div class="w-1/2 p-8 bg-white rounded-lg shadow-md">
                <div class="lg:hidden md:hidden sm:block">
                    <div class="flex items-center">
                        <div
                            class="h-11 w-11 bg-gray-100 rounded-full flex items-center justify-center"
                        >
                            <img
                                src="/loginImage/traksi_logo.png"
                                alt="Logo"
                                class="h-10"
                            />
                        </div>
                        <p
                            class="scroll-m-20 ml-2 text-sky-800 text-md font-semibold tracking-tight"
                        >
                            Monitoring-Traksi
                        </p>
                    </div>
                </div>

                <div class="text-center">
                    <p
                        class="scroll-m-20 text-3xl font-semibold tracking-tight first:mt-0"
                    >
                        Autentikasi Sistem
                    </p>
                    <small class="text-sm font-medium leading-none">
                        Silakan masuk portal web dengan username dan password
                        dengan benar!
                    </small>
                </div>

                <div class="mt-5">
                    <slot />
                </div>

                <small
                    class="flex justify-center mb-7 text-sm font-medium leading-none"
                >
                    @2024. Digital Architect SRS
                </small>
            </div>
        </div>
    </div>
</template>
