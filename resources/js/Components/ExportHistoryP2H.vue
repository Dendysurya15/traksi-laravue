<script setup>
import ExcelJS from "exceljs";
import { ref } from "vue";
import { Button } from "@/Components/ui/button";
import { IconFileExcel, IconLoaderQuarter } from "@tabler/icons-vue";

// Define emits to send toast notifications
const emits = defineEmits(["sendToast"]);

const props = defineProps(["data", "title"]);

// State for loading
const loading = ref(false);

function numberToExcelColumn(number) {
    let column = "";
    let temp;

    while (number > 0) {
        temp = (number - 1) % 26;
        column = String.fromCharCode(65 + temp) + column;
        number = Math.floor((number - temp) / 26);
    }

    return column;
}

const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const generateExcel = async () => {
    loading.value = true; // Set loading state to true

    try {
        const workbook = new ExcelJS.Workbook();
        const currentDate = new Date();

        // Define the locale for Indonesian
        const locale = "id-ID";

        // Get the current month number (0-based: January is 0)
        const currentMonthIndex = currentDate.getMonth();

        // Loop through the months starting from the current month
        for (let i = 0; i <= currentMonthIndex; i++) {
            // Create a date object for the first day of the month
            const date = new Date(currentDate.getFullYear(), i, 1);

            // Get the month name in Indonesian
            const monthName = new Intl.DateTimeFormat(locale, {
                month: "long",
            }).format(date);

            // Get the year
            const year = date.getFullYear();

            // Add a worksheet with the month name
            const worksheet = workbook.addWorksheet(
                `${monthName.toUpperCase()} ${year}`
            );

            // Set main title and merge cells A1 to E1
            worksheet.mergeCells("A1:E1");
            worksheet.getCell("A1").value = props.data.length;
            worksheet.getCell("A1").alignment = { horizontal: "center" };
            worksheet.getCell("A1").font = { bold: true };

            // Set column headers in row 2
            const headers = ["WIL", "ASET", "KODE UNIT", "NO UNIT"];
            worksheet.getCell("A2").value = headers[0];
            worksheet.getCell("B2").value = headers[1];
            worksheet.getCell("C2").value = headers[2];
            worksheet.getCell("D2").value = headers[3];

            // Set month header and merge cells E2 to end of month
            const numberOfDays = new Date(
                currentDate.getFullYear(),
                i + 1,
                0
            ).getDate(); // Number of days in the month

            const endColumnNumber = 4 + numberOfDays; // Start from column E (5th column)
            const endColumnLetter = numberToExcelColumn(endColumnNumber); // Convert to Excel column letter

            worksheet.mergeCells(`E2:${endColumnLetter}2`);
            worksheet.getCell(
                "E2"
            ).value = `${monthName.toUpperCase()} ${year}`;
            worksheet.getCell("E2").alignment = { horizontal: "center" };
            worksheet.getCell("E2").font = { bold: true };

            // Add date headers starting from row 3, starting from column E
            const startDateColumn = 5; // Column E is 5

            for (let j = 1; j <= numberOfDays; j++) {
                worksheet.getCell(3, startDateColumn + j - 1).value = j;
                worksheet.getCell(3, startDateColumn + j - 1).alignment = {
                    horizontal: "center",
                };
            }

            // Set column widths
            worksheet.columns = [
                { width: 15 }, // Column for WIL
                { width: 20 }, // Column for ASET
                { width: 20 }, // Column for KODE UNIT
                { width: 20 }, // Column for NO UNIT
            ];

            // Freeze the first four columns
            worksheet.views = [{ state: "frozen", xSplit: 4 }];
        }

        // await delay(1500);
        // Generate the Excel file
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/octet-stream" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "example.xlsx";
        link.click();

        // Emit success toast
        emits("sendToast", {
            title: "Success",
            color: "green",
            description: "Excel file generated and downloaded successfully!",
        });
    } catch (error) {
        // Delay before emitting error toast
        // await delay(1500); // 2-second delay

        // Emit error toast
        emits("sendToast", {
            title: "Error",
            color: "red",
            description:
                "Failed to generate the Excel file. Please try again." + error,
        });
    } finally {
        loading.value = false; // Set loading state to false
    }
};
</script>

<template>
    <Button
        @click="generateExcel"
        :disabled="loading"
        class="ml-auto bg-green-600 hover:bg-green-500 hover:shadow-sm"
    >
        <span v-if="loading" class="ml-2">
            <div class="flex justify-center gap-1">
                <IconLoaderQuarter size="20" class="animate-spin" />
                Sedang Mengunduh...
            </div>
        </span>
        <div v-else>
            <div class="flex justify-center gap-1">
                <IconFileExcel size="20" />
                {{ props.title }}
            </div>
        </div>
    </Button>
</template>
