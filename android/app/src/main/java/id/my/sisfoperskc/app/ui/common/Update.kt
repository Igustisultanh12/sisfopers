package id.my.sisfoperskc.app.ui.common

import android.content.Context
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.CloudDownload
import androidx.compose.material.icons.outlined.ErrorOutline
import androidx.compose.material.icons.outlined.Refresh
import androidx.compose.material.icons.outlined.SystemUpdate
import androidx.compose.material.icons.outlined.Wifi
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.compose.LocalLifecycleOwner
import androidx.lifecycle.repeatOnLifecycle
import id.my.sisfoperskc.app.BuildConfig
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.SwitchRow
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.theme.Sx
import id.my.sisfoperskc.app.update.AppUpdater
import id.my.sisfoperskc.app.update.ReleaseInfo
import id.my.sisfoperskc.app.update.UpdateState
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

fun fmtBytes(bytes: Long): String = when {
    bytes <= 0 -> "-"
    bytes >= 1024L * 1024 -> String.format(Locale("id"), "%.1f MB", bytes / 1048576.0)
    else -> String.format(Locale("id"), "%.0f KB", bytes / 1024.0)
}

/** Kartu "Pembaruan Aplikasi" di halaman Akun (seperti SISINDEN). */
@Composable
fun UpdateCard(index: Int) {
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val state by AppUpdater.state.collectAsState()
    var auto by remember { mutableStateOf(AppUpdater.isAutoEnabled(ctx)) }
    SxCard(Modifier.fillMaxWidth().appear(index)) {
        SectionTitle("Pembaruan Aplikasi", subtitle = "Versi terpasang ${BuildConfig.VERSION_NAME}")
        Spacer(Modifier.height(12.dp))
        when (val s = state) {
            UpdateState.Idle -> StatusLine(Icons.Outlined.SystemUpdate, Tone.Blue, "Periksa apakah ada versi baru SISFOPERS KC.", lastChecked(ctx))
            UpdateState.Checking -> StatusLine(Icons.Outlined.Refresh, Tone.Blue, "Memeriksa pembaruan…", null, spinning = true)
            is UpdateState.UpToDate -> StatusLine(Icons.Outlined.CheckCircle, Tone.Green, "Aplikasi sudah versi terbaru.", lastChecked(ctx))
            is UpdateState.Available -> ReleaseBox(s.info)
            is UpdateState.Downloading -> ProgressBox("Mengunduh versi ${s.version}…", s.percent)
            is UpdateState.Installing -> StatusLine(Icons.Outlined.SystemUpdate, Tone.Blue, "Memasang versi ${s.version}…", "Ikuti konfirmasi pemasangan dari Android bila muncul.", spinning = true)
            is UpdateState.NeedsInstallPermission -> Notice(
                "Izinkan SISFOPERS KC memasang aplikasi (\"Instal aplikasi tidak dikenal\"), lalu kembali ke sini. Pemasangan akan dilanjutkan otomatis.",
                Tone.Amber, Icons.Outlined.ErrorOutline,
            )
            is UpdateState.Failed -> Notice(s.message, Tone.Red, Icons.Outlined.ErrorOutline)
        }
        Spacer(Modifier.height(12.dp))
        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            when (val s = state) {
                is UpdateState.Available -> PrimaryButton(
                    "Unduh & Pasang", { scope.launch { AppUpdater.downloadAndInstall(ctx, s.info) } },
                    Modifier.weight(1f), icon = Icons.Outlined.CloudDownload, height = 48.dp,
                )
                is UpdateState.NeedsInstallPermission -> PrimaryButton(
                    "Buka Izin Pemasangan", { ctx.startActivity(AppUpdater.installPermissionIntent(ctx)) },
                    Modifier.weight(1f), height = 48.dp,
                )
                is UpdateState.Failed -> {
                    val info = s.info
                    if (info != null) {
                        PrimaryButton("Coba Lagi", { scope.launch { AppUpdater.downloadAndInstall(ctx, info) } }, Modifier.weight(1f), icon = Icons.Outlined.Refresh, height = 48.dp)
                    } else {
                        SoftButton("Periksa Lagi", { scope.launch { AppUpdater.check(ctx) } }, Modifier.weight(1f), icon = Icons.Outlined.Refresh, height = 48.dp)
                    }
                }
                else -> SoftButton(
                    "Periksa Pembaruan", { scope.launch { AppUpdater.check(ctx) } }, Modifier.weight(1f),
                    icon = Icons.Outlined.Refresh, height = 48.dp,
                    loading = s is UpdateState.Checking,
                    enabled = s !is UpdateState.Downloading && s !is UpdateState.Installing,
                )
            }
        }
        Spacer(Modifier.height(12.dp))
        SwitchRow(
            auto, { auto = it; AppUpdater.setAutoEnabled(ctx, it) },
            "Unduh otomatis via Wi-Fi",
            "Pembaruan diunduh di latar saat terhubung Wi-Fi lalu siap dipasang.",
            Icons.Outlined.Wifi,
        )
    }
}

private fun lastChecked(ctx: Context): String? {
    val t = AppUpdater.lastCheck(ctx)
    if (t <= 0) return null
    return "Terakhir diperiksa " + SimpleDateFormat("d MMM yyyy, HH:mm", Locale("id")).format(Date(t))
}

@Suppress("DEPRECATION")
private fun android.content.pm.PackageManager.canRequestPackageInstallsCompat(): Boolean =
    if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) canRequestPackageInstalls() else true

@Composable
private fun StatusLine(icon: androidx.compose.ui.graphics.vector.ImageVector, tone: Tone, title: String, subtitle: String?, spinning: Boolean = false) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Box(contentAlignment = Alignment.Center) {
            IconBox(icon, tint = tone.fg, bg = tone.bg, size = 42.dp, iconSize = 21.dp, radius = 13.dp)
            if (spinning) {
                androidx.compose.material3.CircularProgressIndicator(
                    color = tone.fg, strokeWidth = 2.dp, modifier = Modifier.width(42.dp).height(42.dp),
                )
            }
        }
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.titleSmall)
            if (subtitle != null) Text(subtitle, style = MaterialTheme.typography.bodySmall)
        }
    }
}

@Composable
private fun ReleaseBox(info: ReleaseInfo) {
    Column(
        Modifier.fillMaxWidth().clip(RoundedCornerShape(18.dp)).background(Sx.BlueSoft).padding(14.dp),
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(Icons.Outlined.SystemUpdate, tint = Color.White, bg = Sx.Blue, size = 40.dp, iconSize = 20.dp, radius = 12.dp)
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text("Versi ${info.version} tersedia", style = MaterialTheme.typography.titleSmall, color = Sx.BlueDark)
                Text(
                    "Ukuran ${fmtBytes(info.apkSize)} · ${if (info.source == "server") "server SISFOPERS KC" else "GitHub Release"}",
                    style = MaterialTheme.typography.bodySmall,
                )
            }
        }
        if (info.notes.isNotBlank()) {
            Spacer(Modifier.height(10.dp))
            Text(info.notes, fontSize = 13.sp, lineHeight = 18.sp, color = Sx.Ink2, modifier = Modifier.heightIn(max = 160.dp).verticalScroll(rememberScrollState()))
        }
    }
}

@Composable
private fun ProgressBox(title: String, percent: Int) {
    val p by animateFloatAsState(percent / 100f, tween(300), label = "upd")
    Column {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Text(title, style = MaterialTheme.typography.titleSmall, modifier = Modifier.weight(1f))
            Text("$percent%", fontWeight = FontWeight.Bold, color = Sx.Blue)
        }
        Spacer(Modifier.height(8.dp))
        Box(Modifier.fillMaxWidth().height(10.dp).clip(RoundedCornerShape(99.dp)).background(Sx.LineSoft)) {
            Box(Modifier.fillMaxWidth(p.coerceIn(0.02f, 1f)).fillMaxHeight().clip(RoundedCornerShape(99.dp)).background(Sx.HeroGradient))
        }
    }
}

/** Dialog global saat ada versi baru (sekali per versi, bisa "Nanti") + progres unduhan. */
@Composable
fun UpdatePrompt() {
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val state by AppUpdater.state.collectAsState()
    var hiddenFor by remember { mutableStateOf<String?>(null) }
    var followProgress by remember { mutableStateOf(false) }
    var resumeTick by remember { mutableIntStateOf(0) }
    val lifecycle = LocalLifecycleOwner.current
    LaunchedEffect(Unit) { AppUpdater.autoCheckOnOpen(ctx) }
    LaunchedEffect(Unit) { lifecycle.repeatOnLifecycle(Lifecycle.State.RESUMED) { resumeTick++ } }

    // Kembali dari layar izin "Instal aplikasi tidak dikenal" → lanjutkan otomatis.
    LaunchedEffect(resumeTick) {
        val cur = AppUpdater.state.value
        if (cur is UpdateState.NeedsInstallPermission && ctx.packageManager.canRequestPackageInstallsCompat()) {
            AppUpdater.downloadAndInstall(ctx, cur.info)
        }
    }

    when (val s = state) {
        is UpdateState.Available -> {
            val info = s.info
            if (hiddenFor != info.version && !AppUpdater.isPromptDismissed(ctx, info.version)) {
                SxDialog(
                    onDismiss = { hiddenFor = info.version; AppUpdater.dismissPrompt(ctx, info.version) },
                    title = "Pembaruan Tersedia",
                    message = "SISFOPERS KC versi ${info.version} sudah tersedia (terpasang ${BuildConfig.VERSION_NAME}).",
                    icon = Icons.Outlined.SystemUpdate,
                    confirmText = "Perbarui",
                    dismissText = "Nanti",
                    onConfirm = {
                        hiddenFor = info.version
                        followProgress = true
                        scope.launch { AppUpdater.downloadAndInstall(ctx, info) }
                    },
                ) { ReleaseBox(info) }
            }
        }
        is UpdateState.Downloading -> if (followProgress) {
            SxDialog(
                onDismiss = { followProgress = false },
                title = "Mengunduh Pembaruan",
                message = "Anda tetap dapat memakai aplikasi. Pemasangan dimulai otomatis setelah unduhan selesai.",
                icon = Icons.Outlined.CloudDownload,
                dismissText = "Sembunyikan",
            ) { ProgressBox("Versi ${s.version}", s.percent) }
        }
        is UpdateState.NeedsInstallPermission -> SxDialog(
            onDismiss = { AppUpdater.setState(UpdateState.Available(s.info)); hiddenFor = s.info.version },
            title = "Izinkan Pemasangan",
            message = "Android meminta izin agar SISFOPERS KC dapat memasang pembaruan. Aktifkan \"Izinkan dari sumber ini\", lalu kembali ke aplikasi — pemasangan dilanjutkan otomatis.",
            icon = Icons.Outlined.ErrorOutline,
            tone = Tone.Amber,
            confirmText = "Buka Pengaturan",
            onConfirm = { ctx.startActivity(AppUpdater.installPermissionIntent(ctx)) },
        )
        is UpdateState.Failed -> if (followProgress) {
            SxDialog(
                onDismiss = { followProgress = false; AppUpdater.resetIfFinished() },
                title = "Pembaruan Gagal",
                message = s.message,
                icon = Icons.Outlined.ErrorOutline,
                tone = Tone.Red,
                dismissText = "Tutup",
                confirmText = "Coba Lagi",
                onConfirm = s.info?.let { info ->
                    val retry: () -> Unit = { scope.launch { AppUpdater.downloadAndInstall(ctx, info) } }
                    retry
                },
            )
        }
        else -> Unit
    }
}
