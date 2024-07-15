<template>
    <DialogContent class="max-w-7xl rounded">
        <DialogHeader>
            <div class="flex justify-center items-center">
                <DialogTitle>{{ selectedNameImage }}</DialogTitle>
                <div class="flex items-center space-x-2">
                    <!-- Additional Icon -->
                    <InformationCircleIcon
                        @click="handleIconClickImagePreview"
                        class="w-6 h-6 text-gray-500 hover:text-gray-700 cursor-pointer"
                    />
                </div>
            </div>
        </DialogHeader>
        <div class="flex items-center justify-center object-cover">
            <img
                v-if="selectedImage"
                :src="selectedImage"
                alt="Selected Image"
                class="object-contain max-h-full"
            />
        </div>
        <div class="flex items-center justify-center"></div>
    </DialogContent>
</template>

<script>
import {
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"; // replace with your actual library
export default {
    name: "ImageDialogContent",
    props: {
        selectedNameImage: {
            type: String,
            required: true,
        },
        selectedImage: {
            type: String,
            required: true,
        },
    },
    methods: {
        handleIconClickImagePreview() {
            // Your logic for handling icon click
        },
    },
};
</script>

<style scoped>
/* Add any necessary styles here */
</style>
