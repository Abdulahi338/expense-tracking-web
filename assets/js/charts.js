/*
 * Chart.js Configuration and Helper Functions
 * Expense Tracking System
 */

// Default Chart.js Configuration
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.color = '#6c757d';
Chart.defaults.plugins.legend.display = true;
Chart.defaults.plugins.legend.position = 'bottom';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 20;

Chart.defaults.elements.line.borderWidth = 3;
Chart.defaults.elements.line.tension = 0.4;
Chart.defaults.elements.point.radius = 4;
Chart.defaults.elements.point.hoverRadius = 6;

Chart.defaults.elements.bar.borderRadius = 8;

Chart.defaults.elements.doughnut.borderWidth = 2;

/**
 * Create a Line Chart
 * @param {string} canvasId - Canvas element ID
 * @param {Object} config - Chart configuration
 * @returns {Chart} Chart instance
 */
function createLineChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: config.data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            ...config.options
        }
    });
}

/**
 * Create a Bar Chart
 * @param {string} canvasId - Canvas element ID
 * @param {Object} config - Chart configuration
 * @returns {Chart} Chart instance
 */
function createBarChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: config.data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            ...config.options
        }
    });
}

/**
 * Create a Doughnut/Pie Chart
 * @param {string} canvasId - Canvas element ID
 * @param {Object} config - Chart configuration
 * @returns {Chart} Chart instance
 */
function createDoughnutChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: config.data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            ...config.options
        }
    });
}

/**
 * Create a Pie Chart
 * @param {string} canvasId - Canvas element ID
 * @param {Object} config - Chart configuration
 * @returns {Chart} Chart instance
 */
function createPieChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: config.data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            ...config.options
        }
    });
}

/**
 * Create Stacked Bar Chart
 * @param {string} canvasId - Canvas element ID
 * @param {Object} config - Chart configuration
 * @returns {Chart} Chart instance
 */
function createStackedBarChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: config.data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            ...config.options
        }
    });
}

/**
 * Create Area Chart
 * @param {string} canvasId - Canvas element ID
 * @param {Object} config - Chart configuration
 * @returns {Chart} Chart instance
 */
function createAreaChart(canvasId, config) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            ...config.data,
            datasets: config.data.datasets.map(dataset => ({
                ...dataset,
                fill: true,
                backgroundColor: dataset.borderColor + '20'
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            elements: {
                line: {
                    tension: 0.4
                }
            },
            ...config.options
        }
    });
}

/**
 * Destroy a Chart
 * @param {string|Chart} chart - Chart instance or canvas ID
 */
function destroyChart(chart) {
    if (chart) {
        if (typeof chart === 'string') {
            chart = Chart.getChart(chart);
        }
        if (chart) {
            chart.destroy();
        }
    }
}

/**
 * Update Chart Data
 * @param {Chart} chart - Chart instance
 * @param {Object} newData - New data to update
 */
function updateChartData(chart, newData) {
    if (chart) {
        if (newData.labels) {
            chart.data.labels = newData.labels;
        }
        if (newData.datasets) {
            chart.data.datasets = newData.datasets;
        }
        chart.update();
    }
}

// Export functions for use in other scripts
window.ChartHelpers = {
    createLineChart,
    createBarChart,
    createDoughnutChart,
    createPieChart,
    createStackedBarChart,
    createAreaChart,
    destroyChart,
    updateChartData
};

