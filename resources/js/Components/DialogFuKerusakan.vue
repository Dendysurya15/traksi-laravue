<template>
    <Dialog v-model:open="open" @close="resetError">
        <DialogTrigger as-child>
            <Button :class="buttonClass" class="gap-1">
                <template v-if="confirmFu">
                    <CheckCircleIcon
                        class="w-5 h-5 text-white cursor-pointer"
                    />
                </template>
                <template v-else>
                    <NoSymbolIcon class="w-5 h-5 text-white cursor-pointer" />
                </template>
                {{ textButton }}
            </Button>
        </DialogTrigger>
        <DialogContent class="w-[400px] rounded-md">
            <DialogHeader>
                <DialogTitle
                    class="flex justify-start content-center items-center text-2xl gap-2"
                >
                    <ExclamationTriangleIcon
                        class="w-7 h-7 text-yellow-500 cursor-pointer"
                    />

                    Konfirmasi Follow Up
                </DialogTitle>
            </DialogHeader>
            <div class="grid gap-4">
                <p
                    class="text-sm leading-6 italic -mb-2"
                    :class="confirmFu ? 'text-green-600' : 'text-red-600'"
                >
                    {{
                        confirmFu
                            ? "Boleh dikosongkan jika tidak perlu!"
                            : "*Wajib melakukan pengisian komentar!"
                    }}
                </p>
                <div>
                    <textarea
                        rows="4"
                        class="block p-2.5 h-[200px] w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        placeholder="Tulis informasi terkait konfirmasi Follow Up!"
                        v-model="formData.komentar"
                    ></textarea>
                </div>
            </div>
            <p v-if="showError" class="text-red-500">Komentar harus diisi!</p>
            <DialogFooter class="flex justify-end items-end">
                <button
                    type="button"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded w-4/12 flex items-center justify-center"
                    :disabled="isSubmitting"
                    @click="submitForm"
                >
                    <span v-if="isSubmitting" class="flex">
                        <span class="mr-1">Submit</span>
                        <span
                            v-for="dot in [0, 1, 2]"
                            :key="dot"
                            class="animate-pulse duration-1000"
                            :style="{
                                animationDelay: `${dot * 333}ms`,
                            }"
                            >.</span
                        >
                    </span>
                    <span v-else>Submit</span>
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { Button } from "@/Components/ui/button";
import { usePage } from "@inertiajs/vue3";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    NoSymbolIcon,
} from "@heroicons/vue/24/solid";
import { useDateFormat, useNow } from "@vueuse/core";
import axios from "axios";

const live_tanggal = useDateFormat(useNow(), "dddd, D MMM YYYY  HH:mm:ss", {
    locales: "id-ID",
});
const props = defineProps<{
    textButton: string;
    buttonClass: string;
    confirmFu: boolean;
    idLaporan: string;
    userFollowUp: string;
    tanggal_submit: string;
}>();

const showError = ref(false);
const open = ref(false);
const page = usePage();
const user = page.props.auth.user;
const isSubmitting = ref(false);
const formData = ref({
    status: props.confirmFu,
    komentar: "",
    idLaporan: props.idLaporan,
    userFollowUp: user.nama_lengkap,
    tanggal_submit: "",
});

const emits = defineEmits(["sendToast", "gasBro"]);

const resetError = () => {
    showError.value = false;
};

watch(open, (newValue) => {
    if (!newValue) {
        resetError();
    }
});

const submitForm = async () => {
    isSubmitting.value = true;

    await new Promise((resolve) => setTimeout(resolve, 1300));

    showError.value = false; // Reset the error message
    if (!props.confirmFu && formData.value.komentar.trim() === "") {
        showError.value = true; // Show the error message
        isSubmitting.value = false; // Stop the loading indicator
        return;
    }

    try {
        // Submit form data to Laravel backend
        const response = await axios.post(
            "/change-status-fu-kerusakan",
            formData.value
        );
        emits("sendToast", {
            title: "Sukses",
            color: "green",
            description: "Berhasil submit follow up!",
        });

        open.value = false; // Close the dialog after successful submission
    } catch (error) {
        emits("sendToast", {
            title: "Error",
            color: "red",
            description: error,
        });
    } finally {
        isSubmitting.value = false; // Stop the loading indicator
    }
};

watch(isSubmitting, (newValue) => {
    if (newValue) {
        // Store the current date and time when the submit button is clicked
        formData.value.tanggal_submit = live_tanggal.value;
    }
});
</script>
