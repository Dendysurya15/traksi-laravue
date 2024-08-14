<template>
    <div>
        <apexchart
            height="350"
            :options="chartOptions"
            :series="series"
        ></apexchart>
    </div>
</template>

<script>
import VueApexCharts from "vue3-apexcharts";

export default {
    components: {
        apexchart: VueApexCharts,
    },
    props: {
        series: {
            type: Array,
            required: true,
        },
        xAxisCategories: {
            type: Array,
            required: true,
        },
    },
    computed: {
        chartOptions() {
            return {
                chart: {
                    height: 350,
                    columnWidth: "70%",
                    type: "bar",
                    zoom: {
                        enabled: false,
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "55%",
                        endingShape: "rounded",
                        fill: {
                            colors: ["#6366f1"],
                        },
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ["transparent"],
                },
                grid: {
                    row: {
                        colors: ["#f3f3f3"],
                        opacity: 0.5,
                    },
                },
                xaxis: {
                    categories: this.xAxisCategories,
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return Math.round(value) + " Unit"; // Round the value to the nearest integer
                        },
                    },
                },
            };
        },
    },
};
</script>
