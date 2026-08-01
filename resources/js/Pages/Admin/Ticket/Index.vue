<template>
  <AuthenticatedLayout>
    <template #header-title>Verifikasi & Pengaduan Tiket Personel</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Daftar Pengaduan & Open Tiket</h1>
          <p class="text-xs text-slate-400 mt-1">Kelola dan verifikasi permohonan ubah pasfoto, perubahan biodata, dan cetak ulang KTA personel.</p>
        </div>
      </div>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold shadow-xs">
        <span>{{ $page.props.flash.success }}</span>
      </div>
      <div v-if="$page.props.flash?.error" class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-xs font-semibold shadow-xs">
        <span>{{ $page.props.flash.error }}</span>
      </div>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          type="text"
          placeholder="Cari No Tiket, Nama, atau NIKC..."
          class="flex-1 px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB]"
        />
        <select
          v-model="filterCategory"
          @change="handleSearch"
          class="px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 font-medium"
        >
          <option value="">Semua Kategori</option>
          <option value="UBAH_FOTO">Pengajuan Ubah Pasfoto</option>
          <option value="UBAH_DATA">Pengajuan Perubahan Data</option>
          <option value="CETAK_KTA">Pengajuan Cetak KTA</option>
        </select>
        <select
          v-model="filterStatus"
          @change="handleSearch"
          class="px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 font-medium"
        >
          <option value="">Semua Status</option>
          <option value="DIPROSES">DIPROSES</option>
          <option value="DISETUJUI">DISETUJUI</option>
          <option value="DITOLAK">DITOLAK</option>
          <option value="SELESAI">SELESAI</option>
        </select>
      </div>

      <!-- Tabel Tiket -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none whitespace-nowrap">
              <th class="p-4">No Tiket</th>
              <th class="p-4">Identitas Personel</th>
              <th class="p-4">Kategori Layanan</th>
              <th class="p-4">Keterangan / Detail Alasan</th>
              <th class="p-4">Lampiran Berkas</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-right">Opsi Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-xs text-slate-600">
            <tr v-for="t in tickets.data" :key="t.id" class="hover:bg-slate-50/40 transition">
              <td class="p-4 whitespace-nowrap">
                <button
                  @click="openDetailModal(t)"
                  class="font-mono font-bold text-[#2563EB] bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg border border-blue-200 text-[11px] hover:underline cursor-pointer transition inline-flex items-center gap-1.5"
                  title="Klik untuk melihat riwayat proses tiket"
                >
                  <span>{{ t.ticket_number }}</span>
                  <span class="text-[10px] opacity-70">📋</span>
                </button>
                <p class="text-[10px] text-slate-400 mt-1">{{ formatDate(t.created_at) }}</p>
              </td>
              <td class="p-4 whitespace-nowrap">
                <div class="flex items-center gap-2.5">
                  <img
                    :src="t.personel?.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(t.personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(t.personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`"
                    class="w-8 h-10 object-cover rounded-lg bg-slate-100 border border-[#E2E8F0] shrink-0"
                  />
                  <div>
                    <p class="font-bold text-slate-800">{{ t.personel?.full_name || '-' }}</p>
                    <p class="text-[10px] text-slate-400">
                      NIKC: <span class="font-mono font-semibold text-slate-600">{{ t.personel?.nikc || '-' }}</span> |
                      TNI {{ t.personel?.matra }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="font-bold text-slate-800">
                  {{ formatCategory(t.category) }}
                </span>
              </td>
              <td class="p-4 max-w-xs leading-relaxed text-slate-700">
                {{ t.description || '-' }}
              </td>
              <td class="p-4 whitespace-nowrap">
                <a
                  v-if="t.attachment_path"
                  :href="`/documents/private-stream?path=${encodeURIComponent(t.attachment_path)}`"
                  target="_blank"
                  class="text-[#2563EB] font-bold hover:underline bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 text-[11px] inline-flex items-center gap-1"
                >
                  Lihat File
                </a>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span :class="statusBadgeClass(t.status)" class="px-2.5 py-1 rounded-xl text-[10px] font-bold border inline-block whitespace-nowrap">
                  {{ t.status }}
                </span>
                <p v-if="t.status === 'DITOLAK' && t.rejection_reason" class="text-[10px] text-red-500 mt-1 max-w-[150px] truncate" :title="t.rejection_reason">
                  Alasan: {{ t.rejection_reason }}
                </p>
              </td>
              <td class="p-4 text-right whitespace-nowrap space-x-2">
                <button
                  @click="openVerifyModal(t)"
                  class="px-3 py-1.5 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs"
                >
                  Proses Tiket
                </button>
              </td>
            </tr>
            <tr v-if="tickets.data.length === 0">
              <td colspan="7" class="p-12 text-center text-slate-400 italic">
                Tidak ada data tiket pengaduan personel.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- MODAL PROSES VERIFIKASI TIKET -->
    <div v-if="selectedTicket" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 my-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-800">Verifikasi & Keputusan Tiket</h3>
            <p class="font-mono text-xs text-[#2563EB] font-bold mt-0.5">{{ selectedTicket.ticket_number }}</p>
          </div>
          <button @click="selectedTicket = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">✕</button>
        </div>

        <!-- Detail Singkat -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-slate-400 font-bold uppercase">Pemohon:</span>
            <span class="font-extrabold text-slate-800">{{ selectedTicket.personel?.full_name }} ({{ selectedTicket.personel?.nikc }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400 font-bold uppercase">Kategori:</span>
            <span class="font-bold text-slate-800">{{ formatCategory(selectedTicket.category) }}</span>
          </div>
          <div v-if="selectedTicket.description" class="pt-2 border-t border-slate-200/60">
            <span class="text-slate-400 font-bold uppercase block mb-0.5">Keterangan / Alasan:</span>
            <p class="text-slate-700 leading-relaxed font-medium bg-white p-2.5 rounded-xl border border-slate-200">{{ selectedTicket.description }}</p>
          </div>
          <div v-if="selectedTicket.category === 'UBAH_FOTO'" class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-[11px] font-semibold flex items-start gap-2">
            <span>Jika Anda memilih status <strong>DISETUJUI</strong>, foto profil lama personel di server akan dihapus secara otomatis dan digantikan dengan foto baru yang dilampirkan.</span>
          </div>
        </div>

        <form @submit.prevent="submitVerify" class="space-y-4">
          <div>
            <label class="block text-xs font-extrabold text-slate-600 uppercase mb-1.5">Keputusan Status Tiket</label>
            <select
              v-model="verifyForm.status"
              class="w-full px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs font-bold outline-none focus:border-[#2563EB] text-slate-700"
              required
            >
              <option value="DIPROSES">DIPROSES (Dalam Penanganan)</option>
              <option value="DISETUJUI">DISETUJUI (Permohonan Diterima)</option>
              <option value="SELESAI">SELESAI (Proses Tuntas)</option>
              <option value="DITOLAK">DITOLAK (Kembalikan / Tolak)</option>
            </select>
          </div>

          <div v-if="verifyForm.status === 'DITOLAK'">
            <label class="block text-xs font-extrabold text-red-600 uppercase mb-1.5">Alasan Penolakan Tiket</label>
            <textarea
              v-model="verifyForm.rejection_reason"
              rows="3"
              class="w-full p-3 border border-red-300 rounded-xl text-xs outline-none focus:border-red-500 text-slate-700"
              placeholder="Tuliskan alasan penolakan secara jelas untuk informasi personel..."
              required
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="selectedTicket = null"
              class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="verifyForm.processing"
              class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-600 disabled:opacity-60 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer"
            >
              Simpan Keputusan Status
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL DETAIL & TIMELINE RIWAYAT PROSES TIKET -->
    <div v-if="detailTicket" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-6 my-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <span class="text-[10px] font-extrabold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 uppercase tracking-widest">
              RIWAYAT PROSES TIKET
            </span>
            <h3 class="font-mono text-base font-black text-slate-800 mt-1">{{ detailTicket.ticket_number }}</h3>
          </div>
          <button @click="detailTicket = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">✕</button>
        </div>

        <!-- Informational Summary -->
        <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Pemohon</span>
            <span class="font-extrabold text-slate-800">{{ detailTicket.personel?.full_name }} ({{ detailTicket.personel?.nikc }})</span>
          </div>
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Kategori Layanan</span>
            <span class="font-bold text-slate-800">{{ formatCategory(detailTicket.category) }}</span>
          </div>
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Matra & Angkatan</span>
            <span class="font-semibold text-slate-700">TNI {{ detailTicket.personel?.matra }} / {{ detailTicket.personel?.angkatan }}</span>
          </div>
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Tanggal Pengajuan</span>
            <span class="font-semibold text-slate-700">{{ formatDate(detailTicket.created_at) }}</span>
          </div>
          <div class="col-span-2 border-t border-slate-200/60 pt-2" v-if="detailTicket.description">
            <span class="text-slate-400 font-bold uppercase text-[10px] block mb-0.5">Keterangan / Alasan</span>
            <p class="text-slate-700 leading-relaxed font-medium bg-white p-2.5 rounded-xl border border-slate-200">{{ detailTicket.description }}</p>
          </div>
        </div>

        <!-- TIMELINE RIWAYAT PROSES (CHRONOLOGICAL) -->
        <div class="space-y-3">
          <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Tahapan & Riwayat Pemrosesan Tiket</h4>
          
          <div class="relative border-l-2 border-slate-200 ml-3 space-y-6 py-1">
            <div
              v-for="(log, idx) in (detailTicket.logs && detailTicket.logs.length > 0 ? detailTicket.logs : generateFallbackLogs(detailTicket))"
              :key="idx"
              class="relative pl-6"
            >
              <!-- Timeline Dot -->
              <div
                :class="statusBadgeClass(log.status)"
                class="absolute -left-[9px] top-0.5 w-4 h-4 rounded-full border-2 border-white ring-2 ring-slate-100 shrink-0"
              ></div>

              <!-- Content -->
              <div class="space-y-1">
                <div class="flex items-center gap-2">
                  <span :class="statusBadgeClass(log.status)" class="px-2 py-0.5 rounded-lg text-[10px] font-bold border">
                    {{ log.status }}
                  </span>
                  <span class="text-[11px] text-slate-400 font-medium">{{ formatDate(log.created_at) }}</span>
                </div>

                <p class="text-xs font-semibold text-slate-800">{{ log.note }}</p>

                <!-- Verifikator / Pelaku Info -->
                <p class="text-[11px] text-slate-500 font-medium">
                  Oleh: <span class="font-bold text-slate-700">{{ getLogUserLabel(log, detailTicket) }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-3 border-t border-slate-100">
          <button
            @click="detailTicket = null"
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Tutup Riwayat
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  tickets: Object,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const filterCategory = ref(props.filters?.category || '');
const filterStatus = ref(props.filters?.status || '');

const selectedTicket = ref(null);
const detailTicket = ref(null);

const verifyForm = useForm({
  status: 'DISETUJUI',
  rejection_reason: '',
});

function openDetailModal(ticket) {
  detailTicket.value = ticket;
}

function handleSearch() {
  router.get(
    route('admin.tickets.index'),
    {
      search: searchQuery.value,
      category: filterCategory.value,
      status: filterStatus.value,
    },
    { preserveState: true, replace: true }
  );
}

function openVerifyModal(ticket) {
  selectedTicket.value = ticket;
  verifyForm.status = ticket.status || 'DISETUJUI';
  verifyForm.rejection_reason = ticket.rejection_reason || '';
}

function submitVerify() {
  if (!selectedTicket.value) return;
  verifyForm.post(route('admin.tickets.update-status', selectedTicket.value.id), {
    onSuccess: () => {
      selectedTicket.value = null;
    },
  });
}

function formatCategory(cat) {
  const map = {
    UBAH_FOTO: 'Ubah Pasfoto',
    UBAH_DATA: 'Perubahan Biodata',
    CETAK_KTA: 'Cetak Ulang KTA',
  };
  return map[cat] || cat;
}

function statusBadgeClass(st) {
  switch (st) {
    case 'DISETUJUI':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'SELESAI':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'DITOLAK':
      return 'bg-red-50 text-red-700 border-red-200';
    default:
      return 'bg-amber-50 text-amber-700 border-amber-200';
  }
}

function formatDate(dt) {
  if (!dt) return '-';
  const d = new Date(dt);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
}

function getLogUserLabel(log, ticket) {
  if (log.user?.personel?.full_name) {
    const roleName = log.user?.role?.name ? ` (${log.user.role.name.toUpperCase().replace('_', ' ')})` : '';
    return `${log.user.personel.full_name}${roleName}`;
  }
  if (log.user_id && log.user_id === ticket.personel?.user_id) {
    return `${ticket.personel?.full_name || 'Personel Pemohon'} (PERSONEL)`;
  }
  return 'Administrator / Verifikator Sistem';
}

function generateFallbackLogs(ticket) {
  const logs = [];
  logs.push({
    status: 'DIPROSES',
    created_at: ticket.created_at,
    note: 'Tiket pengaduan berhasil diajukan oleh personel.',
    user_id: ticket.personel?.user_id,
  });

  if (ticket.verified_at && ticket.status !== 'DIPROSES') {
    let noteStr = `Status tiket diperbarui menjadi ${ticket.status}.`;
    if (ticket.status === 'DITOLAK' && ticket.rejection_reason) {
      noteStr = `Pengajuan ditolak. Alasan: ${ticket.rejection_reason}`;
    } else if (ticket.status === 'DISETUJUI') {
      noteStr = 'Pengajuan disetujui oleh verifikator.';
    } else if (ticket.status === 'SELESAI') {
      noteStr = 'Proses tiket pengaduan selesai.';
    }

    logs.push({
      status: ticket.status,
      created_at: ticket.verified_at,
      note: noteStr,
      user: ticket.verifier,
    });
  }

  return logs;
}
</script>
