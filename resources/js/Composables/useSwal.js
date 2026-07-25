import Swal from 'sweetalert2';

export function useSwal() {
  // 1. Notifikasi Sukses / Peringatan Standar SaaS
  const alertSuccess = (title, text = '') => {
    Swal.fire({
      icon: 'success',
      title,
      text,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' }
    });
  };

  const alertError = (title, text = '') => {
    Swal.fire({
      icon: 'error',
      title,
      text,
      confirmButtonColor: '#EF4444',
      customClass: { popup: 'rounded-2xl' }
    });
  };

  const alertErrorHtml = (title, html = '') => {
    Swal.fire({
      icon: 'error',
      title,
      html,
      confirmButtonColor: '#EF4444',
      customClass: { popup: 'rounded-2xl' }
    });
  };

  // 2. Dialog Konfirmasi Aksi (Misal: Hapus Data / Approve)
  const confirmAction = async (title, text, confirmText = 'Ya, Lanjutkan') => {
    const result = await Swal.fire({
      title,
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2563EB',
      cancelButtonColor: '#1E293B',
      confirmButtonText: confirmText,
      cancelButtonText: 'Batal',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg', cancelButton: 'rounded-lg' }
    });
    return result.isConfirmed;
  };

  // 3. Loading dengan Indikator Persentase Riil
  const showLoadingProgress = (title = 'Sedang Memproses Data...') => {
    return Swal.fire({
      title,
      html: `
        <div class="mt-3 space-y-4">
          <div class="w-full bg-[#F8FAFC] rounded-full h-2.5 border border-[#E2E8F0] overflow-hidden">
            <div id="swal-progress-bar" class="bg-[#2563EB] h-2.5 rounded-full transition-all duration-150" style="width: 0%"></div>
          </div>
          <p id="swal-progress-text" class="text-sm font-semibold text-slate-600">0% Selesai</p>
        </div>
      `,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      customClass: { popup: 'rounded-2xl' },
      didOpen: () => {
        Swal.showLoading(Swal.getConfirmButton());
      }
    });
  };

  // Fungsi untuk memperbarui persentase loading secara dinamis
  const updateLoadingProgress = (percent) => {
    const progressBar = document.getElementById('swal-progress-bar');
    const progressText = document.getElementById('swal-progress-text');
    if (progressBar && progressText) {
      progressBar.style.width = `${percent}%`;
      progressText.innerText = `${percent}% Selesai`;
    }
  };

  const closeLoading = () => {
    Swal.close();
  };

  return {
    alertSuccess,
    alertError,
    alertErrorHtml,
    confirmAction,
    showLoadingProgress,
    updateLoadingProgress,
    closeLoading
  };
}