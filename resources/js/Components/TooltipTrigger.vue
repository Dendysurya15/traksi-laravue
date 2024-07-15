<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <slot name="trigger"></slot>
            </TooltipTrigger>
            <TooltipContent>
                <slot name="content"></slot>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>

<script>
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/ui/tooltip";
export default {
    name: "TooltipTriggerComp",
};
</script>
