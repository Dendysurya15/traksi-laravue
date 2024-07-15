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
import { Button } from "@/components/ui/button";
const emit = defineEmits(["action"]); // Define emitted events

// Function to handle detail click event
function handleDetailClick() {
    emit("action", "detail"); // Emit the 'action' event with action type 'detail'
}

defineProps<{
    label: string;
    onAction: () => void;
}>();
</script>

<template>
    <Button>{{ label }}</Button>
    <!-- <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem> Copy payment ID </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem>View customer</DropdownMenuItem>
            <DropdownMenuItem>View payment details</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu> -->
</template>
