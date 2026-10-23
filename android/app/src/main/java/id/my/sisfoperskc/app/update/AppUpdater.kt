package id.my.sisfoperskc.app.update

import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.pm.PackageInfo
import android.content.pm.PackageInstaller
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.work.Constraints
import androidx.work.CoroutineWorker
import androidx.work.ExistingPeriodicWorkPolicy
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.PeriodicWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters
import id.my.sisfoperskc.app.BuildConfig
import id.my.sisfoperskc.app.MainActivity
import id.my.sisfoperskc.app.R
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.long
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.parseJson
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.push.AppVisibility
import id.my.sisfoperskc.app.push.Notifier
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonObject
import okhttp3.Request
import java.io.File
import java.io.IOException
import java.security.MessageDigest
import java.util.concurrent.TimeUnit

/** Informasi versi terbaru (dari server SISFOPERS KC atau GitHub Release). */
data class ReleaseInfo(
    val version: String,
    val versionCode: Int,
    val apkUrl: String,
    val apkName: String,
    val apkSize: Long,
    val sha256: String?,
    val notes: String,
    val source: String,
)

sealed interface UpdateState {
    data object Idle : UpdateState
    data object Checking : UpdateState
    data class UpToDate(val version: String) : UpdateState
    data class Available(val info: ReleaseInfo) : UpdateState
    data class Downloading(val version: String, val percent: Int) : UpdateState
    data class Installing(val version: String) : UpdateState
    data class NeedsInstallPermission(val info: ReleaseInfo) : UpdateState
    data class Failed(val message: String, val info: ReleaseInfo? = null) : UpdateState
}

/**
 * Pembaruan aplikasi dari dalam aplikasi (seperti SISINDEN).
 *
 * Sumber utama: APK yang diunggah Admin di Pengaturan Sistem (/api/m/app/update).
 * Cadangan: GitHub Release terbaru (tag android-v*). APK diverifikasi SHA-256
 * dan tanda tangannya harus sama dengan aplikasi terpasang, lalu dipasang lewat
 * PackageInstaller.
 */
object AppUpdater {
    private const val PREFS = "sisfopers_update"
    private const val K_AUTO = "auto_update"
    private const val K_LAST_CHECK = "last_check"
    private const val K_NOTIFIED = "notified_version"
    private const val K_DISMISSED = "dismissed_version"
    private const val GITHUB_RELEASES = "https://api.github.com/repos/Igustisultanh12/sisfopers/releases?per_page=15"
    const val NOTIFICATION_ID = 0x5F0A
    private const val WORK_NAME = "sisfopers_app_update"
    private const val WORK_NOW = "sisfopers_app_update_now"

    private val _state = MutableStateFlow<UpdateState>(UpdateState.Idle)
    val state: StateFlow<UpdateState> = _state.asStateFlow()

    private val http by lazy {
        Api.client.newBuilder()
            .readTimeout(120, TimeUnit.SECONDS)
            .followRedirects(true)
            .followSslRedirects(true)
            .build()
    }

    private fun prefs(context: Context) = context.applicationContext.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    fun isAutoEnabled(context: Context) = prefs(context).getBoolean(K_AUTO, true)
    fun setAutoEnabled(context: Context, enabled: Boolean) {
        prefs(context).edit().putBoolean(K_AUTO, enabled).apply()
    }

    fun dismissPrompt(context: Context, version: String) = prefs(context).edit().putString(K_DISMISSED, version).apply()
    fun isPromptDismissed(context: Context, version: String) = prefs(context).getString(K_DISMISSED, null) == version
    fun lastCheck(context: Context): Long = prefs(context).getLong(K_LAST_CHECK, 0L)

    /** Bandingkan "1.10.0" vs "1.9.2" secara numerik; awalan "v" & akhiran "-dev" diabaikan. */
    fun compareVersions(a: String, b: String): Int {
        fun parts(v: String) = v.trim().removePrefix("v").substringBefore('-').split('.').map { it.toIntOrNull() ?: 0 }
        val x = parts(a)
        val y = parts(b)
        for (i in 0 until maxOf(x.size, y.size)) {
            val c = x.getOrElse(i) { 0 }.compareTo(y.getOrElse(i) { 0 })
            if (c != 0) return c
        }
        return 0
    }

    val currentVersion: String get() = BuildConfig.VERSION_NAME.substringBefore('-')

    // ------------------------------------------------------------------ sumber

    private fun fetchJson(url: String): String? {
        val req = Request.Builder().url(url).header("Accept", "application/json").build()
        http.newCall(req).execute().use { res ->
            if (res.code == 429) throw UpdateException("Terlalu sering memeriksa. Coba lagi sebentar lagi.")
            if (!res.isSuccessful) return null
            return res.body?.string()
        }
    }

    private fun fromServer(): ReleaseInfo? {
        val base = Session.baseUrl.trimEnd('/')
        val obj = parseJson(fetchJson("$base/api/m/app/update") ?: return null) as? JsonObject ?: return null
        if (!obj.bool("available")) return null
        val version = obj.str("version").trim()
        if (!Regex("^\\d+(\\.\\d+){1,3}$").matches(version)) return null
        val sha = obj.str("sha256").lowercase().takeIf { Regex("^[0-9a-f]{64}$").matches(it) } ?: return null
        val raw = obj.str("download_url")
        val url = when {
            raw.startsWith("$base/") -> raw
            raw.startsWith("/") -> base + raw
            else -> "$base/api/m/app/update/download?v=$version"
        }
        return ReleaseInfo(version, obj.int("version_code"), url, "SISFOPERSKC-$version.apk", obj.long("size"), sha, obj.str("notes"), "server")
    }

    private fun fromGitHub(): ReleaseInfo? {
        val text = fetchJson(GITHUB_RELEASES) ?: return null
        val list = parseJson(text) as? JsonArray ?: return null
        for (el in list) {
            val r = el as? JsonObject ?: continue
            if (r.bool("draft") || r.bool("prerelease")) continue
            val tag = r.str("tag_name")
            if (!tag.startsWith("android-v")) continue
            val version = tag.removePrefix("android-v").trim()
            if (!Regex("^\\d+(\\.\\d+){1,3}$").matches(version)) continue
            val asset = r.objects("assets").firstOrNull { it.str("name").endsWith(".apk", true) } ?: continue
            val digest = asset.strOrNull("digest")?.removePrefix("sha256:")?.lowercase()?.takeIf { Regex("^[0-9a-f]{64}$").matches(it) }
            return ReleaseInfo(
                version = version,
                versionCode = 0,
                apkUrl = asset.str("browser_download_url"),
                apkName = "SISFOPERSKC-$version.apk",
                apkSize = asset.long("size"),
                sha256 = digest,
                notes = r.str("body").lines().filter { it.isNotBlank() }.take(8).joinToString("\n").take(600),
                source = "github",
            )
        }
        return null
    }

    /** Versi terbaru dari kedua sumber (yang tertinggi menang, server diutamakan bila sama). */
    suspend fun fetchLatest(): ReleaseInfo? = withContext(Dispatchers.IO) {
        val server = try { fromServer() } catch (e: UpdateException) { throw e } catch (_: Exception) { null }
        val github = try { fromGitHub() } catch (_: Exception) { null }
        listOfNotNull(server, github).maxWithOrNull { a, b ->
            val c = compareVersions(a.version, b.version)
            if (c != 0) c else if (a.source == "server") 1 else -1
        }
    }

    private fun isNewer(info: ReleaseInfo?) = info != null && compareVersions(info.version, currentVersion) > 0

    private fun isBusy() = _state.value.let { it is UpdateState.Checking || it is UpdateState.Downloading || it is UpdateState.Installing }

    // ------------------------------------------------------------------ alur

    suspend fun check(context: Context): UpdateState {
        if (isBusy()) return _state.value
        _state.value = UpdateState.Checking
        val result = try {
            val latest = fetchLatest()
            prefs(context).edit().putLong(K_LAST_CHECK, System.currentTimeMillis()).apply()
            if (isNewer(latest)) UpdateState.Available(latest!!) else UpdateState.UpToDate(currentVersion)
        } catch (e: UpdateException) {
            UpdateState.Failed(e.message ?: "Pemeriksaan gagal.")
        } catch (e: kotlinx.coroutines.CancellationException) {
            throw e
        } catch (e: Exception) {
            UpdateState.Failed("Tidak dapat memeriksa pembaruan: ${reason(e)}")
        }
        _state.value = result
        return result
    }

    /** Cek saat aplikasi dibuka (paling sering tiap 30 menit). */
    suspend fun autoCheckOnOpen(context: Context) {
        if (BuildConfig.DEBUG) return
        if (System.currentTimeMillis() - lastCheck(context) < 30 * 60_000L && _state.value !is UpdateState.Available) return
        val r = check(context)
        if (r is UpdateState.UpToDate) _state.value = UpdateState.Idle
    }

    suspend fun downloadAndInstall(context: Context, info: ReleaseInfo): UpdateState {
        val app = context.applicationContext
        if (_state.value is UpdateState.Downloading || _state.value is UpdateState.Installing) return _state.value
        _state.value = UpdateState.Downloading(info.version, 0)
        val result = try {
            val apk = try {
                download(app, info)
            } catch (e: UpdateException) {
                throw e
            } catch (e: kotlinx.coroutines.CancellationException) {
                throw e
            } catch (e: Exception) {
                throw UpdateException("Unduhan v${info.version} gagal: ${reason(e)}")
            }
            verify(app, apk, info)
            when {
                AppVisibility.inCall -> UpdateState.Failed("Pembaruan ditunda karena sedang dalam panggilan.", info)
                Build.VERSION.SDK_INT >= Build.VERSION_CODES.O && !app.packageManager.canRequestPackageInstalls() ->
                    UpdateState.NeedsInstallPermission(info)
                else -> {
                    try {
                        install(app, apk, info.version)
                    } catch (e: Exception) {
                        throw UpdateException("Pemasangan v${info.version} gagal: ${reason(e)}")
                    }
                    UpdateState.Installing(info.version)
                }
            }
        } catch (e: UpdateException) {
            UpdateState.Failed(e.message ?: "Pembaruan gagal.", info)
        } catch (e: kotlinx.coroutines.CancellationException) {
            _state.value = UpdateState.Available(info)
            throw e
        } catch (e: Exception) {
            UpdateState.Failed("Pembaruan gagal: ${reason(e)}", info)
        }
        _state.value = result
        if (result is UpdateState.NeedsInstallPermission && !AppVisibility.foreground) {
            notify(app, "Pembaruan SISFOPERS KC ${info.version} siap dipasang", "Buka aplikasi untuk mengizinkan pemasangan pembaruan.", openIntent(app))
        }
        return result
    }

    suspend fun backgroundCheck(context: Context, allowDownload: Boolean): UpdateState {
        val result = check(context)
        if (result !is UpdateState.Available) {
            if (result is UpdateState.UpToDate) _state.value = UpdateState.Idle
            return result
        }
        return if (allowDownload && isAutoEnabled(context)) downloadAndInstall(context, result.info)
        else {
            notifyAvailable(context, result.info)
            result
        }
    }

    fun notifyAvailable(context: Context, info: ReleaseInfo, force: Boolean = false) {
        val p = prefs(context)
        if (!force && p.getString(K_NOTIFIED, null) == info.version) return
        p.edit().putString(K_NOTIFIED, info.version).apply()
        notify(context, "Pembaruan SISFOPERS KC ${info.version} tersedia", info.notes.ifBlank { "Ketuk untuk mengunduh dan memasang versi terbaru." }, openIntent(context))
    }

    /** Push "app_update" dari server (Admin baru mengunggah APK). */
    fun onServerPush(context: Context, version: String, title: String, body: String) {
        val app = context.applicationContext
        if (version.isNotBlank() && compareVersions(version, currentVersion) <= 0) return
        prefs(app).edit().putString(K_NOTIFIED, version).apply()
        notify(app, title.ifBlank { "Pembaruan SISFOPERS KC $version tersedia" }, body.ifBlank { "Ketuk untuk mengunduh dan memasang versi terbaru." }, openIntent(app))
        if (isAutoEnabled(app) && !BuildConfig.DEBUG) {
            val req = OneTimeWorkRequestBuilder<UpdateWorker>()
                .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.UNMETERED).build())
                .build()
            WorkManager.getInstance(app).enqueueUniqueWork(WORK_NOW, ExistingWorkPolicy.REPLACE, req)
        }
    }

    internal fun reason(e: Throwable): String = when (e) {
        is java.net.UnknownHostException -> "tidak ada koneksi internet"
        is java.net.SocketTimeoutException -> "koneksi terlalu lama"
        is javax.net.ssl.SSLException -> "koneksi aman (HTTPS) gagal — periksa tanggal & jam HP"
        is SecurityException -> "izin pemasangan ditolak Android"
        is IOException -> e.message?.takeIf { it.isNotBlank() } ?: "gangguan jaringan"
        else -> e.javaClass.simpleName + (e.message?.let { ": $it" } ?: "")
    }

    fun installPermissionIntent(context: Context): Intent =
        Intent(android.provider.Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES, Uri.parse("package:${context.packageName}"))
            .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)

    class UpdateException(message: String) : Exception(message)

    private fun openIntent(context: Context): PendingIntent = PendingIntent.getActivity(
        context, NOTIFICATION_ID,
        Intent(context, MainActivity::class.java)
            .putExtra(Notifier.EXTRA_URL, "/account/settings")
            .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP),
        PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
    )

    private suspend fun download(context: Context, info: ReleaseInfo): File = withContext(Dispatchers.IO) {
        val dir = File(context.cacheDir, "updates").apply { mkdirs() }
        dir.listFiles()?.filter { !it.name.startsWith(info.apkName) }?.forEach { it.delete() }
        val target = File(dir, info.apkName)
        if (target.exists() && info.sha256 != null && sha256(target) == info.sha256) return@withContext target
        val part = File(dir, info.apkName + ".part")
        val req = Request.Builder().url(info.apkUrl)
            .header("Accept", "application/vnd.android.package-archive,application/octet-stream,*/*")
            .build()
        http.newCall(req).execute().use { res ->
            if (res.code == 404) throw UpdateException("APK pembaruan sudah tidak ada di server. Periksa ulang beberapa saat lagi.")
            if (!res.isSuccessful) throw UpdateException("Unduhan pembaruan gagal (HTTP ${res.code}).")
            val body = res.body ?: throw UpdateException("Berkas pembaruan kosong.")
            val total = body.contentLength().takeIf { it > 0 } ?: info.apkSize
            var done = 0L
            var last = -1
            body.byteStream().use { input ->
                part.outputStream().use { out ->
                    val buf = ByteArray(64 * 1024)
                    while (true) {
                        val n = input.read(buf)
                        if (n < 0) break
                        out.write(buf, 0, n)
                        done += n
                        val pct = if (total > 0) ((done * 100) / total).toInt().coerceIn(0, 100) else 0
                        if (pct != last) {
                            last = pct
                            _state.value = UpdateState.Downloading(info.version, pct)
                        }
                    }
                }
            }
        }
        if (target.exists()) target.delete()
        if (!part.renameTo(target)) throw UpdateException("Berkas pembaruan tidak dapat disimpan.")
        target
    }

    private suspend fun verify(context: Context, apk: File, info: ReleaseInfo) = withContext(Dispatchers.IO) {
        if (info.sha256 != null && sha256(apk) != info.sha256) {
            apk.delete()
            throw UpdateException("Berkas pembaruan rusak (SHA-256 tidak cocok). Coba unduh lagi.")
        }
        val pm = context.packageManager
        val archive = archiveInfo(pm, apk.absolutePath) ?: throw UpdateException("Berkas pembaruan bukan APK yang valid.")
        if (archive.packageName != context.packageName) throw UpdateException("Berkas pembaruan bukan untuk aplikasi ini.")
        val installed = installedInfo(pm, context.packageName)
        if (installed != null && signerDigests(archive).intersect(signerDigests(installed)).isEmpty()) {
            apk.delete()
            throw UpdateException(
                "APK v${info.version} ditandatangani kunci yang berbeda dengan aplikasi terpasang sehingga tidak dapat dipasang sebagai pembaruan. Hapus aplikasi lama sekali, lalu pasang APK v${info.version}.",
            )
        }
    }

    private fun sha256(file: File): String {
        val md = MessageDigest.getInstance("SHA-256")
        file.inputStream().use { input ->
            val buf = ByteArray(64 * 1024)
            while (true) {
                val n = input.read(buf)
                if (n < 0) break
                md.update(buf, 0, n)
            }
        }
        return md.digest().joinToString("") { "%02x".format(it) }
    }

    @Suppress("DEPRECATION")
    private fun archiveInfo(pm: PackageManager, path: String): PackageInfo? {
        val flags = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) PackageManager.GET_SIGNING_CERTIFICATES else PackageManager.GET_SIGNATURES
        return pm.getPackageArchiveInfo(path, flags)
    }

    @Suppress("DEPRECATION")
    private fun installedInfo(pm: PackageManager, pkg: String): PackageInfo? = try {
        val flags = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) PackageManager.GET_SIGNING_CERTIFICATES else PackageManager.GET_SIGNATURES
        pm.getPackageInfo(pkg, flags)
    } catch (_: PackageManager.NameNotFoundException) {
        null
    }

    @Suppress("DEPRECATION")
    private fun signerDigests(info: PackageInfo): Set<String> {
        val sigs = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
            val si = info.signingInfo ?: return emptySet()
            if (si.hasMultipleSigners()) si.apkContentsSigners else si.signingCertificateHistory
        } else {
            info.signatures
        }
        return sigs.orEmpty().map { sig ->
            MessageDigest.getInstance("SHA-256").digest(sig.toByteArray()).joinToString("") { "%02x".format(it) }
        }.toSet()
    }

    private suspend fun install(context: Context, apk: File, version: String) = withContext(Dispatchers.IO) {
        val installer = context.packageManager.packageInstaller
        val params = PackageInstaller.SessionParams(PackageInstaller.SessionParams.MODE_FULL_INSTALL).apply {
            setAppPackageName(context.packageName)
            setSize(apk.length())
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                setRequireUserAction(PackageInstaller.SessionParams.USER_ACTION_NOT_REQUIRED)
            }
        }
        val sessionId = installer.createSession(params)
        installer.openSession(sessionId).use { session ->
            apk.inputStream().use { input ->
                session.openWrite("base.apk", 0, apk.length()).use { out ->
                    input.copyTo(out, 64 * 1024)
                    session.fsync(out)
                }
            }
            val intent = Intent(context, UpdateInstallReceiver::class.java).putExtra(UpdateInstallReceiver.EXTRA_VERSION, version)
            val flags = PendingIntent.FLAG_UPDATE_CURRENT or (if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) PendingIntent.FLAG_MUTABLE else 0)
            val pi = PendingIntent.getBroadcast(context, sessionId, intent, flags)
            session.commit(pi.intentSender)
        }
    }

    internal fun setState(value: UpdateState) {
        _state.value = value
    }

    fun resetIfFinished() {
        val s = _state.value
        if (s is UpdateState.Failed || s is UpdateState.UpToDate) _state.value = UpdateState.Idle
    }

    internal fun notify(context: Context, title: String, text: String, intent: PendingIntent?) {
        if (!Notifier.canNotify(context)) return
        val n = NotificationCompat.Builder(context, Notifier.CH_GENERAL)
            .setSmallIcon(R.drawable.ic_notification)
            .setColor(0xFF2563EB.toInt())
            .setContentTitle(title)
            .setContentText(text)
            .setStyle(NotificationCompat.BigTextStyle().bigText(text))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .apply { if (intent != null) setContentIntent(intent) }
            .build()
        try {
            NotificationManagerCompat.from(context).notify(NOTIFICATION_ID, n)
        } catch (_: SecurityException) {
        }
    }

    /** Pemeriksaan berkala tiap ±6 jam (unduhan otomatis hanya lewat Wi-Fi). */
    fun schedule(context: Context) {
        if (BuildConfig.DEBUG) return
        val req = PeriodicWorkRequestBuilder<UpdateWorker>(6, TimeUnit.HOURS)
            .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build())
            .build()
        WorkManager.getInstance(context).enqueueUniquePeriodicWork(WORK_NAME, ExistingPeriodicWorkPolicy.KEEP, req)
    }

    fun isUnmetered(context: Context): Boolean {
        val cm = context.getSystemService(android.net.ConnectivityManager::class.java) ?: return false
        return !cm.isActiveNetworkMetered
    }

    fun onPackageReplaced(context: Context) {
        File(context.cacheDir, "updates").listFiles()?.forEach { it.delete() }
        _state.value = UpdateState.Idle
        notify(context, "SISFOPERS KC diperbarui", "Versi ${BuildConfig.VERSION_NAME} sudah terpasang. Ketuk untuk membuka.", openIntent(context))
    }
}

class UpdateWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {
    override suspend fun doWork(): Result {
        AppUpdater.backgroundCheck(applicationContext, allowDownload = AppUpdater.isUnmetered(applicationContext))
        return Result.success()
    }
}

/** Hasil sesi PackageInstaller. */
class UpdateInstallReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        val version = intent.getStringExtra(EXTRA_VERSION).orEmpty()
        when (val status = intent.getIntExtra(PackageInstaller.EXTRA_STATUS, PackageInstaller.STATUS_FAILURE)) {
            PackageInstaller.STATUS_PENDING_USER_ACTION -> {
                @Suppress("DEPRECATION")
                val confirm: Intent = (if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                    intent.getParcelableExtra(Intent.EXTRA_INTENT, Intent::class.java)
                } else {
                    intent.getParcelableExtra(Intent.EXTRA_INTENT)
                }) ?: return
                confirm.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                if (AppVisibility.foreground) {
                    try {
                        context.startActivity(confirm)
                        return
                    } catch (_: Exception) {
                    }
                }
                val pi = PendingIntent.getActivity(context, AppUpdater.NOTIFICATION_ID, confirm, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
                AppUpdater.notify(context, "Pembaruan SISFOPERS KC $version siap", "Ketuk untuk memasang pembaruan.", pi)
            }
            PackageInstaller.STATUS_SUCCESS -> AppUpdater.setState(UpdateState.UpToDate(version))
            else -> {
                val msg = intent.getStringExtra(PackageInstaller.EXTRA_STATUS_MESSAGE).orEmpty()
                val text = when (status) {
                    PackageInstaller.STATUS_FAILURE_ABORTED -> "Pemasangan dibatalkan."
                    PackageInstaller.STATUS_FAILURE_CONFLICT -> "Pembaruan bentrok dengan aplikasi terpasang (tanda tangan berbeda). Hapus aplikasi sekali, lalu pasang versi $version."
                    PackageInstaller.STATUS_FAILURE_STORAGE -> "Ruang penyimpanan HP tidak cukup."
                    else -> "Pemasangan gagal${if (msg.isNotBlank()) ": $msg" else "."}"
                }
                AppUpdater.setState(UpdateState.Failed(text))
                if (!AppVisibility.foreground && status != PackageInstaller.STATUS_FAILURE_ABORTED) {
                    AppUpdater.notify(context, "Pembaruan SISFOPERS KC gagal", text, null)
                }
            }
        }
    }

    companion object {
        const val EXTRA_VERSION = "version"
    }
}

/** Dipanggil Android setelah aplikasi berhasil diperbarui. */
class PackageReplacedReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Intent.ACTION_MY_PACKAGE_REPLACED) AppUpdater.onPackageReplaced(context)
    }
}
