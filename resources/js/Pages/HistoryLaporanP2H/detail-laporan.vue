<script setup lang="ts">
import { watchOnce, useDateFormat } from "@vueuse/core";
import axios from "axios";
import { watch } from "vue";
import { useAxios } from "@vueuse/integrations/useAxios";
import ToastNotification from "@/Components/ToastNotification.vue";
import {
    Carousel,
    type CarouselApi,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/Components/ui/carousel";
import { Head, usePage } from "@inertiajs/vue3";
import { LaporanP2H } from "@/types/laporanP2h";
import DialogFuKerusakan from "@/Components/DialogFuKerusakan.vue";
import { ref, computed, onUnmounted } from "vue";

import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from "@/Components/ui/hover-card";

import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
const emit = defineEmits([
    "is-back-to-table",
    "open-toast",
    "refresh-data-table",
]);

import {
    ArrowsPointingOutIcon,
    CalendarIcon,
    UserCircleIcon,
} from "@heroicons/vue/24/solid";
import {
    ArrowLeftCircleIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    MapIcon,
    PhotoIcon,
} from "@heroicons/vue/24/solid";

const showToast = ref(false);
const toastTitle = ref("");
const toastColor = ref("");
const toastDescription = ref("");

let toastTimeout = null;

import { Badge } from "@/Components/ui/badge";
import { Skeleton } from "@/Components/ui/skeleton";
const props = defineProps<{ data: LaporanP2H[] }>();

const data = ref<LaporanP2H[]>(props.data);

const statusFollowUpObject = computed(() => {
    if (data.value.status_follow_up) {
        try {
            const parsedObject = JSON.parse(data.value.status_follow_up);
            return parsedObject;
        } catch (error) {
            console.error("Error parsing status_follow_up JSON:", error);
            return {};
        }
    }
    return {};
});

const page = usePage();
const user = page.props.auth.user;
const shouldShowDialogs = computed(() =>
    ["Askep", "Admin", "Manager"].includes(user.jabatan)
);

const fetchingDataFU = ref(false);
const fetchErrorFU = ref(null); // Add a new ref to store the error message

const handleShowToast = async ({ title, description, color, id }) => {
    showToast.value = true;
    toastTitle.value = title;
    toastDescription.value = description;
    const colorNow = color === "green" ? "bg-green-500" : "bg-red-500";
    toastColor.value = colorNow;
    try {
        fetchingDataFU.value = true;
        fetchErrorFU.value = null;
        const response = await axios.get(
            `/get-detail-laporan-p2h/${data.value.id}`
        );
        data.value = response.data;
    } catch (error) {
        console.error("Error fetching data:", error);
        fetchErrorFU.value = error.message;
    } finally {
        fetchingDataFU.value = false; // Set loading state to false after fetch is complete
    }
};

const formattedDate = useDateFormat(
    data.value.tanggal_upload,
    "dddd, D MMMM YYYY hh:mm",
    { locales: "id-ID" }
);
const kerusakan_unit = ref<LaporanP2H[]>(null);
const emblaMainApi = ref<CarouselApi>();
const emblaThumbnailApi = ref<CarouselApi>();
const selectedIndex = ref(0);
const selectedCardIndex = ref(0);
const selectedImage = ref<string | null>(null);
const selectedImageFotoUnit = `/img/documents/LaporP2H/${data.value.foto_unit}`;
const selectedNameImage = ref<string | null>(null);
const selectedNameImageFotoUnit = data.value.foto_unit;
const imageExists = ref<boolean[]>([]);

const {
    data: fetchedData,
    error: fetchError,
    isLoading: fetchingData,
    execute: fetchData,
} = useAxios(`/get-info-kerusakan/${data.value.id}`, axios);

watch(fetchedData, (newData) => {
    kerusakan_unit.value = newData; // Assuming fetchedData is the actual data you want to assign
    checkImages();
});

watch(fetchError, (newError) => {
    if (newError) {
        console.error("Error fetching data:", newError);
    }
});

function onSelect() {
    if (!emblaMainApi.value || !emblaThumbnailApi.value) return;
    selectedIndex.value = emblaMainApi.value.selectedScrollSnap();
    emblaThumbnailApi.value.scrollTo(emblaMainApi.value.selectedScrollSnap());
}

function onThumbClick(index: number) {
    if (!emblaMainApi.value || !emblaThumbnailApi.value) return;
    emblaMainApi.value.scrollTo(index);
    selectedCardIndex.value = index;
}

function handleCardClick(index: number) {
    selectedCardIndex.value = index;
    if (emblaMainApi.value) {
        emblaMainApi.value.scrollTo(index);
    }
}

function handleIconClickImagePreview() {
    if (kerusakan_unit.value) {
        selectedImage.value = `/img/documents/LaporP2H/${
            kerusakan_unit.value[selectedCardIndex.value].foto
        }`;
        selectedNameImage.value =
            kerusakan_unit.value[selectedCardIndex.value].foto;
    }
}

watchOnce(emblaMainApi, (emblaMainApi) => {
    if (!emblaMainApi) return;

    onSelect();
    emblaMainApi.on("select", onSelect);
    emblaMainApi.on("reInit", onSelect);
});
function backToTable() {
    emit("is-back-to-table");
    emit("refresh-data-table");
}
function checkImages() {
    if (kerusakan_unit.value) {
        kerusakan_unit.value.forEach((item, index) => {
            const img = new Image();
            img.onload = () => {
                imageExists.value[index] = true;
            };
            img.onerror = () => {
                imageExists.value[index] = false;
            };
            img.src = `/img/documents/LaporP2H/${item.foto}`;
        });
    }
}
</script>

<template>
    <Head title="Detail Laporan P2H" />

    <div>
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center font-medium text-md gap-2">
                <ArrowLeftCircleIcon
                    @click="backToTable"
                    class="w-8 h-8 text-gray-400 cursor-pointer"
                />

                <span
                    class="text-lg xs:text-base sm:text-base rs:text-sm font-semibold text-gray-500"
                >
                    Detail Laporan Pemeriksaan P2H
                </span>

                <span
                    class="text-lg xs:text-base sm:text-base rs:text-sm font-extrabold text-gray-500"
                    >{{ formattedDate }}</span
                >
            </div>
            <div class="flex gap-2">
                <Dialog>
                    <div class="flex items-center">
                        <DialogTrigger as-child>
                            <Badge
                                class="bg-gray-200 gap-2 rounded-sm text-black hover:bg-gray-300 cursor-pointer"
                            >
                                <span class="rs:hidden xs:hidden sm:hidden"
                                    >Foto Unit</span
                                >
                                <PhotoIcon
                                    class="w-7 h-7 text-blue-950 cursor-pointer"
                                />
                            </Badge>
                        </DialogTrigger>
                    </div>

                    <DialogContent class="max-w-7xl rounded">
                        <DialogHeader>
                            <div class="flex justify-center items-center">
                                <DialogTitle>{{
                                    selectedNameImageFotoUnit
                                }}</DialogTitle>
                                <div class="flex items-center space-x-2">
                                    <!-- Additional Icon -->
                                    <InformationCircleIcon
                                        @click="handleIconClickImagePreview"
                                        class="w-6 h-6 text-gray-500 hover:text-gray-700 cursor-pointer"
                                    />
                                </div>
                            </div>
                        </DialogHeader>
                        <div
                            class="flex items-center justify-center object-cover"
                        >
                            <img
                                v-if="selectedImageFotoUnit"
                                :src="selectedImageFotoUnit"
                                alt="Terjadi kesalahan silahkan hubungi Tim Pengembang!"
                                class="object-contain max-h-full"
                            />
                        </div>
                        <div class="flex items-center justify-center"></div>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
        <!-- <Separator class="mt-2" /> -->
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="text-2xl mt-2 ml-3 font-semibold text-gray-800">
                    {{ data.jenis_unit }} {{ data.unit_kerja }}
                    {{ data.kode_unit }}
                </div>
                <!-- header -->
                <div class="ml-3 mt-2 inline-flex gap-3">
                    <div class="flex items-center text-gray-600">
                        <template v-if="fetchingDataFu">
                            <div class="flex items-center">
                                <Skeleton class="w-7 h-7 mr-2 bg-gray-200" />
                                <Skeleton class="w-3 h-7 w-20 bg-gray-200" />
                            </div>
                        </template>
                        <template v-else-if="fetchErrorFU">
                            <div class="flex items-center text-red-500">
                                <ExclamationCircleIcon class="w-5 h-5 mr-2" />
                                <p>{{ fetchError }}</p>
                            </div>
                        </template>
                        <template v-else>
                            <HoverCard>
                                <template
                                    v-if="
                                        Object.keys(statusFollowUpObject)
                                            .length === 0
                                    "
                                >
                                    <ExclamationTriangleIcon
                                        class="w-5 h-5 mr-2 text-yellow-500 animate-pulse"
                                    />
                                    <p>Belum Follow Up!</p>
                                </template>
                                <template v-else>
                                    <!-- Display something else when statusFollowUpObject is not empty -->
                                    <CheckCircleIcon
                                        class="w-6 h-6 mr-2 text-green-500"
                                    />
                                    <HoverCardTrigger as-child>
                                        <p class="underline cursor-pointer">
                                            Sudah Follow Up!
                                        </p>
                                    </HoverCardTrigger>
                                </template>
                                <HoverCardContent class="w-80">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="relative flex h-3 w-3">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full"
                                                :class="
                                                    statusFollowUpObject.status
                                                        ? 'bg-green-400 opacity-75'
                                                        : 'bg-red-400 opacity-75'
                                                "
                                            ></span>
                                            <span
                                                class="relative inline-flex rounded-full h-3 w-3"
                                                :class="
                                                    statusFollowUpObject.status
                                                        ? 'bg-green-500'
                                                        : 'bg-red-500'
                                                "
                                            ></span>
                                        </span>
                                        <p
                                            class="text-xs italic"
                                            :class="
                                                statusFollowUpObject.status
                                                    ? 'text-green-500'
                                                    : 'text-red-500'
                                            "
                                        >
                                            {{
                                                statusFollowUpObject.status
                                                    ? "Unit Dapat Beroperasi!"
                                                    : "Unit disarankan tidak beroperasi!"
                                            }}
                                        </p>
                                    </div>

                                    <div class="flex justify-between space-x-4">
                                        <div class="space-y-1">
                                            <h4 class="text-md font-semibold">
                                                {{
                                                    statusFollowUpObject.userFollowUp
                                                }}
                                            </h4>
                                            <p class="text-sm">
                                                {{
                                                    statusFollowUpObject.komentar
                                                }}
                                            </p>
                                            <div class="flex items-center pt-2">
                                                <CalendarIcon
                                                    class="mr-2 h-4 w-4 opacity-70"
                                                />
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    Updated
                                                    {{
                                                        statusFollowUpObject.tanggal_submit
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </HoverCardContent>
                            </HoverCard>
                        </template>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <div v-if="fetchingData" class="loading-indicator">
                            <Skeleton class="w-48 h-7 mr-2 bg-gray-200" />
                        </div>
                        <div v-else-if="fetchError" class="error-message">
                            Error fetching data:
                            {{ fetchError.message }}
                        </div>
                        <div v-else class="flex items-center">
                            <span
                                class="inline-flex mr-2 items-center justify-center w-5 h-5 ms-2 text-xs font-semibold text-white bg-red-500 rounded-full"
                            >
                                {{ kerusakan_unit.length }}
                            </span>
                            Total Laporan Kerusakan
                        </div>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <UserCircleIcon class="w-6 h-6 mr-2 text-gray-600" />
                        {{ data.user }}
                    </div>
                </div>
                <!-- content -->
                <div class="grid grid-cols-2 mt-4">
                    <div
                        class="col-span-1 hidden xl:flex lg:flex justify-center"
                    >
                        <div class="w-10/12">
                            <div v-if="fetchingData" class="loading-indicator">
                                <Skeleton
                                    class="h-[300px] bg-gray-200 w-full rounded-xl"
                                />
                                <div class="space-y-2 flex gap-3 mt-3 w-9/12">
                                    <Skeleton
                                        class="col-span-1 h-32 w-32 mt-2 bg-gray-200"
                                    />
                                    <Skeleton
                                        class="col-span-1 h-32 w-32 bg-gray-200"
                                    />
                                    <Skeleton
                                        class="col-span-1 h-32 w-32 bg-gray-200"
                                    />
                                </div>
                            </div>
                            <div v-else-if="fetchError" class="error-message">
                                Error fetching data:
                                {{ fetchError.message }}
                            </div>
                            <div v-else>
                                <div class="relative">
                                    <Carousel
                                        class="relative"
                                        @init-api="
                                            (val) => (emblaMainApi = val)
                                        "
                                    >
                                        <CarouselContent>
                                            <CarouselItem
                                                v-for="(
                                                    item, index
                                                ) in kerusakan_unit"
                                                :key="index"
                                            >
                                                <div class="">
                                                    <Card>
                                                        <CardContent
                                                            class="flex items-center justify-center p-0"
                                                        >
                                                            <div
                                                                class="embla__slide__thumbnail"
                                                            >
                                                                <img
                                                                    v-if="
                                                                        imageExists[
                                                                            index
                                                                        ]
                                                                    "
                                                                    :src="`/img/documents/LaporP2H/${item.foto}`"
                                                                    alt="Kerusakan Image"
                                                                    class="object-cover w-full h-full"
                                                                />
                                                                <div
                                                                    v-else
                                                                    class="w-full h-96 flex gap-2 items-center justify-center text-black font-bold"
                                                                >
                                                                    <PhotoIcon
                                                                        class="w-7 h-7 text-gray-900 cursor-pointer"
                                                                    />Image
                                                                    tidak
                                                                    tersedia
                                                                </div>
                                                            </div>
                                                        </CardContent>
                                                    </Card>
                                                </div>
                                            </CarouselItem>
                                        </CarouselContent>
                                        <CarouselPrevious />
                                        <CarouselNext />
                                    </Carousel>

                                    <div class="absolute top-0 right-0 p-2">
                                        <Dialog>
                                            <div class="flex items-center">
                                                <!-- <div>
                                                    <CloudArrowDownIcon
                                                        @click="
                                                            handleIconClickImagePreview
                                                        "
                                                        class="w-9 h-9 hover:text-gray-300 text-gray-100 cursor-pointer"
                                                    />
                                                </div> -->
                                                <DialogTrigger as-child>
                                                    <ArrowsPointingOutIcon
                                                        v-if="
                                                            imageExists[
                                                                selectedIndex
                                                            ]
                                                        "
                                                        @click="
                                                            handleIconClickImagePreview
                                                        "
                                                        class="w-8 h-8 hover:text-gray-700 text-gray-500 bg-white rounded cursor-pointer"
                                                    />
                                                </DialogTrigger>
                                            </div>

                                            <DialogContent
                                                class="max-w-7xl rounded"
                                            >
                                                <DialogHeader>
                                                    <div
                                                        class="flex justify-center items-center"
                                                    >
                                                        <DialogTitle>{{
                                                            selectedNameImage
                                                        }}</DialogTitle>
                                                    </div>
                                                </DialogHeader>
                                                <div
                                                    class="flex items-center justify-center object-cover"
                                                >
                                                    <img
                                                        v-if="selectedImage"
                                                        :src="selectedImage"
                                                        alt="Selected Image"
                                                        class="object-contain max-h-full"
                                                    />
                                                </div>
                                                <div
                                                    class="flex items-center justify-center"
                                                ></div>
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                </div>

                                <Carousel
                                    class="relative w-full mt-4"
                                    @init-api="
                                        (val) => (emblaThumbnailApi = val)
                                    "
                                >
                                    <CarouselContent class="flex gap-1 ml-0">
                                        <CarouselItem
                                            v-for="(
                                                item, index
                                            ) in kerusakan_unit"
                                            :key="index"
                                            class="pl-0 basis-1/4 cursor-pointer"
                                            @click="onThumbClick(index)"
                                        >
                                            <div
                                                class="p-1"
                                                :class="
                                                    index === selectedIndex
                                                        ? 'opacity-100'
                                                        : 'opacity-50'
                                                "
                                            >
                                                <Card>
                                                    <CardContent
                                                        class="flex aspect-square items-center justify-center p-6"
                                                    >
                                                        <div
                                                            class="embla__slide__thumbnail"
                                                        >
                                                            <img
                                                                v-if="
                                                                    imageExists[
                                                                        index
                                                                    ]
                                                                "
                                                                :src="`/img/documents/LaporP2H/${item.foto}`"
                                                                alt="Kerusakan Image"
                                                                class="object-cover w-full h-full"
                                                            />
                                                            <div
                                                                v-else
                                                                class="w-full h-16 flex gap-2 items-center justify-center text-black font-bold text-xs"
                                                            >
                                                                <PhotoIcon
                                                                    class="w-7 h-7 text-gray-900 cursor-pointer"
                                                                />Image tidak
                                                                tersedia
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            </div>
                                        </CarouselItem>
                                    </CarouselContent>
                                </Carousel>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-full xl:col-span-1 lg:col-span-1">
                        <p class="font-bold text-lg text-gray-800 mb-3">
                            List Part/Bagian Terindikasi Kerusakan di Unit
                        </p>

                        <div class="overflow-y-auto" style="height: 550px">
                            <Card
                                v-for="(item, index) in fetchedData"
                                :key="index"
                                class="mb-2 p-1 cursor-pointer flex items-center"
                                :class="{
                                    'bg-gray-700': index === selectedCardIndex,
                                }"
                                @click="handleCardClick(index)"
                            >
                                <div
                                    class="w-10/12 rs:w-10/12 xs:w-10/12 sm:w-10/12 md:w-10/12"
                                >
                                    <CardHeader>
                                        <CardTitle
                                            class="text-lg"
                                            :class="{
                                                'text-white':
                                                    index === selectedCardIndex,
                                            }"
                                        >
                                            {{ item.title_kerusakan }}
                                        </CardTitle>
                                        <CardDescription
                                            class="text-md"
                                            :class="{
                                                'text-white':
                                                    index === selectedCardIndex,
                                            }"
                                        >
                                            {{ item.komentar }}
                                        </CardDescription>
                                    </CardHeader>
                                </div>

                                <div
                                    class="hidden xs:block rs:block sm:block md:block p-1 w-2/12 rs:w-2/12 xs:w-2/12 sm:w-2/12 md:w-2/12 flex justify-center"
                                >
                                    <Dialog>
                                        <template
                                            v-if="index === selectedCardIndex"
                                        >
                                            <DialogTrigger as-child>
                                                <img
                                                    v-if="imageExists[index]"
                                                    :src="`/img/documents/LaporP2H/${item.foto}`"
                                                    @click.stop="
                                                        index ===
                                                            selectedCardIndex &&
                                                            handleIconClickImagePreview()
                                                    "
                                                    :class="{
                                                        'cursor-pointer':
                                                            index ===
                                                            selectedCardIndex,
                                                        'cursor-not-allowed opacity-50':
                                                            index !==
                                                            selectedCardIndex,
                                                    }"
                                                    alt="Kerusakan Image"
                                                    class="object-cover w-full h-full"
                                                />
                                            </DialogTrigger>
                                        </template>
                                        <img
                                            v-else
                                            :src="`/img/documents/LaporP2H/${item.foto}`"
                                            alt="Kerusakan Image"
                                            class="object-cover w-full h-full cursor-not-allowed opacity-50"
                                        />

                                        <DialogContent
                                            class="max-w-7xl rounded"
                                        >
                                            <DialogHeader>
                                                <div
                                                    class="flex justify-center items-center"
                                                >
                                                    <DialogTitle>{{
                                                        selectedNameImage
                                                    }}</DialogTitle>
                                                </div>
                                            </DialogHeader>
                                            <div
                                                class="flex items-center justify-center object-cover"
                                            >
                                                <img
                                                    v-if="selectedImage"
                                                    :src="selectedImage"
                                                    alt="Image Preview"
                                                    class="object-contain max-h-full"
                                                />
                                            </div>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </Card>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <div class="flex gap-2">
                        <!-- <Button class="bg-red-500">
                            <NoSymbolIcon class="w-5 h-5 mr-2" />Unit Tidak
                            Dapat Digunakan !!</Button
                        >
                        <Button class="bg-green-500">
                            <CheckCircleIcon class="w-5 h-5 mr-2" />
                            Unit Dapat Beroperasi</Button
                        > -->

                        <!-- ... (existing code) -->

                        <div class="flex justify-end gap-4 mt-3">
                            <template
                                <template
                                v-if="
                                    shouldShowDialogs &&
                                    data.status_follow_up === null
                                "
                            >
                                <DialogFuKerusakan
                                    :textButton="'Unit Tidak Dapat Digunakan!'"
                                    :confirmFu="false"
                                    :idLaporan="data.id"
                                    :buttonClass="'bg-red-500 hover:bg-red-600'"
                                    @sendToast="handleShowToast"
                                />
                                <DialogFuKerusakan
                                    :textButton="'Unit Dapat Beroperasi'"
                                    :confirmFu="true"
                                    :idLaporan="data.id"
                                    :buttonClass="'bg-green-500 hover:bg-green-600'"
                                    @sendToast="handleShowToast"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ToastNotification
        :showToast="showToast"
        :title="toastTitle"
        :description="toastDescription"
        :color="toastColor"
        :duration="8000"
        @close-toast="showToast = false"
    />
</template>
