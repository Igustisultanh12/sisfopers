package id.my.sisfoperskc.app.ui.admin

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.Assessment
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.CloudUpload
import androidx.compose.material.icons.outlined.ConfirmationNumber
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Dns
import androidx.compose.material.icons.outlined.DoneAll
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.HourglassTop
import androidx.compose.material.icons.outlined.Image
import androidx.compose.material.icons.outlined.Lan
import androidx.compose.material.icons.outlined.Login
import androidx.compose.material.icons.outlined.ManageAccounts
import androidx.compose.material.icons.outlined.Map
import androidx.compose.material.icons.outlined.NotificationsActive
import androidx.compose.material.icons.outlined.PictureAsPdf
import androidx.compose.material.icons.outlined.Print
import androidx.compose.material.icons.outlined.Save
import androidx.compose.material.icons.outlined.Security
import androidx.compose.material.icons.outlined.Send
import androidx.compose.material.icons.outlined.Settings
import androidx.compose.material.icons.outlined.Sms
import androidx.compose.material.icons.outlined.TableChart
import androidx.compose.material.icons.outlined.Timeline
import androidx.compose.material.icons.outlined.VideoCall
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.auth.MatraOpts
import id.my.sisfoperskc.app.ui.common.ActivityRow
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.ReasonDialog
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SheetAction
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SwitchF
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.filterChips
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberDownloader
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.personel.TicketTimeline
import id.my.sisfoperskc.app.ui.personel.ticketCategory
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

// ------------------------------------------------------------------ Tiket pengaduan (admin/koordinator)

@Composable
fun TicketsScreen() {
    val scope = rememberCoroutineScope()
    val state = rememberPaged("/admin/tickets", "tickets")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    var deciding by remember { mutableStateOf<Pair<String, JsonObject>?>(null) }
    var deleting by remember { mutableStateOf<JsonObject?>(null) }
    var busy by remember { mutableStateOf(false) }
    ShellScaffold(title = "Pengaduan & Tiket", subtitle = "Permohonan perubahan data personel") { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nomor tiket atau nama...",
            filterBar = { state.filterChips("status", listOf(Opt("", "Semua"), Opt("DIPROSES", "Diproses"), Opt("DISETUJUI", "Disetujui"), Opt("SELESAI", "Selesai"), Opt("DITOLAK", "Ditolak"))) },
            emptyTitle = "Tidak ada tiket",
        ) { t, _ ->
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { selected = t }) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.ConfirmationNumber, Sx.Red)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(t.str("ticket_number"), style = MaterialTheme.typography.titleSmall)
                        Text("${ticketCategory(t.str("category"))} • ${t.obj("personel").str("full_name")}", style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    }
                    StatusBadge(t.str("status"))
                }
            }
        }
    }
    selected?.let { t ->
        SxSheet({ selected = null }) {
            Text(t.str("ticket_number"), style = MaterialTheme.typography.headlineSmall)
            Text(ticketCategory(t.str("category")), style = MaterialTheme.typography.bodySmall)
            Spacer(Modifier.height(12.dp))
            PersonelRow(t.obj("personel"), trailing = { StatusBadge(t.str("status")) })
            t.strOrNull("description")?.let { Text(it, style = MaterialTheme.typography.bodyMedium, modifier = Modifier.padding(top = 12.dp)) }
            t.strOrNull("rejection_reason")?.let { Notice("Alasan: $it", Tone.Red, modifier = Modifier.padding(top = 8.dp)) }
            FileLink("Lampiran", t.strOrNull("attachment_path"), Modifier.padding(top = 10.dp))
            TicketTimeline(t)
            Spacer(Modifier.height(12.dp))
            Text("Perbarui Status", style = MaterialTheme.typography.titleSmall)
            Spacer(Modifier.height(8.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                SoftButton("Disetujui", { deciding = "DISETUJUI" to t }, Modifier.weight(1f), tone = Tone.Green)
                SoftButton("Selesai", { deciding = "SELESAI" to t }, Modifier.weight(1f), tone = Tone.Blue)
                SoftButton("Tolak", { deciding = "DITOLAK" to t }, Modifier.weight(1f), tone = Tone.Red)
            }
            Spacer(Modifier.height(8.dp))
            SheetAction(Icons.Outlined.Delete, "Hapus tiket", Tone.Red) { selected = null; deleting = t }
        }
    }
    deciding?.let { (status, t) ->
        ReasonDialog(
            title = "Ubah status menjadi ${id.my.sisfoperskc.app.ui.components.statusLabel(status)}?",
            label = if (status == "DITOLAK") "Alasan penolakan" else "Catatan (opsional)", required = status == "DITOLAK",
            confirmText = "Simpan", tone = if (status == "DITOLAK") Tone.Red else Tone.Blue, loading = busy, onDismiss = { deciding = null },
        ) { note ->
            scope.launch {
                busy = true
                val r = Api.send("/admin/tickets/${t.str("id")}/status", mapOf("status" to status, "rejection_reason" to note.ifBlank { null }))
                busy = false
                if (Ui.report(r)) { deciding = null; selected = null; state.reload(silent = true) }
            }
        }
    }
    deleting?.let { t ->
        SxDialog(onDismiss = { deleting = null }, title = "Hapus tiket ${t.str("ticket_number")}?", icon = Icons.Outlined.WarningAmber, tone = Tone.Red,
            confirmText = "Hapus", confirmColor = Sx.Red, onConfirm = {
                deleting = null
                scope.launch { if (Ui.report(Api.send("/admin/tickets/${t.str("id")}", method = "DELETE"))) state.reload(silent = true) }
            })
    }
}

// ------------------------------------------------------------------ Monitoring

@Composable
fun ActivityLogScreen() {
    val state = rememberPaged("/admin/monitoring/aktivitas", "logs")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    ShellScaffold(title = "Aktivitas Sistem", subtitle = "Audit trail seluruh perubahan data") { inner ->
        PagedList(state, Modifier.padding(top = inner.calculateTopPadding()), searchHint = "Cari aksi atau pengguna...", itemSpacing = 10.dp) { a, _ ->
            Column(Modifier.fillMaxWidth()) {
                ActivityRow(JsonObject(a + ("full_name" to (a.obj("user").obj("personel")["full_name"] ?: a.obj("user")["username"] ?: kotlinx.serialization.json.JsonPrimitive("Sistem")))))
            }
        }
    }
    selected?.let { a ->
        SxSheet({ selected = null }) {
            InfoRow("Aksi", a.str("action"))
            InfoRow("Model", a.str("model_type"))
            InfoRow("IP", a.str("ip_address"))
            InfoRow("Waktu", Fmt.dateTime(a.str("created_at")))
            Text(a.obj("new_values").toString(), style = MaterialTheme.typography.bodySmall)
        }
    }
}

@Composable
fun LoginLogScreen() {
    val state = rememberPaged("/admin/monitoring/login", "logs")
    ShellScaffold(title = "Log Login & Sesi", subtitle = "Riwayat akses akun") { inner ->
        PagedList(state, Modifier.padding(top = inner.calculateTopPadding()), searchHint = "Cari pengguna atau IP...", itemSpacing = 10.dp) { l, _ ->
            val u = l.obj("user")
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp)) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.Login, if (l.str("browser").contains("Android")) Sx.Green else Sx.Blue, size = 40.dp, iconSize = 20.dp)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(u.obj("personel").strOrNull("full_name") ?: u.str("username", "Pengguna"), style = MaterialTheme.typography.titleSmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                        Text("${l.str("browser")} • ${l.str("os")} • ${l.str("ip_address")}", style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    }
                    Text(Fmt.relative(l.strOrNull("login_at") ?: l.str("created_at")), style = MaterialTheme.typography.bodySmall)
                }
            }
        }
    }
}

@Composable
fun WaLogScreen() {
    val state = rememberPaged("/admin/monitoring/whatsapp", "logs")
    ShellScaffold(title = "Log WhatsApp", subtitle = "Riwayat pengiriman pesan gateway") { inner ->
        PagedList(state, Modifier.padding(top = inner.calculateTopPadding()), searchHint = null, itemSpacing = 10.dp) { l, _ ->
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp)) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.Sms, Sx.Green, size = 40.dp, iconSize = 20.dp)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(l.str("recipient_number"), style = MaterialTheme.typography.titleSmall)
                        Text(Fmt.dateTime(l.str("created_at")), style = MaterialTheme.typography.bodySmall)
                    }
                    StatusBadge(l.str("status"))
                }
                Text(l.str("message"), style = MaterialTheme.typography.bodySmall, maxLines = 4, overflow = TextOverflow.Ellipsis, modifier = Modifier.padding(top = 8.dp))
                l.strOrNull("error_response")?.let { Text(it, style = MaterialTheme.typography.bodySmall.copy(color = Sx.Red), maxLines = 2, overflow = TextOverflow.Ellipsis) }
            }
        }
    }
}

// ------------------------------------------------------------------ Laporan

@Composable
fun ReportsScreen() {
    val download = rememberDownloader()
    val me by Session.me.collectAsState()
    val base = if (me?.isPjuRole == true) "/pju/laporan" else "/admin/laporan"
    ShellScaffold(title = "Laporan Pelaporan", subtitle = "Unduh rekap PDF & Excel") { inner ->
        Column(Modifier.padding(top = inner.calculateTopPadding()).padding(16.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
            ReportCard("Laporan Data Personel", "Rekap seluruh personel sesuai cakupan akses Anda.", Icons.Outlined.Assessment, 0,
                { download("$base/personel/pdf", "Laporan_Personel.pdf") }, { download("$base/personel/excel", "Laporan_Personel.xlsx") })
            ReportCard("Laporan Kewilayahan", "Sebaran personel per wilayah / satuan.", Icons.Outlined.Map, 1,
                { download("$base/wilayah/pdf", "Laporan_Wilayah.pdf") }, { download("$base/wilayah/excel", "Laporan_Wilayah.xlsx") })
            Notice("Laporan kehadiran per kegiatan tersedia pada detail setiap Broadcast.", Tone.Blue)
        }
    }
}

@Composable
private fun ReportCard(title: String, desc: String, icon: androidx.compose.ui.graphics.vector.ImageVector, index: Int, onPdf: () -> Unit, onExcel: () -> Unit) {
    SxCard(Modifier.fillMaxWidth().appear(index)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(icon, Sx.Amber)
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(title, style = MaterialTheme.typography.titleMedium)
                Text(desc, style = MaterialTheme.typography.bodySmall)
            }
        }
        Spacer(Modifier.height(14.dp))
        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            SoftButton("PDF", onPdf, Modifier.weight(1f), icon = Icons.Outlined.PictureAsPdf, tone = Tone.Red)
            SoftButton("Excel", onExcel, Modifier.weight(1f), icon = Icons.Outlined.TableChart, tone = Tone.Green)
        }
    }
}

// ------------------------------------------------------------------ Pengaturan sistem (termasuk FCM)

@Composable
fun SettingsScreen() {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/admin/pengaturan")
    val form = rememberForm()
    var init by remember { mutableStateOf(false) }
    var fcm by remember { mutableStateOf<JsonObject?>(null) }
    val fcmForm = rememberForm("fcm_enabled" to "0")
    var waTesting by remember { mutableStateOf(false) }
    var testPhone by remember { mutableStateOf("") }

    suspend fun loadFcm() {
        val r = Api.get("/admin/pengaturan/fcm/status") as? ApiResult.Ok ?: return
        fcm = r.data.obj("status")
        fcmForm["fcm_enabled"] = if (fcm.bool("enabled")) "1" else "0"
    }
    LaunchedEffect(Unit) { loadFcm() }

    ShellScaffold(title = "Pengaturan Sistem", subtitle = "Konfigurasi aplikasi & integrasi") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding()).imePadding()) { data ->
            val s = data.obj("settings")
            LaunchedEffect(s) {
                if (!init) {
                    form.setAll(s.mapValues { (_, v) -> id.my.sisfoperskc.app.data.asStrOrEmpty(v) })
                    init = true
                }
            }
            // FCM
            FormSection("Notifikasi Push Aplikasi", "Firebase Cloud Messaging untuk aplikasi Android", Icons.Outlined.NotificationsActive) {
                val st = fcm
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Badge(if (st.bool("server_ready")) "Server siap" else "Server belum siap", if (st.bool("server_ready")) Tone.Green else Tone.Amber)
                    Badge(if (st.bool("client_ready")) "Aplikasi siap" else "Aplikasi belum siap", if (st.bool("client_ready")) Tone.Green else Tone.Amber)
                    Badge("${st.int("devices")} perangkat", Tone.Blue)
                }
                st?.strOrNull("project_id")?.let { InfoRow("Project ID", it) }
                st?.strOrNull("client_email")?.let { InfoRow("Service Account", it) }
                SwitchF(fcmForm, "fcm_enabled", "Aktifkan notifikasi push", "Kirim notifikasi real-time ke aplikasi Android")
                FileF(fcmForm, "service_account", "Service Account (JSON)", Accept.Json, helper = "Firebase › Project settings › Service accounts › Generate new private key")
                FileF(fcmForm, "google_services", "google-services.json", Accept.Json, helper = "Unduh dari aplikasi Android paket id.my.sisfoperskc.app")
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    SoftButton("Simpan", {
                        scope.launch { if (Ui.report(fcmForm.submit("/admin/pengaturan/fcm"))) { fcmForm.files.clear(); loadFcm() } }
                    }, Modifier.weight(1f), icon = Icons.Outlined.CloudUpload, loading = fcmForm.submitting)
                    SoftButton("Kirim Uji", { scope.launch { Ui.report(Api.send("/admin/pengaturan/fcm/test")) } }, Modifier.weight(1f), icon = Icons.Outlined.Send, tone = Tone.Green)
                    SoftButton("Hapus", { scope.launch { if (Ui.report(Api.send("/admin/pengaturan/fcm", method = "DELETE"))) loadFcm() } }, Modifier.weight(0.8f), icon = Icons.Outlined.Delete, tone = Tone.Red)
                }
            }
            FormSection("Identitas Aplikasi", icon = Icons.Outlined.Settings) {
                TextFieldF(form, "app_name", "Nama Aplikasi", required = true)
                TextFieldF(form, "app_title", "Judul Aplikasi")
                TextFieldF(form, "footer_text", "Teks Footer")
                FileF(form, "logo_tni", "Logo TNI", Accept.Image, existingPath = s.strOrNull("logo_tni"))
                FileF(form, "logo_ad", "Logo TNI AD", Accept.Image, existingPath = s.strOrNull("logo_ad"))
                FileF(form, "logo_al", "Logo TNI AL", Accept.Image, existingPath = s.strOrNull("logo_al"))
                FileF(form, "logo_au", "Logo TNI AU", Accept.Image, existingPath = s.strOrNull("logo_au"))
                FileF(form, "login_background", "Latar Halaman Login", Accept.Image, existingPath = s.strOrNull("login_background"))
            }
            FormSection("Operasional", icon = Icons.Outlined.Security) {
                SwitchF(form, "under_maintenance", "Mode Pemeliharaan", "Batasi akses personel sementara")
                SwitchF(form, "disable_whatsapp_otp", "Nonaktifkan OTP WhatsApp", "OTP dikirim via email")
                SwitchF(form, "turnstile_enabled", "Cloudflare Turnstile", "Proteksi bot pada login web")
                if (form["turnstile_enabled"] == "1") {
                    TextFieldF(form, "turnstile_site_key", "Turnstile Site Key")
                    TextFieldF(form, "turnstile_secret_key", "Turnstile Secret Key", password = true)
                }
            }
            FormSection("WhatsApp Gateway", icon = Icons.Outlined.Sms) {
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    TextFieldF(form, "wa_host", "Host", Modifier.weight(1.5f), required = true)
                    TextFieldF(form, "wa_port", "Port", Modifier.weight(1f), required = true, keyboardType = KeyboardType.Number)
                }
                TextFieldF(form, "wa_session", "Nama Sesi", required = true)
                TextFieldF(form, "wa_api_key", "API Key", password = true)
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    SoftButton("Cek Koneksi", {
                        scope.launch {
                            waTesting = true
                            val r = Api.send("/admin/pengaturan/wa-test")
                            waTesting = false
                            if (r is ApiResult.Ok) Ui.toast(r.message ?: "-", if (r.data.bool("status")) id.my.sisfoperskc.app.ui.components.MsgKind.Success else id.my.sisfoperskc.app.ui.components.MsgKind.Warning) else Ui.report(r)
                        }
                    }, Modifier.weight(1f), icon = Icons.Outlined.Lan, loading = waTesting)
                    SoftButton("Deteksi IP", {
                        scope.launch {
                            val r = Api.get("/admin/pengaturan/detect-ip")
                            if (r is ApiResult.Ok && r.data.strOrNull("ip") != null) Ui.success("IP publik server: ${r.data.str("ip")}") else Ui.report(r)
                        }
                    }, Modifier.weight(1f), icon = Icons.Outlined.Dns, tone = Tone.Purple)
                }
                Row(verticalAlignment = Alignment.Bottom, horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    PlainTextField(testPhone, { testPhone = it }, Modifier.weight(1f), label = "Uji kirim ke nomor", keyboardType = KeyboardType.Phone, placeholder = "08xxxx")
                    SoftButton("Kirim", { scope.launch { Ui.report(Api.send("/admin/pengaturan/wa-test-send", mapOf("phone" to testPhone))) } }, icon = Icons.Outlined.Send, tone = Tone.Green, height = 56.dp)
                }
            }
            FormSection("Video Call & Relay", icon = Icons.Outlined.VideoCall) {
                TextFieldF(form, "agora_app_id", "Agora App ID")
                TextFieldF(form, "agora_app_certificate", "Agora App Certificate", password = true)
                TextFieldF(form, "metered_app_name", "Metered App Name")
                TextFieldF(form, "metered_api_key", "Metered API Key", password = true)
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    TextFieldF(form, "coturn_host", "Coturn Host", Modifier.weight(1.5f))
                    TextFieldF(form, "coturn_port", "Port", Modifier.weight(1f), keyboardType = KeyboardType.Number)
                }
                TextFieldF(form, "coturn_secret", "Coturn Secret", password = true)
            }
            PrimaryButton("Simpan Pengaturan", {
                if (!form.require("app_name" to "Nama aplikasi", "wa_host" to "WA host", "wa_port" to "WA port", "wa_session" to "Sesi WA")) return@PrimaryButton
                val keys = listOf(
                    "app_name", "app_title", "footer_text", "wa_host", "wa_port", "wa_api_key", "wa_session", "disable_whatsapp_otp", "under_maintenance",
                    "coturn_host", "coturn_port", "coturn_secret", "metered_app_name", "metered_api_key", "agora_app_id", "agora_app_certificate",
                    "turnstile_enabled", "turnstile_site_key", "turnstile_secret_key",
                )
                scope.launch {
                    val body = form.body(keys).toMutableMap()
                    listOf("disable_whatsapp_otp", "under_maintenance", "turnstile_enabled").forEach { k -> body[k] = form[k].ifBlank { "0" } }
                    if (Ui.report(form.submit("/admin/pengaturan", body = body))) loader.refresh()
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Save)
        }
    }
}

// ------------------------------------------------------------------ Manajemen akun PJU

val PjuRoleOpts = opts(
    "ka_bacadnas" to "Ka Bacadnas", "ses_bacadnas" to "Ses Bacadnas", "kapus_komcad" to "Kapus Komcad", "pembina_matra" to "Pembina Matra",
    "pembina_kodam" to "Pembina Kodam", "pembina_kodaeral" to "Pembina Kodaeral", "pembina_kodau" to "Pembina Kodau",
    "pembina_kodim" to "Pembina Kodim", "pembina_lanal" to "Pembina Lanal", "pembina_lanud" to "Pembina Lanud",
)

@Composable
fun PjuManagementScreen() {
    val scope = rememberCoroutineScope()
    val download = rememberDownloader()
    val state = rememberPaged("/admin/pju-management", "pjuUsers")
    var editing by remember { mutableStateOf<JsonObject?>(null) }
    var creating by remember { mutableStateOf(false) }
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    var deleting by remember { mutableStateOf<JsonObject?>(null) }
    var created by remember { mutableStateOf<JsonObject?>(null) }
    ShellScaffold(
        title = "Manajemen Akun PJU", subtitle = "Akun pejabat pembina",
        fab = { FloatingActionButton(onClick = { creating = true }, containerColor = Sx.Blue, contentColor = Color.White) { Icon(Icons.Outlined.Add, "Tambah PJU") } },
    ) { inner ->
        PagedList(state, Modifier.padding(top = inner.calculateTopPadding()), searchHint = "Cari nama, email, jabatan...", emptyTitle = "Belum ada akun PJU") { p, _ ->
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { selected = p }) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.ManageAccounts, Sx.Indigo)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(p.str("full_name"), style = MaterialTheme.typography.titleSmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                        Text(p.str("jabatan_pju"), style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    }
                    Badge(if (p.bool("is_active")) "Aktif" else "Nonaktif", if (p.bool("is_active")) Tone.Green else Tone.Gray)
                }
                Spacer(Modifier.height(8.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Badge(PjuRoleOpts.firstOrNull { it.value == p.str("role_pju") }?.label ?: p.str("role_pju"), Tone.Purple)
                    p.strOrNull("matra")?.let { Badge(Fmt.matra(it), Tone.Blue) }
                }
            }
        }
    }
    selected?.let { p ->
        SxSheet({ selected = null }) {
            Text(p.str("full_name"), style = MaterialTheme.typography.headlineSmall)
            Spacer(Modifier.height(8.dp))
            InfoRow("Username", p.str("username"))
            InfoRow("Email", p.str("email"))
            InfoRow("NRP", p.strOrNull("nrp"))
            InfoRow("Jabatan", p.str("jabatan_pju"))
            InfoRow("Satuan", p.strOrNull("satuan_wilayah"))
            Spacer(Modifier.height(10.dp))
            SheetAction(Icons.Outlined.Edit, "Ubah Akun") { selected = null; editing = p }
            SheetAction(Icons.Outlined.Print, "Cetak Lembar Akun", Tone.Green) { download("/admin/pju-management/${p.str("id")}/print-account", "Akun_PJU_${p.str("username")}.html") }
            SheetAction(Icons.Outlined.Delete, "Hapus Akun", Tone.Red) { selected = null; deleting = p }
        }
    }
    if (creating || editing != null) {
        val e = editing
        val form = rememberForm(
            "full_name" to e?.str("full_name"), "nrp" to e?.strOrNull("nrp"), "email" to e?.str("email"), "phone_number" to e?.strOrNull("phone_number"),
            "role" to (e?.str("role_pju") ?: "pembina_matra"), "jabatan_pju" to e?.str("jabatan_pju"), "matra" to e?.strOrNull("matra"), "satuan_wilayah" to e?.strOrNull("satuan_wilayah"),
        )
        SxSheet({ creating = false; editing = null }) {
            Text(if (e == null) "Tambah Akun PJU" else "Ubah Akun PJU", style = MaterialTheme.typography.headlineSmall)
            Spacer(Modifier.height(14.dp))
            Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                TextFieldF(form, "full_name", "Nama Lengkap", required = true)
                TextFieldF(form, "nrp", "NRP")
                TextFieldF(form, "email", "Email", required = true, keyboardType = KeyboardType.Email, icon = Icons.Outlined.Email)
                TextFieldF(form, "phone_number", "Nomor HP", required = true, keyboardType = KeyboardType.Phone)
                SelectF(form, "role", "Peran PJU", PjuRoleOpts, required = true)
                TextFieldF(form, "jabatan_pju", "Jabatan", required = true)
                SelectF(form, "matra", "Matra", MatraOpts)
                TextFieldF(form, "satuan_wilayah", "Satuan Wilayah")
                TextFieldF(form, "password", if (e == null) "Kata Sandi (kosongkan = otomatis)" else "Kata Sandi Baru (opsional)", password = true)
                PrimaryButton("Simpan", {
                    if (!form.require("full_name" to "Nama", "email" to "Email", "phone_number" to "Nomor HP", "role" to "Peran", "jabatan_pju" to "Jabatan")) return@PrimaryButton
                    scope.launch {
                        val r = form.submit(if (e == null) "/admin/pju-management" else "/admin/pju-management/${e.str("id")}", method = if (e == null) "POST" else "PUT")
                        if (r is ApiResult.Ok) {
                            creating = false; editing = null
                            val cp = r.data.obj("flash").objOrNull("created_pju")
                            if (cp != null) created = cp else Ui.report(r)
                            state.reload(silent = true)
                        } else Ui.report(r)
                    }
                }, Modifier.fillMaxWidth(), loading = form.submitting)
            }
        }
    }
    deleting?.let { p ->
        SxDialog(onDismiss = { deleting = null }, title = "Hapus akun ${p.str("full_name")}?", icon = Icons.Outlined.WarningAmber, tone = Tone.Red,
            confirmText = "Hapus", confirmColor = Sx.Red, onConfirm = {
                deleting = null
                scope.launch { if (Ui.report(Api.send("/admin/pju-management/${p.str("id")}", method = "DELETE"))) state.reload(silent = true) }
            })
    }
    created?.let { c ->
        SxDialog(onDismiss = { created = null }, title = "Akun PJU dibuat", message = "Serahkan kredensial berikut kepada pejabat terkait.", icon = Icons.Outlined.CheckCircle, tone = Tone.Green,
            dismissText = "Tutup", confirmText = "Cetak", onConfirm = { download("/admin/pju-management/${c.str("id")}/print-account", "Akun_PJU.html") }) {
            InfoRow("Nama", c.str("full_name"))
            InfoRow("Jabatan", c.str("jabatan_pju"))
            InfoRow("Username", c.str("username"))
            InfoRow("Email", c.str("email"))
            InfoRow("Kata Sandi", c.str("password"))
        }
    }
}

// ------------------------------------------------------------------ Mail gateway

@Composable
fun MailGatewayScreen() {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/admin/mail-gateway")
    val form = rememberForm()
    var init by remember { mutableStateOf(false) }
    var testEmail by remember { mutableStateOf("") }
    var testing by remember { mutableStateOf(false) }
    ShellScaffold(title = "Mail Gateway", subtitle = "SMTP & OTP email") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding()).imePadding()) { data ->
            val c = data.obj("mailConfig")
            LaunchedEffect(c) {
                if (!init) { form.setAll(c.mapValues { (_, v) -> id.my.sisfoperskc.app.data.asStrOrEmpty(v) }); init = true }
            }
            FormSection("Konfigurasi SMTP", icon = Icons.Outlined.Email) {
                ChipsF(form, "mail_mailer", "Mailer", opts("smtp" to "SMTP", "log" to "Log (uji)"), required = true)
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    TextFieldF(form, "mail_host", "Host", Modifier.weight(1.5f), required = true)
                    TextFieldF(form, "mail_port", "Port", Modifier.weight(1f), required = true, keyboardType = KeyboardType.Number)
                }
                TextFieldF(form, "mail_username", "Username")
                TextFieldF(form, "mail_password", "Password", password = true)
                ChipsF(form, "mail_encryption", "Enkripsi", opts("tls" to "TLS", "ssl" to "SSL", "none" to "Tanpa"))
                TextFieldF(form, "mail_from_address", "Alamat Pengirim", required = true, keyboardType = KeyboardType.Email)
                TextFieldF(form, "mail_from_name", "Nama Pengirim", required = true)
                SwitchF(form, "enable_email_otp", "Aktifkan OTP via Email", "Cadangan bila WhatsApp tidak tersedia")
            }
            PrimaryButton("Simpan", {
                if (!form.require("mail_mailer" to "Mailer", "mail_host" to "Host", "mail_port" to "Port", "mail_from_address" to "Alamat pengirim", "mail_from_name" to "Nama pengirim")) return@PrimaryButton
                scope.launch {
                    val body = form.body().toMutableMap()
                    body["enable_email_otp"] = form["enable_email_otp"].ifBlank { "0" }
                    if (Ui.report(form.submit("/admin/mail-gateway", body = body))) loader.refresh()
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Save)
            SxCard(Modifier.fillMaxWidth()) {
                SectionTitle("Uji Kirim Email")
                Spacer(Modifier.height(10.dp))
                PlainTextField(testEmail, { testEmail = it }, placeholder = "email@contoh.go.id", keyboardType = KeyboardType.Email)
                Spacer(Modifier.height(10.dp))
                SoftButton("Kirim Email Uji", {
                    scope.launch {
                        testing = true
                        Ui.report(Api.send("/admin/mail-gateway/test", mapOf("recipient_email" to testEmail)))
                        testing = false
                    }
                }, Modifier.fillMaxWidth(), icon = Icons.Outlined.Send, loading = testing)
            }
        }
    }
}
