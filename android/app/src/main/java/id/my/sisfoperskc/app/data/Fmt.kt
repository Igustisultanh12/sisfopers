package id.my.sisfoperskc.app.data

import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale
import java.util.TimeZone

object Fmt {
    private val id = Locale("id", "ID")
    private val wib = TimeZone.getTimeZone("Asia/Jakarta")
    private val months = listOf("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des")
    private val monthsLong = listOf("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember")
    private val days = listOf("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")

    fun parse(value: String?): Date? {
        if (value.isNullOrBlank() || value == "null") return null
        val v = value.trim().replace(Regex("\\.\\d+"), "")
        val patterns = listOf(
            "yyyy-MM-dd'T'HH:mm:ssXXX",
            "yyyy-MM-dd'T'HH:mm:ssX",
            "yyyy-MM-dd HH:mm:ssXXX",
            "yyyy-MM-dd HH:mm:ss",
            "yyyy-MM-dd'T'HH:mm:ss",
            "yyyy-MM-dd'T'HH:mm",
            "yyyy-MM-dd",
        )
        for (p in patterns) {
            try {
                val f = SimpleDateFormat(p, Locale.US)
                f.timeZone = wib
                f.isLenient = false
                return f.parse(v) ?: continue
            } catch (_: Exception) {
            }
        }
        return null
    }

    private fun cal(d: Date) = Calendar.getInstance(wib, id).apply { time = d }

    /** 23 Okt 2026 */
    fun date(value: String?, fallback: String = "-"): String {
        val d = parse(value) ?: return value?.takeIf { it.isNotBlank() && it != "null" } ?: fallback
        val c = cal(d)
        return "${c.get(Calendar.DAY_OF_MONTH)} ${months[c.get(Calendar.MONTH)]} ${c.get(Calendar.YEAR)}"
    }

    /** 23 Oktober 2026 */
    fun dateLong(value: String?, fallback: String = "-"): String {
        val d = parse(value) ?: return value ?: fallback
        val c = cal(d)
        return "${c.get(Calendar.DAY_OF_MONTH)} ${monthsLong[c.get(Calendar.MONTH)]} ${c.get(Calendar.YEAR)}"
    }

    /** Kamis, 23 Oktober 2026 */
    fun dayDate(value: String?): String {
        val d = parse(value) ?: return value ?: "-"
        val c = cal(d)
        return "${days[c.get(Calendar.DAY_OF_WEEK) - 1]}, ${c.get(Calendar.DAY_OF_MONTH)} ${monthsLong[c.get(Calendar.MONTH)]} ${c.get(Calendar.YEAR)}"
    }

    /** 23 Okt 2026, 10:15 WIB */
    fun dateTime(value: String?, fallback: String = "-"): String {
        val d = parse(value) ?: return value?.takeIf { it.isNotBlank() && it != "null" } ?: fallback
        val c = cal(d)
        return "${c.get(Calendar.DAY_OF_MONTH)} ${months[c.get(Calendar.MONTH)]} ${c.get(Calendar.YEAR)}, " +
            String.format(Locale.US, "%02d:%02d", c.get(Calendar.HOUR_OF_DAY), c.get(Calendar.MINUTE)) + " WIB"
    }

    fun time(value: String?): String {
        val d = parse(value) ?: return ""
        val c = cal(d)
        return String.format(Locale.US, "%02d:%02d", c.get(Calendar.HOUR_OF_DAY), c.get(Calendar.MINUTE))
    }

    fun relative(value: String?): String {
        val d = parse(value) ?: return value ?: ""
        val diff = (System.currentTimeMillis() - d.time) / 1000
        return when {
            diff < 0 -> dateTime(value)
            diff < 60 -> "Baru saja"
            diff < 3600 -> "${diff / 60} menit lalu"
            diff < 86400 -> "${diff / 3600} jam lalu"
            diff < 86400 * 7 -> "${diff / 86400} hari lalu"
            else -> date(value)
        }
    }

    /** yyyy-MM-dd untuk input form */
    fun isoDate(value: String?): String {
        val d = parse(value) ?: return ""
        val f = SimpleDateFormat("yyyy-MM-dd", Locale.US); f.timeZone = wib
        return f.format(d)
    }

    fun isoDateTimeLocal(value: String?): String {
        val d = parse(value) ?: return ""
        val f = SimpleDateFormat("yyyy-MM-dd'T'HH:mm", Locale.US); f.timeZone = wib
        return f.format(d)
    }

    fun fromMillisDate(millis: Long): String {
        val f = SimpleDateFormat("yyyy-MM-dd", Locale.US); f.timeZone = TimeZone.getTimeZone("UTC")
        return f.format(Date(millis))
    }

    fun toMillisDate(iso: String?): Long? {
        if (iso.isNullOrBlank()) return null
        return try {
            val f = SimpleDateFormat("yyyy-MM-dd", Locale.US); f.timeZone = TimeZone.getTimeZone("UTC")
            f.parse(iso.take(10))?.time
        } catch (_: Exception) {
            null
        }
    }

    fun isPast(value: String?): Boolean = parse(value)?.before(Date()) ?: false

    fun matra(code: String?): String = when (code?.uppercase()) {
        "AD" -> "TNI AD"
        "AL" -> "TNI AL"
        "AU" -> "TNI AU"
        "ALL", null, "" -> "Semua Matra"
        else -> code
    }

    fun matraLong(code: String?): String = when (code?.uppercase()) {
        "AD" -> "Matra Darat"
        "AL" -> "Matra Laut"
        "AU" -> "Matra Udara"
        else -> "Semua Matra"
    }

    fun title(s: String?): String = s.orEmpty().replace('_', ' ').lowercase()
        .split(' ').joinToString(" ") { w -> w.replaceFirstChar { it.titlecase(id) } }

    fun category(c: String?): String = when (c?.lowercase()) {
        "latihan" -> "Latihan"
        "apel" -> "Apel"
        "mobilisasi" -> "Mobilisasi"
        "pengumuman" -> "Pengumuman"
        "tugas" -> "Tugas"
        else -> title(c)
    }

    fun number(n: Int): String = String.format(id, "%,d", n)

    fun initials(name: String?): String {
        val parts = name.orEmpty().trim().split(Regex("\\s+")).filter { it.isNotBlank() && it.first().isLetter() }
        return when {
            parts.isEmpty() -> "KC"
            parts.size == 1 -> parts[0].take(2).uppercase()
            else -> (parts[0].take(1) + parts[1].take(1)).uppercase()
        }
    }

    fun greeting(): String {
        val h = Calendar.getInstance(wib).get(Calendar.HOUR_OF_DAY)
        return when (h) {
            in 4..10 -> "Selamat pagi"
            in 11..14 -> "Selamat siang"
            in 15..17 -> "Selamat sore"
            else -> "Selamat malam"
        }
    }

    fun duration(seconds: Long): String {
        val m = seconds / 60
        val s = seconds % 60
        return String.format(Locale.US, "%02d:%02d", m, s)
    }
}
