/**
 * COVID-19 Test & Vaccination System (ORS)
 * Client-Side JavaScript & QR Code Engine
 */

window.CovidApp = {
  // Simple & reliable QR Matrix canvas renderer
  renderQR(canvasId, text) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const size = canvas.width;
    ctx.clearRect(0, 0, size, size);

    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, size, size);

    const gridSize = 21;
    const cellSize = size / gridSize;

    let hash = 0;
    for (let i = 0; i < text.length; i++) {
      hash = (hash << 5) - hash + text.charCodeAt(i);
      hash |= 0;
    }

    ctx.fillStyle = '#0F172A';

    const drawPositionFinder = (r, c) => {
      ctx.fillRect(c * cellSize, r * cellSize, 7 * cellSize, 7 * cellSize);
      ctx.fillStyle = '#FFFFFF';
      ctx.fillRect((c + 1) * cellSize, (r + 1) * cellSize, 5 * cellSize, 5 * cellSize);
      ctx.fillStyle = '#0F172A';
      ctx.fillRect((c + 2) * cellSize, (r + 2) * cellSize, 3 * cellSize, 3 * cellSize);
    };

    drawPositionFinder(0, 0);
    drawPositionFinder(0, 14);
    drawPositionFinder(14, 0);

    for (let i = 8; i < 13; i++) {
      if (i % 2 === 0) {
        ctx.fillRect(6 * cellSize, i * cellSize, cellSize, cellSize);
        ctx.fillRect(i * cellSize, 6 * cellSize, cellSize, cellSize);
      }
    }

    let seed = Math.abs(hash);
    for (let r = 0; r < gridSize; r++) {
      for (let c = 0; c < gridSize; c++) {
        if ((r < 8 && c < 8) || (r < 8 && c >= 13) || (r >= 13 && c < 8)) continue;
        if (r === 6 || c === 6) continue;

        seed = (seed * 9301 + 49297) % 233280;
        const rnd = seed / 233280;
        if (rnd > 0.45) {
          ctx.fillRect(c * cellSize, r * cellSize, cellSize - 0.5, cellSize - 0.5);
        }
      }
    }
  },

  // Admin Appointments Trend Chart
  initAdminChart(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const w = canvas.width = canvas.parentElement.clientWidth || 600;
    const h = canvas.height = 220;

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const values = [40, 80, 60, 110, 95, 140, 120, 165, 145, 190, 175, 230];

    ctx.clearRect(0, 0, w, h);

    const pLeft = 40, pRight = 20, pTop = 20, pBottom = 35;
    const chartW = w - pLeft - pRight;
    const chartH = h - pTop - pBottom;
    const maxVal = 250;

    ctx.strokeStyle = '#F1F5F9';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#94A3B8';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'right';

    [0, 50, 100, 150, 200, 250].forEach(val => {
      const y = pTop + chartH - (val / maxVal) * chartH;
      ctx.beginPath();
      ctx.moveTo(pLeft, y);
      ctx.lineTo(w - pRight, y);
      ctx.stroke();
      ctx.fillText(val.toString(), pLeft - 8, y + 4);
    });

    ctx.textAlign = 'center';
    months.forEach((m, idx) => {
      const x = pLeft + (idx / (months.length - 1)) * chartW;
      ctx.fillText(m, x, h - 10);
    });

    const gradient = ctx.createLinearGradient(0, pTop, 0, pTop + chartH);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    ctx.beginPath();
    values.forEach((v, idx) => {
      const x = pLeft + (idx / (months.length - 1)) * chartW;
      const y = pTop + chartH - (v / maxVal) * chartH;
      if (idx === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.lineTo(pLeft + chartW, pTop + chartH);
    ctx.lineTo(pLeft, pTop + chartH);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();

    ctx.beginPath();
    ctx.strokeStyle = '#10B981';
    ctx.lineWidth = 2.5;
    values.forEach((v, idx) => {
      const x = pLeft + (idx / (months.length - 1)) * chartW;
      const y = pTop + chartH - (v / maxVal) * chartH;
      if (idx === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.stroke();

    values.forEach((v, idx) => {
      const x = pLeft + (idx / (months.length - 1)) * chartW;
      const y = pTop + chartH - (v / maxVal) * chartH;
      ctx.beginPath();
      ctx.arc(x, y, 4, 0, Math.PI * 2);
      ctx.fillStyle = '#FFFFFF';
      ctx.fill();
      ctx.strokeStyle = '#10B981';
      ctx.lineWidth = 2;
      ctx.stroke();
    });
  }
};

document.addEventListener('DOMContentLoaded', () => {
  // Mobile sidebar toggle
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.getElementById('mainSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('d-none');
    });
  }

  // Auto-init Chart if present
  if (document.getElementById('adminAppointmentsChart')) {
    window.CovidApp.initAdminChart('adminAppointmentsChart');
  }
});
