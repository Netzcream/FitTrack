import ApexCharts from "apexcharts";
import "preline";

window.ApexCharts = ApexCharts;

document.addEventListener("livewire:navigated", () => {
    window.HSStaticMethods?.autoInit();
});

const initApexPlaceholders = () => {
    document.querySelectorAll("[data-apex-placeholder]").forEach((el) => {
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

        // Si tenés 1 ó 2 series, asignamos colores por defecto
        const lightPalette = ["#2563eb", "#9333ea", "#10b981", "#f59e0b"];
        const darkPalette = ["#3b82f6", "#a855f7", "#34d399", "#fbbf24"];
        const colors = (isDark ? darkPalette : lightPalette).slice(
            0,
            Math.max(1, series.length)
        );

        const options = {
            chart: {
                type: "area",
                height: 300,
                toolbar: { show: false },
                zoom: { enabled: false },
                foreColor: isDark ? "#a3a3a3" : "#6b7280", // opcional, mejora ejes en dark
            },
            series,
            colors,
            dataLabels: { enabled: false },
            stroke: { curve: "smooth", width: 2 },
            grid: {
                strokeDashArray: 2,
                borderColor: isDark ? "#404040" : "#e5e7eb",
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 0.1,
                    opacityFrom: 0.5,
                    opacityTo: 0,
                    stops: [50, 100, 100, 100],
                },
            },
            xaxis: {
                type: "category",
                categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: isDark ? "#a3a3a3" : "#9ca3af",
                        fontSize: "13px",
                        fontFamily: "Inter, ui-sans-serif",
                    },
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: isDark ? "#a3a3a3" : "#9ca3af",
                        fontSize: "13px",
                        fontFamily: "Inter, ui-sans-serif",
                    },
                    formatter: (v) => v,
                },
            },
            tooltip: {
                theme: isDark ? "dark" : "light",
                y: { formatter: (v) => v }, // solo número (alumnos)
            },
            legend: { show: false },
        };

        const chart = new ApexCharts(el, options);
        chart.render();
    });
};

document.addEventListener("DOMContentLoaded", initApexPlaceholders);
window.addEventListener("livewire:navigated", initApexPlaceholders);
