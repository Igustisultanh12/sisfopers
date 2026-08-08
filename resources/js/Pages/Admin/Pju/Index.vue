<template>
  <AuthenticatedLayout>
    <template #header-title>Manajemen Akun Pejabat Utama (PJU)</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

      <!-- Header & Action Button -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Manajemen Akun Pejabat Utama (PJU)</h1>
          <p class="text-xs text-slate-400 mt-1">Pengelolaan akun otorisasi PJU Bacadnas, Kapus Komcad, Pembina Matra, dan Pembina Kewilayahan Satuan.</p>
        </div>
        <button
          @click="openCreateModal"
          class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer self-start sm:self-auto flex items-center gap-2"
        >
          <span>+</span> Tambah Akun PJU Baru
        </button>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari kata kunci: Nama Pejabat, Email, Jabatan PJU, Satuan..."
            class="w-full pl-4 pr-10 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB]"
          />
          <button
            @click="handleSearch"
            class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600 font-bold"
          >
            Cari
          </button>
        </div>

        <select
          v-model="filterRole"
          @change="handleSearch"
          class="px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 font-medium"
        >
          <option value="">Semua Tingkat Jabatan PJU</option>
          <option value="ka_bacadnas">Kepala Bacadnas</option>
          <option value="ses_bacadnas">Sekretaris Bacadnas</option>
          <option value="kapus_komcad">Kapus Komcad</option>
          <option value="pembina_matra">Pembina Matra (AD / AL / AU)</option>
          <option value="pembina_kodam">Pembina Tingkat Kodam</option>
          <option value="pembina_kodaeral">Pembina Tingkat Kodaeral</option>
          <option value="pembina_kodau">Pembina Tingkat Kodau</option>
          <option value="pembina_kodim">Pembina Tingkat Kodim</option>
          <option value="pembina_lanal">Pembina Tingkat Lanal</option>
          <option value="pembina_lanud">Pembina Tingkat Lanud</option>
        </select>
      </div>

      <!-- Tabel Akun PJU -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none whitespace-nowrap">
              <th class="p-4">Pejabat Utama</th>
              <th class="p-4">Jabatan PJU</th>
              <th class="p-4">Peran & Fitur Khusus</th>
              <th class="p-4">Matra & Satuan Wilayah</th>
              <th class="p-4">Kontak / Email</th>
              <th class="p-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-xs text-slate-600">
            <tr v-for="user in pjuUsers.data" :key="user.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-blue-100 border border-blue-200 text-blue-700 flex items-center justify-center font-black text-sm uppercase shrink-0">
                    {{ (user.full_name || user.username || 'PJU').charAt(0) }}
                  </div>
                  <div>
                    <p class="font-extrabold text-slate-800">{{ user.full_name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">Username: {{ user.username }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4 whitespace-nowrap font-bold text-slate-800">
                {{ user.jabatan_pju || '-' }}
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold border bg-blue-50 text-blue-700 border-blue-200 uppercase inline-block">
                  {{ formatRoleLabel(user.role_pju) }}
                </span>
                <span v-if="user.role_pju === 'pembina_matra'" class="ml-1.5 px-2 py-0.5 rounded-lg text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase inline-block">
                  + Broadcast Kegiatan
                </span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <div class="space-y-0.5">
                  <p class="font-bold text-slate-700">
                    Matra: <span class="text-blue-600 uppercase">{{ user.matra || 'SEMUA MATRA' }}</span>
                  </p>
                  <p class="text-[10px] text-slate-400">
                    Satuan: {{ user.satuan_wilayah || 'Mabes / Pusat' }}
                  </p>
                </div>
              </td>
              <td class="p-4 whitespace-nowrap text-slate-600">
                <p class="font-semibold">{{ user.email }}</p>
                <p class="text-[10px] text-slate-400">{{ user.phone_number || '-' }}</p>
              </td>
              <td class="p-4 text-right whitespace-nowrap space-x-1.5">
                <a
                  :href="route('admin.pju.print-account', user.id)"
                  target="_blank"
                  class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded-lg border border-emerald-200 transition inline-block"
                >
                  Cetak Akun
                </a>
                <button
                  @click="openEditModal(user)"
                  class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-[11px] font-bold rounded-lg border border-amber-200 transition cursor-pointer"
                >
                  Edit
                </button>
                <button
                  @click="deleteUser(user)"
                  class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 text-[11px] font-bold rounded-lg border border-red-200 transition cursor-pointer"
                >
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="pjuUsers.data.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-400 italic">
                Belum ada data akun Pejabat Utama (PJU) yang terdaftar.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginasi -->
        <div class="p-4 bg-slate-50 border-t border-[#E2E8F0] flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
          <span>Menampilkan {{ pjuUsers.from || 0 }} sampai {{ pjuUsers.to || 0 }} dari {{ pjuUsers.total }} akun PJU</span>
          <div class="flex gap-2">
            <Link
              v-for="(link, i) in pjuUsers.links"
              :key="i"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-[#2563EB] text-white border-[#2563EB]' : (link.url ? 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' : 'bg-slate-100 text-slate-300 border-slate-100 cursor-not-allowed')"
            />
          </div>
        </div>
      </div>

    </div>

    <!-- MODAL TAMBAH / EDIT AKUN PJU -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-5 my-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-extrabold text-slate-800">
            {{ isEditing ? 'Edit Data Akun Pejabat Utama (PJU)' : 'Tambah Akun Pejabat Utama (PJU) Baru' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Pejabat & Pangkat</label>
            <input
              v-model="form.full_name"
              type="text"
              required
              placeholder="Contoh: Mayjen TNI Dr. H. Ahmad, S.I.P."
              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB]"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Jabatan Resmi PJU</label>
              <input
                v-model="form.jabatan_pju"
                type="text"
                required
                placeholder="Contoh: Ka Bacadnas / Pembina Matra AL"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB]"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tingkat Role / Akses PJU</label>
              <select
                v-model="form.role"
                required
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] bg-white font-medium"
              >
                <option value="ka_bacadnas">Ka Bacadnas (Kepala Bacadnas)</option>
                <option value="ses_bacadnas">Ses Bacadnas (Sekretaris Bacadnas)</option>
                <option value="kapus_komcad">Kapus Komcad (Kepala Pusat Komcad)</option>
                <option value="pembina_matra">Pembina Matra (+ Akses Broadcast)</option>
                <option value="pembina_kodam">Pembina Tingkat Kodam</option>
                <option value="pembina_kodaeral">Pembina Tingkat Kodaeral</option>
                <option value="pembina_kodau">Pembina Tingkat Kodau</option>
                <option value="pembina_kodim">Pembina Tingkat Kodim</option>
                <option value="pembina_lanal">Pembina Tingkat Lanal</option>
                <option value="pembina_lanud">Pembina Tingkat Lanud</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Matra Wewenang</label>
              <select
                v-model="form.matra"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] bg-white font-medium"
              >
                <option :value="null">Semua Matra (Nasional)</option>
                <option value="AD">TNI AD</option>
                <option value="AL">TNI AL</option>
                <option value="AU">TNI AU</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Satuan Wilayah / Komando</label>
              <input
                v-model="form.satuan_wilayah"
                type="text"
                placeholder="Contoh: Kodam I/BB / Lanal Jakarta"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB]"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Alamat Email Dinas</label>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="pju@bacadnas.tni.mil.id"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB]"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif</label>
              <input
                v-model="form.phone_number"
                type="text"
                required
                placeholder="081234567890"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB]"
              />
            </div>
          </div>

          <!-- KATA SANDI OTOMATIS TERGENERATE -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="font-bold text-slate-700">
                Kata Sandi (Password)
                <span v-if="!isEditing" class="text-blue-600 font-extrabold ml-1">(Otomatis Ter-generate)</span>
                <span v-else class="font-normal text-slate-400">(Kosongkan jika tidak diubah)</span>
              </label>
              <button
                v-if="!isEditing"
                type="button"
                @click="generatePassword"
                class="text-[10px] font-bold text-blue-600 hover:underline flex items-center gap-1 cursor-pointer"
              >
                🔄 Acak Ulang Password
              </button>
            </div>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPasswordText ? 'text' : 'password'"
                :placeholder="isEditing ? 'Minimal 6 karakter kombinasi' : 'Password otomatis'"
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] font-mono text-slate-800 font-bold bg-slate-50/50"
              />
              <button
                type="button"
                @click="showPasswordText = !showPasswordText"
                class="absolute right-3 top-2.5 text-[10px] font-bold text-slate-400 hover:text-slate-600"
              >
                {{ showPasswordText ? 'Sembunyikan' : 'Tampilkan' }}
              </button>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2.5 text-slate-500 hover:text-slate-800 font-bold cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-600 text-white font-bold rounded-xl shadow-md transition cursor-pointer"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Buat Akun PJU' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL SUKSES PEMBUATAN AKUN & CETAK KREDENSIAL PJU -->
    <div v-if="createdPjuModal" class="fixed inset-0 z-50 bg-slate-900/75 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 my-auto text-left relative animate-in fade-in zoom-in duration-200">
        <div class="text-center space-y-2">
          <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 mx-auto flex items-center justify-center font-black text-2xl shadow-xs">
            ✓
          </div>
          <span class="text-[10px] font-extrabold uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-lg inline-block">
            AKUN PJU BERHASIL DIBUAT
          </span>
          <h3 class="text-lg font-black text-slate-800 tracking-tight">
            {{ createdPjuModal.full_name }}
          </h3>
          <p class="text-xs text-slate-500">
            {{ createdPjuModal.jabatan_pju }}
          </p>
        </div>

        <!-- Box Kredensial PJU -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3 text-xs">
          <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
            <span class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Username Login</span>
            <span class="font-mono font-bold text-slate-800">{{ createdPjuModal.username }}</span>
          </div>
          <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
            <span class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Email Dinas</span>
            <span class="font-mono font-bold text-slate-800">{{ createdPjuModal.email }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Password Otomatis</span>
            <span class="font-mono font-black text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded">{{ createdPjuModal.password }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2 pt-2">
          <a
            :href="route('admin.pju.print-account', { id: createdPjuModal.id, pass: createdPjuModal.password })"
            target="_blank"
            class="w-full py-3 bg-[#2563EB] hover:bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>🖨️</span> Cetak Lembar Informasi Akun PJU
          </a>
          <button
            @click="createdPjuModal = null"
            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer"
          >
            Tutup Dialog
          </button>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  pjuUsers: Object,
  roles: Array,
  filters: Object,
  createdPju: Object,
});

const searchQuery = ref(props.filters?.search || '');
const filterRole = ref(props.filters?.role || '');
const showModal = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);
const showPasswordText = ref(true);
const createdPjuModal = ref(props.createdPju || null);

watch(() => props.createdPju, (val) => {
  if (val) {
    createdPjuModal.value = val;
  }
});

const form = useForm({
  full_name: '',
  email: '',
  phone_number: '',
  role: 'ka_bacadnas',
  jabatan_pju: '',
  matra: null,
  satuan_wilayah: '',
  password: '',
});

function generatePassword() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
  let result = 'Pju@';
  for (let i = 0; i < 6; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  form.password = result;
}

function handleSearch() {
  router.get(
    route('admin.pju.index'),
    {
      search: searchQuery.value,
      role: filterRole.value,
    },
    { preserveState: true, replace: true }
  );
}

function openCreateModal() {
  isEditing.value = false;
  editingUserId.value = null;
  form.reset();
  generatePassword();
  showModal.value = true;
}

function openEditModal(user) {
  isEditing.value = true;
  editingUserId.value = user.id;
  form.full_name = user.full_name || '';
  form.email = user.email || '';
  form.phone_number = user.phone_number || '';
  form.role = user.role_pju || 'ka_bacadnas';
  form.jabatan_pju = user.jabatan_pju || '';
  form.matra = user.matra || null;
  form.satuan_wilayah = user.satuan_wilayah || '';
  form.password = '';
  showModal.value = true;
}

function submitForm() {
  if (isEditing.value) {
    form.put(route('admin.pju.update', editingUserId.value), {
      onSuccess: () => {
        showModal.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('admin.pju.store'), {
      onSuccess: (page) => {
        showModal.value = false;
        if (page.props.createdPju) {
          createdPjuModal.value = page.props.createdPju;
        }
        form.reset();
      },
    });
  }
}

function deleteUser(user) {
  const name = user.full_name || user.username;
  if (confirm(`Apakah Anda yakin ingin menghapus akun PJU ${name}?`)) {
    router.delete(route('admin.pju.destroy', user.id));
  }
}

function formatRoleLabel(roleName) {
  const labels = {
    ka_bacadnas: 'Ka Bacadnas',
    ses_bacadnas: 'Ses Bacadnas',
    kapus_komcad: 'Kapus Komcad',
    pembina_matra: 'Pembina Matra',
    pembina_kodam: 'Pembina Kodam',
    pembina_kodaeral: 'Pembina Kodaeral',
    pembina_kodau: 'Pembina Kodau',
    pembina_kodim: 'Pembina Kodim',
    pembina_lanal: 'Pembina Lanal',
    pembina_lanud: 'Pembina Lanud',
  };
  return labels[roleName] || roleName;
}
</script>
