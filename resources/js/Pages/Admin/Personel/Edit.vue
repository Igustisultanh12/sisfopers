<template>
  <AuthenticatedLayout>
    <template #header-title>Mutasi & Perubahan Data Personel</template>

    <div class="max-w-4xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40 flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-slate-800">Sunting Dokumen Anggota</h3>
          <p class="text-xs text-slate-400 mt-0.5">Ubah parameter kredensial, pangkat, matra, serta penataan berkas administrasi personel.</p>
        </div>
        <Link :href="route('admin.personel.index')" class="text-xs font-bold text-[#2563EB] hover:underline transition">
          Kembali ke Daftar
        </Link>
      </div>

      <div class="p-8">
        <form @submit.prevent="submitUpdate" class="space-y-6 max-w-3xl">
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap Personel</label>
              <input type="text" v-model="form.full_name" :class="form.errors.full_name ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.full_name" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.full_name }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Alamat Email Sistem</label>
              <input type="email" v-model="form.email" :class="form.errors.email ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.email" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.email }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor WhatsApp Aktif</label>
              <input type="text" v-model="form.phone_number" :class="form.errors.phone_number ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.phone_number" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.phone_number }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-2">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pangkat</label>
              <select v-model="form.pangkat" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium animate-none" required>
                <option v-for="pangkat in pangkatOptions" :key="pangkat.nama" :value="pangkat.nama">{{ pangkat.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Matra</label>
              <select v-model="form.matra" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium" required>
                <option value="AD">TNI AD</option>
                <option value="AL">TNI AL</option>
                <option value="AU">TNI AU</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tahun Angkatan</label>
              <input type="text" v-model="form.angkatan" maxlength="4" :class="form.errors.angkatan ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.angkatan" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.angkatan }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor Induk Komcad (NIKC)</label>
              <input type="text" v-model="form.nikc" :class="form.errors.nikc ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" placeholder="Belum Terbit (-)" />
              <div v-if="form.errors.nikc" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.nikc }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Peran Akun</label>
              <select v-model="form.role" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium" required>
                <option value="personel">Anggota Komcad</option>
                <option value="kordinator_angkatan">Koordinator Angkatan</option>
                <option value="kordinator_matra">Koordinator Matra</option>
                <option value="admin">Administrator Sistem</option>
              </select>
            </div>
          </div>

          <div class="pt-4 border-t border-[#E2E8F0]">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Perbarui Pasfoto Profil (Abaikan jika tidak ingin diubah)</label>
            <div class="flex items-center gap-4">
              <div class="w-12 h-16 bg-slate-100 border border-[#E2E8F0] rounded-xl overflow-hidden shrink-0 shadow-xs">
                <img :src="photoPreview || (personel.photo_profile ? `/documents/private/${personel.photo_profile}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`)" class="w-full h-full object-cover" alt="Pasfoto Personel" />
              </div>
              <input type="file" @change="handlePhotoChange" :class="form.errors.photo_profile ? 'text-red-500' : ''" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 cursor-pointer" accept="image/*" />
            </div>
            <div v-if="form.errors.photo_profile" class="text-red-500 text-[11px] mt-2 font-semibold">⚠️ {{ form.errors.photo_profile }}</div>
          </div>

          <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
            <button type="submit" :disabled="form.processing" class="py-2.5 px-6 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-60 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/10 transition cursor-pointer">
              Simpan Amandemen Profil
            </button>
          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({ 
  personel: Object,
  pangkatOptions: {
    type: Array,
    default: () => []
  }
});
const pangkatOptions = props.pangkatOptions || [];

const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();
const photoPreview = ref(null);

// Form inisialisasi membawa data riil entitas dari database MySQL
const form = useForm({
  _method: 'PUT', // Menggunakan spoofing METHOD PUT agar dibaca aman oleh router & controller Laravel multipart data
  full_name: props.personel.full_name,
  email: props.personel.user?.email || '',
  phone_number: props.personel.phone_number,
  matra: props.personel.matra,
  angkatan: props.personel.angkatan,
  pangkat: props.personel.pangkat || 'Prada KC',
  nikc: props.personel.nikc || '',
  photo_profile: null,
  role: props.personel.user?.role?.name || 'personel',
});

const handlePhotoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.photo_profile = file;
    photoPreview.value = URL.createObjectURL(file); // Membuat URL lokal blob temporer untuk visual preview pasfoto
  }
};

const submitUpdate = () => {
  // Dikirim via method POST untuk membungkus data biner multipart berkas gambar dengan aman menuju web router proxy
  form.post(route('admin.personel.update', props.personel.uuid), {
    forceFormData: true,
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Menyimpan Perubahan Profil Anggota...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Pembaruan Berhasil', 'Data Mutasi Personel Berhasil Disinkronkan.');
    },
    onError: (errors) => {
      closeLoading();
      const firstErrorKey = Object.keys(errors)[0];
      const errorMessage = errors[firstErrorKey] || 'Harap periksa kelengkapan field parameter inputan.';
      alertError('Gagal Memperbarui', errorMessage);
    }
  });
};
</script>
