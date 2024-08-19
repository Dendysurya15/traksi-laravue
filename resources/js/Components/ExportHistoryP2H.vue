<script setup>
import ExcelJS from "exceljs";
import { ref } from "vue";
import { Button } from "@/Components/ui/button";
import { IconFileExcel, IconLoaderQuarter } from "@tabler/icons-vue";

// Define emits to send toast notifications
const emits = defineEmits(["sendToast"]);

const props = defineProps(["data", "title", "regWilEst", "dateUntilNow"]);

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

const extractNumber = (no_unit) => {
    // Use a regular expression to match all digits in the string
    const matches = no_unit.match(/\d+/g);
    // Join all matched numbers into a single string or default to an empty string if no match is found
    return matches ? matches.join("") : "";
};

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
                ); // Replace with your desired background color

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
                            cell.value = parseInt(day);
                            cell.alignment = {
                                horizontal: "center",
                                vertical: "middle", // Center the value vertically
                            };

                            cell.font = {
                                bold: true,
                            };

                            cell.border = {
                                top: {
                                    style: "medium",
                                    color: { argb: "FF000000" },
                                }, // Black border
                                left: {
                                    style: "medium",
                                    color: { argb: "FF000000" },
                                },
                                bottom: {
                                    style: "medium",
                                    color: { argb: "FF000000" },
                                },
                                right: {
                                    style: "medium",
                                    color: { argb: "FF000000" },
                                },
                            };

                            // Check if the day is a Sunday
                            const currentDate = new Date(dateValue);
                            if (isSunday(currentDate)) {
                                cell.fill = {
                                    type: "pattern",
                                    pattern: "solid",
                                    fgColor: { argb: "FFFF0000" }, // Red background for Sunday
                                };
                            } else {
                                cell.fill = {
                                    type: "pattern",
                                    pattern: "solid",
                                    fgColor: { argb: "FF92D050" }, // Green background for other days
                                };
                            }
                        }
                    );

                    // Add the extra column for TOTAL KERUSAKAN after the loop
                    const totalKerusakanColumnIndex =
                        startDateColumn + Object.keys(datesForMonth).length;
                    const totalKerusakanCell = worksheet.getCell(
                        rowIndex,
                        totalKerusakanColumnIndex
                    );
                    totalKerusakanCell.value = "TOTAL KERUSAKAN";
                    totalKerusakanCell.alignment = {
                        horizontal: "center",
                        vertical: "middle", // Center the value vertically
                        wrapText: true, // Enable text wrapping
                    };
                    totalKerusakanCell.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "FF92D050" }, // Green background
                    };
                    totalKerusakanCell.font = {
                        bold: true,
                    };
                    totalKerusakanCell.border = {
                        top: {
                            style: "medium",
                            color: { argb: "FF000000" },
                        }, // Black border
                        left: {
                            style: "medium",
                            color: { argb: "FF000000" },
                        },
                        bottom: {
                            style: "medium",
                            color: { argb: "FF000000" },
                        },
                        right: {
                            style: "medium",
                            color: { argb: "FF000000" },
                        },
                    };
                    worksheet.getColumn(totalKerusakanColumnIndex).width = 15;
                } else {
                    console.warn(`No date data available for ${monthNameKey}`);
                }

                const regWilEst = props.regWilEst; // Adjust this according to your actual data source
                let rowIndex = 4; // Start from the row below headers

                worksheet.views = [{ state: "frozen", xSplit: 4, ySplit: 3 }];

                regWilEst.forEach((regional) => {
                    regional.wilayah.forEach((wilayah) => {
                        let wilayahStartRowIndex = rowIndex; // Start row index for the wilayah (Column A)
                        let wilayahTotalPCount = 0; // Initialize the total count of "P" for the wilayah

                        applyStyleCell(
                            "center",
                            worksheet.getCell(`A${rowIndex}`),
                            wilayah.nama,
                            "FFF4B083"
                        );

                        wilayah.estate.forEach((estate) => {
                            let estateStartRowIndex = rowIndex; // Start row index for the estate (Column B)
                            let estatePCount = 0; // Initialize the count of "P" for the estate

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

                                                let countP = 0; // Initialize the count for "P"

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
                                                        cell.value = "P"; // Fill with "P"
                                                        countP++; // Increment the count of "P"
                                                        estatePCount++; // Increment the estate count of "P"
                                                        cell.fill = {
                                                            type: "pattern",
                                                            pattern: "solid",
                                                            fgColor: {
                                                                argb: "FFFF0000",
                                                            }, // Red background
                                                        };
                                                    }
                                                }

                                                // Add an extra column for item.jum_kerusakan_per_unit
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
                                                    item.jum_kerusakan_per_unit
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
                                // for (let day = 1; day <= numberOfDays; day++) {
                                //     const columnIndex =
                                //         startDateColumn + day - 1;
                                //     const columnLetter =
                                //         numberToExcelColumn(columnIndex);
                                //     const columnTotalCell = worksheet.getCell(
                                //         `${columnLetter}${rowIndex}`
                                //     );
                                //     const countPInColumn = worksheet
                                //         .getColumn(columnIndex)
                                //         .values.filter(
                                //             (value) => value === "P"
                                //         ).length;
                                //     applyStyleCell(
                                //         "center",
                                //         columnTotalCell,
                                //         countPInColumn > 0 ? countPInColumn : ""
                                //     );
                                // }

                                // Add extra column for total of "P" for the estate
                                // const extraColumnIndex =
                                //     startDateColumn + numberOfDays;
                                // const extraColumnLetter =
                                //     numberToExcelColumn(extraColumnIndex);
                                // applyStyleCell(
                                //     "center",
                                //     worksheet.getCell(
                                //         `${extraColumnLetter}${rowIndex}`
                                //     ),
                                //     estatePCount
                                // );

                                applyStyleCell(
                                    "center",
                                    worksheet.getCell(`D${rowIndex}`),
                                    estate.total
                                );
                                rowIndex++;

                                // Add estate "P" count to wilayah total
                                wilayahTotalPCount += estatePCount;
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

                        // // Add totals for each day in the extra column for wilayah
                        // for (let day = 1; day <= numberOfDays; day++) {
                        //     const columnIndex = startDateColumn + day - 1;
                        //     const columnLetter =
                        //         numberToExcelColumn(columnIndex);
                        //     const columnTotalCell = worksheet.getCell(
                        //         `${columnLetter}${rowIndex}`
                        //     );
                        //     const countPInColumn = worksheet
                        //         .getColumn(columnIndex)
                        //         .values.filter((value) => value === "P").length;
                        //     applyStyleCell(
                        //         "center",
                        //         columnTotalCell,
                        //         countPInColumn > 0 ? countPInColumn : ""
                        //     );
                        // }

                        // Add extra column for total of "P" for the wilayah
                        // const extraColumnIndex = startDateColumn + numberOfDays;
                        // const extraColumnLetter =
                        //     numberToExcelColumn(extraColumnIndex);
                        // applyStyleCell(
                        //     "center",
                        //     worksheet.getCell(
                        //         `${extraColumnLetter}${rowIndex}`
                        //     ),
                        //     wilayahTotalPCount
                        // );

                        applyStyleCell(
                            "center",
                            worksheet.getCell(`D${rowIndex}`),
                            wilayah.total
                        );
                        rowIndex++; // Move to the next row after total
                    });
                });

                // regWilEst.forEach((regional) => {
                //     regional.wilayah.forEach((wilayah) => {
                //         let wilayahStartRowIndex = rowIndex; // Start row index for the wilayah (Column A)
                //         let wilayahTotalPCount = 0; // Initialize the total count of "P" for the wilayah

                //         applyStyleCell(
                //             "center",
                //             worksheet.getCell(`A${rowIndex}`),
                //             wilayah.nama,
                //             "FFF4B083"
                //         );

                //         wilayah.estate.forEach((estate) => {
                //             let estateStartRowIndex = rowIndex; // Start row index for the estate (Column B)
                //             let estatePCount = 0; // Initialize the count of "P" for the estate

                //             applyStyleCell(
                //                 "center",
                //                 worksheet.getCell(`B${rowIndex}`),
                //                 estate.est,
                //                 "FF9CC2E5"
                //             );

                //             if (
                //                 estate.data &&
                //                 typeof estate.data === "object"
                //             ) {
                //                 Object.entries(estate.data).forEach(
                //                     ([key, items]) => {
                //                         if (Array.isArray(items)) {
                //                             items.forEach((item) => {
                //                                 applyStyleCell(
                //                                     "center",
                //                                     worksheet.getCell(
                //                                         `C${rowIndex}`
                //                                     ),
                //                                     key
                //                                 );

                //                                 applyStyleCell(
                //                                     "center",
                //                                     worksheet.getCell(
                //                                         `D${rowIndex}`
                //                                     ),
                //                                     item.no_unit || ""
                //                                 );

                //                                 let countP = 0; // Initialize the count for "P"

                //                                 for (
                //                                     let day = 1;
                //                                     day <= numberOfDays;
                //                                     day++
                //                                 ) {
                //                                     const fullDate = `${yearMonth}-${String(
                //                                         day
                //                                     ).padStart(2, "0")}`;
                //                                     const columnIndex =
                //                                         startDateColumn +
                //                                         day -
                //                                         1;
                //                                     const columnLetter =
                //                                         numberToExcelColumn(
                //                                             columnIndex
                //                                         );
                //                                     const cell =
                //                                         worksheet.getCell(
                //                                             `${columnLetter}${rowIndex}`
                //                                         );

                //                                     applyStyleCell(
                //                                         "center",
                //                                         cell,
                //                                         ""
                //                                     );

                //                                     const currentDate =
                //                                         new Date(fullDate);
                //                                     if (isSunday(currentDate)) {
                //                                         cell.fill = {
                //                                             type: "pattern",
                //                                             pattern: "solid",
                //                                             fgColor: {
                //                                                 argb: "FFFFD965",
                //                                             }, // Yellow background for Sundays
                //                                         };
                //                                     }

                //                                     if (
                //                                         item.data &&
                //                                         item.data[fullDate]
                //                                     ) {
                //                                         cell.value = "P"; // Fill with "P"
                //                                         countP++; // Increment the count of "P"
                //                                         estatePCount++; // Increment the estate count of "P"
                //                                         cell.fill = {
                //                                             type: "pattern",
                //                                             pattern: "solid",
                //                                             fgColor: {
                //                                                 argb: "FFFF0000",
                //                                             }, // Red background
                //                                         };
                //                                     }
                //                                 }

                //                                 // Add an extra column for item.jum_kerusakan_per_unit
                //                                 const extraColumnIndex =
                //                                     startDateColumn +
                //                                     numberOfDays;
                //                                 const extraColumnLetter =
                //                                     numberToExcelColumn(
                //                                         extraColumnIndex
                //                                     );
                //                                 applyStyleCell(
                //                                     "center",
                //                                     worksheet.getCell(
                //                                         `${extraColumnLetter}${rowIndex}`
                //                                     ),
                //                                     item.jum_kerusakan_per_unit
                //                                 );

                //                                 rowIndex++; // Increment rowIndex after processing each item
                //                             });
                //                         }
                //                     }
                //                 );

                //                 // Merge cells in Column B for estate
                //                 if (estateStartRowIndex < rowIndex - 1) {
                //                     worksheet.mergeCells(
                //                         `B${estateStartRowIndex}:B${
                //                             rowIndex - 1
                //                         }`
                //                     );
                //                 }

                //                 // Add total row for estate
                //                 applyStyleCell(
                //                     "center",
                //                     worksheet.getCell(`B${rowIndex}`),
                //                     "TOTAL " + estate.est,
                //                     "FF9CC2E5"
                //                 );
                //                 worksheet.mergeCells(
                //                     `B${rowIndex}:C${rowIndex}`
                //                 );

                //                 // Summarize the "P" values in the total row
                //                 for (let day = 1; day <= numberOfDays; day++) {
                //                     const columnIndex =
                //                         startDateColumn + day - 1;
                //                     const columnLetter =
                //                         numberToExcelColumn(columnIndex);
                //                     const columnTotalCell = worksheet.getCell(
                //                         `${columnLetter}${rowIndex}`
                //                     );
                //                     const countPInColumn = worksheet
                //                         .getColumn(columnIndex)
                //                         .values.filter(
                //                             (value) => value === "P"
                //                         ).length;
                //                     applyStyleCell(
                //                         "center",
                //                         columnTotalCell,
                //                         countPInColumn > 0 ? countPInColumn : ""
                //                     );
                //                 }

                //                 applyStyleCell(
                //                     "center",
                //                     worksheet.getCell(`D${rowIndex}`),
                //                     estate.total
                //                 );
                //                 rowIndex++;

                //                 // Add estate "P" count to wilayah total
                //                 wilayahTotalPCount += estatePCount;
                //             }
                //         });

                //         // Merge cells in Column A for wilayah (region)
                //         if (wilayahStartRowIndex < rowIndex - 1) {
                //             worksheet.mergeCells(
                //                 `A${wilayahStartRowIndex}:A${rowIndex - 1}`
                //             );
                //         }

                //         // Add a total row after all items for wilayah
                //         applyStyleCell(
                //             "center",
                //             worksheet.getCell(`A${rowIndex}`),
                //             wilayah.nama.toUpperCase(),
                //             "FFF4B083"
                //         );
                //         worksheet.mergeCells(`A${rowIndex}:C${rowIndex}`);

                //         // Add total row for the count of "P" for wilayah
                //         for (let day = 1; day <= numberOfDays; day++) {
                //             const columnIndex = startDateColumn + day - 1;
                //             const columnLetter =
                //                 numberToExcelColumn(columnIndex);
                //             const columnTotalCell = worksheet.getCell(
                //                 `${columnLetter}${rowIndex}`
                //             );
                //             const countPInColumn = worksheet
                //                 .getColumn(columnIndex)
                //                 .values.filter((value) => value === "P").length;
                //             applyStyleCell(
                //                 "center",
                //                 columnTotalCell,
                //                 countPInColumn > 0 ? countPInColumn : ""
                //             );
                //         }

                //         applyStyleCell(
                //             "center",
                //             worksheet.getCell(`D${rowIndex}`),
                //             wilayahTotalPCount
                //         );
                //         rowIndex++; // Move to the next row after total
                //     });
                // });

                // regWilEst.forEach((regional) => {
                //     regional.wilayah.forEach((wilayah) => {
                //         let wilayahStartRowIndex = rowIndex; // Start row index for the wilayah (Column A)

                //         applyStyleCell(
                //             "center",
                //             worksheet.getCell(`A${rowIndex}`),
                //             wilayah.nama,
                //             "FFF4B083"
                //         );

                //         wilayah.estate.forEach((estate) => {
                //             let estateStartRowIndex = rowIndex; // Start row index for the estate (Column B)

                //             applyStyleCell(
                //                 "center",
                //                 worksheet.getCell(`B${rowIndex}`),
                //                 estate.est,
                //                 "FF9CC2E5"
                //             );

                //             if (
                //                 estate.data &&
                //                 typeof estate.data === "object"
                //             ) {
                //                 Object.entries(estate.data).forEach(
                //                     ([key, items]) => {
                //                         if (Array.isArray(items)) {
                //                             items.forEach((item) => {
                //                                 applyStyleCell(
                //                                     "center",
                //                                     worksheet.getCell(
                //                                         `C${rowIndex}`
                //                                     ),
                //                                     key
                //                                 );

                //                                 applyStyleCell(
                //                                     "center",
                //                                     worksheet.getCell(
                //                                         `D${rowIndex}`
                //                                     ),
                //                                     item.no_unit || ""
                //                                 );

                //                                 for (
                //                                     let day = 1;
                //                                     day <= numberOfDays;
                //                                     day++
                //                                 ) {
                //                                     const fullDate = `${yearMonth}-${String(
                //                                         day
                //                                     ).padStart(2, "0")}`;
                //                                     const columnIndex =
                //                                         startDateColumn +
                //                                         day -
                //                                         1;
                //                                     const columnLetter =
                //                                         numberToExcelColumn(
                //                                             columnIndex
                //                                         );
                //                                     const cell =
                //                                         worksheet.getCell(
                //                                             `${columnLetter}${rowIndex}`
                //                                         );

                //                                     applyStyleCell(
                //                                         "center",
                //                                         cell,
                //                                         ""
                //                                     );

                //                                     const currentDate =
                //                                         new Date(fullDate);
                //                                     if (isSunday(currentDate)) {
                //                                         cell.fill = {
                //                                             type: "pattern",
                //                                             pattern: "solid",
                //                                             fgColor: {
                //                                                 argb: "FFFFD965",
                //                                             }, // Yellow background for Sundays
                //                                         };
                //                                     }

                //                                     if (
                //                                         item.data &&
                //                                         item.data[fullDate]
                //                                     ) {
                //                                         cell.value = "P"; // Fill with any value
                //                                         cell.fill = {
                //                                             type: "pattern",
                //                                             pattern: "solid",
                //                                             fgColor: {
                //                                                 argb: "FFFF0000",
                //                                             }, // Red background
                //                                         };
                //                                     }
                //                                 }

                //                                 // Add an extra column for item.jum_kerusakan_per_unit
                //                                 const extraColumnIndex =
                //                                     startDateColumn +
                //                                     numberOfDays;
                //                                 const extraColumnLetter =
                //                                     numberToExcelColumn(
                //                                         extraColumnIndex
                //                                     );
                //                                 applyStyleCell(
                //                                     "center",
                //                                     worksheet.getCell(
                //                                         `${extraColumnLetter}${rowIndex}`
                //                                     ),
                //                                     item.jum_kerusakan_per_unit
                //                                 );

                //                                 rowIndex++; // Increment rowIndex after processing each item
                //                             });
                //                         }
                //                     }
                //                 );

                //                 // Merge cells in Column B for estate
                //                 if (estateStartRowIndex < rowIndex - 1) {
                //                     worksheet.mergeCells(
                //                         `B${estateStartRowIndex}:B${
                //                             rowIndex - 1
                //                         }`
                //                     );
                //                 }

                //                 // Add a total row after all items
                //                 applyStyleCell(
                //                     "center",
                //                     worksheet.getCell(`B${rowIndex}`),
                //                     "TOTAL " + estate.est,
                //                     "FF9CC2E5"
                //                 );
                //                 worksheet.mergeCells(
                //                     `B${rowIndex}:C${rowIndex}`
                //                 );

                //                 applyStyleCell(
                //                     "center",
                //                     worksheet.getCell(`D${rowIndex}`),
                //                     estate.total
                //                 );
                //                 rowIndex++; // Move to the next row after total
                //             }
                //         });

                //         // Merge cells in Column A for wilayah (region)
                //         if (wilayahStartRowIndex < rowIndex - 1) {
                //             worksheet.mergeCells(
                //                 `A${wilayahStartRowIndex}:A${rowIndex - 1}`
                //             );
                //         }

                //         // Add a total row after all items
                //         applyStyleCell(
                //             "center",
                //             worksheet.getCell(`A${rowIndex}`),
                //             wilayah.nama.toUpperCase(),
                //             "FFF4B083"
                //         );
                //         worksheet.mergeCells(`A${rowIndex}:C${rowIndex}`);

                //         applyStyleCell(
                //             "center",
                //             worksheet.getCell(`D${rowIndex}`),
                //             wilayah.total
                //         );
                //         rowIndex++; // Move to the next row after total
                //     });
                // });

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
            link.href = URL.createObjectURL(blob);
            link.download = "example.xlsx";
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
