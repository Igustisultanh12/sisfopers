package id.my.sisfoperskc.app.ui.admin

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.WarningAmber
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
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.Pill
import id.my.sisfoperskc.app.ui.components.ReasonDialog
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.filterChips
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.personel.pengkinianLabel
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

// ------------------------------------------------------------------ Manajemen SKEP

@Composable
fun SkepScreen() {
    val scope = rememberCoroutineScope()
    var tab by remember { mutableStateOf(0) }
    val requests = rememberPaged("/admin/skep", "skepRequests", pageParam = "request_page", searchParam = "search_request")
    val database = rememberPaged("/admin/skep", "skepData", pageParam = "skep_page")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    var deciding by remember { mutableStateOf<Pair<String, JsonObject>?>(null) }
    var deleting by remember { mutableStateOf<JsonObject?>(null) }
    var importing by remember { mutableStateOf(false) }
    var busy by remember { mutableStateOf(false) }

    ShellScaffold(
        title = "Manajemen SKEP", subtitle = "Database NIKC & pengajuan verifikasi",
        actions = { SoftButton("Impor", { importing = true }, icon = Icons.Outlined.UploadFile, height = 40.dp) },
    ) { inner ->
        Column(Modifier.fillMaxSize().padding(top = inner.calculateTopPadding())) {
            Row(Modifier.padding(horizontal = 16.dp, vertical = 4.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                Pill("Pengajuan", tab == 0, { tab = 0 }, count = requests.total)
                Pill("Database SKEP", tab == 1, { tab = 1 }, count = database.total)
            }
            Box(Modifier.weight(1f)) {
                if (tab == 0) {
                    PagedList(requests, searchHint = "Cari pengajuan...", emptyTitle = "Tidak ada pengajuan SKEP") { r, _ ->
                        SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { selected = r }) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                IconBox(Icons.Outlined.Description, Sx.Indigo)
                                Spacer(Modifier.width(12.dp))
                                Column(Modifier.weight(1f)) {
                                    Text(r.str("nama_lengkap"), style = MaterialTheme.typography.titleSmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                                    Text("${r.str("pangkat")} • ${r.str("nikc")}", style = MaterialTheme.typography.bodySmall)
                                }
                                StatusBadge(r.str("status"))
                            }
                            Spacer(Modifier.height(8.dp))
                            Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                                MatraBadge(r.str("matra"))
                                Badge("Angkatan ${r.str("angkatan")}", Tone.Gray)
                                Badge(Fmt.relative(r.str("created_at")), Tone.Gray)
                            }
                        }
                    }
                } else {
                    PagedList(database, searchHint = "Cari nama atau NIKC...", filterBar = { database.filterChips("matra", MatraFilter) }, emptyTitle = "Database SKEP kosong") { r, _ ->
                        SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp)) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Column(Modifier.weight(1f)) {
                                    Text(r.str("nama_lengkap"), style = MaterialTheme.typography.titleSmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                                    Text("${r.str("pangkat")} • NIKC ${r.str("nikc")}", style = MaterialTheme.typography.bodySmall)
                                    Spacer(Modifier.height(6.dp))
                                    Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                                        MatraBadge(r.str("matra"))
                                        Badge("Angkatan ${r.str("angkatan")}", Tone.Gray)
                                    }
                                }
                                SoftButton("", { deleting = r }, icon = Icons.Outlined.Delete, tone = Tone.Red, height = 40.dp)
                            }
                        }
                    }
                }
            }
        }
    }

    selected?.let { r ->
        SxSheet({ selected = null }) {
            Text(r.str("nama_lengkap"), style = MaterialTheme.typography.headlineSmall)
            Spacer(Modifier.height(4.dp))
            StatusBadge(r.str("status"))
            Spacer(Modifier.height(10.dp))
            InfoRow("NIKC", r.str("nikc"))
            InfoRow("NIK", r.strOrNull("nik"))
            InfoRow("Tanggal Lahir", Fmt.dateLong(r.strOrNull("dob")))
            InfoRow("Pangkat", r.str("pangkat"))
            InfoRow("Matra / Angkatan", "${Fmt.matra(r.str("matra"))} / ${r.str("angkatan")}")
            InfoRow("No. WhatsApp", r.strOrNull("phone_number"))
            InfoRow("Diajukan", Fmt.dateTime(r.str("created_at")))
            r.strOrNull("admin_notes")?.let { InfoRow("Catatan", it) }
            FileLink("Berkas SKEP (PDF)", r.strOrNull("skep_file"), Modifier.padding(top = 10.dp))
            if (r.str("status") == "PENDING") {
                Spacer(Modifier.height(14.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    SoftButton("Tolak", { deciding = "REJECTED" to r }, Modifier.weight(1f), icon = Icons.Outlined.Cancel, tone = Tone.Red, height = 50.dp)
                    SoftButton("Setujui", { deciding = "APPROVED" to r }, Modifier.weight(1f), icon = Icons.Outlined.CheckCircle, tone = Tone.Green, height = 50.dp)
                }
            }
        }
    }
    deciding?.let { (status, r) ->
        ReasonDialog(
            title = if (status == "APPROVED") "Setujui pengajuan SKEP?" else "Tolak pengajuan SKEP?",
            label = if (status == "APPROVED") "Catatan (opsional)" else "Alasan penolakan",
            message = if (status == "APPROVED") "NIKC ${r.str("nikc")} akan ditambahkan ke database SKEP." else null,
            required = status == "REJECTED", confirmText = if (status == "APPROVED") "Setujui" else "Tolak",
            tone = if (status == "APPROVED") Tone.Green else Tone.Red, loading = busy,
            onDismiss = { deciding = null },
        ) { notes ->
            scope.launch {
                busy = true
                val res = Api.send("/admin/skep/verify/${r.str("id")}", mapOf("status" to status, "admin_notes" to notes.ifBlank { null }))
                busy = false
                if (Ui.report(res)) { deciding = null; selected = null; requests.reload(silent = true); database.reload(silent = true) }
            }
        }
    }
    deleting?.let { r ->
        SxDialog(
            onDismiss = { deleting = null }, title = "Hapus data SKEP?", message = "${r.str("nama_lengkap")} — ${r.str("nikc")}",
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = "Hapus", confirmColor = Sx.Red, loading = busy,
            onConfirm = {
                scope.launch {
                    busy = true
                    val res = Api.send("/admin/skep/${r.str("id")}", method = "DELETE")
                    busy = false
                    if (Ui.report(res)) { deleting = null; database.reload(silent = true) }
                }
            },
        )
    }
    if (importing) {
        val form = rememberForm()
        SxDialog(
            onDismiss = { importing = false }, title = "Impor Database SKEP", icon = Icons.Outlined.UploadFile,
            message = "Unggah berkas Excel (.xlsx/.xls) sesuai format templat SKEP.", confirmText = "Impor", loading = form.submitting,
            onConfirm = {
                if (!form.require("file" to "Berkas Excel")) return@SxDialog
                scope.launch { if (Ui.report(form.submit("/admin/skep/import"))) { importing = false; database.reload(silent = true) } }
            },
        ) { FileF(form, "file", "Berkas Excel", Accept.Excel, required = true, helper = "Maks 5 MB") }
    }
}

// ------------------------------------------------------------------ Verifikasi pengkinian data

@Composable
fun PengkinianVerifyScreen() {
    val scope = rememberCoroutineScope()
    val state = rememberPaged("/admin/pengkinian-data", "items")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    var rejecting by remember { mutableStateOf<JsonObject?>(null) }
    var busy by remember { mutableStateOf(false) }
    ShellScaffold(title = "Verifikasi Pengkinian", subtitle = "Pengajuan perubahan status personel") { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama atau NIKC...",
            filterBar = {
                state.filterChips("jenis", listOf(Opt("", "Semua"), Opt("TNI_AD", "TNI AD"), Opt("TNI_AL", "TNI AL"), Opt("TNI_AU", "TNI AU"), Opt("POLRI", "POLRI"), Opt("MENINGGAL", "Meninggal")))
            },
            emptyTitle = "Tidak ada pengajuan",
        ) { it, _ ->
            val p = it.obj("personel")
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { selected = it }) {
                PersonelRow(p, trailing = { StatusBadge(it.str("status")) })
                Spacer(Modifier.height(8.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Badge(pengkinianLabel(it.str("jenis_pengkinian")), if (it.str("jenis_pengkinian") == "MENINGGAL") Tone.Red else Tone.Blue)
                    Badge(Fmt.relative(it.str("created_at")), Tone.Gray)
                }
            }
        }
    }
    selected?.let { item ->
        val p = item.obj("personel")
        SxSheet({ selected = null }) {
            PersonelRow(p, trailing = { StatusBadge(item.str("status")) })
            Spacer(Modifier.height(10.dp))
            InfoRow("Jenis", pengkinianLabel(item.str("jenis_pengkinian")))
            InfoRow("NRP", item.strOrNull("nrp"))
            InfoRow("Satuan", item.strOrNull("satuan"))
            InfoRow("Jabatan", item.strOrNull("jabatan"))
            InfoRow("TMT Pengangkatan", Fmt.dateLong(item.strOrNull("tmt_pengangkatan")))
            InfoRow("TMT Masuk Satuan", Fmt.dateLong(item.strOrNull("tmt_masuk_satuan")))
            InfoRow("Catatan", item.strOrNull("catatan"))
            item.strOrNull("rejection_reason")?.let { Notice("Alasan ditolak: $it", Tone.Red) }
            FileLink("Dokumen Pendukung", item.strOrNull("document_path"), Modifier.padding(top = 10.dp))
            if (item.str("status") == "PENDING") {
                Spacer(Modifier.height(14.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    SoftButton("Tolak", { rejecting = item }, Modifier.weight(1f), icon = Icons.Outlined.Cancel, tone = Tone.Red, height = 50.dp)
                    SoftButton("Verifikasi", {
                        scope.launch {
                            busy = true
                            val r = Api.send("/admin/verifikasi-pengkinian/${item.str("id")}/verify")
                            busy = false
                            if (Ui.report(r)) { selected = null; state.reload(silent = true) }
                        }
                    }, Modifier.weight(1f), icon = Icons.Outlined.CheckCircle, tone = Tone.Green, loading = busy, height = 50.dp)
                }
            }
        }
    }
    rejecting?.let { item ->
        ReasonDialog("Tolak pengkinian?", label = "Alasan penolakan", loading = busy, onDismiss = { rejecting = null }) { reason ->
            scope.launch {
                busy = true
                val r = Api.send("/admin/verifikasi-pengkinian/${item.str("id")}/reject", mapOf("reason" to reason))
                busy = false
                if (Ui.report(r)) { rejecting = null; selected = null; state.reload(silent = true) }
            }
        }
    }
}

// ------------------------------------------------------------------ Verifikasi pendidikan

@Composable
fun EducationVerifyScreen() {
    val scope = rememberCoroutineScope()
    val state = rememberPaged("/admin/verifikasi-pendidikan", "items")
    var rejecting by remember { mutableStateOf<JsonObject?>(null) }
    var busy by remember { mutableStateOf(false) }
    ShellScaffold(title = "Verifikasi Pendidikan", subtitle = "Validasi ijazah & sertifikat") { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama, institusi, prodi...",
            filterBar = { state.filterChips("jenis", listOf(Opt("", "Semua"), Opt("AKADEMIK", "Akademik"), Opt("DIKLAT", "Diklat"), Opt("MILITER", "Militer"))) },
            emptyTitle = "Tidak ada pendidikan yang perlu diverifikasi",
        ) { p, _ ->
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp)) {
                PersonelRow(p, trailing = { MatraBadge(p.str("matra")) })
                p.objects("riwayat_pendidikan").forEach { e ->
                    Spacer(Modifier.height(10.dp))
                    SxCard(Modifier.fillMaxWidth(), color = Sx.Bg, padding = PaddingValues(12.dp), radius = 16.dp) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            IconBox(Icons.Outlined.School, Sx.Purple, size = 36.dp, iconSize = 18.dp, radius = 12.dp)
                            Spacer(Modifier.width(10.dp))
                            Column(Modifier.weight(1f)) {
                                Text("${e.str("jenjang")} ${e.str("program_studi")}".trim(), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                                Text("${e.str("nama_institusi")} • ${e.str("tahun_lulus")}", style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                            }
                            Badge(Fmt.title(e.str("jenis")), Tone.Gray)
                        }
                        e.strOrNull("nomor_ijazah")?.let { InfoRow("No. Ijazah", it) }
                        FileLink("Ijazah", e.strOrNull("file_ijazah_path"), Modifier.padding(top = 6.dp))
                        FileLink("Sertifikat", e.strOrNull("file_sertifikat_path"), Modifier.padding(top = 6.dp))
                        Spacer(Modifier.height(8.dp))
                        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            SoftButton("Tolak", { rejecting = e }, Modifier.weight(1f), icon = Icons.Outlined.Cancel, tone = Tone.Red)
                            SoftButton("Verifikasi", {
                                scope.launch { if (Ui.report(Api.send("/admin/pendidikan/${e.str("id")}/verify"))) state.reload(silent = true) }
                            }, Modifier.weight(1f), icon = Icons.Outlined.CheckCircle, tone = Tone.Green)
                        }
                    }
                }
            }
        }
    }
    rejecting?.let { e ->
        ReasonDialog("Tolak pendidikan?", label = "Alasan penolakan", loading = busy, onDismiss = { rejecting = null }) { reason ->
            scope.launch {
                busy = true
                val r = Api.send("/admin/pendidikan/${e.str("id")}/reject", mapOf("reason" to reason))
                busy = false
                if (Ui.report(r)) { rejecting = null; state.reload(silent = true) }
            }
        }
    }
}
