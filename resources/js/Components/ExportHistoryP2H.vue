<script setup>
import ExcelJS from "exceljs";
import { ref } from "vue";
import { Button } from "@/Components/ui/button";
import { IconFileExcel } from "@tabler/icons-vue";

// Define emits to send toast notifications
const emits = defineEmits(["sendToast"]);

const props = defineProps(["data", "title"]);

const generateExcel = async () => {
    try {
        // Create a new workbook and add a worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Sheet 1");

        // Add some data
        worksheet.columns = [
            { header: "ID", key: "id", width: 10 },
            { header: "Name", key: "name", width: 30 },
            { header: "Email", key: "email", width: 30 },
        ];

        worksheet.addRow([3, "Sam", new Date()]);
        worksheet.addRow({
            id: 1,
            name: "John Doe",
            email: "john@example.com",
        });
        worksheet.addRow({
            id: 2,
            name: "Jane Smith",
            email: "jane@example.com",
        });

        const buffer = await workbook.xlsx.writeBuffer();

        const blob = new Blob([buffer], { type: "application/octet-stream" });

        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "example.xlsx";
        link.click();

        URL.revokeObjectURL(link.href);

        // Emit success toast
        emits("sendToast", {
            title: "Success",
            color: "green",
            description: "Excel file generated and downloaded successfully!",
        });
    } catch (error) {
        // Emit error toast
        emits("sendToast", {
            title: "Error",
            color: "red",
            description:
                "Failed to generate the Excel file. Please try again." + error,
        });
    }
};
</script>

<template>
    <Button
        @click="generateExcel"
        class="ml-auto bg-green-600 hover:bg-green-500 flex justify-center gap-1 hover:shadow-sm"
    >
        <IconFileExcel size="20" />
        {{ props.title }}
    </Button>
</template>

<style scoped>
/* Add any custom styles here if needed */
</style>
