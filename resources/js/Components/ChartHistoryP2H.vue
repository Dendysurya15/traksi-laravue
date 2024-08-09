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
    // name: "ChartHistoryP2H",
    components: {
        apexchart: VueApexCharts,
    },
    props: {
        series: {
            type: Array,
            required: true,
        },
        chartOptions: {
            type: Object,
            required: true,
        },
    },
};
</script>
