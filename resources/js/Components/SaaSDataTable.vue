<template>
  <div class="w-full overflow-hidden bg-white border border-[#E2E8F0] rounded-xl shadow-sm">
    <!-- Header Tabel / Slot Filter -->
    <div v-if="$slots.filters" class="p-5 border-b border-[#E2E8F0] bg-slate-50/50 flex flex-wrap gap-4 items-center justify-between">
      <slot name="filters"></slot>
    </div>

    <!-- Konten Data -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-500 font-semibold text-xs border-b border-[#E2E8F0] uppercase tracking-wider select-none">
            <slot name="headers"></slot>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-[#334155]">
          <slot name="rows"></slot>
          <tr v-if="isEmpty">
            <td colspan="100%" class="p-12 text-center text-sm text-slate-400">
              <div class="flex flex-col items-center justify-center gap-2">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <span>Tidak ada rekaman data yang ditemukan dalam sistem.</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer Paginasi -->
    <div v-if="pagination && pagination.total > 0" class="p-4 bg-slate-50 border-t border-[#E2E8F0] flex items-center justify-between text-xs text-slate-500">
      <div>
        Menampilkan <span class="font-medium text-slate-700">{{ pagination.from }}</span> hingga <span class="font-medium text-slate-700">{{ pagination.to }}</span> dari <span class="font-medium text-slate-700">{{ pagination.total }}</span> entitas.
      </div>
      <div class="flex gap-1.5">
        <button 
          v-for="(link, idx) in pagination.links" 
          :key="idx"
          :disabled="!link.url || link.active"
          @click="$emit('page-change', link.url)"
          v-html="link.label"
          class="px-3 py-1.5 rounded border text-xs font-medium transition"
          :class="[
            link.active ? 'bg-[#2563EB] text-white border-[#2563EB]' : 'bg-white text-slate-600 border-[#E2E8F0] hover:bg-slate-50',
            !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
          ]"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isEmpty: { type: Boolean, default: false },
  pagination: { type: Object, default: null }
});
defineEmits(['page-change']);
</script>