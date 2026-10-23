package id.my.sisfoperskc.app.ui.common

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import android.provider.Settings
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ArrowForward
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.CameraAlt
import androidx.compose.material.icons.outlined.Cake
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.DoneAll
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Map
import androidx.compose.material.icons.outlined.Mic
import androidx.compose.material.icons.outlined.MilitaryTech
import androidx.compose.material.icons.outlined.Notifications
import androidx.compose.material.icons.outlined.NotificationsActive
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Phone
import androidx.compose.material.icons.outlined.QrCode2
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Security
import androidx.compose.material.icons.outlined.Shield
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material.icons.outlined.Work
import androidx.compose.material3.Icon
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
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalClipboardManager
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.AnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.core.app.NotificationManagerCompat
import androidx.core.content.ContextCompat
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.compose.LocalLifecycleOwner
import androidx.lifecycle.repeatOnLifecycle
import id.my.sisfoperskc.app.BuildConfig
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Auth
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.push.PushManager
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.AppLogo
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.CircleIconButton
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.RemoteImage
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.AppState
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.nav.go
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

fun notifIcon(icon: String?): Pair<ImageVector, Tone> = when (icon?.lowercase()) {
    "megaphone", "broadcast", "calendar" -> Icons.Outlined.Campaign to Tone.Blue
    "user", "user-plus", "users" -> Icons.Outlined.Person to Tone.Purple
    "check", "check-circle", "success" -> Icons.Outlined.CheckCircle to Tone.Green
    "job", "briefcase" -> Icons.Outlined.Work to Tone.Amber
    "education", "school", "academic" -> Icons.Outlined.School to Tone.Purple
    "form", "document", "file" -> Icons.Outlined.EditNote to Tone.Green
    "chat", "message" -> Icons.Outlined.SupportAgent to Tone.Cyan
    "shield", "security", "lock" -> Icons.Outlined.Shield to Tone.Red
    else -> Icons.Outlined.Notifications to Tone.Blue
}

@Composable
fun NotificationsScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val state = rememberPaged("/notifications", "notifications")
    val me by Session.me.collectAsState()
    ShellScaffold(
        title = "Notifikasi",
        subtitle = "Pemberitahuan & instruksi terbaru",
        topLevel = true,
        actions = {
            CircleIconButton(Icons.Outlined.DoneAll, {
                scope.launch {
                    val r = Api.send("/notifications/read-all")
                    if (Ui.report(r, "Semua notifikasi ditandai sudah dibaca.")) {
                        AppState.unread.value = 0
                        state.reload(silent = true)
                    }
                }
            }, contentDescription = "Tandai semua dibaca")
        },
    ) { inner ->
        PagedList(
            state,
            Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = null,
            emptyTitle = "Belum ada notifikasi",
            emptyMessage = "Pemberitahuan baru akan muncul di sini dan sebagai notifikasi ponsel.",
            itemSpacing = 10.dp,
        ) { n, _ ->
            val data = n.obj("data")
            val unread = n.strOrNull("read_at") == null
            val (icon, tone) = notifIcon(data.strOrNull("icon"))
            SxCard(
                Modifier.fillMaxWidth(),
                color = if (unread) Sx.BlueSoft.copy(alpha = 0.6f) else Sx.Card,
                border = if (unread) Sx.BlueLine else Sx.Line,
                padding = PaddingValues(14.dp),
                onClick = {
                    scope.launch {
                        if (unread) {
                            Api.send("/notifications/${n.str("id")}/read")
                            AppState.unread.value = (AppState.unread.value - 1).coerceAtLeast(0)
                            state.replace({ it.str("id") == n.str("id") }, JsonObject(n + ("read_at" to kotlinx.serialization.json.JsonPrimitive("now"))))
                        }
                        val target = Rt.fromWebUrl(data.strOrNull("url"), me)
                        if (target != Rt.NOTIF) nav.go(target)
                    }
                },
            ) {
                Row(verticalAlignment = Alignment.Top) {
                    IconBox(icon, tint = tone.fg, bg = tone.bg, size = 42.dp, iconSize = 21.dp)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text(data.str("title", "Pemberitahuan"), style = MaterialTheme.typography.titleSmall.copy(fontSize = 14.sp), modifier = Modifier.weight(1f), maxLines = 2, overflow = TextOverflow.Ellipsis)
                            if (unread) Box(Modifier.size(9.dp).clip(CircleShape).background(Sx.Blue))
                        }
                        Spacer(Modifier.height(2.dp))
                        Text(data.str("message"), style = MaterialTheme.typography.bodyMedium.copy(fontSize = 13.sp))
                        Spacer(Modifier.height(6.dp))
                        Text(Fmt.relative(n.str("created_at")), fontSize = 11.sp, color = Sx.Faint)
                    }
                }
            }
        }
    }
}

@Composable
fun AccountScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val clip = LocalClipboardManager.current
    val loader = rememberPage("/account/settings")
    var editing by remember { mutableStateOf(false) }
    var pwd by remember { mutableStateOf(false) }
    var mfaSetup by remember { mutableStateOf<JsonObject?>(null) }
    var mfaCode by remember { mutableStateOf("") }
    var mfaBusy by remember { mutableStateOf(false) }
    var permTick by remember { mutableIntStateOf(0) }
    val lifecycle = LocalLifecycleOwner.current
    LaunchedEffect(Unit) { lifecycle.repeatOnLifecycle(Lifecycle.State.RESUMED) { permTick++ } }
    val me by Session.me.collectAsState()

    ShellScaffold(title = "Akun Saya", subtitle = me?.roleLabel, topLevel = true) { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            val user = data.obj("user")
            val p = data.objOrNull("personel")
            val name = p?.str("full_name") ?: me?.fullName ?: user.str("username")
            // Profil
            Box(Modifier.fillMaxWidth().appear(0).clip(RoundedCornerShape(28.dp)).background(Sx.HeroGradient)) {
                id.my.sisfoperskc.app.ui.auth.AnimatedBlobs()
                Column(Modifier.fillMaxWidth().padding(22.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                    Avatar(user.strOrNull("avatar") ?: p?.strOrNull("photo_profile"), name, 92.dp, ring = Color.White)
                    Spacer(Modifier.height(12.dp))
                    Text(name, color = Color.White, fontSize = 20.sp, fontWeight = FontWeight.ExtraBold, textAlign = TextAlign.Center)
                    Text(me?.roleLabel ?: data.str("role_name"), color = Color.White.copy(0.8f), fontSize = 13.sp)
                    Spacer(Modifier.height(10.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        if (p != null) MatraBadge(p.str("matra"))
                        Badge("@${user.str("username")}", Tone.Gray)
                    }
                }
            }
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp), modifier = Modifier.appear(1)) {
                SoftButton("Ubah Profil", { editing = true }, Modifier.weight(1f), icon = Icons.Outlined.Person)
                SoftButton("Kata Sandi", { pwd = true }, Modifier.weight(1f), icon = Icons.Outlined.Lock, tone = Tone.Purple)
            }

            SxCard(Modifier.fillMaxWidth().appear(2)) {
                SectionTitle("Informasi Akun")
                Spacer(Modifier.height(6.dp))
                InfoRow("Username", user.str("username"), icon = Icons.Outlined.Person)
                InfoRow("Email", user.str("email"), icon = Icons.Outlined.Email)
                InfoRow("Nomor HP", user.strOrNull("phone_number") ?: p?.strOrNull("phone_number"), icon = Icons.Outlined.Phone)
                InfoRow("Terdaftar", user.str("created_at"), icon = Icons.Outlined.Info)
            }

            if (p != null) {
                SxCard(Modifier.fillMaxWidth().appear(3)) {
                    SectionTitle("Data Personel")
                    Spacer(Modifier.height(6.dp))
                    InfoRow("NIKC", p.str("nikc"), icon = Icons.Outlined.Badge)
                    InfoRow("Pangkat", me?.pangkatLabel ?: p.str("pangkat"), icon = Icons.Outlined.MilitaryTech)
                    InfoRow("Matra / Angkatan", "${Fmt.matra(p.str("matra"))} / ${p.str("angkatan")}", icon = Icons.Outlined.Shield)
                    InfoRow("Tempat, Tgl Lahir", "${p.str("pob")}, ${Fmt.dateLong(p.str("dob"))}", icon = Icons.Outlined.Cake)
                    InfoRow("Kotama", p.strOrNull("kotama"), icon = Icons.Outlined.Map)
                    InfoRow("Satuan Kewilayahan", p.strOrNull("satuan_kewilayahan"), icon = Icons.Outlined.Map)
                    InfoRow("Alamat", listOfNotNull(p.strOrNull("address"), p.strOrNull("city"), p.strOrNull("province")).joinToString(", "), icon = Icons.Outlined.Home)
                    InfoRow("Status Profil", p.str("status_profile"), icon = Icons.Outlined.VerifiedUser)
                }
            }

            // Keamanan
            SxCard(Modifier.fillMaxWidth().appear(4)) {
                SectionTitle("Keamanan", subtitle = "Autentikasi dua langkah (Google Authenticator)")
                Spacer(Modifier.height(10.dp))
                val enabled = user.bool("google2fa_enabled")
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.Security, if (enabled) Sx.Green else Sx.Muted)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(if (enabled) "2FA aktif" else "2FA belum aktif", style = MaterialTheme.typography.titleSmall)
                        Text(if (enabled) "Akun Anda terlindungi kode OTP aplikasi autentikator." else "Aktifkan untuk perlindungan ekstra.", style = MaterialTheme.typography.bodySmall)
                    }
                    SoftButton(if (enabled) "Nonaktifkan" else "Aktifkan", {
                        scope.launch {
                            mfaBusy = true
                            val r = Api.send("/account/settings/mfa/toggle")
                            mfaBusy = false
                            if (r is ApiResult.Ok) {
                                val setup = r.data.obj("flash").objOrNull("mfa_setup")
                                if (setup != null) mfaSetup = setup else { Ui.report(r); loader.refresh() }
                            } else Ui.report(r)
                        }
                    }, tone = if (enabled) Tone.Red else Tone.Green, loading = mfaBusy)
                }
            }

            // Notifikasi & izin perangkat
            SxCard(Modifier.fillMaxWidth().appear(5)) {
                SectionTitle("Notifikasi & Izin Perangkat")
                Spacer(Modifier.height(8.dp))
                val tick = permTick
                val notifOk = remember(tick) { NotificationManagerCompat.from(ctx).areNotificationsEnabled() }
                val camOk = remember(tick) { ContextCompat.checkSelfPermission(ctx, Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED }
                val micOk = remember(tick) { ContextCompat.checkSelfPermission(ctx, Manifest.permission.RECORD_AUDIO) == PackageManager.PERMISSION_GRANTED }
                PermRow(Icons.Outlined.NotificationsActive, "Notifikasi", if (notifOk) "Diizinkan" else "Dimatikan", notifOk) {
                    val i = if (Build.VERSION.SDK_INT >= 26) Intent(Settings.ACTION_APP_NOTIFICATION_SETTINGS).putExtra(Settings.EXTRA_APP_PACKAGE, ctx.packageName)
                    else Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS, Uri.parse("package:" + ctx.packageName))
                    ctx.startActivity(i.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
                }
                PermRow(Icons.Outlined.CameraAlt, "Kamera", if (camOk) "Diizinkan" else "Belum diizinkan", camOk) { openAppSettings(ctx) }
                PermRow(Icons.Outlined.Mic, "Mikrofon", if (micOk) "Diizinkan" else "Belum diizinkan", micOk) { openAppSettings(ctx) }
                PermRow(
                    Icons.Outlined.Notifications, "Push Real-time",
                    if (PushManager.isReady()) "Aktif (Firebase)" else "Mode sinkronisasi berkala", PushManager.isReady(),
                ) {
                    scope.launch {
                        Auth.refreshMe(ctx)
                        PushManager.registerDevice(ctx, force = true)
                        Ui.toast(if (PushManager.isReady()) "Perangkat terdaftar untuk notifikasi push." else "Notifikasi push belum dikonfigurasi admin. Aplikasi memakai sinkronisasi berkala.")
                    }
                }
            }

            SxCard(Modifier.fillMaxWidth().appear(6), onClick = { nav.navigate(Rt.ABOUT) }, padding = PaddingValues(16.dp)) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.Info)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text("Tentang Aplikasi", style = MaterialTheme.typography.titleSmall)
                        Text("Versi ${BuildConfig.VERSION_NAME}", style = MaterialTheme.typography.bodySmall)
                    }
                    Icon(Icons.AutoMirrored.Outlined.ArrowForward, null, tint = Sx.Faint)
                }
            }
            SoftButton("Keluar", { AppState.logoutRequest.value++ }, Modifier.fillMaxWidth().appear(7), icon = Icons.AutoMirrored.Outlined.Logout, tone = Tone.Red, height = 52.dp)

            if (editing) {
                val form = rememberForm("username" to user.str("username"), "email" to user.str("email"), "phone_number" to (user.strOrNull("phone_number") ?: p?.strOrNull("phone_number")))
                SxDialog(
                    onDismiss = { editing = false }, title = "Ubah Profil", icon = Icons.Outlined.Person, confirmText = "Simpan", loading = form.submitting,
                    onConfirm = {
                        scope.launch {
                            val r = form.submit("/account/settings")
                            if (Ui.report(r)) { editing = false; loader.refresh(); Auth.refreshMe(ctx) }
                        }
                    },
                ) {
                    Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                        TextFieldF(form, "username", "Username", required = true)
                        TextFieldF(form, "email", "Email", required = true, keyboardType = KeyboardType.Email)
                        TextFieldF(form, "phone_number", "Nomor HP", keyboardType = KeyboardType.Phone)
                        FileF(form, "avatar", "Foto Avatar", Accept.Image, helper = "JPG/PNG maks 2 MB")
                    }
                }
            }
            if (pwd) {
                val form = rememberForm()
                SxDialog(
                    onDismiss = { pwd = false }, title = "Ubah Kata Sandi", icon = Icons.Outlined.Lock, confirmText = "Simpan", loading = form.submitting,
                    onConfirm = {
                        if (!form.require("current_password" to "Kata sandi saat ini", "password" to "Kata sandi baru", "password_confirmation" to "Konfirmasi")) return@SxDialog
                        scope.launch { if (Ui.report(form.submit("/account/settings/password"))) pwd = false }
                    },
                ) {
                    Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                        TextFieldF(form, "current_password", "Kata Sandi Saat Ini", password = true, required = true)
                        TextFieldF(form, "password", "Kata Sandi Baru", password = true, required = true, helper = "Minimal 8 karakter")
                        TextFieldF(form, "password_confirmation", "Konfirmasi Kata Sandi Baru", password = true, required = true)
                    }
                }
            }
            mfaSetup?.let { setup ->
                SxDialog(
                    onDismiss = { mfaSetup = null; mfaCode = "" }, title = "Aktifkan 2FA", icon = Icons.Outlined.QrCode2,
                    message = "Pindai kode QR dengan Google Authenticator, lalu masukkan kode 6 digit.",
                    confirmText = "Verifikasi", loading = mfaBusy,
                    onConfirm = {
                        scope.launch {
                            mfaBusy = true
                            val r = Api.send("/account/settings/mfa/verify", mapOf("secret" to setup.str("secret"), "code" to mfaCode))
                            mfaBusy = false
                            if (Ui.report(r, "2FA berhasil diaktifkan.")) { mfaSetup = null; mfaCode = ""; loader.refresh() }
                        }
                    },
                ) {
                    Box(Modifier.fillMaxWidth(), contentAlignment = Alignment.Center) {
                        Box(Modifier.size(190.dp).clip(RoundedCornerShape(16.dp)).background(Color.White)) {
                            RemoteImage(setup.strOrNull("qr_image"), Modifier.fillMaxWidth().height(190.dp), contentScale = androidx.compose.ui.layout.ContentScale.Fit)
                        }
                    }
                    Spacer(Modifier.height(10.dp))
                    Text(
                        setup.str("secret"), fontWeight = FontWeight.Bold, textAlign = TextAlign.Center, color = Sx.BlueDark,
                        modifier = Modifier.fillMaxWidth().pressable({ clip.setText(AnnotatedString(setup.str("secret"))); Ui.success("Kunci disalin") }),
                    )
                    Spacer(Modifier.height(10.dp))
                    PlainTextField(mfaCode, { mfaCode = it }, placeholder = "Kode 6 digit", keyboardType = KeyboardType.Number, maxLength = 6)
                }
            }
        }
    }
}

private fun openAppSettings(ctx: android.content.Context) {
    ctx.startActivity(Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS, Uri.parse("package:" + ctx.packageName)).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
}

@Composable
private fun PermRow(icon: ImageVector, title: String, status: String, ok: Boolean, onClick: () -> Unit) {
    Row(Modifier.fillMaxWidth().clip(RoundedCornerShape(14.dp)).pressable(onClick).padding(vertical = 8.dp), verticalAlignment = Alignment.CenterVertically) {
        IconBox(icon, tint = if (ok) Sx.Green else Sx.Amber, size = 38.dp, iconSize = 19.dp, radius = 12.dp)
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.titleSmall)
            Text(status, style = MaterialTheme.typography.bodySmall)
        }
        StatusBadge(if (ok) "AKTIF" else "PENDING")
    }
}

@Composable
fun AboutScreen() {
    ShellScaffold(title = "Tentang Aplikasi") { inner ->
        Column(Modifier.padding(top = inner.calculateTopPadding()).padding(16.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
            SxCard(Modifier.fillMaxWidth().appear(0)) {
                Column(Modifier.fillMaxWidth(), horizontalAlignment = Alignment.CenterHorizontally) {
                    AppLogo(84.dp)
                    Spacer(Modifier.height(12.dp))
                    Text("SISFOPERS KC", style = MaterialTheme.typography.headlineSmall)
                    Text("Sistem Informasi Personel Komponen Cadangan Tri Matra", style = MaterialTheme.typography.bodySmall, textAlign = TextAlign.Center)
                    Spacer(Modifier.height(8.dp))
                    Badge("Versi ${BuildConfig.VERSION_NAME} (${BuildConfig.VERSION_CODE})", Tone.Blue)
                }
            }
            SxCard(Modifier.fillMaxWidth().appear(1)) {
                SectionTitle("Fitur Utama")
                Spacer(Modifier.height(6.dp))
                listOf(
                    "Pendaftaran & verifikasi personel berbasis SKEP",
                    "Menu dinamis sesuai peran: Admin, Koordinator, PJU, Personel",
                    "Broadcast kegiatan & konfirmasi kehadiran",
                    "Formulir & rekrutmen digital",
                    "Pusat Layanan Informasi dengan chat & panggilan video",
                    "Vicon rapat dinas multi-peserta",
                    "Notifikasi push real-time",
                ).forEach { Text("• $it", style = MaterialTheme.typography.bodyMedium, modifier = Modifier.padding(vertical = 3.dp)) }
            }
            SxCard(Modifier.fillMaxWidth().appear(2)) {
                InfoRow("Server", Session.baseUrl)
                InfoRow("Notifikasi Push", if (PushManager.isReady()) "Firebase aktif" else "Sinkronisasi berkala")
            }
            Notice("Data personel bersifat rahasia dinas. Jangan bagikan akun dan kode OTP kepada siapa pun.", Tone.Amber, Icons.Outlined.Shield)
        }
    }
}
