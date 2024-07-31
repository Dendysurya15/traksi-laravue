<script setup lang="ts">
import { ref, onUnmounted, onMounted } from "vue";
import { Button } from "@/Components/ui/button";
import {
    ToastAction,
    ToastDescription,
    ToastProvider,
    ToastRoot,
    ToastTitle,
    ToastViewport,
} from "radix-vue";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/solid";
const props = defineProps<{
    showToast: boolean;
    title: string;
    description: string;
    color: string;
    duration: number;
}>();

const emit = defineEmits(["close-toast"]);

let toastTimeout = null;
let closeTimeout = null;

const closeToast = () => {
    emit("close-toast");
};

// Set a timeout to close the toast after the specified duration
onMounted(() => {
    if (props.duration) {
        toastTimeout = setTimeout(() => {
            // Introduce a delay before closing the toast
            closeTimeout = setTimeout(() => {
                closeToast();
            }, 500); // Adjust the delay duration as needed (in milliseconds)
        }, props.duration);
    }
});

// Clean up the timeouts when the component is unmounted
onUnmounted(() => {
    clearTimeout(toastTimeout);
    clearTimeout(closeTimeout);
});
</script>

<template>
    <ToastProvider>
        <ToastRoot
            v-model:open="props.showToast"
            class="text-white rounded-md shadow-[hsl(206_22%_7%_/_35%)_0px_10px_38px_-10px,_hsl(206_22%_7%_/_20%)_0px_10px_20px_-15px] p-[15px] grid [grid-template-areas:_'title_action'_'description_action'] grid-cols-[auto_max-content] gap-x-[15px] items-center data-[state=open]:animate-slideIn data-[state=closed]:animate-hide data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=cancel]:translate-x-0 data-[swipe=cancel]:transition-[transform_200ms_ease-out] data-[swipe=end]:animate-swipeOut"
            :class="props.color"
        >
            <div class="flex items-center gap-3">
                <div class="">
                    <p class="font-extrabold text-xl">
                        {{ props.title }}
                    </p>

                    <p
                        class="[grid-area:_description] m-0 text-slate11 text-[13px] leading-[1.3]"
                    >
                        {{ props.description }}
                    </p>
                </div>
            </div>

            <ToastAction
                class="[grid-area:_action]"
                as-child
                alt-text="Close"
                @click="closeToast"
            >
                <Button variant="secondary"> Tutup </Button>
            </ToastAction>
        </ToastRoot>
        <ToastViewport
            class="[--viewport-padding:_25px] fixed bottom-0 right-0 flex flex-col p-[var(--viewport-padding)] gap-[10px] w-[390px] max-w-[100vw] m-0 list-none z-[2147483647] outline-none"
        />
    </ToastProvider>
</template>
