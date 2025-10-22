// chart.js - dùng chung cho các trang báo cáo
function renderLineChart(canvasId, labels, data, label, color) {
  const ctx = document.getElementById(canvasId).getContext('2d');
  return new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        fill: true,
        backgroundColor: color.bg,
        borderColor: color.border,
        borderWidth: 2,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true, position: 'top' }
      },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' đ' } }
      }
    }
  });
}

function renderBarChart(canvasId, labels, data, label, color) {
  const ctx = document.getElementById(canvasId).getContext('2d');
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        backgroundColor: color.bg,
        borderColor: color.border,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true, position: 'top' }
      },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' đ' } }
      }
    }
  });
}
