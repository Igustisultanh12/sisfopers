package id.my.sisfoperskc.app.ui.personel

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Business
import androidx.compose.material.icons.outlined.ConfirmationNumber
import androidx.compose.material.icons.outlined.Construction
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Send
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material.icons.outlined.Work
import androidx.compose.material.icons.outlined.WorkOff
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.asStr
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.data.strings
import id.my.sisfoperskc.app.ui.auth.AsnJenisOpts
import id.my.sisfoperskc.app.ui.auth.OtpBoxes
import id.my.sisfoperskc.app.ui.components.KtaCardView
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.ChoiceChips
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.Divider
import id.my.sisfoperskc.app.ui.components.EmptyState
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.FilePickerField
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.FormState
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.RemoteImage
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.WilayahFields
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

// ------------------------------------------------------------------ Riwayat pekerjaan (dengan OTP)

val JobTypeOpts = opts("ASN" to "ASN", "SWASTA" to "Swasta", "WIRASWASTA" to "Wiraswasta", "PELAJAR" to "Pelajar/Mahasiswa", "TIDAK_BEKERJA" to "Tidak Bekerja")

@Composable
fun MyJobScreen() {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/personel/pekerjaan")
    var otpFor by remember { mutableStateOf<String?>(null) } // INITIAL_FILL | UPDATE_JOB | PHK
    var verifiedFor by remember { mutableStateOf<String?>(null) }

    ShellScaffold(title = "Riwayat Pekerjaan", subtitle = "Data pekerjaan terkini Anda") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            val histories = data.objects("histories")
            val current = histories.firstOrNull { it.bool("is_current") } ?: histories.firstOrNull()
            Notice("Perubahan data pekerjaan memerlukan verifikasi OTP yang dikirim ke WhatsApp/email Anda.", Tone.Blue, Icons.Outlined.Lock)
            if (current != null) {
                SxCard(Modifier.fillMaxWidth().appear(0), elevation = 6.dp) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        IconBox(if (current.bool("is_phk")) Icons.Outlined.WorkOff else Icons.Outlined.Work, if (current.bool("is_phk")) Sx.Red else Sx.Amber)
                        Spacer(Modifier.width(12.dp))
                        Column(Modifier.weight(1f)) {
                            Text("Pekerjaan Saat Ini", style = MaterialTheme.typography.bodySmall)
                            Text(current.str("nama_perusahaan", Fmt.title(current.str("tipe_pekerjaan"))), style = MaterialTheme.typography.titleMedium, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        }
                        Badge(Fmt.title(current.str("tipe_pekerjaan")), Tone.Amber)
                    }
                    Spacer(Modifier.height(10.dp))
                    InfoRow("Jabatan", current.strOrNull("jabatan"))
                    current.strOrNull("nip")?.let { InfoRow("NIP", it) }
                    current.strOrNull("nomor_karyawan")?.let { InfoRow("No. Karyawan", it) }
                    InfoRow("TMT Mulai", Fmt.dateLong(current.strOrNull("tmt_mulai")))
                    InfoRow("Lokasi", listOf(current.str("kecamatan"), current.str("kabupaten"), current.str("provinsi")).filter { it.isNotBlank() }.joinToString(", "))
                    if (current.bool("is_phk")) {
                        InfoRow("TMT PHK", Fmt.dateLong(current.strOrNull("tmt_phk")), valueColor = Sx.Red)
                        InfoRow("Alasan", current.strOrNull("alasan_phk"))
                    }
                }
            } else {
                EmptyState("Belum ada data pekerjaan", "Isi data pekerjaan Anda saat ini.", Icons.Outlined.Business)
            }
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                PrimaryButton(if (current == null) "Isi Data Pekerjaan" else "Perbarui Pekerjaan", { otpFor = if (histories.isEmpty()) "INITIAL_FILL" else "UPDATE_JOB" }, Modifier.weight(1.4f), icon = Icons.Outlined.Work)
                if (current != null && !current.bool("is_phk")) SoftButton("Lapor PHK", { otpFor = "PHK" }, Modifier.weight(1f), icon = Icons.Outlined.WorkOff, tone = Tone.Red, height = 54.dp)
            }
            if (histories.size > 1) {
                SectionTitle("Riwayat Sebelumnya")
                histories.filter { it != current }.forEachIndexed { i, h ->
                    SxCard(Modifier.fillMaxWidth().appear(i + 1), padding = PaddingValues(14.dp)) {
                        Text(h.str("nama_perusahaan", Fmt.title(h.str("tipe_pekerjaan"))), style = MaterialTheme.typography.titleSmall)
                        Text("${Fmt.title(h.str("tipe_pekerjaan"))} • ${h.str("jabatan")} • ${Fmt.date(h.strOrNull("tmt_mulai"))}", style = MaterialTheme.typography.bodySmall)
                    }
                }
            }
        }
    }

    otpFor?.let { action ->
        JobOtpDialog(action, onDismiss = { otpFor = null }) { verifiedFor = action; otpFor = null }
    }
    verifiedFor?.let { action ->
        if (action == "PHK") {
            val form = rememberForm()
            SxDialog(
                onDismiss = { verifiedFor = null }, title = "Lapor PHK", icon = Icons.Outlined.WorkOff, tone = Tone.Red,
                confirmText = "Kirim", confirmColor = Sx.Red, loading = form.submitting,
                onConfirm = {
                    if (!form.require("tmt_phk" to "TMT PHK", "alasan_phk" to "Alasan")) return@SxDialog
                    scope.launch { if (Ui.report(form.submit("/personel/pekerjaan/phk"))) { verifiedFor = null; loader.refresh() } }
                },
            ) {
                Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    DateF(form, "tmt_phk", "TMT PHK", required = true)
                    TextFieldF(form, "alasan_phk", "Alasan PHK", required = true, minLines = 3)
                }
            }
        } else {
            JobFormSheet(onDismiss = { verifiedFor = null }) { form ->
                if (Ui.report(form.submit("/personel/pekerjaan"))) { verifiedFor = null; loader.refresh() }
            }
        }
    }
}

@Composable
private fun JobOtpDialog(action: String, onDismiss: () -> Unit, onVerified: () -> Unit) {
    val scope = rememberCoroutineScope()
    var otp by remember { mutableStateOf("") }
    var info by remember { mutableStateOf<String?>(null) }
    var error by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }
    var cooldown by remember { mutableIntStateOf(0) }
    suspend fun request() {
        busy = true
        when (val r = Api.send("/personel/pekerjaan/otp-request", mapOf("action_type" to action))) {
            is ApiResult.Ok -> { info = r.message ?: "Kode OTP terkirim."; cooldown = 60 }
            is ApiResult.Err -> { info = r.message; Ui.error(r.message) }
        }
        busy = false
    }
    LaunchedEffect(Unit) { request() }
    LaunchedEffect(cooldown) { if (cooldown > 0) { delay(1000); cooldown-- } }
    SxDialog(
        onDismiss = onDismiss, title = "Verifikasi OTP", message = info ?: "Mengirim kode OTP...", icon = Icons.Outlined.Lock,
        confirmText = "Verifikasi", loading = busy,
        onConfirm = {
            if (otp.length != 6) { error = true; return@SxDialog }
            scope.launch {
                busy = true
                when (val r = Api.send("/personel/pekerjaan/otp-verify", mapOf("otp_code" to otp, "action_type" to action))) {
                    is ApiResult.Ok -> { Ui.success("OTP terverifikasi."); onVerified() }
                    is ApiResult.Err -> { error = true; otp = ""; Ui.error(r.message) }
                }
                busy = false
            }
        },
    ) {
        OtpBoxes(otp, { otp = it; error = false }, error = error)
        Spacer(Modifier.height(10.dp))
        Text(
            if (cooldown > 0) "Kirim ulang dalam ${cooldown} detik" else "Kirim ulang kode",
            color = if (cooldown > 0) Sx.Muted else Sx.Blue, fontWeight = FontWeight.Bold, fontSize = 13.sp, textAlign = TextAlign.Center,
            modifier = Modifier.fillMaxWidth().then(if (cooldown == 0) Modifier.pressable({ scope.launch { request() } }) else Modifier),
        )
    }
}


@Composable
private fun JobFormSheet(onDismiss: () -> Unit, onSubmit: suspend (FormState) -> Unit) {
    val scope = rememberCoroutineScope()
    val form = rememberForm("tipe_pekerjaan" to "SWASTA")
    SxSheet(onDismiss) {
        Text("Data Pekerjaan", style = MaterialTheme.typography.headlineSmall)
        Text("Sesi OTP berlaku 15 menit.", style = MaterialTheme.typography.bodySmall)
        Spacer(Modifier.height(16.dp))
        Column(verticalArrangement = Arrangement.spacedBy(14.dp)) {
            ChipsF(form, "tipe_pekerjaan", "Jenis Pekerjaan", JobTypeOpts, required = true)
            val type = form["tipe_pekerjaan"]
            when (type) {
                "TIDAK_BEKERJA" -> {
                    DateF(form, "tmt_phk", "Sejak Tanggal (opsional)")
                    TextFieldF(form, "alasan_phk", "Keterangan (opsional)", minLines = 2)
                }
                else -> {
                    TextFieldF(form, "nama_perusahaan", if (type == "ASN") "Nama Instansi" else if (type == "PELAJAR") "Nama Sekolah / Kampus" else "Nama Perusahaan / Usaha", required = true)
                    TextFieldF(form, "jabatan", if (type == "PELAJAR") "Program / Jurusan" else "Jabatan", required = true)
                    if (type == "ASN") {
                        TextFieldF(form, "nip", "NIP", required = true, keyboardType = KeyboardType.Number)
                        SelectF(form, "asn_jenis", "Jenis ASN", AsnJenisOpts, required = true)
                        DateF(form, "asn_tmt", "TMT ASN", required = true)
                        FileF(form, "asn_sk", "SK ASN (opsional)", Accept.ImageOrPdf, helper = "Maks 2 MB")
                    } else {
                        TextFieldF(form, "nomor_karyawan", "Nomor Karyawan / NIM (opsional)")
                        DateF(form, "tmt_mulai", "TMT Mulai", required = true)
                    }
                    WilayahFields(form, provinceKey = "provinsi", cityKey = "kabupaten", districtKey = "kecamatan", villageKey = null, cityRequired = true)
                    TextFieldF(form, "alamat_lengkap", "Alamat Lengkap", required = true, minLines = 2)
                    TextFieldF(form, "postal_code", "Kode Pos", required = true, keyboardType = KeyboardType.Number, maxLength = 10)
                }
            }
            PrimaryButton("Simpan Pekerjaan", {
                val t = form["tipe_pekerjaan"]
                val ok = when (t) {
                    "TIDAK_BEKERJA" -> true
                    "ASN" -> form.require("nama_perusahaan" to "Instansi", "jabatan" to "Jabatan", "nip" to "NIP", "asn_jenis" to "Jenis ASN", "asn_tmt" to "TMT ASN", "provinsi" to "Provinsi", "kabupaten" to "Kabupaten", "kecamatan" to "Kecamatan", "alamat_lengkap" to "Alamat", "postal_code" to "Kode pos")
                    else -> form.require("nama_perusahaan" to "Nama", "jabatan" to "Jabatan", "tmt_mulai" to "TMT mulai", "provinsi" to "Provinsi", "kabupaten" to "Kabupaten", "kecamatan" to "Kecamatan", "alamat_lengkap" to "Alamat", "postal_code" to "Kode pos")
                }
                if (ok) scope.launch { onSubmit(form) }
            }, Modifier.fillMaxWidth(), loading = form.submitting)
        }
    }
}

// ------------------------------------------------------------------ Pengkinian data

val PengkinianOpts = opts(
    "TNI_AD" to "Diangkat menjadi TNI AD", "TNI_AL" to "Diangkat menjadi TNI AL", "TNI_AU" to "Diangkat menjadi TNI AU",
    "POLRI" to "Diangkat menjadi POLRI", "MENINGGAL" to "Meninggal Dunia",
)

fun pengkinianLabel(code: String?) = PengkinianOpts.firstOrNull { it.value == code }?.label ?: Fmt.title(code)

@Composable
fun MyPengkinianScreen() {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/personel/pengkinian-data")
    var creating by remember { mutableStateOf(false) }
    ShellScaffold(title = "Pengkinian Data", subtitle = "Laporkan perubahan status keanggotaan") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            Notice("Ajukan pengkinian bila Anda diangkat menjadi prajurit TNI/POLRI atau untuk melaporkan personel yang meninggal dunia, disertai dokumen resmi.", Tone.Blue)
            PrimaryButton("Ajukan Pengkinian", { creating = true }, Modifier.fillMaxWidth(), icon = Icons.Outlined.Add)
            val items = data.objects("items")
            if (items.isEmpty()) EmptyState("Belum ada pengajuan", "Riwayat pengajuan pengkinian akan tampil di sini.", Icons.Outlined.UploadFile)
            items.forEachIndexed { i, it ->
                SxCard(Modifier.fillMaxWidth().appear(i), padding = PaddingValues(16.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Column(Modifier.weight(1f)) {
                            Text(pengkinianLabel(it.str("jenis_pengkinian")), style = MaterialTheme.typography.titleSmall)
                            Text("Diajukan ${Fmt.dateTime(it.str("created_at"))}", style = MaterialTheme.typography.bodySmall)
                        }
                        StatusBadge(it.str("status"))
                    }
                    Spacer(Modifier.height(8.dp))
                    it.strOrNull("nrp")?.let { v -> InfoRow("NRP", v) }
                    it.strOrNull("satuan")?.let { v -> InfoRow("Satuan", v) }
                    it.strOrNull("jabatan")?.let { v -> InfoRow("Jabatan", v) }
                    it.strOrNull("tmt_pengangkatan")?.let { v -> InfoRow("TMT Pengangkatan", Fmt.dateLong(v)) }
                    it.strOrNull("rejection_reason")?.let { v -> Notice("Alasan ditolak: $v", Tone.Red) }
                    FileLink("Dokumen pendukung", it.strOrNull("document_path"), Modifier.padding(top = 8.dp))
                }
            }
        }
    }
    if (creating) {
        val form = rememberForm()
        SxSheet({ creating = false }) {
            Text("Ajukan Pengkinian", style = MaterialTheme.typography.headlineSmall)
            Spacer(Modifier.height(16.dp))
            Column(verticalArrangement = Arrangement.spacedBy(14.dp)) {
                SelectF(form, "jenis_pengkinian", "Jenis Pengkinian", PengkinianOpts, required = true)
                if (form["jenis_pengkinian"] != "MENINGGAL" && form["jenis_pengkinian"].isNotBlank()) {
                    TextFieldF(form, "nrp", "NRP Baru")
                    DateF(form, "tmt_pengangkatan", "TMT Pengangkatan")
                    DateF(form, "tmt_masuk_satuan", "TMT Masuk Satuan")
                    TextFieldF(form, "satuan", "Satuan")
                    TextFieldF(form, "jabatan", "Jabatan")
                }
                TextFieldF(form, "catatan", "Catatan", minLines = 3)
                FileF(form, "document", "Dokumen Resmi (SK/Akta)", Accept.ImageOrPdf, required = true, helper = "PDF/JPG maks 4 MB")
                PrimaryButton("Kirim Pengajuan", {
                    if (!form.require("jenis_pengkinian" to "Jenis", "document" to "Dokumen")) return@PrimaryButton
                    scope.launch { if (Ui.report(form.submit("/personel/pengkinian-data"))) { creating = false; loader.refresh() } }
                }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Send)
            }
        }
    }
}

// ------------------------------------------------------------------ Tiket pengaduan

val TicketCategoryOpts = opts("UBAH_FOTO" to "Ubah Pas Foto", "UBAH_DATA" to "Perubahan Biodata", "CETAK_KTA" to "Cetak Ulang KTA")
fun ticketCategory(c: String?) = TicketCategoryOpts.firstOrNull { it.value == c }?.label ?: Fmt.title(c)

@Composable
fun TicketTimeline(t: JsonObject) {
    val logs = t.objects("logs")
    if (logs.isEmpty()) return
    Column(Modifier.padding(top = 8.dp)) {
        logs.forEachIndexed { i, l ->
            Row {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Box(Modifier.size(10.dp).clip(CircleShape).background(id.my.sisfoperskc.app.ui.components.toneOf(l.str("status")).fg))
                    if (i < logs.lastIndex) Box(Modifier.width(2.dp).height(28.dp).background(Sx.Line))
                }
                Spacer(Modifier.width(10.dp))
                Column(Modifier.padding(bottom = 6.dp)) {
                    Text("${id.my.sisfoperskc.app.ui.components.statusLabel(l.str("status"))} • ${Fmt.dateTime(l.str("created_at"))}", fontSize = 12.sp, fontWeight = FontWeight.Bold, color = Sx.Ink2)
                    l.strOrNull("note")?.let { Text(it, fontSize = 12.sp, color = Sx.Muted) }
                }
            }
        }
    }
}

@Composable
fun MyTicketsScreen() {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/personel/tickets")
    var creating by remember { mutableStateOf(false) }
    ShellScaffold(title = "Tiket Pengaduan", subtitle = "Ajukan perubahan data & layanan") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            PrimaryButton("Buat Tiket Baru", { creating = true }, Modifier.fillMaxWidth(), icon = Icons.Outlined.Add)
            val tickets = data.objects("tickets")
            if (tickets.isEmpty()) EmptyState("Belum ada tiket", "Tiket pengaduan yang Anda ajukan akan tampil di sini.", Icons.Outlined.ConfirmationNumber)
            tickets.forEachIndexed { i, t ->
                SxCard(Modifier.fillMaxWidth().appear(i), padding = PaddingValues(16.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        IconBox(Icons.Outlined.ConfirmationNumber, Sx.Red)
                        Spacer(Modifier.width(12.dp))
                        Column(Modifier.weight(1f)) {
                            Text(t.str("ticket_number"), style = MaterialTheme.typography.titleSmall)
                            Text(ticketCategory(t.str("category")), style = MaterialTheme.typography.bodySmall)
                        }
                        StatusBadge(t.str("status"))
                    }
                    t.strOrNull("description")?.let { Text(it, style = MaterialTheme.typography.bodyMedium, modifier = Modifier.padding(top = 10.dp)) }
                    t.strOrNull("rejection_reason")?.let { Notice("Alasan: $it", Tone.Red, modifier = Modifier.padding(top = 8.dp)) }
                    FileLink("Lampiran", t.strOrNull("attachment_path"), Modifier.padding(top = 8.dp))
                    TicketTimeline(t)
                }
            }
        }
    }
    if (creating) {
        val form = rememberForm("category" to "UBAH_DATA")
        SxSheet({ creating = false }) {
            Text("Tiket Baru", style = MaterialTheme.typography.headlineSmall)
            Spacer(Modifier.height(16.dp))
            Column(verticalArrangement = Arrangement.spacedBy(14.dp)) {
                ChipsF(form, "category", "Kategori", TicketCategoryOpts, required = true)
                TextFieldF(form, "description", "Uraian Permohonan", minLines = 4, maxLength = 2000)
                FileF(form, "attachment", "Lampiran Pendukung", Accept.ImageOrPdf, helper = "Foto baru / dokumen pendukung, maks 5 MB")
                PrimaryButton("Kirim Tiket", {
                    scope.launch { if (Ui.report(form.submit("/personel/tickets"))) { creating = false; loader.refresh() } }
                }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Send)
            }
        }
    }
}

// ------------------------------------------------------------------ Formulir & rekrutmen (personel)

@Composable
fun MyFormsScreen() {
    val nav = LocalNav.current
    val loader = rememberPage("/personel/formulir")
    ShellScaffold(title = "Formulir & Rekrutmen", subtitle = "Isi formulir yang ditujukan kepada Anda") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            data.strOrNull("ineligibleError")?.let { Notice(it, Tone.Amber) }
            if (!data.bool("hasCompletedEducation", true)) Notice("Lengkapi riwayat pendidikan terlebih dahulu agar dapat mengisi formulir.", Tone.Amber)
            val forms = data.objects("forms")
            if (forms.isEmpty()) EmptyState("Belum ada formulir", "Formulir & rekrutmen aktif akan tampil di sini.", Icons.Outlined.EditNote)
            forms.forEachIndexed { i, f ->
                SxCard(Modifier.fillMaxWidth().appear(i), onClick = { nav.navigate(Rt.with(Rt.MY_FORM, "uuid" to f.str("uuid"))) }, padding = PaddingValues(16.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        IconBox(Icons.Outlined.EditNote, Sx.Green)
                        Spacer(Modifier.width(12.dp))
                        Column(Modifier.weight(1f)) {
                            Text(f.str("title"), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                            Text("Batas: ${f.str("deadline")}", style = MaterialTheme.typography.bodySmall)
                        }
                    }
                    Spacer(Modifier.height(10.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                        Badge(Fmt.title(f.str("category")), Tone.Green)
                        when {
                            f.bool("is_submitted") -> StatusBadge(f.strOrNull("submission_status") ?: "SUBMITTED")
                            f.bool("is_deadline_passed") -> Badge("Ditutup", Tone.Gray)
                            else -> Badge("Belum diisi", Tone.Amber)
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun MyFormScreen(uuid: String) {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/personel/formulir/$uuid")
    val form = remember { FormState() }
    ShellScaffold(title = "Isi Formulir") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding()).imePadding()) { data ->
            val f = data.obj("form")
            val existing = data.objOrNull("existingResponse")
            val closed = data.bool("isClosed") || data.bool("isDeadlinePassed")
            SxCard(Modifier.fillMaxWidth().appear(0)) {
                Badge(Fmt.title(f.str("category")), Tone.Green)
                Spacer(Modifier.height(8.dp))
                Text(f.str("title"), style = MaterialTheme.typography.headlineSmall)
                f.strOrNull("description")?.let { Text(it, style = MaterialTheme.typography.bodyMedium, modifier = Modifier.padding(top = 6.dp)) }
                Spacer(Modifier.height(8.dp))
                Text("Batas pengisian: ${Fmt.dateTime(f.str("deadline"))}", style = MaterialTheme.typography.bodySmall)
            }
            when {
                existing != null -> FormAnswers(f, existing)
                closed -> Notice("Formulir ini sudah ditutup.", Tone.Gray)
                !data.bool("hasCompletedEducation", true) -> Notice("Lengkapi riwayat pendidikan Anda terlebih dahulu.", Tone.Amber)
                else -> {
                    val reqs = f.objects("requirements")
                    if (reqs.isNotEmpty()) {
                        FormSection("Berkas Persyaratan", icon = Icons.Outlined.UploadFile) {
                            reqs.forEach { r ->
                                val key = "req_" + r.str("id")
                                val types = r.strings("file_types")
                                val accept = when {
                                    types.isEmpty() -> Accept.Any
                                    types.all { it.lowercase() == "pdf" } -> Accept.Pdf
                                    types.all { it.lowercase() in setOf("jpg", "jpeg", "png", "webp") } -> Accept.Image
                                    else -> Accept.ImageOrPdf
                                }
                                FilePickerField(
                                    form.file(key), { form.setFile(key, it) }, r.str("name"), accept, r.bool("required"), form.error(key),
                                    helper = listOfNotNull(r.strOrNull("description"), types.takeIf { it.isNotEmpty() }?.joinToString("/") { it.uppercase() }).joinToString(" • "),
                                )
                            }
                        }
                    }
                    val qs = f.objects("questions")
                    if (qs.isNotEmpty()) {
                        FormSection("Pertanyaan", icon = Icons.Outlined.EditNote) {
                            qs.forEachIndexed { i, q ->
                                val key = "q_" + q.str("id")
                                when (q.str("type")) {
                                    "multiple_choice" -> ChoiceChips(form[key], { form[key] = it }, q.strings("options").map { Opt(it, it) }, "${i + 1}. ${q.str("question")}", q.bool("required"), form.error(key))
                                    "textarea" -> PlainTextField(form[key], { form[key] = it }, label = "${i + 1}. ${q.str("question")}", required = q.bool("required"), error = form.error(key), minLines = 4)
                                    else -> PlainTextField(form[key], { form[key] = it }, label = "${i + 1}. ${q.str("question")}", required = q.bool("required"), error = form.error(key))
                                }
                            }
                        }
                    }
                    PrimaryButton("Kirim Formulir", {
                        val missing = reqs.filter { it.bool("required") && form.file("req_" + it.str("id")) == null }.map { ("req_" + it.str("id")) to it.str("name") } +
                            qs.filter { it.bool("required") && form["q_" + it.str("id")].isBlank() }.map { ("q_" + it.str("id")) to it.str("question") }
                        if (missing.isNotEmpty()) {
                            form.errors = missing.associate { (k, _) -> k to "Wajib diisi." }
                            Ui.error("Masih ada ${missing.size} isian wajib yang belum lengkap.")
                            return@PrimaryButton
                        }
                        scope.launch { if (Ui.report(form.submit("/personel/formulir/$uuid"), "Formulir berhasil dikirim.")) loader.refresh() }
                    }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Send)
                }
            }
        }
    }
}

@Composable
fun FormAnswers(f: JsonObject, response: JsonObject) {
    SxCard(Modifier.fillMaxWidth().appear(1)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Text("Respons Terkirim", style = MaterialTheme.typography.titleMedium, modifier = Modifier.weight(1f))
            StatusBadge(response.str("status"))
        }
        Text(Fmt.dateTime(response.str("submitted_at")), style = MaterialTheme.typography.bodySmall)
        response.strOrNull("verification_notes")?.let { Notice("Catatan verifikator: $it", Tone.Blue, modifier = Modifier.padding(top = 8.dp)) }
        val answers = response.obj("answers")
        val files = response.obj("uploaded_files")
        f.objects("questions").forEach { q ->
            val a = answers[q.str("id")]
            val text = when (a) {
                is JsonObject -> a.str("answer")
                null -> "-"
                else -> a.asStr() ?: "-"
            }
            Spacer(Modifier.height(8.dp))
            Text(q.str("question"), fontSize = 12.sp, color = Sx.Muted, fontWeight = FontWeight.SemiBold)
            Text(text, style = MaterialTheme.typography.bodyMedium)
        }
        f.objects("requirements").forEach { r ->
            val v = files[r.str("id")]
            val path = when (v) {
                is JsonObject -> v.strOrNull("file_path")
                null -> null
                else -> v.asStr()
            }
            FileLink(r.str("name"), path, Modifier.padding(top = 8.dp))
        }
    }
}

// ------------------------------------------------------------------ E-KTA personel

@Composable
fun MyEktaScreen() {
    val loader = rememberPage("/personel/ekta")
    ShellScaffold(title = "E-KTA Saya", subtitle = "Kartu Tanda Anggota Elektronik") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            val p = data.obj("personel")
            KtaCardView(p, p.objOrNull("ekta"), data.objOrNull("ektaSettings"), Modifier.appear(0))
            if (data.bool("is_under_development")) {
                SxCard(Modifier.fillMaxWidth().appear(2), color = Sx.AmberSoft, border = Sx.Amber.copy(0.3f)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        IconBox(Icons.Outlined.Construction, Sx.Amber)
                        Spacer(Modifier.width(12.dp))
                        Text(data.str("development_notice", "Fitur dalam pengembangan"), style = MaterialTheme.typography.titleSmall, color = Sx.AmberInk)
                    }
                    Spacer(Modifier.height(8.dp))
                    Text(data.str("development_description"), style = MaterialTheme.typography.bodyMedium)
                }
            }
        }
    }
}

