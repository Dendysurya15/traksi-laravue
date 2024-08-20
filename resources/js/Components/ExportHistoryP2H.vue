<script setup>
import ExcelJS from "exceljs";
import { ref } from "vue";
import { Button } from "@/Components/ui/button";
import { IconFileExcel, IconLoaderQuarter } from "@tabler/icons-vue";

// Define emits to send toast notifications
const emits = defineEmits(["sendToast"]);

const props = defineProps(["data", "title", "lhp", "dateUntilNow"]);

// State for loading
const loading = ref(false);

//handle column AA, BB , CC
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

function isSunday(date) {
    return date.getDay() === 0; // Sunday is 0 in JavaScript Date
}

const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function applyStyleCell(position, cell, text, background = "FFFFFFFF") {
    cell.value = text;
    cell.alignment = { horizontal: position, vertical: "middle" };
    cell.font = { bold: true };
    cell.fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: background },
    };
    cell.border = {
        top: { style: "medium", color: { argb: "FF000000" } },
        left: { style: "medium", color: { argb: "FF000000" } },
        bottom: { style: "medium", color: { argb: "FF000000" } },
        right: { style: "medium", color: { argb: "FF000000" } },
    };
}

const generateExcel = async () => {
    loading.value = true; // Set loading state to true

    setTimeout(async () => {
        try {
            const workbook = new ExcelJS.Workbook();
            const currentDate = new Date();

            // Define the locale for Indonesian
            const locale = "id-ID";

            // Get the current month number (0-based: January is 0)
            const currentMonthIndex = currentDate.getMonth();
            const startDateColumn = 5;
            // Loop through the months starting from the current month
            for (let i = 0; i <= currentMonthIndex; i++) {
                // Create a date object for the first day of the month
                const date = new Date(currentDate.getFullYear(), i, 1);

                // Get the month name in Indonesian
                const monthName = new Intl.DateTimeFormat(locale, {
                    month: "long",
                }).format(date);

                const formattedDate = `${date.getFullYear()}-${String(
                    date.getMonth() + 1
                ).padStart(2, "0")}`;
                // You can now store this formatted date in a variable or array
                // For example:
                let yearMonth = formattedDate;

                // Get the year
                const year = date.getFullYear();

                // Add a worksheet with the month name
                const worksheet = workbook.addWorksheet(
                    `${monthName.toUpperCase()} ${year}`
                );

                // Set main title and merge cells A1 to E1
                worksheet.mergeCells("A1:E1");
                worksheet.getCell("A1").value = "MONITORING UNIT PERBAIKAN";
                applyStyleCell(
                    "left",
                    worksheet.getCell("A1"),
                    "MONITORING UNIT PERBAIKAN"
                );

                // Set column headers in row 2
                const headers = ["WIL", "ASET", "KODE UNIT", "NO UNIT"];
                headers.forEach((header, index) => {
                    const cellAddress = String.fromCharCode(65 + index) + "2"; // Convert index to column letter
                    applyStyleCell(
                        "center",
                        worksheet.getCell(cellAddress),
                        header,
                        "FF92D050"
                    ); // Replace with your desired background color
                });
                worksheet.columns = [
                    { width: 10 }, // Column for WIL
                    { width: 10 }, // Column for ASET
                    { width: 15 }, // Column for KODE UNIT
                    { width: 15 }, // Column for NO UNIT
                ];

                worksheet.mergeCells("A2:A3");
                worksheet.mergeCells("B2:B3");
                worksheet.mergeCells("C2:C3");
                worksheet.mergeCells("D2:D3");

                // Set month header and merge cells E2 to end of month
                const numberOfDays = new Date(
                    currentDate.getFullYear(),
                    i + 1,
                    0
                ).getDate(); // Number of days in the month

                //tambah 1 untuk kolom total kerusakan
                const endColumnNumber = 4 + numberOfDays + 1; // Start from column E (5th column)
                const endColumnLetter = numberToExcelColumn(endColumnNumber); // Convert to Excel column letter
                const monthCell = worksheet.getCell("E2");
                worksheet.mergeCells(`E2:${endColumnLetter}2`);

                applyStyleCell(
                    "center",
                    monthCell,
                    `${monthName.toUpperCase()} ${year}`,
                    "FF92D050"
                );

                // Add date headers using props.dateUntilNow for the current month
                const monthNameKey = monthName.slice(0, 3); // Extract "Jan", "Feb", etc.
                const datesForMonth = props.dateUntilNow[monthNameKey];

                if (datesForMonth) {
                    const rowIndex = 3; // Row index where the dates are written
                    const row = worksheet.getRow(rowIndex);
                    row.height = 30;

                    Object.entries(datesForMonth).forEach(
                        ([day, dateValue], index) => {
                            const columnIndex = startDateColumn + index; // Adjust for the correct column
                            const cell = worksheet.getCell(
                                rowIndex,
                                columnIndex
                            );
                            const currentDate = new Date(dateValue);

                            const backgroundColor = isSunday(currentDate)
                                ? "FFFF0000"
                                : "FF92D050"; // Red for Sundays, Green for other days
                            applyStyleCell(
                                "center",
                                cell,
                                parseInt(day),
                                backgroundColor
                            );
                        }
                    );

                    // Add the extra column for TOTAL KERUSAKAN after the loop
                    const totalKerusakanColumnIndex =
                        startDateColumn + Object.keys(datesForMonth).length;
                    const totalKerusakanCell = worksheet.getCell(
                        rowIndex,
                        totalKerusakanColumnIndex
                    );
                    applyStyleCell(
                        "center",
                        totalKerusakanCell,
                        "TOTAL KERUSAKAN",
                        "FF92D050"
                    );
                    worksheet.getColumn(totalKerusakanColumnIndex).width = 15;
                    totalKerusakanCell.alignment = {
                        horizontal: "center",
                        wrapText: true,
                    };
                } else {
                    console.warn(`No date data available for ${monthNameKey}`);
                }

                const regWilEstLHP = props.lhp; // Adjust this according to your actual data source
                let rowIndex = 4;

                worksheet.views = [{ state: "frozen", xSplit: 4, ySplit: 3 }];

                regWilEstLHP.forEach((regional) => {
                    regional.wilayah.forEach((wilayah) => {
                        let wilayahStartRowIndex = rowIndex; // Start row index for the wilayah (Column A)
                        let wilayahTotalKerusakan = 0; // Initialize the total count of "P" for the wilayah

                        applyStyleCell(
                            "center",
                            worksheet.getCell(`A${rowIndex}`),
                            wilayah.nama,
                            "FFF4B083"
                        );

                        wilayah.estate.forEach((estate) => {
                            let estateStartRowIndex = rowIndex; // Start row index for the estate (Column B)
                            let estateTotalKerusakan = 0; // Initialize the count of "P" for the estate

                            applyStyleCell(
                                "center",
                                worksheet.getCell(`B${rowIndex}`),
                                estate.est,
                                "FF9CC2E5"
                            );

                            if (
                                estate.data &&
                                typeof estate.data === "object"
                            ) {
                                Object.entries(estate.data).forEach(
                                    ([key, items]) => {
                                        if (Array.isArray(items)) {
                                            items.forEach((item) => {
                                                applyStyleCell(
                                                    "center",
                                                    worksheet.getCell(
                                                        `C${rowIndex}`
                                                    ),
                                                    key
                                                );

                                                applyStyleCell(
                                                    "center",
                                                    worksheet.getCell(
                                                        `D${rowIndex}`
                                                    ),
                                                    item.no_unit || ""
                                                );

                                                for (
                                                    let day = 1;
                                                    day <= numberOfDays;
                                                    day++
                                                ) {
                                                    const fullDate = `${yearMonth}-${String(
                                                        day
                                                    ).padStart(2, "0")}`;
                                                    const columnIndex =
                                                        startDateColumn +
                                                        day -
                                                        1;
                                                    const columnLetter =
                                                        numberToExcelColumn(
                                                            columnIndex
                                                        );
                                                    const cell =
                                                        worksheet.getCell(
                                                            `${columnLetter}${rowIndex}`
                                                        );

                                                    applyStyleCell(
                                                        "center",
                                                        cell,
                                                        ""
                                                    );

                                                    const currentDate =
                                                        new Date(fullDate);
                                                    if (isSunday(currentDate)) {
                                                        cell.fill = {
                                                            type: "pattern",
                                                            pattern: "solid",
                                                            fgColor: {
                                                                argb: "FFFFD965",
                                                            }, // Yellow background for Sundays
                                                        };
                                                    }

                                                    if (
                                                        item.data &&
                                                        item.data[fullDate]
                                                    ) {
                                                        const currentItem =
                                                            item.data[fullDate];
                                                        const statusFollowUp =
                                                            currentItem.status_follow_up;
                                                        const status =
                                                            statusFollowUp.status;

                                                        // Check if status is false or an empty string
                                                        if (status === false) {
                                                            cell.value = "P";
                                                            cell.fill = {
                                                                type: "pattern",
                                                                pattern:
                                                                    "solid",
                                                                fgColor: {
                                                                    argb: "FFFF0000",
                                                                }, // Yellow background for Sundays
                                                            };
                                                        } else {
                                                            cell.value =
                                                                currentItem.kerusakan_unit_part; // or any other appropriate value or action
                                                        }

                                                        estateTotalKerusakan++;
                                                    }
                                                }

                                                let nonEmptyNonNumericCount = 0;
                                                for (
                                                    let day = 1;
                                                    day <= numberOfDays;
                                                    day++
                                                ) {
                                                    const columnIndex =
                                                        startDateColumn +
                                                        day -
                                                        1;
                                                    const columnLetter =
                                                        numberToExcelColumn(
                                                            columnIndex
                                                        );
                                                    const cellValue =
                                                        worksheet.getCell(
                                                            `${columnLetter}${rowIndex}`
                                                        ).value;

                                                    // Check if the cell value is non-empty and non-numeric
                                                    if (
                                                        cellValue !== "" &&
                                                        isNaN(cellValue)
                                                    ) {
                                                        nonEmptyNonNumericCount++;
                                                    }
                                                }

                                                // Add the extra column with the count of non-empty and non-numeric cells
                                                const extraColumnIndex =
                                                    startDateColumn +
                                                    numberOfDays;
                                                const extraColumnLetter =
                                                    numberToExcelColumn(
                                                        extraColumnIndex
                                                    );
                                                applyStyleCell(
                                                    "center",
                                                    worksheet.getCell(
                                                        `${extraColumnLetter}${rowIndex}`
                                                    ),
                                                    nonEmptyNonNumericCount
                                                );

                                                rowIndex++; // Increment rowIndex after processing each item
                                            });
                                        }
                                    }
                                );

                                // Merge cells in Column B for estate
                                if (estateStartRowIndex < rowIndex - 1) {
                                    worksheet.mergeCells(
                                        `B${estateStartRowIndex}:B${
                                            rowIndex - 1
                                        }`
                                    );
                                }

                                // Add total row for estate
                                applyStyleCell(
                                    "center",
                                    worksheet.getCell(`B${rowIndex}`),
                                    "TOTAL " + estate.est,
                                    "FF9CC2E5"
                                );
                                worksheet.mergeCells(
                                    `B${rowIndex}:C${rowIndex}`
                                );

                                // Add totals for each day in the extra column
                                for (let day = 1; day <= numberOfDays; day++) {
                                    const columnIndex =
                                        startDateColumn + day - 1;
                                    const columnLetter =
                                        numberToExcelColumn(columnIndex);
                                    const columnTotalCell = worksheet.getCell(
                                        `${columnLetter}${rowIndex}`
                                    );

                                    // Count non-empty cells within the estate boundary
                                    const countKerusakanInColumn = worksheet
                                        .getColumn(columnIndex)
                                        .values.slice(
                                            estateStartRowIndex,
                                            rowIndex - 1
                                        )
                                        .filter((value) => value != "").length;
                                    const fullDate = `${yearMonth}-${String(
                                        day
                                    ).padStart(2, "0")}`;
                                    const currentDate = new Date(fullDate);
                                    const backgroundColor = isSunday(
                                        currentDate
                                    )
                                        ? "FFF4B083"
                                        : "FF9CC2E5";
                                    applyStyleCell(
                                        "center",
                                        columnTotalCell,
                                        countKerusakanInColumn > 0
                                            ? countKerusakanInColumn
                                            : "",
                                        backgroundColor
                                    );
                                }

                                // Add total for the extra column (Total Kerusakan)
                                const extraColumnIndex =
                                    startDateColumn + numberOfDays;
                                const extraColumnLetter =
                                    numberToExcelColumn(extraColumnIndex);
                                applyStyleCell(
                                    "center",
                                    worksheet.getCell(
                                        `${extraColumnLetter}${rowIndex}`
                                    ),
                                    estateTotalKerusakan,
                                    "FF9CC2E5"
                                );

                                applyStyleCell(
                                    "center",
                                    worksheet.getCell(`D${rowIndex}`),
                                    estate.total,
                                    "FF9CC2E5"
                                );
                                rowIndex++;

                                // Add estateTotalKerusakan to wilayahTotalKerusakan
                                wilayahTotalKerusakan += estateTotalKerusakan;
                            }
                        });

                        // Merge cells in Column A for wilayah (region)
                        if (wilayahStartRowIndex < rowIndex - 1) {
                            worksheet.mergeCells(
                                `A${wilayahStartRowIndex}:A${rowIndex - 1}`
                            );
                        }

                        // Add a total row after all items for wilayah
                        applyStyleCell(
                            "center",
                            worksheet.getCell(`A${rowIndex}`),
                            wilayah.nama.toUpperCase(),
                            "FFF4B083"
                        );
                        worksheet.mergeCells(`A${rowIndex}:C${rowIndex}`);

                        // Add totals for each day in the extra column for wilayah
                        for (let day = 1; day <= numberOfDays; day++) {
                            const columnIndex = startDateColumn + day - 1;
                            const columnLetter =
                                numberToExcelColumn(columnIndex);
                            const columnTotalCell = worksheet.getCell(
                                `${columnLetter}${rowIndex}`
                            );

                            // Count non-empty cells within the wilayah boundary
                            const countKerusakanInColumn = worksheet
                                .getColumn(columnIndex)
                                .values.slice(
                                    wilayahStartRowIndex,
                                    rowIndex - 1
                                )
                                .filter(
                                    (value) => value !== "" && isNaN(value)
                                ).length;

                            applyStyleCell(
                                "center",
                                columnTotalCell,
                                countKerusakanInColumn > 0
                                    ? countKerusakanInColumn
                                    : "",
                                "FFF4B083"
                            );
                        }

                        const extraColumnIndex = startDateColumn + numberOfDays;
                        const extraColumnLetter =
                            numberToExcelColumn(extraColumnIndex);
                        applyStyleCell(
                            "center",
                            worksheet.getCell(
                                `${extraColumnLetter}${rowIndex}`
                            ),
                            wilayahTotalKerusakan,
                            "FFF4B083"
                        );

                        applyStyleCell(
                            "center",
                            worksheet.getCell(`D${rowIndex}`),
                            wilayah.total,
                            "FFF4B083"
                        );
                        rowIndex++; // Move to the next row after total
                    });
                });

                // Set column widths

                // Freeze the first four columns
                // worksheet.views = [{ state: "frozen", xSplit: 4, ySplit: 3 }];
            }

            // await delay(1500);
            // Generate the Excel file
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: "application/octet-stream",
            });
            const link = document.createElement("a");
            const currentYear = new Date().getFullYear(); // Get the current year
            link.href = URL.createObjectURL(blob);
            link.download = `${props.title} ${currentYear}.xlsx`; // Concatenate the title with the current year
            link.click();

            // Emit success toast
            emits("sendToast", {
                title: "Success",
                color: "green",
                description:
                    "Excel file generated and downloaded successfully!",
            });
        } catch (error) {
            // Delay before emitting error toast
            // await delay(1500); // 2-second delay

            // Emit error toast
            emits("sendToast", {
                title: "Error",
                color: "red",
                description:
                    "Failed to generate the Excel file. Please try again." +
                    error,
            });
        } finally {
            loading.value = false; // Set loading state to false
        }
    }, 0); // 0 milliseconds delay to allow UI update
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
