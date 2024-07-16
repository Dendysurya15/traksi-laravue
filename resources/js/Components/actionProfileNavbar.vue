<script setup>
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuShortcut,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import {
    ArrowDownIcon,
    ArrowLeftStartOnRectangleIcon,
    ChevronDownIcon,
    UserIcon,
} from "@heroicons/vue/24/solid";
import { router } from "@inertiajs/vue3";
defineProps({
    userAuth: String,
});
const handleLogout = () => {
    router.post("/logout", {}, { method: "post" });
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline"
                >{{ userAuth }}
                <ChevronDownIcon class="ml-2 w-4 h-4 text-black"
            /></Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56 mr-2">
            <DropdownMenuLabel>Menu</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <Link href="/profile">
                    <DropdownMenuItem class="cursor-pointer gap-2">
                        <UserIcon class="w-4 h-4 text-black" />
                        Profile
                    </DropdownMenuItem>
                </Link>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                @click="handleLogout"
                class="text-red-500 cursor-pointer font-bold gap-2 hover:bg-red-100"
            >
                <ArrowLeftStartOnRectangleIcon class="w-4 h-4 text-red-600" />
                <span>Keluar</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
