<template>
  <AuthenticatedLayout>
    <template #header-title>Konfirmasi & Validasi Berkas Pendaftaran</template>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
        <input type="text" v-model="search" @keyup.enter="handleFilter" class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none w-full sm:w-64 focus:border-[#2563EB]" placeholder="Cari nama atau NIK pendaftar..." />
        <select v-model="matra" @change="handleFilter" class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] w-full sm:w-auto bg-white">
          <option value="">Semua Matra</option>
          <option value="AD">TNI AD</option>
          <option value="AL">TNI AL</option>
          <option value="AU">TNI AU</option>
        </select>
      </div>
    </div>

    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto w-full">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
            <th class="p-4">Calon Personel</th>
            <th class="p-4">Kontak & Email</th>
            <th class="p-4">Matra</th>
            <th class="p-4">Angkatan</th>
            <th class="p-4">Tanggal Daftar</th>
            <th class="p-4 text-right">Tindakan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
          <tr v-for="item in pendaftar.data" :key="item.id" class="hover:bg-slate-50/30 transition">
            <td class="p-4">
              <div class="font-bold text-slate-800">{{ item.full_name }}</div>
              <div class="text-xs text-slate-400 mt-0.5">NIK: {{ item.nik }}</div>
            </td>
            <td class="p-4">
              <div class="font-medium text-slate-700">{{ item.phone_number }}</div>
              <div class="text-xs text-slate-400 mt-0.5">{{ item.user?.email }}</div>
            </td>
            <td class="p-4">
              <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40">
                TNI {{ item.matra }}
              </span>
            </td>
            <td class="p-4 font-semibold text-slate-700">{{ item.angkatan }}</td>
            <td class="p-4 text-slate-400 text-xs">{{ formatDate(item.created_at) }}</td>
            <td class="p-4 text-right">
              <button @click="openActionModal(item)" class="text-xs font-bold text-[#2563EB] hover:underline bg-[#2563EB]/5 px-3 py-1.5 rounded-xl transition cursor-pointer border border-transparent hover:border-blue-100">
                Tinjau Pendaftaran
              </button>
            </td>
          </tr>
          <tr v-if="pendaftar.data.length === 0">
            <td colspan="6" class="p-12 text-center text-slate-400 text-sm">Tidak ada antrean pendaftaran personel baru.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 animate-fade-in">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl max-w-4xl w-full p-6 shadow-xl flex flex-col md:flex-row gap-6 max-h-[90vh] overflow-y-auto">
        
        <div class="flex-1 space-y-5 border-b md:border-b-0 md:border-r border-[#E2E8F0] pb-6 md:pb-0 md:pr-6">
          <div>
            <span class="text-[10px] bg-slate-100 px-2 py-1 rounded font-bold text-slate-500 uppercase tracking-wider">Halaman Verifikasi </span>
            <h3 class="text-base font-bold text-slate-800 mt-1.5">Profil / Biodata Pendaftar</h3>
          </div>

          <div class="flex flex-col sm:flex-row gap-5 items-start bg-slate-50/60 p-4 rounded-xl border border-[#E2E8F0]">
            <div class="w-24 h-32 bg-slate-200 rounded-lg border border-[#E2E8F0] overflow-hidden shadow-xs shrink-0 relative group">
              <img v-if="selectedItem?.photo_profile" :src="getDocumentUrl(selectedItem?.photo_profile)" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-[10px] font-bold bg-slate-100 uppercase gap-1 p-1 text-center">
                <span>📷</span>
                <span>NO PHOTO</span>
              </div>
              <label class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white text-[10px] font-bold cursor-pointer transition duration-200 text-center p-1">
                <span>📷 {{ selectedItem?.photo_profile ? 'Ganti Foto' : 'Unggah Foto' }}</span>
                <input type="file" accept="image/*" class="hidden" @change="e => handleFileUpload(e, 'photo_profile')" />
              </label>
            </div>
            
            <div class="text-xs space-y-2 text-slate-600 w-full">
              <p class="text-sm font-bold text-slate-800">{{ selectedItem?.full_name }}</p>
              <p><strong>Pangkat :</strong> <span class="font-bold text-slate-700">{{ formatLongRank(selectedItem?.pangkat) }}</span></p>
              <p><strong>NIKC :</strong> <span class="font-mono font-bold text-[#2563EB] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/60">{{ selectedItem?.nikc || '-' }}</span></p>
              <p><strong>Nomor Induk Kependudukan:</strong> {{ selectedItem?.nik }}</p>
              <p><strong>Nomor Kontrol Hub WhatsApp:</strong> {{ selectedItem?.phone_number }}</p>
              <p><strong>Alamat Email Akun:</strong> {{ selectedItem?.user?.email }}</p>
              <p><strong>Pilihan Alokasi Matra:</strong> TNI {{ selectedItem?.matra }}</p>
              <p><strong>Tahun Kelulusan Angkatan:</strong> Angkatan {{ selectedItem?.angkatan }}</p>

              <!-- Box Informasi ASN jika pendaftar adalah ASN -->
              <div v-if="selectedItem?.is_asn" class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-xl text-blue-800 space-y-1.5 animate-slide-up">
                <p class="font-extrabold text-[10px] uppercase tracking-wider text-blue-900">💼 Status: Aparatur Sipil Negara (ASN)</p>
                <p><strong>Nama Satuan Kerja:</strong> {{ selectedItem?.job_histories?.[0]?.nama_perusahaan || '-' }}</p>
                <p><strong>NIP ASN:</strong> {{ selectedItem?.asn_nip }}</p>
                <p><strong>Jenis ASN:</strong> {{ selectedItem?.asn_jenis }}</p>
                <p><strong>TMT Pengangkatan:</strong> {{ selectedItem?.asn_tmt }}</p>
                <p v-if="selectedItem?.asn_sk">
                  <strong>SK Pengangkatan:</strong> 
                  <a :href="route('personel.document.download', { path: selectedItem.asn_sk })" target="_blank" class="text-blue-700 hover:underline font-bold">Unduh Berkas SK ASN</a>
                </p>
              </div>
            </div>
          </div>

          <!-- Box KTP Lampiran -->
          <div class="space-y-2 mt-4">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dokumen KTP Lampiran</h4>
            <div class="relative group border border-[#E2E8F0] rounded-xl overflow-hidden bg-slate-100 h-40 flex items-center justify-center shadow-xs">
              <img v-if="selectedItem?.ktp_document" :src="getDocumentUrl(selectedItem?.ktp_document)" class="w-full h-full object-cover cursor-pointer" @click="enlargeKtp = true" />
              <div v-else class="text-slate-400 text-xs font-bold flex flex-col items-center gap-1">
                <span>📄 Tidak Ada Lampiran KTP</span>
              </div>
              
              <div class="absolute bottom-2 right-2 flex items-center gap-2">
                <button v-if="selectedItem?.ktp_document" type="button" @click="enlargeKtp = true" class="text-white text-[11px] font-bold bg-slate-950/80 hover:bg-slate-900 px-2.5 py-1 rounded-lg shadow-xs">🔍 Perbesar</button>
                <label class="text-white text-[11px] font-bold bg-blue-600 hover:bg-blue-700 px-2.5 py-1 rounded-lg cursor-pointer shadow-xs flex items-center gap-1">
                  <span>📤 {{ selectedItem?.ktp_document ? 'Ganti KTP' : 'Unggah KTP' }}</span>
                  <input type="file" accept="image/*,application/pdf" class="hidden" @change="e => handleFileUpload(e, 'ktp_document')" />
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="w-full md:w-80 shrink-0 flex flex-col justify-between space-y-5">
          <div class="space-y-4">
            <div>
              <h3 class="text-base font-bold text-slate-800">Otorisasi Kelulusan</h3>
              <p class="text-xs text-slate-400 mt-0.5">Tentukan keputusan akhir kelayakan berkas permohonan pendaftaran.</p>
            </div>

            <div class="space-y-4 pt-1">
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Status Penilaian</label>
                <select v-model="form.status" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] font-medium text-slate-700">
                  <option value="APPROVED">SETUJUI</option>
                  <option value="REJECTED">TOLAK PERMOHONAN BERKAS</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Catatan Administratif / Alasan</label>
                <textarea v-model="form.admin_notes" rows="4" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Contoh: Dokumen NIK valid, alokasi kode NIKC siap diterbitkan..."></textarea>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2.5 pt-3 border-t border-[#E2E8F0]">
            <button @click="showModal = false" class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">Batal</button>
            <button @click="submitVerification" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">Proses Keputusan</button>
          </div>
        </div>

      </div>
    </div>

    <!-- Lightbox Modal Perbesar KTP -->
    <div v-if="enlargeKtp" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4" @click="enlargeKtp = false">
      <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl bg-white/5 border border-white/10 p-2 flex items-center justify-center" @click.stopPropagation>
        <img :src="getDocumentUrl(selectedItem?.ktp_document)" class="max-w-full max-h-[85vh] object-contain rounded-xl" />
        <button @click="enlargeKtp = false" class="absolute top-4 right-4 bg-slate-900/70 hover:bg-slate-950 text-white rounded-full p-2 shadow-md transition cursor-pointer border-none flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({ pendaftar: Object, filters: Object });
const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const search = ref(props.filters.search || '');
const matra = ref(props.filters.matra || '');
const showModal = ref(false);
const selectedItem = ref(null);
const enlargeKtp = ref(false);

const form = ref({
  status: 'APPROVED',
  admin_notes: ''
});

let pendaftarInterval = null;

onMounted(() => {
  pendaftarInterval = setInterval(() => {
    router.reload({
      only: ['pendaftar'],
      preserveScroll: true,
      preserveState: true,
    });
  }, 10000); // Polling antrean pendaftaran setiap 10 detik
});

onUnmounted(() => {
  if (pendaftarInterval) {
    clearInterval(pendaftarInterval);
  }
});

const handleFilter = () => {
  router.get(route('admin.verification.index'), { search: search.value, matra: matra.value }, { preserveState: true });
};

const openActionModal = (item) => {
  selectedItem.value = item;
  form.value.status = 'APPROVED';
  form.value.admin_notes = '';
  showModal.value = true;
  enlargeKtp.value = false;
};

const submitVerification = () => {
  showModal.value = false;
  router.post(route('admin.verification.verify', selectedItem.value.uuid), form.value, {
    onBefore: () => showLoadingProgress('Memproses Keputusan Validasi...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Verifikasi Berhasil', 'Status kelulusan berkas pendaftar telah berhasil diperbarui.');
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Memproses', 'Terjadi kesalahan interupsi data server.');
    }
  });
};



const getDocumentUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;

  let cleanPath = path.replace(/^(storage\/|public\/)+/, '');
  return `/storage/${cleanPath}`;
};



const isUploading = ref(false);

const handleFileUpload = (event, type) => {
  const file = event.target.files[0];
  if (!file || !selectedItem.value) return;

  const formData = new FormData();
  formData.append('type', type);
  formData.append('file', file);

  isUploading.value = true;
  router.post(route('admin.verification.upload-document', selectedItem.value.uuid), formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (page) => {
      isUploading.value = false;
      // Refresh selectedItem photo/ktp
      const updated = page.props.pendaftar.data.find(p => p.uuid === selectedItem.value.uuid);
      if (updated) {
        selectedItem.value = updated;
      }
    },
    onError: () => {
      isUploading.value = false;
    }
  });
};

const formatLongRank = (pangkat) => {
  if (!pangkat) return '-';
  let p = pangkat.toUpperCase().trim();
  let isWanita = false;

  if (p.includes('(W)') || p.includes(' W')) {
    isWanita = true;
    p = p.replace('(W)', '').replace(' W', '').trim();
  }

  const map = {
    'PRADA': 'Prajurit Dua',
    'PRATU': 'Prajurit Satu',
    'PRAKA': 'Prajurit Kepala',
    'KOPDA': 'Kopral Dua',
    'KOPTU': 'Kopral Satu',
    'KOPKA': 'Kopral Kepala',
    'SERDA': 'Sersan Dua',
    'SERTU': 'Sersan Satu',
    'SERKA': 'Sersan Kepala',
    'SERMA': 'Sersan Mayor',
    'PELDA': 'Pembantu Letnan Dua',
    'PELTU': 'Pembantu Letnan Satu',
    'LETDA': 'Letnan Dua',
    'LETTU': 'Letnan Satu',
    'KAPTEN': 'Kapten',
    'MAYOR': 'Mayor',
    'LETKOL': 'Letnan Kolonel',
    'KOLONEL': 'Kolonel',
  };

  const parts = p.split(' ');
  const rankKey = parts[0];
  const korps = parts[1] ? ' ' + parts[1].charAt(0).toUpperCase() + parts[1].slice(1).toLowerCase() : '';

  let longRank = map[rankKey] ? (map[rankKey] + korps) : (p.charAt(0).toUpperCase() + p.slice(1).toLowerCase());

  if (!longRank.toUpperCase().includes('KC')) {
    if (isWanita) return `${longRank} KC/W`;
    return `${longRank} KC`;
  }

  return longRank;
};

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>