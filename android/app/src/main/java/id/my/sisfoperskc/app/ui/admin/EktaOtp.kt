package id.my.sisfoperskc.app.ui.admin

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Fingerprint
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Password
import androidx.compose.material.icons.outlined.Print
import androidx.compose.material.icons.outlined.Publish
import androidx.compose.material.icons.outlined.Settings
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.auth.OtpBoxes
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.SparkCounter
import id.my.sisfoperskc.app.ui.components.StatRow
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.filterChips
import id.my.sisfoperskc.app.ui.components.rememberDownloader
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.personel.EktaCard
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch

// ------------------------------------------------------------------ E-KTA

@Composable
fun EktaListScreen() {
    val nav = LocalNav.current
    val state = rememberPaged("/admin/ekta", "personels", mapOf("status" to "all"))
    var settings by remember { mutableStateOf(false) }
    ShellScaffold(
        title = "E-KTA Personel", subtitle = "Penerbitan kartu anggota elektronik",
        actions = { SoftButton("Atur", { settings = true }, icon = Icons.Outlined.Settings, height = 40.dp) },
    ) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama, NIKC, pangkat...",
            header = { root ->
                val s = root.obj("stats")
                if (root != null) item(key = "stats") {
                    StatRow {
                        SparkCounter("Total", s.int("total_personel"), Sx.Blue, Modifier.weight(1f))
                        SparkCounter("Terbit", s.int("total_terbit"), Sx.Green, Modifier.weight(1f))
                        SparkCounter("Belum", s.int("total_belum_terbit"), Sx.Amber, Modifier.weight(1f))
                    }
                }
            },
            filterBar = { state.filterChips("status", listOf(Opt("all", "Semua"), Opt("terbit", "Sudah Terbit"), Opt("belum_terbit", "Belum Terbit"))) },
        ) { p, _ ->
            val ekta = p.objOrNull("ekta")
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { nav.navigate(Rt.with(Rt.EKTA_DETAIL, "id" to p.str("id"))) }) {
                PersonelRow(p, trailing = { if (ekta?.str("status") == "TERBIT") Badge("Terbit", Tone.Green) else Badge("Belum", Tone.Amber) })
                if (ekta?.strOrNull("nomor_kta") != null) {
                    Spacer(Modifier.height(8.dp))
                    Text("No. ${ekta.str("nomor_kta")}", style = MaterialTheme.typography.bodySmall)
                }
            }
        }
    }
    if (settings) EktaSettingsDialog(onDismiss = { settings = false }) { settings = false; state.reload(silent = true) }
}

@Composable
private fun EktaSettingsDialog(onDismiss: () -> Unit, onSaved: () -> Unit) {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/admin/ekta")
    val form = rememberForm()
    LaunchedEffect(loader.data) {
        loader.data?.obj("ektaSettings")?.let { s ->
            form.setAll(
                mapOf(
                    "ekta_signer_name" to s.strOrNull("signer_name"), "ekta_signer_rank" to s.strOrNull("signer_rank"),
                    "ekta_signer_title" to s.strOrNull("signer_title"), "ekta_font_family" to s.strOrNull("font_family"), "ekta_font_size" to s.strOrNull("font_size"),
                ),
            )
        }
    }
    SxSheet(onDismiss) {
        Text("Pengaturan E-KTA", style = MaterialTheme.typography.headlineSmall)
        Spacer(Modifier.height(14.dp))
        Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
            TextFieldF(form, "ekta_signer_name", "Nama Pejabat Penandatangan")
            TextFieldF(form, "ekta_signer_rank", "Pangkat Pejabat")
            TextFieldF(form, "ekta_signer_title", "Jabatan Pejabat")
            TextFieldF(form, "ekta_font_family", "Jenis Font")
            TextFieldF(form, "ekta_font_size", "Ukuran Font", placeholder = "11px")
            FileF(form, "ekta_logo_komcad", "Logo Komcad", Accept.Image, existingPath = loader.data?.obj("ektaSettings")?.strOrNull("logo_komcad"))
            FileF(form, "ekta_signature", "Tanda Tangan", Accept.Image, existingPath = loader.data?.obj("ektaSettings")?.strOrNull("signature"))
            FileF(form, "ekta_stamp", "Stempel", Accept.Image, existingPath = loader.data?.obj("ektaSettings")?.strOrNull("stamp"))
            PrimaryButton("Simpan Pengaturan", {
                scope.launch { if (Ui.report(form.submit("/admin/ekta/settings"))) onSaved() }
            }, Modifier.fillMaxWidth(), loading = form.submitting)
        }
    }
}

@Composable
fun EktaDetailScreen(id: String) {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/admin/ekta/$id")
    var otpStage by remember { mutableStateOf(0) } // 0 none, 1 otp dialog, 2 form
    var otp by remember { mutableStateOf("") }
    var otpInfo by remember { mutableStateOf<String?>(null) }
    var otpError by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }
    ShellScaffold(title = "Detail E-KTA") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            val p = data.obj("personel")
            val ekta = p.objOrNull("ekta")
            EktaCard(p, p.str("pangkat"), ekta?.strOrNull("nomor_kta"))
            SxCard(Modifier.fillMaxWidth().appear(1)) {
                Row { SectionTitle("Data Kartu", Modifier.weight(1f)); StatusBadge(ekta?.str("status") ?: "BELUM_TERBIT") }
                InfoRow("Nama", p.str("full_name"))
                InfoRow("NIKC", p.str("nikc"))
                InfoRow("Pangkat", p.str("pangkat"))
                InfoRow("Matra / Angkatan", "${Fmt.matra(p.str("matra"))} / ${p.str("angkatan")}")
                InfoRow("Tempat, Tgl Lahir", "${p.str("pob")}, ${Fmt.dateLong(p.str("dob"))}")
                ekta?.strOrNull("nomor_kta")?.let { InfoRow("Nomor KTA", it) }
                ekta?.strOrNull("published_at")?.let { InfoRow("Diterbitkan", Fmt.dateTime(it)) }
            }
            Notice("Penerbitan E-KTA memerlukan otorisasi OTP yang dikirim ke email resmi administrator.", Tone.Blue, Icons.Outlined.Lock)
            PrimaryButton(if (ekta?.str("status") == "TERBIT") "Terbitkan Ulang E-KTA" else "Terbitkan E-KTA", {
                scope.launch {
                    busy = true
                    val r = Api.send("/admin/ekta/request-otp")
                    busy = false
                    if (r is ApiResult.Ok) { otpInfo = r.message; otp = ""; otpStage = 1 } else Ui.report(r)
                }
            }, Modifier.fillMaxWidth(), loading = busy, icon = Icons.Outlined.Publish)

            if (otpStage == 1) {
                SxDialog(
                    onDismiss = { otpStage = 0 }, title = "Otorisasi OTP", message = otpInfo, icon = Icons.Outlined.Lock,
                    confirmText = "Verifikasi", loading = busy,
                    onConfirm = {
                        scope.launch {
                            busy = true
                            val r = Api.send("/admin/ekta/verify-otp", mapOf("otp" to otp))
                            busy = false
                            if (r is ApiResult.Ok) { otpStage = 2; Ui.success(r.message ?: "OTP sah.") } else { otpError = true; otp = ""; Ui.report(r) }
                        }
                    },
                ) { OtpBoxes(otp, { otp = it; otpError = false }, error = otpError) }
            }
            if (otpStage == 2) {
                val s = p.objOrNull("sinyalmen")
                val form = rememberForm(
                    "tahun_lulus" to p.str("angkatan"), "pangkat" to p.str("pangkat"), "kesatuan_matra" to Fmt.matra(p.str("matra")),
                    "tinggi_berat" to s?.let { "${it.str("tinggi_badan")} cm / ${it.str("berat_badan")} kg" },
                    "rambut" to s?.strOrNull("rambut"), "mata" to s?.strOrNull("mata"), "golongan_darah" to s?.strOrNull("golongan_darah"),
                    "tempat_lahir" to p.strOrNull("pob"), "tanggal_lahir" to Fmt.dateLong(p.strOrNull("dob")), "alamat" to p.strOrNull("address"),
                )
                SxSheet({ otpStage = 0 }) {
                    Text("Konfirmasi Data E-KTA", style = MaterialTheme.typography.headlineSmall)
                    Spacer(Modifier.height(14.dp))
                    Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            TextFieldF(form, "nomor_urut", "Nomor Urut", Modifier.weight(1f))
                            TextFieldF(form, "tahun_lulus", "Tahun", Modifier.weight(1f))
                        }
                        TextFieldF(form, "pangkat", "Pangkat")
                        TextFieldF(form, "jabatan", "Jabatan")
                        TextFieldF(form, "kesatuan_matra", "Kesatuan / Matra")
                        TextFieldF(form, "berlaku_sampai", "Berlaku Sampai", placeholder = "Seumur hidup")
                        TextFieldF(form, "tinggi_berat", "Tinggi / Berat")
                        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            TextFieldF(form, "rambut", "Rambut", Modifier.weight(1f))
                            TextFieldF(form, "mata", "Mata", Modifier.weight(1f))
                            TextFieldF(form, "golongan_darah", "Gol. Darah", Modifier.weight(0.8f))
                        }
                        TextFieldF(form, "tempat_lahir", "Tempat Lahir")
                        TextFieldF(form, "tanggal_lahir", "Tanggal Lahir")
                        TextFieldF(form, "agama", "Agama")
                        TextFieldF(form, "alamat", "Alamat", minLines = 2)
                        TextFieldF(form, "tanda_kehormatan", "Tanda Kehormatan", minLines = 2)
                        PrimaryButton("Terbitkan Sekarang", {
                            scope.launch { if (Ui.report(form.submit("/admin/ekta/$id/publish"))) { otpStage = 0; loader.refresh() } }
                        }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.VerifiedUser)
                    }
                }
            }
        }
    }
}

// ------------------------------------------------------------------ OTP manual & OTP reset password

@Composable
fun OtpManualScreen(reset: Boolean) {
    val scope = rememberCoroutineScope()
    val download = rememberDownloader()
    val base = if (reset) "/admin/otp-reset-password" else "/admin/otp-manual"
    val state = rememberPaged(base, "personels")
    var confirmAll by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }
    ShellScaffold(
        title = if (reset) "OTP Reset Password" else "OTP Manual",
        subtitle = if (reset) "Kode reset kata sandi personel" else "Kode verifikasi akun personel",
    ) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama atau NIKC...",
            header = {
                item(key = "actions") {
                    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        Notice(
                            if (reset) "Generate OTP reset lalu cetak dan serahkan kepada personel. Personel memakai menu \"Lupa kata sandi\" di aplikasi."
                            else "OTP manual dipakai personel yang tidak menerima OTP WhatsApp/email. Berlaku 72 jam.",
                            Tone.Blue, if (reset) Icons.Outlined.Password else Icons.Outlined.Fingerprint,
                        )
                        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            SoftButton("Generate Semua", { confirmAll = true }, Modifier.weight(1f), icon = Icons.Outlined.Fingerprint)
                            SoftButton("Cetak Massal", { download("$base/bulk-pdf", if (reset) "OTP_Reset_Massal.pdf" else "OTP_Massal.pdf") }, Modifier.weight(1f), icon = Icons.Outlined.Print, tone = Tone.Green)
                        }
                    }
                }
            },
        ) { p, _ ->
            val code = if (reset) p.strOrNull("reset_password_otp") else p.strOrNull("manual_otp")
            val exp = if (reset) p.strOrNull("reset_password_otp_expired_at") else p.strOrNull("manual_otp_expired_at")
            val printed = if (reset) p.strOrNull("reset_password_otp_printed_at") else p.strOrNull("manual_otp_printed_at")
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp)) {
                PersonelRow(p, trailing = { MatraBadge(p.str("matra")) })
                Spacer(Modifier.height(10.dp))
                if (code != null) {
                    Row {
                        Column(Modifier.weight(1f)) {
                            Text("Kode OTP", style = MaterialTheme.typography.bodySmall)
                            Text(code, style = MaterialTheme.typography.headlineSmall.copy(letterSpacing = 4.sp), color = Sx.BlueDeep)
                        }
                        Column(horizontalAlignment = androidx.compose.ui.Alignment.End) {
                            Text("Berlaku s.d.", style = MaterialTheme.typography.bodySmall)
                            Text(Fmt.dateTime(exp), style = MaterialTheme.typography.labelMedium)
                            if (printed != null) Badge("Sudah dicetak", Tone.Gray)
                        }
                    }
                    Spacer(Modifier.height(10.dp))
                }
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    SoftButton(if (code == null) "Generate OTP" else "Generate Ulang", {
                        scope.launch { if (Ui.report(Api.send("$base/${p.str("id")}/generate"))) state.reload(silent = true) }
                    }, Modifier.weight(1f), icon = Icons.Outlined.Fingerprint)
                    if (code != null) SoftButton("Cetak PDF", {
                        download("$base/${p.str("id")}/pdf", "OTP_${p.str("full_name").replace(' ', '_')}.pdf")
                    }, Modifier.weight(1f), icon = Icons.Outlined.Print, tone = Tone.Green)
                }
                if (!reset && p.bool("face_verified")) Text("Akun sudah terverifikasi.", style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(top = 6.dp))
            }
        }
    }
    if (confirmAll) {
        SxDialog(
            onDismiss = { confirmAll = false }, title = "Generate OTP massal?", message = "OTP baru akan dibuat untuk seluruh personel dalam daftar.",
            icon = Icons.Outlined.Badge, confirmText = "Generate", loading = busy,
            onConfirm = {
                scope.launch {
                    busy = true
                    val r = Api.send("$base/generate-all")
                    busy = false
                    if (Ui.report(r)) { confirmAll = false; state.reload(silent = true) }
                }
            },
        )
    }
}
