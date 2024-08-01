import { h } from "vue";
import {
    ExclamationTriangleIcon,
    EyeIcon,
    PencilIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/solid";
import { setActionTriggered } from "@/lib/actionStateTable";
import { updateSharedState } from "@/lib/sharedDataState";
import { LaporanP2H } from "@/types/laporanP2h";
import { Checkbox } from "@/Components/ui/checkbox";
import { ArrowsUpDownIcon } from "@heroicons/vue/24/solid";

import { useDateFormat } from "@vueuse/core";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
// Function to handle action click
const handleActionClick = (actionType: string, data: LaporanP2H) => {
    setActionTriggered(true);
    updateSharedState(actionType, data);
};

const renderKerusakanBadge = (value: string | null | undefined) => {
    if (!value) {
        return h(Badge, { class: "bg-green-600 text-white" }, "Unit Normal");
    } else {
        try {
            // Try to parse the value as JSON
            const parsedValue = JSON.parse(value);
            if (Array.isArray(parsedValue)) {
                const badges = parsedValue.slice(0, 2).map((val: string) =>
                    h(
                        Badge,
                        {
                            class: "bg-red-500 text-white  hover:bg-red-600 rounded-sm mb-1 mr-1",
                        },
                        val
                    )
                );

                if (parsedValue.length > 2) {
                    badges.push(
                        h(
                            Badge,
                            {
                                class: "bg-red-500 text-white hover:bg-red-600 rounded-sm mb-1",
                            },
                            "...."
                        )
                    );
                }

                return badges;
            } else {
                // If parsedValue is not an array, render a single badge
                return h(Badge, { class: "bg-green-600 text-black" }, value);
            }
        } catch (e) {
            // If parsing fails, render a single badge
            return h(Badge, { class: "bg-green-600 text-black" }, value);
        }
    }
};

// Example data structure for columns
export const columns: ColumnDef<LaporanP2H>[] = [
    {
        id: "select",
        header: ({ table }) =>
            h(Checkbox, {
                checked: table.getIsAllPageRowsSelected(),
                "onUpdate:checked": (value: boolean) =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: "Select all",
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                checked: row.getIsSelected(),
                "onUpdate:checked": (value: boolean) =>
                    row.toggleSelected(!!value),
                ariaLabel: "Select row",
            }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        id: "status_follow_up",
        accessorKey: "status_follow_up",
        header: () =>
            h("div", { class: "flex items-center" }, [
                h("span", { class: "mr-2" }, "Status FU Kerusakan"),
            ]),
        cell: ({ row }) => {
            const statusFollowUp = row.getValue("status_follow_up");
            const kerusakanUnitExist = row.getValue("kerusakan_unit_part");

            if (!kerusakanUnitExist) {
                return h("div", { class: "flex items-center w-48" }, [
                    h(CheckCircleIcon, {
                        class: "h-5 w-5 text-gray-500 ",
                    }),
                    h(
                        "span",
                        { class: "ml-2 text-gray-500" },
                        "Tidak Perlu Follow Up!"
                    ),
                ]);
            }
            if (!statusFollowUp || statusFollowUp === "") {
                return h("div", { class: "flex items-center w-36" }, [
                    h(ExclamationTriangleIcon, {
                        class: "h-5 w-5 text-yellow-500 animate-bounce",
                    }),
                    h(
                        "span",
                        { class: "ml-2 text-yellow-500" },
                        "Butuh Follow Up!"
                    ),
                ]);
            } else {
                return h(
                    "div",
                    { class: "flex items-center w-36 text-green-500" },
                    [
                        h(CheckCircleIcon, { class: "h-5 w-5" }),
                        h("span", { class: "ml-2" }, "Sudah Follow Up"),
                    ]
                );
            }
        },
        enableSorting: true,
    },
    {
        id: "tanggal_upload",
        accessorKey: "tanggal_upload",
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: "ghost",
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === "asc"),
                },
                () => [
                    "TANGGAL UPLOAD",
                    h(ArrowsUpDownIcon, { class: "ml-2 h-4 w-4" }),
                ]
            ),
        cell: ({ row }) => {
            const formattedDate = useDateFormat(
                row.getValue("tanggal_upload"),
                "dddd, D MMMM YYYY HH:mm",
                { locales: "id-ID" }
            );

            return h("div", { class: "" }, formattedDate.value);
        },
        filterFn: (row, columnId, filterValue) => {
            const rawDate = row.getValue(columnId);
            const formattedDate = useDateFormat(
                rawDate,
                "dddd, D MMMM YYYY HH:mm",
                { locales: "id-ID" }
            ).value;
            return formattedDate.includes(filterValue);
        },
    },
    {
        accessorKey: "jenis_unit",
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: "ghost",
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === "asc"),
                },
                () => [
                    "JENIS UNIT",
                    h(ArrowsUpDownIcon, { class: "ml-2 h-4 w-4" }),
                ]
            ),
        cell: ({ row }) =>
            h("div", { class: "lowercase" }, row.getValue("jenis_unit")),
    },
    {
        accessorKey: "kode_unit",
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: "ghost",
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === "asc"),
                },
                () => [
                    "KODE UNIT",
                    h(ArrowsUpDownIcon, { class: "ml-2 h-4 w-4" }),
                ]
            ),
    },
    {
        accessorKey: "unit_kerja",
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: "ghost",
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === "asc"),
                },
                () => [
                    "UNIT KERJA",
                    h(ArrowsUpDownIcon, { class: "ml-2 h-4 w-4" }),
                ]
            ),
    },
    {
        header: "LIST KERUSAKAN",
        accessorKey: "kerusakan_unit_part",
        cell: ({ row }) =>
            renderKerusakanBadge(row.getValue("kerusakan_unit_part")),
    },
    {
        header: "DRIVER / USER",
        accessorKey: "user",
    },
    {
        id: "actions",
        header: "AKSI",
        enableHiding: false,
        cell: ({ row }) => {
            const data = row.original;
            const kerusakanUnitPart = row.getValue("kerusakan_unit_part");

            return kerusakanUnitPart
                ? h(
                      "div",
                      { class: "flex items-center space-x-2 cursor-pointer" },
                      [
                          h(TooltipProvider, null, () =>
                              h(Tooltip, null, () => [
                                  h(
                                      TooltipTrigger,
                                      {
                                          onClick: () =>
                                              handleActionClick("detail", data),
                                      },
                                      () =>
                                          h(EyeIcon, {
                                              class: "w-5 h-5 hover:text-yellow-700 text-yellow-500",
                                          })
                                  ),
                                  h(TooltipContent, null, () =>
                                      h("p", null, "Detail Laporan Kerusakan")
                                  ),
                              ])
                          ),
                      ]
                  )
                : null;
        },
    },
];
