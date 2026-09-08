<?php
/**
 * COVID-19 Symptom Checker & Guidelines
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$pageTitle = "Symptoms & Guidelines";

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-body">
      <div class="row g-4">
        <!-- Left: Interactive Symptom Evaluator -->
        <div class="col-lg-8">
          <div class="panel-card-custom mb-0">
            <div class="panel-header-custom">
              <h2 class="panel-title-custom">
                <i class="fa-solid fa-heart-pulse text-danger"></i> Interactive Covid-19 Symptom Triage
              </h2>
            </div>
            <div class="p-4">
              <p class="small text-muted mb-3">Check the symptoms you are currently experiencing for an automated preliminary clinical risk evaluation:</p>

              <form id="phpSymptomForm" onsubmit="evaluateSymptoms(event)">
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <div class="form-check p-3 border rounded-3 bg-light">
                      <input class="form-check-input ms-0 me-2" type="checkbox" id="sympFever">
                      <label class="form-check-label small fw-semibold" for="sympFever">High Fever or Chills (> 100.4°F)</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check p-3 border rounded-3 bg-light">
                      <input class="form-check-input ms-0 me-2" type="checkbox" id="sympCough">
                      <label class="form-check-label small fw-semibold" for="sympCough">Persistent Dry Cough</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check p-3 border rounded-3 bg-light">
                      <input class="form-check-input ms-0 me-2" type="checkbox" id="sympSmell">
                      <label class="form-check-label small fw-semibold" for="sympSmell">Sudden Loss of Taste or Smell</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check p-3 border rounded-3 bg-light">
                      <input class="form-check-input ms-0 me-2" type="checkbox" id="sympBreath">
                      <label class="form-check-label small fw-semibold" for="sympBreath">Shortness of Breath / Chest Tightness</label>
                    </div>
                  </div>
                </div>

                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Current Body Temperature (°F)</label>
                    <input type="number" step="0.1" id="sympTemp" class="form-control" value="98.6">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Blood Oxygen SpO2 Level (%)</label>
                    <input type="number" id="sympSpo2" class="form-control" value="98" min="70" max="100">
                  </div>
                </div>

                <button type="submit" class="btn btn-emerald w-100 py-2">
                  <i class="fa-solid fa-stethoscope me-1"></i> Calculate Risk Assessment
                </button>
              </form>

              <div id="phpSymptomResult" class="mt-4 d-none"></div>
            </div>
          </div>
        </div>

        <!-- Right: Safety Guidelines -->
        <div class="col-lg-4">
          <div class="panel-card-custom mb-0">
            <div class="panel-header-custom">
              <h3 class="panel-title-custom fs-6">
                <i class="fa-solid fa-book-medical text-success"></i> Health Guidelines
              </h3>
            </div>
            <div class="p-3 d-flex flex-column gap-3">
              <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                <div class="fw-bold text-success small mb-1"><i class="fa-solid fa-mask-face me-1"></i> Wear a Face Mask</div>
                <p class="small text-muted mb-0">Use N95 or multi-layer surgical masks in crowded public areas to filter aerosol particles.</p>
              </div>

              <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">
                <div class="fw-bold text-primary small mb-1"><i class="fa-solid fa-hands-bubbles me-1"></i> Hand Hygiene</div>
                <p class="small text-muted mb-0">Regularly wash hands with soap for at least 20 seconds or apply 70% alcohol hand sanitizer.</p>
              </div>

              <div class="p-3 bg-purple bg-opacity-10 border border-purple border-opacity-25 rounded-3" style="background-color: #F5F3FF; border-color: #DDD6FE;">
                <div class="fw-bold small mb-1" style="color: #7C3AED;"><i class="fa-solid fa-house-chimney-medical me-1"></i> Quarantine Duration</div>
                <p class="small text-muted mb-0">Isolate for a minimum of 7 days if you test positive for COVID-19 or show high viral symptoms.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function evaluateSymptoms(e) {
  e.preventDefault();
  const fever = document.getElementById('sympFever').checked;
  const cough = document.getElementById('sympCough').checked;
  const smell = document.getElementById('sympSmell').checked;
  const breath = document.getElementById('sympBreath').checked;
  const temp = parseFloat(document.getElementById('sympTemp').value) || 98.6;
  const spo2 = parseInt(document.getElementById('sympSpo2').value) || 98;

  let score = 0;
  if (fever || temp >= 100.4) score += 2;
  if (cough) score += 1;
  if (smell) score += 2;
  if (breath || spo2 < 94) score += 4;

  const resultBox = document.getElementById('phpSymptomResult');
  resultBox.classList.remove('d-none');

  if (score >= 4 || spo2 < 94) {
    resultBox.innerHTML = `
      <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
        <h5 class="fw-bold text-danger fs-6 mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> High Risk of COVID-19 / Respiratory Distress</h5>
        <p class="small text-muted mb-2">Your oxygen saturation (<strong>${spo2}%</strong>) or severe symptoms indicate potential urgency.</p>
        <ul class="small text-danger mb-3 ps-3">
          <li>Book an immediate RT-PCR Test at the nearest hospital.</li>
          <li>Consult a medical officer or contact COVID-19 Helpline (1166).</li>
        </ul>
        <a href="patient_book.php" class="btn btn-sm btn-danger">Book RT-PCR Test Now</a>
      </div>
    `;
  } else if (score >= 2) {
    resultBox.innerHTML = `
      <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
        <h5 class="fw-bold text-dark fs-6 mb-1"><i class="fa-solid fa-circle-exclamation text-warning me-1"></i> Moderate Risk / Mild Symptoms</h5>
        <p class="small text-muted mb-2">You show symptoms commonly linked with viral respiratory infection.</p>
        <p class="small text-muted mb-0">&bull; Stay hydrated, take steam inhalation, and book an RT-PCR test for verification.</p>
      </div>
    `;
  } else {
    resultBox.innerHTML = `
      <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
        <h5 class="fw-bold text-success fs-6 mb-1"><i class="fa-solid fa-circle-check me-1"></i> Normal / Low Risk Range</h5>
        <p class="small text-muted mb-0">No severe COVID-19 symptoms detected. Continue taking preventive safety precautions.</p>
      </div>
    `;
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
