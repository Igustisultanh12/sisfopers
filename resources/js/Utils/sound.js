/**
 * Utilitas Suara Notifikasi Dinas SISFOPERSKC
 * Memanfaatkan Web Audio API murni tanpa ketergantungan berkas audio eksternal.
 * Menghasilkan nada lonceng ganda (harmonic two-tone chime) yang elegan dan profesional.
 */

let audioCtx = null;

export const playNotificationSound = () => {
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;

    if (!audioCtx) {
      audioCtx = new AudioContextClass();
    }

    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }

    const now = audioCtx.currentTime;

    // Nada pertama (880 Hz - A5)
    const osc1 = audioCtx.createOscillator();
    const gain1 = audioCtx.createGain();

    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(880, now);

    gain1.gain.setValueAtTime(0.12, now);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.25);

    osc1.connect(gain1);
    gain1.connect(audioCtx.destination);

    osc1.start(now);
    osc1.stop(now + 0.25);

    // Nada kedua (1320 Hz - E6) berjarak 0.08 detik
    const osc2 = audioCtx.createOscillator();
    const gain2 = audioCtx.createGain();

    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(1320, now + 0.08);

    gain2.gain.setValueAtTime(0.15, now + 0.08);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

    osc2.connect(gain2);
    gain2.connect(audioCtx.destination);

    osc2.start(now + 0.08);
    osc2.stop(now + 0.45);
  } catch (err) {
    // Menangani pembatasan autoplay peramban sebelum interaksi pengguna
    console.debug('Pemutaran nada notifikasi diabaikan:', err);
  }
};
