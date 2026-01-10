import ApexCharts from "apexcharts";
import "preline";

window.ApexCharts = ApexCharts;

document.addEventListener("livewire:navigated", () => {
    window.HSStaticMethods?.autoInit();
});

const scheduleApexInit = () => {
    if (window._apexInitScheduled) return;
    window._apexInitScheduled = true;
    requestAnimationFrame(() => {
        window._apexInitScheduled = false;
        initApexPlaceholders();
    });
};

const initApexPlaceholders = () => {
    document.querySelectorAll("[data-apex-placeholder]").forEach((el) => {
        if (el.dataset.apexForce === "true") {
            if (el._apexChart) {
                el._apexChart.destroy();
                el._apexChart = null;
            }
            el._apexInited = false;
        }
        if (el._apexInited) return;
        el._apexInited = true;

        const categories = (() => {
            try {
                return JSON.parse(el.getAttribute("data-categories") || "[]");
            } catch {
                return [];
            }
        })();

        const series = (() => {
            try {
                return JSON.parse(el.getAttribute("data-series") || "[]");
            } catch {
                return [];
            }
        })();

        const isDark =
            document.documentElement.classList.contains("dark") ||
            window.matchMedia("(prefers-color-scheme: dark)").matches;

        const lightPalette = ["#2563eb", "#9333ea", "#10b981", "#f59e0b"];
        const darkPalette = ["#3b82f6", "#a855f7", "#34d399", "#fbbf24"];
        let colors = (isDark ? darkPalette : lightPalette).slice(
            0,
            Math.max(1, series.length)
        );
        const colorsOverride = (() => {
            const raw = el.dataset.chartColors;
            if (!raw) return null;
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : null;
            } catch {
                return raw
                    .split(",")
                    .map((value) => value.trim())
                    .filter(Boolean);
            }
        })();
        if (colorsOverride?.length) {
            colors = colorsOverride;
        }

        const chartType = el.dataset.chartType || "area";
        const chartHeight = Number(el.dataset.chartHeight || 300);
        const strokeWidth = (() => {
            const raw = el.dataset.chartStroke;
            if (!raw) return 2;
            if (raw.includes(",")) {
                return raw
                    .split(",")
                    .map((value) => Number(value.trim()))
                    .filter((value) => !Number.isNaN(value));
            }
            const numeric = Number(raw);
            return Number.isNaN(numeric) ? 2 : numeric;
        })();
        const markerSize = (() => {
            const raw = el.dataset.chartMarkerSize;
            if (!raw) return chartType === "line" ? 3 : 0;
            if (raw.includes(",")) {
                return raw
                    .split(",")
                    .map((value) => Number(value.trim()))
                    .filter((value) => !Number.isNaN(value));
            }
            const numeric = Number(raw);
            return Number.isNaN(numeric) ? 0 : numeric;
        })();
        const fillOpacity = (() => {
            const raw = el.dataset.chartFillOpacity;
            if (!raw) return undefined;
            if (raw.includes(",")) {
                return raw
                    .split(",")
                    .map((value) => Number(value.trim()))
                    .filter((value) => !Number.isNaN(value));
            }
            const numeric = Number(raw);
            return Number.isNaN(numeric) ? undefined : numeric;
        })();
        const yMax = el.dataset.chartYmax ? Number(el.dataset.chartYmax) : undefined;
        const yMin = el.dataset.chartYmin ? Number(el.dataset.chartYmin) : undefined;
        const showLegend = el.dataset.chartLegend === "true";
        const xaxisType = el.dataset.chartXaxisType || "category";
        const xMin = el.dataset.chartXmin ? Number(el.dataset.chartXmin) : undefined;
        const xMax = el.dataset.chartXmax ? Number(el.dataset.chartXmax) : undefined;
        const sparkline = el.dataset.chartSparkline === "true";
        const showXLabels = el.dataset.chartXlabels !== "false";
        const showYLabels = el.dataset.chartYlabels !== "false";
        const showGrid = el.dataset.chartGrid !== "false";
        const xAnnotations = (() => {
            const raw = el.dataset.chartXAnnotations;
            if (!raw) return [];
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch {
                return [];
            }
        })();

        const options = {
            chart: {
                type: chartType,
                height: chartHeight,
                toolbar: { show: false },
                zoom: { enabled: false },
                foreColor: isDark ? "#a3a3a3" : "#6b7280",
                sparkline: { enabled: sparkline },
            },
            series,
            colors,
            dataLabels: { enabled: false },
            stroke: { curve: "smooth", width: strokeWidth },
            markers: { size: markerSize },
            grid: {
                show: showGrid,
                strokeDashArray: 2,
                borderColor: isDark ? "#404040" : "#e5e7eb",
            },
            fill: {
                type: "gradient",
                opacity: fillOpacity,
                gradient: {
                    shadeIntensity: 0.2,
                    opacityFrom: 0.5,
                    opacityTo: 0.05,
                    stops: [0, 90, 100],
                },
            },
            xaxis: {
                type: xaxisType,
                categories,
                min: xMin,
                max: xMax,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    show: !sparkline && showXLabels,
                    style: {
                        colors: isDark ? "#a3a3a3" : "#9ca3af",
                        fontSize: "12px",
                        fontFamily: "Inter, ui-sans-serif",
                    },
                },
            },
            yaxis: {
                min: yMin,
                max: yMax,
                labels: {
                    show: !sparkline && showYLabels,
                    style: {
                        colors: isDark ? "#a3a3a3" : "#9ca3af",
                        fontSize: "12px",
                        fontFamily: "Inter, ui-sans-serif",
                    },
                    formatter: (v) => v,
                },
            },
            tooltip: {
                theme: isDark ? "dark" : "light",
                y: { formatter: (v) => v },
            },
            legend: { show: showLegend },
            annotations: xAnnotations.length
                ? { xaxis: xAnnotations }
                : undefined,
        };

        const chart = new ApexCharts(el, options);
        el._apexChart = chart;
        chart.render();
    });
};

document.addEventListener("DOMContentLoaded", scheduleApexInit);
document.addEventListener("livewire:load", scheduleApexInit);
document.addEventListener("livewire:init", () => {
    scheduleApexInit();
    if (window.Livewire?.hook) {
        Livewire.hook("message.processed", () => scheduleApexInit());
        Livewire.hook("commit", ({ succeed }) => {
            succeed(() => scheduleApexInit());
        });
    }
});
window.addEventListener("livewire:navigated", scheduleApexInit);
