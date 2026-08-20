import Chart from 'chart.js/auto';

const dataElement = document.getElementById('dashboard-chart-data');
if (dataElement) {
    const data = JSON.parse(dataElement.textContent);

    const labels = data.trendDays;
    const commonOptions = {
        responsive: true,
        plugins: {
            legend: { display: false },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 },
            },
        },
    };

    const patientsCanvas = document.getElementById('patients-trend');
    if (patientsCanvas) {
        new Chart(patientsCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'ผู้ป่วยใหม่',
                    data: data.patientTrend,
                    backgroundColor: 'rgba(14, 116, 144, 0.7)',
                    borderRadius: 4,
                }],
            },
            options: commonOptions,
        });
    }

    const appointmentsCanvas = document.getElementById('appointments-trend');
    if (appointmentsCanvas) {
        new Chart(appointmentsCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'นัดหมาย',
                    data: data.appointmentTrend,
                    borderColor: '#0e7490',
                    backgroundColor: 'rgba(14, 116, 144, 0.1)',
                    fill: true,
                    tension: 0.3,
                }],
            },
            options: commonOptions,
        });
    }
}