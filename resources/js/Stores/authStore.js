import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    role: null,
    permissions: [],
    unreadNotificationsCount: 0,
    isSidebarOpen: true,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
    isAdmin: (state) => state.role === 'admin',
    isKomandan: (state) => state.role === 'komandan',
    isPersonel: (state) => state.role === 'personel',
  },

  actions: {
    setAuthData(pageProps) {
      this.user = pageProps.auth?.user || null;
      this.role = pageProps.auth?.user?.role?.name || null;
    },

    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },

    async fetchUnreadNotifications() {
      try {
        const response = await axios.get('/api/notifications/unread-count');
        this.unreadNotificationsCount = response.data.count;
      } catch (error) {
        console.error('Gagal mengambil data notifikasi:', error);
      }
    },

    clearAuth() {
      this.user = null;
      this.role = null;
      this.unreadNotificationsCount = 0;
    }
  }
});