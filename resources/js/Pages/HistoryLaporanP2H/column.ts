import { h } from "vue";
import { EyeIcon, PencilIcon } from "@heroicons/vue/24/solid";
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
                            class: "bg-red-500 text-white rounded-sm mb-1 mr-1",
                        },
                        val
                    )
                );

                if (parsedValue.length > 2) {
                    badges.push(
                        h(
                            Badge,
                            {
                                class: "bg-red-500 text-white rounded-sm mb-1",
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
                    "Tanggal Upload",
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
                    "Jenis Unit",
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
                    "Kode Unit",
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
                    "Unit Kerja",
                    h(ArrowsUpDownIcon, { class: "ml-2 h-4 w-4" }),
                ]
            ),
    },
    {
        header: "List Kerusakan",
        accessorKey: "kerusakan_unit_part",
        cell: ({ row }) =>
            renderKerusakanBadge(row.getValue("kerusakan_unit_part")),
    },
    {
        header: "Driver / User",
        accessorKey: "user",
    },
    {
        id: "actions",
        header: "Action",
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
