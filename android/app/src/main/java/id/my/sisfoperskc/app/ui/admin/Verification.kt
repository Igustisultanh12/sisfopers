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
import androidx.compose.material.icons.outlined.Cake
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.MilitaryTech
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Phone
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material.icons.outlined.Work
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.DocumentTile
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.ReasonDialog
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.filterChips
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.NotificationBell
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch

val MatraFilter = listOf(Opt("", "Semua"), Opt("AD", "TNI AD"), Opt("AL", "TNI AL"), Opt("AU", "TNI AU"))

@Composable
fun VerificationListScreen() {
    val nav = LocalNav.current
    val state = rememberPaged("/admin/verifikasi", "pendaftar")
    ShellScaffold(title = "Konfirmasi Pendaftaran", subtitle = "Verifikasi berkas calon pengguna", topLevel = true, actions = { NotificationBell() }) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama, NIKC, NIK...",
            filterBar = { state.filterChips("matra", MatraFilter) },
            emptyTitle = "Tidak ada pendaftar", emptyMessage = "Semua pendaftaran telah diproses.",
        ) { p, _ ->
            val reg = p.obj("registration")
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { nav.navigate(Rt.with(Rt.VERIF_DETAIL, "uuid" to p.str("uuid"))) }) {
                PersonelRow(p, trailing = { StatusBadge(reg.strOrNull("status_verification") ?: "PENDING") })
                Spacer(Modifier.height(10.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp), verticalAlignment = Alignment.CenterVertically) {
                    MatraBadge(p.str("matra"))
                    Badge("Angkatan ${p.str("angkatan")}", Tone.Gray)
                    Spacer(Modifier.weight(1f))
                    Text(Fmt.relative(p.str("created_at")), style = MaterialTheme.typography.bodySmall)
                }
            }
        }
    }
}

@Composable
fun VerificationDetailScreen(uuid: String) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/admin/verifikasi/$uuid")
    var approve by remember { mutableStateOf(false) }
    var reject by remember { mutableStateOf(false) }
    var upload by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }

    fun decide(status: String, notes: String?) {
        scope.launch {
            busy = true
            val r = Api.send("/admin/verifikasi/$uuid", mapOf("status" to status, "admin_notes" to notes))
            busy = false
            if (Ui.report(r, if (status == "APPROVED") "Pendaftaran disetujui." else "Pendaftaran ditolak.")) {
                approve = false; reject = false
                nav.popBackStack()
            }
        }
    }

    ShellScaffold(title = "Detail Pendaftar", subtitle = "Periksa kesesuaian berkas") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            val p = data.obj("pendaftar")
            val reg = p.obj("registration")
            val user = p.obj("user")
            val status = reg.strOrNull("status_verification") ?: "PENDING"
            SxCard(Modifier.fillMaxWidth().appear(0)) {
                PersonelRow(p, trailing = { StatusBadge(status) })
                Spacer(Modifier.height(10.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    MatraBadge(p.str("matra"))
                    Badge("Angkatan ${p.str("angkatan")}", Tone.Gray)
                    Badge(p.str("sumber_rekrutmen", "Reguler"), Tone.Purple)
                }
                if (reg.strOrNull("admin_notes") != null) {
                    Spacer(Modifier.height(10.dp))
                    Text("Catatan: ${reg.str("admin_notes")}", style = MaterialTheme.typography.bodySmall)
                }
            }
            SectionTitle("Berkas Pendaftaran", Modifier.appear(1), action = "Ganti berkas", onAction = { upload = true })
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp), modifier = Modifier.appear(1)) {
                DocumentTile("Pas Foto", p.strOrNull("photo_profile"), Modifier.weight(1f))
                DocumentTile("KTP", p.strOrNull("ktp_document"), Modifier.weight(1f))
            }
            FileLink("SK ASN", p.strOrNull("asn_sk"))
            FileLink("Berkas SKEP", p.strOrNull("skep_file"))
            SxCard(Modifier.fillMaxWidth().appear(2)) {
                SectionTitle("Data Diri")
                InfoRow("NIKC", p.str("nikc"), icon = Icons.Outlined.Badge)
                InfoRow("NIK", p.str("nik"), icon = Icons.Outlined.Badge)
                InfoRow("Pangkat", p.str("pangkat"), icon = Icons.Outlined.MilitaryTech)
                InfoRow("Jenis Kelamin", if (p.str("gender") == "P") "Perempuan" else "Laki-laki", icon = Icons.Outlined.Person)
                InfoRow("Tempat, Tgl Lahir", "${p.str("pob")}, ${Fmt.dateLong(p.str("dob"))}", icon = Icons.Outlined.Cake)
                InfoRow("Email", user.str("email"), icon = Icons.Outlined.Email)
                InfoRow("Nomor HP", p.str("phone_number"), icon = Icons.Outlined.Phone)
                InfoRow("Alamat", listOf(p.str("address"), p.str("village"), p.str("district"), p.str("city"), p.str("province"), p.str("postal_code")).filter { it.isNotBlank() && it != "-" }.joinToString(", "), icon = Icons.Outlined.Home)
                InfoRow("Akun Aktif", if (user.bool("is_active")) "Ya" else "Belum", icon = Icons.Outlined.VerifiedUser)
            }
            if (p.bool("is_asn")) {
                SxCard(Modifier.fillMaxWidth().appear(3)) {
                    SectionTitle("Data ASN")
                    InfoRow("NIP", p.str("asn_nip"), icon = Icons.Outlined.Badge)
                    InfoRow("Jenis", p.str("asn_jenis"), icon = Icons.Outlined.Work)
                    InfoRow("TMT", Fmt.dateLong(p.str("asn_tmt")), icon = Icons.Outlined.Cake)
                }
            }
            p.objects("job_histories").forEach { j ->
                SxCard(Modifier.fillMaxWidth()) {
                    SectionTitle("Pekerjaan: ${j.str("tipe_pekerjaan")}")
                    InfoRow("Instansi", j.str("nama_perusahaan"), icon = Icons.Outlined.Work)
                    InfoRow("Jabatan", j.str("jabatan"))
                    InfoRow("TMT", Fmt.dateLong(j.str("tmt_mulai")))
                }
            }
            if (status == "PENDING") {
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp), modifier = Modifier.appear(4)) {
                    SoftButton("Tolak", { reject = true }, Modifier.weight(1f), icon = Icons.Outlined.Cancel, tone = Tone.Red, height = 52.dp)
                    PrimaryButton("Setujui", { approve = true }, Modifier.weight(1.3f), icon = Icons.Outlined.CheckCircle, color = Sx.Green)
                }
            }

            if (approve) {
                ReasonDialog(
                    title = "Setujui pendaftaran?", label = "Catatan (opsional)", required = false,
                    message = "Akun ${p.str("full_name")} akan diaktifkan dan notifikasi dikirim.",
                    confirmText = "Setujui", tone = Tone.Green, loading = busy,
                    onDismiss = { approve = false },
                ) { decide("APPROVED", it.ifBlank { null }) }
            }
            if (reject) {
                ReasonDialog(
                    title = "Tolak pendaftaran?", label = "Alasan penolakan", required = true,
                    confirmText = "Tolak", tone = Tone.Red, loading = busy,
                    onDismiss = { reject = false },
                ) { decide("REJECTED", it) }
            }
            if (upload) {
                val form = rememberForm("type" to "photo_profile")
                SxDialog(
                    onDismiss = { upload = false }, title = "Ganti Berkas Pendaftar", icon = Icons.Outlined.UploadFile,
                    confirmText = "Unggah", loading = form.submitting,
                    onConfirm = {
                        if (!form.require("file" to "Berkas")) return@SxDialog
                        scope.launch {
                            if (Ui.report(form.submit("/admin/verifikasi/$uuid/upload-document"), "Berkas diperbarui.")) { upload = false; loader.refresh() }
                        }
                    },
                ) {
                    Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                        ChipsF(form, "type", "Jenis Berkas", opts("photo_profile" to "Pas Foto", "ktp_document" to "KTP"))
                        FileF(form, "file", "Berkas", Accept.ImageOrPdf, required = true, helper = "JPG/PNG/PDF maks 5 MB")
                    }
                }
            }
        }
    }
}

