<script setup lang="ts">
import { MoreHorizontal } from "lucide-vue-next";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { defineEmits, defineProps } from "vue";
import { EyeIcon, PencilIcon } from "@heroicons/vue/24/solid";

const emit = defineEmits(["action"]); // Define emitted events

// // Function to handle detail click event
function handleActionClick(actionType: string) {
    emit("action", actionType); // Emit the 'action' event with the provided action type
}

const props = defineProps<{
    data: {
        id: string;
    };
}>();
</script>

<template>
    <DropdownMenuItem class="p-2" @click="handleActionClick('detail')"
        ><EyeIcon class="w-4 h-4 mr-3" />Detail Laporan</DropdownMenuItem
    >
    <!-- <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>

            <DropdownMenuSeparator />
            <DropdownMenuItem class="p-2" @click="handleActionClick('detail')"
                ><EyeIcon class="w-4 h-4 mr-3" />Detail
                Laporan</DropdownMenuItem
            >
            <DropdownMenuItem class="p-2" @click="handleActionClick('edit')"
                ><PencilIcon class="w-4 h-4 mr-3" />Edit Menu</DropdownMenuItem
            >
        </DropdownMenuContent>
    </DropdownMenu> -->
</template>
