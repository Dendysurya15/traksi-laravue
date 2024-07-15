<script setup lang="ts">
import { watchOnce, useDateFormat } from "@vueuse/core";
import axios from "axios";
import { watch } from "vue";
import { useAxios } from "@vueuse/integrations/useAxios";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";
import {
    Carousel,
    type CarouselApi,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/Components/ui/carousel";
import { Head } from "@inertiajs/vue3";
import { LaporanP2H } from "@/types/laporanP2h";

import { ref, computed } from "vue";

import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
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
const emit = defineEmits(["is-back-to-table"]);
import {
    ArrowsPointingOutIcon,
    MagnifyingGlassCircleIcon,
} from "@heroicons/vue/24/solid";
import {
    ArrowLeftCircleIcon,
    ExclamationTriangleIcon,
    MapIcon,
    PhotoIcon,
} from "@heroicons/vue/24/solid";
import { Separator } from "@/Components/ui/separator";
import { Badge } from "@/Components/ui/badge";
import { Skeleton } from "@/Components/ui/skeleton";
const props = defineProps<{ data: LaporanP2H[] }>();

const data = ref<LaporanP2H[]>(props.data);
const formattedDate = useDateFormat(
    data.value.tanggal_upload,
    "dddd, D MMMM YYYY hh:mm",
    { locales: "id-ID" }
);

const kerusakan_unit = ref<LaporanP2H[]>(null);
const emblaMainApi = ref<CarouselApi>();
const emblaThumbnailApi = ref<CarouselApi>();
const selectedIndex = ref(0);
const selectedImage = ref<string | null>(null);
const selectedNameImage = ref<string | null>(null);
const selectedNameImageFotoUnit = ref<string | null>(null);
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
}

function handleIconClickImagePreview() {
    if (kerusakan_unit.value) {
        selectedImage.value = `/img/documents/LaporP2H/${
            kerusakan_unit.value[selectedIndex.value].foto
        }`;
        selectedNameImage.value =
            kerusakan_unit.value[selectedIndex.value].foto;
    }
}

function handleIconClickImageFotoUnitPreview(foto_unit) {
    selectedImage.value = `/img/documents/LaporP2H/${foto_unit}`;
    selectedNameImageFotoUnit.value = foto_unit;
}

watchOnce(emblaMainApi, (emblaMainApi) => {
    if (!emblaMainApi) return;

    onSelect();
    emblaMainApi.on("select", onSelect);
    emblaMainApi.on("reInit", onSelect);
});
function backToTable() {
    emit("is-back-to-table");
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
    <Head title="History" />
    <!-- 
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            History
        </h2>
    </template> -->

    <div>
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center font-medium text-md gap-2">
                <ArrowLeftCircleIcon
                    @click="backToTable"
                    class="w-8 h-8 text-gray-400 cursor-pointer"
                />

                <template v-if="fetchingData">
                    <Skeleton class="w-[200px] h-7 bg-gray-200" />
                </template>
                <template v-else-if="fetchError">
                    <Badge variant="secondary" class="text-sm cursor-pointer">
                        <ExclamationTriangleIcon class="w-5 h-5 mr-2" />
                        Status Error
                    </Badge>
                </template>
                <template v-else>
                    <Badge variant="destructive" class="text-sm cursor-pointer">
                        <ExclamationTriangleIcon class="w-5 h-5 mr-2" />
                        Unit Terdapat Kerusakan
                    </Badge>
                </template>

                Detail Laporan Pemeriksaan P2H pada<span
                    class="font-extrabold"
                    >{{ formattedDate }}</span
                >
            </div>
            <div class="flex gap-2">
                <!-- <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <MapIcon
                                class="w-7 h-7 text-green-500 cursor-pointer"
                            />
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Koordinat Ketika User Upload</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider> -->

                <Dialog>
                    <div class="flex items-center">
                        <DialogTrigger as-child>
                            <Badge
                                class="bg-gray-200 gap-2 rounded-sm text-black hover:bg-gray-300 cursor-pointer"
                            >
                                Foto Unit
                                <PhotoIcon
                                    @click="
                                        handleIconClickImageFotoUnitPreview(
                                            data.foto_unit
                                        )
                                    "
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
                                v-if="selectedImage"
                                :src="selectedImage"
                                alt="Terjadi kesalahan silahkan hubungi Tim Pengembang!"
                                class="object-contain max-h-full"
                            />
                        </div>
                        <div class="flex items-center justify-center"></div>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
        <Separator class="mt-2" />
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="text-2xl mt-5 ml-3 font-semibold text-gray-800">
                    {{ data.jenis_unit }} {{ data.unit_kerja }}
                    {{ data.kode_unit }}
                </div>
                <div class="ml-3 italic">Driver : {{ data.user }}</div>

                <div class="grid grid-cols-2 mt-4">
                    <div class="col-span-1 flex justify-center">
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
                                                        class="w-8 h-8 hover:text-gray-300 text-gray-100 cursor-pointer"
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
                                                        <div
                                                            class="flex items-center space-x-2"
                                                        >
                                                            <!-- Additional Icon -->
                                                            <InformationCircleIcon
                                                                @click="
                                                                    handleIconClickImagePreview
                                                                "
                                                                class="w-6 h-6 text-gray-500 hover:text-gray-700 cursor-pointer"
                                                            />
                                                        </div>
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
                    <div class="col-span-1">
                        <p class="font-bold text-lg text-gray-800 mb-3">
                            List Part/Bagian Terindikasi Kerusakan di Unit
                        </p>

                        <div class="overflow-y-auto" style="height: 550px">
                            <Card
                                v-for="(item, index) in fetchedData"
                                :key="index"
                                class="mb-2 p-1"
                            >
                                <CardHeader>
                                    <CardTitle class="text-lg">{{
                                        item.title_kerusakan
                                    }}</CardTitle>
                                    <CardDescription class="text-md">
                                        {{ item.komentar }}
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
