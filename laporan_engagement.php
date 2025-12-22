<?php
  require 'vendor/autoload.php';
  require 'fungsi_proses.php';
  require 'fungsi_konversi.php';
  
  // Set the page title for the header
  $pageTitle = "Laporan ENGAGEMENT";
  $activePage = "laporan_engagement";
  
  // Include header template
  include 'includes/header.php';
?>

<style>
/* Evidence Preview 4x6 Style */
.evidence-preview-card {
  background: #ffffff;
  border: 3px solid #dee2e6;
  border-radius: 12px;
  padding: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  transition: transform 0.2s, box-shadow 0.2s;
  margin-bottom: 16px;
}

.evidence-preview-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.18);
  border-color: #0d6efd;
}

.evidence-image-wrapper {
  width: 100%;
  padding-bottom: 150%; /* 4x6 ratio: 6/4 = 1.5 = 150% */
  position: relative;
  background: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e9ecef;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
}

.evidence-image-4x6 {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.evidence-info {
  margin-top: 10px;
  text-align: center;
  padding: 4px 8px;
  background: #f8f9fa;
  border-radius: 4px;
}

.evidence-info small {
  font-size: 0.75rem;
  word-break: break-all;
}

.evidence-preview-container {
  min-height: 50px;
  margin-top: 12px;
}

/* Responsive adjustments - Ukuran besar dan konsisten di semua device */
@media (max-width: 576px) {
  .evidence-preview-card {
    padding: 10px;
  }
  
  .evidence-image-wrapper {
    padding-bottom: 150%; /* Tetap 4x6 di mobile */
    border-width: 2px;
  }
  
  .evidence-preview-container .col-12 {
    padding-left: 8px;
    padding-right: 8px;
  }
}

@media (min-width: 577px) and (max-width: 768px) {
  .evidence-image-wrapper {
    padding-bottom: 150%; /* Tetap 4x6 di tablet */
  }
  
  .evidence-preview-container .col-md-6 {
    padding-left: 10px;
    padding-right: 10px;
  }
}

@media (min-width: 769px) {
  .evidence-image-wrapper {
    padding-bottom: 150%; /* Tetap 4x6 di desktop */
  }
  
  .evidence-preview-container .col-lg-4 {
    padding-left: 12px;
    padding-right: 12px;
  }
}

/* Print styles untuk screenshot */
@media print {
  .evidence-preview-card {
    page-break-inside: avoid;
    break-inside: avoid;
    border: 2px solid #000;
  }
  
  .evidence-image-4x6 {
    max-width: 100%;
    height: auto;
  }
  
  .evidence-image-wrapper {
    padding-bottom: 150%;
  }
}

/* Optimasi untuk screenshot dengan ukuran besar */
.evidence-preview-container .row {
  margin-left: -8px;
  margin-right: -8px;
}

.evidence-preview-container .row > [class*="col-"] {
  padding-left: 8px;
  padding-right: 8px;
}
</style>

<!-- Progress bar overlay -->
<div id="progressOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:2000;background:rgba(255,255,255,0.85);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div style="min-width:340px;max-width:90vw;padding:2rem 2.5rem;background:#ffffff;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,0.15);display:flex;flex-direction:column;align-items:center;border:1px solid #dee2e6;">
    <div class="mb-3">
      <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <div id="progressBarStatus" class="text-primary mb-2" style="font-size:1.1rem;text-align:center;"></div>
    <div class="progress w-100 mb-2" style="height: 24px;">
      <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div>
    </div>
    <div id="progressBarServerMsg" class="text-warning mt-2" style="font-size:0.98rem;"></div>
  </div>
</div>

<!-- Main Content -->
<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4 shadow-sm">
      <div class="breadcrumb-title pe-3">
        <h4 class="mb-0 fw-bold"><i class="bi bi-heart-fill me-2"></i>Laporan ENGAGEMENT</h4>
      </div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Laporan ENGAGEMENT</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row g-3 g-md-4">
      <!-- Left Column: Input Form -->
      <div class="col-12 col-lg-5 col-xl-4 col-xxl-4 d-flex">
        <div class="card rounded-4 w-100 shadow-lg border-0" style="background: #ffffff; border: 1px solid #cfe2ff !important;">
          <div class="card-body p-3 p-md-4">
            <h5 class="mb-3 fw-bold"><i class="bi bi-heart-fill text-danger me-2"></i>Form Input Engagement</h5>
            
            <form id="engagementForm" method="post" enctype="multipart/form-data">
              <!-- Alert container -->
              <div id="formAlerts" class="mb-3"></div>

              <!-- Tanggal dan Judul -->
              <div class="mb-3">
                <label for="tanggal" class="form-label fw-bold">
                  <i class="bi bi-calendar-event"></i> Tanggal: <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
              </div>

              <div class="mb-3">
                <label for="judul" class="form-label fw-bold">
                  <i class="bi bi-type"></i> Judul Laporan: <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="judul" name="judul" placeholder="Contoh: Engagement Terhadap Konten Negatif Terkait Penanganan Bencana Aceh-Sumatera di Wilayah Merpati-14" required>
                <small class="form-text text-muted">Judul akan digunakan untuk nama file laporan</small>
              </div>

              <!-- Input Link -->
              <div class="mb-3">
                <label for="linkInput" class="form-label fw-bold">
                  <i class="bi bi-link-45deg"></i> Tautan Konten yang Dikomen/Like (satu per baris): <span class="text-danger">*</span>
                </label>
                <textarea id="linkInput" name="linkInput" class="form-control" rows="4" placeholder="https://www.tiktok.com/@infonusa/video/7585392394850307344
https://www.facebook.com/...
https://www.instagram.com/..." required></textarea>
                <div class="form-text mt-2">
                  <i class="bi bi-check-circle-fill text-success"></i> Platform yang didukung: X (Twitter), Instagram, Facebook, TikTok, YouTube, Snack Video<br>
                  <i class="bi bi-info-circle-fill text-info"></i> Platform akan terdeteksi secara otomatis<br>
                  <i class="bi bi-lightbulb-fill text-warning"></i> <strong>Catatan:</strong> Link ini adalah konten yang dikomen/like oleh banyak akun
                </div>
              </div>

              <!-- Info Deteksi Link -->
              <div id="linkInfo" class="alert alert-info d-none mb-3">
                <i class="bi bi-info-circle"></i> <strong>Terdeteksi:</strong> <span id="linkCount">0</span> link konten
              </div>

              <!-- Container untuk input akun & narasi per link -->
              <div id="akunNarasiSection" class="mb-3 d-none">
                <label class="form-label fw-bold mb-3">
                  <i class="bi bi-person-fill"></i> Data Akun yang Komen/Like dan Narasi per Link: <span class="text-danger">*</span>
                </label>
                <div id="linkFormsContainer">
                  <!-- Form per link akan di-generate di sini -->
                </div>
                <small class="form-text text-muted">
                  <i class="bi bi-info-circle"></i> Format: <strong>Nama Akun | Narasi</strong> (satu per baris)<br>
                  <i class="bi bi-lightbulb-fill"></i> Setiap link memiliki input terpisah untuk memudahkan mapping
                </small>
              </div>

              <!-- Submit Button -->
              <div class="d-grid gap-2">
                <button type="button" id="btnDetectLinks" class="btn btn-info">
                  <i class="bi bi-search"></i> Deteksi Link & Generate Form
                </button>
                <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg d-none">
                  <i class="bi bi-file-earmark-pdf"></i> Generate Laporan
                </button>
                <button type="button" id="btnClearAll" class="btn btn-outline-danger d-none">
                  <i class="bi bi-trash"></i> Hapus Semua Data
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Right Column: Preview & Results -->
      <div class="col-12 col-lg-7 col-xl-8 col-xxl-8">
        <!-- Preview Card -->
        <div class="card rounded-4 shadow-lg border-0 mb-3" style="background: #ffffff; border: 1px solid #cfe2ff !important;">
          <div class="card-body p-3 p-md-4">
            <h5 class="mb-3 fw-bold"><i class="bi bi-eye me-2"></i>Preview Data</h5>
            
            <div id="previewContainer">
              <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                <p class="mt-3">Preview data akan ditampilkan di sini</p>
                <p class="text-muted small">Masukkan link dan klik "Deteksi Link & Generate Form" untuk melihat preview</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Results Card -->
        <div class="card rounded-4 shadow-lg border-0" id="resultsCard" style="display: none; background: #ffffff; border: 1px solid #cfe2ff !important;">
          <div class="card-header bg-danger text-white d-flex align-items-center" style="border-radius: 16px 16px 0 0;">
            <div class="result-icon-wrapper me-2" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
              <i class="bi bi-heart-fill" style="font-size: 20px;"></i>
            </div>
            <h6 class="mb-0 fw-bold">Hasil Laporan ENGAGEMENT</h6>
          </div>
          <div class="card-body p-4">
            <div class="text-center py-4 result-placeholder">
              <div class="result-icon-large mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="bi bi-heart" style="font-size: 32px; color: #dc3545;"></i>
              </div>
              <p class="mt-2 text-muted fw-medium">Hasil laporan akan ditampilkan di sini</p>
            </div>
            <div class="result-content" id="engagementResultContent">
              <!-- Result content will be filled by JavaScript -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const linkInput = document.getElementById('linkInput');
  const btnDetectLinks = document.getElementById('btnDetectLinks');
  const dynamicForms = document.getElementById('dynamicForms');
  const accountInfo = document.getElementById('accountInfo');
  const accountCount = document.getElementById('accountCount');
  const previewContainer = document.getElementById('previewContainer');
  const btnSubmit = document.getElementById('btnSubmit');
  const formAlerts = document.getElementById('formAlerts');

  // Function to detect platform from URL
  function detectPlatform(url) {
    url = url.toLowerCase();
    if (url.includes('x.com') || url.includes('twitter.com')) {
      return 'X';
    } else if (url.includes('instagram.com')) {
      return 'Instagram';
    } else if (url.includes('facebook.com')) {
      return 'Facebook';
    } else if (url.includes('tiktok.com')) {
      return 'TikTok';
    } else if (url.includes('youtube.com')) {
      return 'YouTube';
    } else if (url.includes('snackvideo.com')) {
      return 'Snack Video';
    }
    return 'Unknown';
  }

  // Function to extract account name from URL (basic extraction)
  function extractAccountName(url, platform) {
    try {
      const urlObj = new URL(url);
      const path = urlObj.pathname;
      
      if (platform === 'X' || platform === 'Twitter') {
        const match = path.match(/\/([^\/]+)/);
        return match ? match[1] : '';
      } else if (platform === 'Instagram') {
        const match = path.match(/\/([^\/\?]+)/);
        return match ? match[1].replace('@', '') : '';
      } else if (platform === 'Facebook') {
        const match = path.match(/\/([^\/\?]+)/);
        return match ? match[1] : '';
      } else if (platform === 'TikTok') {
        const match = path.match(/@([^\/]+)/);
        return match ? match[1] : '';
      } else if (platform === 'YouTube') {
        if (path.includes('/channel/') || path.includes('/user/') || path.includes('/c/') || path.includes('/@')) {
          const match = path.match(/(?:channel\/|user\/|c\/|@)([^\/\?]+)/);
          return match ? match[1] : '';
        }
        return '';
      } else if (platform === 'Snack Video') {
        const match = path.match(/\/([^\/\?]+)/);
        return match ? match[1] : '';
      }
    } catch (e) {
      console.error('Error extracting account name:', e);
    }
    return '';
  }

  // Function to validate URL
  function isValidUrl(string) {
    try {
      const url = new URL(string);
      return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (_) {
      return false;
    }
  }

  // Storage key for localStorage
  const STORAGE_KEY = 'engagement_form_data';
  let isLoadingData = false; // Flag to prevent save during loading

  // Function to save form data to localStorage
  function saveFormData() {
    if (isLoadingData) return; // Don't save while loading
    const data = {
      tanggal: document.getElementById('tanggal').value,
      judul: document.getElementById('judul').value,
      links: linkInput.value,
      akunNarasi: {}
    };

    // Save akun & narasi per link
    const linkForms = document.querySelectorAll('.link-form-section');
    linkForms.forEach((formSection, linkIndex) => {
      const textarea = formSection.querySelector(`textarea[name="akun_narasi_link_${linkIndex}"]`);
      if (textarea) {
        data.akunNarasi[linkIndex] = textarea.value;
      }
      
      // Save file input info (we can't save files, but we can save metadata)
      const fileInput = formSection.querySelector(`input[name="evidence_link_${linkIndex}[]"]`);
      if (fileInput && fileInput.files.length > 0) {
        data.akunNarasi[linkIndex + '_files'] = {
          count: fileInput.files.length,
          names: Array.from(fileInput.files).map(f => f.name)
        };
      }
    });

    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
      console.error('Error saving to localStorage:', e);
    }
  }

  // Function to load form data from localStorage
  function loadFormData() {
    try {
      isLoadingData = true; // Set flag to prevent auto-save during loading
      
      const savedData = localStorage.getItem(STORAGE_KEY);
      if (!savedData) {
        isLoadingData = false;
        return false;
      }

      const data = JSON.parse(savedData);

      // Load tanggal and judul
      if (data.tanggal) {
        document.getElementById('tanggal').value = data.tanggal;
      }
      if (data.judul) {
        document.getElementById('judul').value = data.judul;
      }
      if (data.links) {
        linkInput.value = data.links;
        
        // Parse and generate forms if links exist
        const links = data.links.trim().split('\n').filter(l => l.trim().length > 0 && isValidUrl(l.trim()));
        if (links.length > 0) {
          // Generate forms first
          generateForms(links);
          
          // Load akun & narasi data after forms are generated
          setTimeout(() => {
            if (data.akunNarasi) {
              Object.keys(data.akunNarasi).forEach(key => {
                if (!key.endsWith('_files')) {
                  const linkIndex = parseInt(key);
                  const textarea = document.querySelector(`textarea[name="akun_narasi_link_${linkIndex}"]`);
                  if (textarea && data.akunNarasi[key]) {
                    textarea.value = data.akunNarasi[key];
                    // Trigger input event to update preview (but don't save again to avoid loop)
                    const event = new Event('input', { bubbles: true });
                    Object.defineProperty(event, 'target', { value: textarea, enumerable: true });
                    textarea.dispatchEvent(event);
                  }
                }
              });
              
              // Show file info if files were previously selected
              Object.keys(data.akunNarasi).forEach(key => {
                if (key.endsWith('_files')) {
                  const linkIndex = parseInt(key.replace('_files', ''));
                  const fileInput = document.querySelector(`input[name="evidence_link_${linkIndex}[]"]`);
                  if (fileInput && data.akunNarasi[key]) {
                    const fileInfo = data.akunNarasi[key];
                    // Show info that files need to be re-selected
                    const fileSection = fileInput.closest('.evidence-upload-section');
                    if (fileSection && !fileSection.querySelector('.text-warning')) {
                      const infoDiv = document.createElement('small');
                      infoDiv.className = 'text-warning d-block mt-1';
                      infoDiv.innerHTML = `<i class="bi bi-info-circle"></i> ${fileInfo.count} file(s) sebelumnya dipilih. Silakan pilih ulang file setelah refresh.`;
                      fileSection.appendChild(infoDiv);
                    }
                  }
                }
              });
              
              updatePreview();
            }
          }, 200);
        }
      }

      isLoadingData = false; // Reset flag after loading
      return true;
    } catch (e) {
      console.error('Error loading from localStorage:', e);
      isLoadingData = false; // Reset flag on error
      return false;
    }
  }

  // Function to show evidence preview with 4x6 size
  function showEvidencePreview(fileInput, linkIndex) {
    const previewContainer = document.getElementById(`evidencePreview_${linkIndex}`);
    if (!previewContainer) return;
    
    const files = fileInput.files;
    if (!files || files.length === 0) {
      previewContainer.innerHTML = '';
      return;
    }
    
    let previewHTML = '<div class="row g-2">';
    
    Array.from(files).forEach((file, index) => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.getElementById(`evidenceImg_${linkIndex}_${index}`);
          if (img) {
            img.src = e.target.result;
          }
        };
        reader.readAsDataURL(file);
        
        previewHTML += `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="evidence-preview-card">
              <div class="evidence-image-wrapper">
                <img id="evidenceImg_${linkIndex}_${index}" src="" alt="Evidence ${index + 1}" class="evidence-image-4x6">
              </div>
              <div class="evidence-info">
                <small class="text-muted">${file.name}</small>
              </div>
            </div>
          </div>
        `;
      }
    });
    
    previewHTML += '</div>';
    previewContainer.innerHTML = previewHTML;
    
    // Load images after HTML is inserted
    Array.from(files).forEach((file, index) => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.getElementById(`evidenceImg_${linkIndex}_${index}`);
          if (img) {
            img.src = e.target.result;
          }
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Function to clear all form data
  function clearFormData() {
    if (confirm('Apakah Anda yakin ingin menghapus semua data? Data yang sudah diinput akan hilang.')) {
      // Clear localStorage
      localStorage.removeItem(STORAGE_KEY);
      
      // Clear form inputs
      document.getElementById('tanggal').value = '<?php echo date('Y-m-d'); ?>';
      document.getElementById('judul').value = '';
      linkInput.value = '';
      
      // Hide sections
      document.getElementById('akunNarasiSection').classList.add('d-none');
      document.getElementById('linkInfo').classList.add('d-none');
      document.getElementById('btnSubmit').classList.add('d-none');
      document.getElementById('btnClearAll').classList.add('d-none');
      
      // Clear preview
      previewContainer.innerHTML = `
        <div class="text-center py-5 text-muted">
          <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
          <p class="mt-3">Preview data akan ditampilkan di sini</p>
          <p class="text-muted small">Masukkan link dan klik "Deteksi Link & Generate Form" untuk melihat preview</p>
        </div>
      `;
      
      showAlert('success', 'Semua data telah dihapus!');
    }
  }

  // Function to parse akun & narasi input per link
  function parseAkunNarasi() {
    const linkFormsContainer = document.getElementById('linkFormsContainer');
    if (!linkFormsContainer) {
      return { accounts: [], linkMapping: [] };
    }
    
    const result = {
      accounts: [],
      linkMapping: [] // Array of { accountIndex, linkIndex }
    };
    
    // Get all link form sections
    const linkForms = linkFormsContainer.querySelectorAll('.link-form-section');
    
    linkForms.forEach((formSection, linkIndex) => {
      const textarea = formSection.querySelector(`textarea[name="akun_narasi_link_${linkIndex}"]`);
      if (!textarea || !textarea.value.trim()) {
        return; // Skip empty forms
      }
      
      // Parse lines from textarea
      const lines = textarea.value.trim().split('\n')
        .map(line => line.trim())
        .filter(line => line.length > 0);
      
      lines.forEach((line) => {
        const parts = line.split('|').map(p => p.trim());
        if (parts.length >= 2 || (parts.length === 1 && parts[0].length > 0)) {
          const accountIndex = result.accounts.length;
          const namaAkun = parts.length >= 2 ? parts[0] : parts[0];
          const narasi = parts.length >= 2 ? parts.slice(1).join('|') : '';
          
          result.accounts.push({
            nama_akun: namaAkun,
            narasi: narasi
          });
          
          result.linkMapping.push({
            accountIndex: accountIndex,
            linkIndex: linkIndex
          });
        }
      });
    });
    
    return result;
  }

  // Function to generate form fields per link
  function generateForms(links) {
    if (links.length === 0) {
      return;
    }

    // Show sections
    const akunNarasiSection = document.getElementById('akunNarasiSection');
    const linkInfo = document.getElementById('linkInfo');
    const linkCount = document.getElementById('linkCount');
    const linkFormsContainer = document.getElementById('linkFormsContainer');
    
    if (akunNarasiSection) {
      akunNarasiSection.classList.remove('d-none');
    }
    if (linkInfo && linkCount) {
      linkCount.textContent = links.length;
      linkInfo.classList.remove('d-none');
    }
    
    // Clear existing forms
    if (linkFormsContainer) {
      linkFormsContainer.innerHTML = '';
    }
    
    // Generate form for each link
    links.forEach((link, linkIndex) => {
      const platform = detectPlatform(link);
      const platformBadge = platform === 'X' ? 'bg-dark' : 
                            platform === 'Instagram' ? 'bg-danger' :
                            platform === 'Facebook' ? 'bg-primary' :
                            platform === 'TikTok' ? 'bg-dark' :
                            platform === 'YouTube' ? 'bg-danger' :
                            'bg-secondary';
      
      const formHTML = `
        <div class="link-form-section mb-3 p-3 border rounded bg-light" data-link-index="${linkIndex}">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <strong>Link ${linkIndex + 1}</strong>
              <span class="badge ${platformBadge} ms-2">${platform}</span>
            </div>
          </div>
          <div class="mb-2">
            <small class="text-muted text-break d-block">${link}</small>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-bold mb-1">
              Akun yang Komen/Like & Narasi: <span class="text-danger">*</span>
            </label>
            <textarea 
              name="akun_narasi_link_${linkIndex}" 
              class="form-control form-control-sm akun-narasi-input" 
              rows="4" 
              placeholder="Format: Nama Akun | Narasi (satu per baris)&#10;&#10;Contoh:&#10;Posmanto | Ayo gaes jangan mudah terprovokasi...&#10;johanpranowo5 | Apresiasi setinggi-tingginya...&#10;Budi waluyo | Indonesia satu jgn mau dipecah belah..."></textarea>
          </div>
          <div class="evidence-upload-section" data-link-index="${linkIndex}">
            <label class="form-label small fw-bold mb-1">
              <i class="bi bi-images"></i> Upload Evidence (Opsional):
            </label>
            <input type="file" class="form-control form-control-sm evidence-file-input" data-link-index="${linkIndex}" name="evidence_link_${linkIndex}[]" multiple accept="image/*">
            <div class="evidence-preview-container mt-2" id="evidencePreview_${linkIndex}"></div>
          </div>
        </div>
      `;
      
      if (linkFormsContainer) {
        linkFormsContainer.insertAdjacentHTML('beforeend', formHTML);
      }
    });
    
    // Add event listeners to all textareas
    const akunNarasiInputs = document.querySelectorAll('.akun-narasi-input');
    akunNarasiInputs.forEach(textarea => {
      textarea.addEventListener('input', function() {
        updatePreview();
        saveFormData(); // Auto-save on input
      });
    });
    
    // Add event listeners to file inputs with preview
    const fileInputs = document.querySelectorAll('.evidence-file-input');
    fileInputs.forEach(fileInput => {
      fileInput.addEventListener('change', function(e) {
        const linkIndex = this.getAttribute('data-link-index');
        showEvidencePreview(this, linkIndex);
        saveFormData(); // Auto-save on file selection
      });
    });
    
    btnSubmit.classList.remove('d-none');
    document.getElementById('btnClearAll').classList.remove('d-none');
    
    // Generate preview
    generatePreview(links);
  }


  // Function to update preview
  function updatePreview() {
    const links = linkInput.value.trim().split('\n').filter(l => l.trim().length > 0 && isValidUrl(l.trim()));
    generatePreview(links);
  }

  // Function to generate preview
  function generatePreview(links) {
    const akunNarasiData = parseAkunNarasi();
    
    let previewHTML = '<div class="table-responsive"><table class="table table-bordered table-hover">';
    previewHTML += '<thead class="table-light"><tr><th>No.</th><th>Tautan Konten</th><th>Nama Akun</th><th>Narasi</th><th>Platform</th></tr></thead><tbody>';
    
    if (links.length === 0) {
      previewHTML += '<tr><td colspan="5" class="text-center text-muted">Masukkan link konten terlebih dahulu</td></tr>';
    } else if (akunNarasiData.accounts.length === 0) {
      previewHTML += '<tr><td colspan="5" class="text-center text-muted">Masukkan data akun dan narasi untuk melihat preview</td></tr>';
    } else {
      // Show all akun entries with their assigned links
      akunNarasiData.accounts.forEach((data, index) => {
        // Get link index from mapping
        const mapping = akunNarasiData.linkMapping.find(m => m.accountIndex === index);
        const linkIndex = mapping ? Math.max(0, Math.min(mapping.linkIndex, links.length - 1)) : 0;
        const link = links[linkIndex] || links[0] || '-';
        const platform = link !== '-' ? detectPlatform(link) : '-';
        
        previewHTML += `<tr data-index="${index}">
          <td>${index + 1}</td>
          <td><a href="${link}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">${link}</a></td>
          <td class="preview-nama-akun">${data.nama_akun || '-'}</td>
          <td class="preview-narasi">${data.narasi ? (data.narasi.length > 50 ? data.narasi.substring(0, 50) + '...' : data.narasi) : '-'}</td>
          <td><span class="badge bg-primary">${platform}</span></td>
        </tr>`;
      });
    }
    
    previewHTML += '</tbody></table></div>';
    previewContainer.innerHTML = previewHTML;
  }

  // Auto-save event listeners
  document.getElementById('tanggal').addEventListener('change', saveFormData);
  document.getElementById('judul').addEventListener('input', saveFormData);
  linkInput.addEventListener('input', saveFormData);

  // Clear all button event listener
  document.getElementById('btnClearAll').addEventListener('click', clearFormData);

  // Event listener for detect button
  btnDetectLinks.addEventListener('click', function() {
    const linksText = linkInput.value.trim();
    
    if (!linksText) {
      showAlert('danger', 'Silakan masukkan link terlebih dahulu!');
      return;
    }

    // Parse links (split by newline)
    const links = linksText.split('\n')
      .map(link => link.trim())
      .filter(link => link.length > 0 && isValidUrl(link));

    if (links.length === 0) {
      showAlert('danger', 'Tidak ada link yang valid ditemukan! Pastikan link menggunakan format http:// atau https://');
      return;
    }

    // Check for unsupported platforms
    const unsupportedLinks = links.filter(link => {
      const platform = detectPlatform(link);
      return platform === 'Unknown';
    });

    if (unsupportedLinks.length > 0) {
      showAlert('warning', `Beberapa link tidak didukung: ${unsupportedLinks.length} link. Platform yang didukung: X, Instagram, Facebook, TikTok, YouTube, Snack Video`);
    } else {
      showAlert('success', `Berhasil mendeteksi ${links.length} link!`);
    }

    generateForms(links);
  });

  // Function to show alert
  function showAlert(type, message) {
    formAlerts.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  }

  // Function to display engagement results
  function displayEngagementResults(data) {
    const resultsCard = document.getElementById('resultsCard');
    const resultContent = document.getElementById('engagementResultContent');
    const placeholder = document.querySelector('#resultsCard .result-placeholder');
    
    if (!resultsCard || !resultContent) return;
    
    // Show results card
    resultsCard.style.display = 'block';
    
    // Hide placeholder
    if (placeholder) {
      placeholder.style.display = 'none';
    }
    
    // Build results HTML
    let html = '';
    
    // WhatsApp Format Section
    if (data.wa_format) {
      html += `
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">
              <i class="bi bi-whatsapp text-success"></i> Format WhatsApp (Copy untuk kirim WA)
            </label>
            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="wa-format">
              <i class="bi bi-clipboard"></i> Copy
            </button>
          </div>
          <textarea id="wa-format" class="form-control result-textarea" rows="15" readonly style="font-family: 'Courier New', monospace; font-size: 13px;">${escapeHtml(data.wa_format)}</textarea>
        </div>
      `;
    }
    
    // Download Section
    html += `
      <div>
        <label class="form-label fw-bold mb-2">
          <i class="bi bi-download"></i> Download File Laporan
        </label>
        <div class="d-flex flex-column gap-2">
    `;
    
    if (data.download_url && data.file_exists !== false) {
      const baseUrl = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');
      html += `
        <a href="${baseUrl}/${data.download_url}" class="btn btn-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
          <i class="bi bi-file-earmark-word"></i> Download Word Document
        </a>
      `;
    } else {
      html += `
        <div class="alert alert-warning small mb-0">
          <i class="bi bi-exclamation-triangle"></i> File Word tidak tersedia atau belum dibuat
        </div>
      `;
    }
    
    html += `
        </div>
        ${data.file_name ? `<small class="text-muted mt-2"><i class="bi bi-info-circle"></i> File: ${data.file_name}</small>` : ''}
      </div>
    `;
    
    resultContent.innerHTML = html;
    
    // Add copy functionality
    const copyButtons = resultContent.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
          targetElement.select();
          document.execCommand('copy');
          
          // Show feedback
          const originalText = this.innerHTML;
          this.innerHTML = '<i class="bi bi-check"></i> Copied!';
          this.classList.add('btn-success');
          this.classList.remove('btn-outline-success');
          
          setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-success');
          }, 2000);
        }
      });
    });
    
    // Scroll to results card
    resultsCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Helper function to escape HTML
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Form submission handler
  document.getElementById('engagementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate inputs
    const links = linkInput.value.trim().split('\n').filter(l => l.trim().length > 0 && isValidUrl(l.trim()));
    const akunNarasiData = parseAkunNarasi();
    
    if (links.length === 0) {
      alert('Minimal harus ada satu link konten!');
      linkInput.focus();
      return false;
    }
    
    if (akunNarasiData.accounts.length === 0) {
      alert('Data akun dan narasi harus diisi!\n\nFormat: Nama Akun | Narasi (satu per baris)\nSetiap link memiliki input terpisah.');
      const linkFormsContainer = document.getElementById('linkFormsContainer');
      if (linkFormsContainer) {
        const firstTextarea = linkFormsContainer.querySelector('textarea');
        if (firstTextarea) {
          firstTextarea.focus();
        }
      }
      return false;
    }
    
    // Validate format
    const invalidLines = akunNarasiData.accounts.filter(data => !data.nama_akun || data.nama_akun.length === 0);
    if (invalidLines.length > 0) {
      alert('Beberapa baris tidak memiliki nama akun. Pastikan format: Nama Akun | Narasi');
      return false;
    }
    
    const formData = new FormData(this);
    
    // Add links (will be assigned to akun entries)
    links.forEach((link, index) => {
      formData.append('link[]', link);
      formData.append('platform[]', detectPlatform(link));
    });
    
    // Add akun and narasi data with link mapping
    akunNarasiData.accounts.forEach((data, index) => {
      formData.append('nama_akun[]', data.nama_akun);
      formData.append('narasi[]', data.narasi);
      
      // Get link index from mapping
      const mapping = akunNarasiData.linkMapping.find(m => m.accountIndex === index);
      const linkIndex = mapping ? Math.max(0, Math.min(mapping.linkIndex, links.length - 1)) : 0;
      formData.append('link_index[]', linkIndex);
    });
    
    formData.append('action', 'generate_engagement_report');

    // Show progress overlay
    document.getElementById('progressOverlay').style.display = 'flex';
    document.getElementById('progressBarStatus').textContent = 'Memproses laporan...';
    document.getElementById('progressBar').style.width = '10%';

    fetch('api_engagement.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressBarStatus').textContent = 'Laporan berhasil dibuat!';
        
        setTimeout(() => {
          document.getElementById('progressOverlay').style.display = 'none';
          showAlert('success', 'Laporan berhasil dibuat!');
          
          // Clear form data after successful submission
          localStorage.removeItem(STORAGE_KEY);
          
          // Display results in results section
          displayEngagementResults(data);
        }, 1000);
      } else {
        document.getElementById('progressOverlay').style.display = 'none';
        showAlert('danger', data.message || 'Terjadi kesalahan saat membuat laporan');
      }
    })
    .catch(error => {
      document.getElementById('progressOverlay').style.display = 'none';
      showAlert('danger', 'Terjadi kesalahan: ' + error.message);
      console.error('Error:', error);
    });
  });

  // Load saved data on page load
  loadFormData();
});
</script>

<?php
// Include custom JavaScript for this page
include 'includes/js-includes.php';

// Include footer template
include 'includes/footer.php';
?>

