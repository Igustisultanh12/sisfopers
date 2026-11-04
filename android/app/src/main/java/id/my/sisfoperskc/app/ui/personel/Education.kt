package id.my.sisfoperskc.app.ui.personel

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.School
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
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.EmptyState
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.ReasonDialog
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

fun jenjangFor(jenis: String): List<String> = when (jenis) {
    "MILITER" -> listOf("Letnan Dua Perwira Komcad", "Sersan Dua Bintara Komcad", "Prajurit dua Tamtama Komcad", "Lain")
    "DIKLAT" -> listOf("Sertifikasi", "Kursus", "Pelatihan", "Penataran", "Lain")
    else -> listOf("SD", "SMP", "SMA", "D3", "D4", "S1", "S2", "S3", "PROFESI", "LAIN")
}

/** Pengelola riwayat pendidikan (dipakai personel dan admin). */
@Composable
fun EducationManager(path: String, storePath: String, itemPrefix: String, canVerify: Boolean) {
    val scope = rememberCoroutineScope()
    val loader = rememberPage(path)
    var editing by remember { mutableStateOf<JsonObject?>(null) }
    var creating by remember { mutableStateOf(false) }
    var deleting by remember { mutableStateOf<JsonObject?>(null) }
    var rejecting by remember { mutableStateOf<JsonObject?>(null) }
    var busy by remember { mutableStateOf(false) }

    PageContent(loader) { data ->
        val p = data.objects("items")
        Notice("Pendidikan yang ditambah atau diubah akan menunggu verifikasi admin/koordinator.", Tone.Blue)
        PrimaryButton("Tambah Riwayat Pendidikan", { creating = true }, Modifier.fillMaxWidth(), icon = Icons.Outlined.Add)
        if (p.isEmpty()) EmptyState("Belum ada riwayat pendidikan", "Tambahkan pendidikan akademik, diklat, atau militer Anda.", Icons.Outlined.School)
        p.forEachIndexed { i, e ->
            val verified = e.strOrNull("verified_at") != null
            SxCard(Modifier.fillMaxWidth().appear(i), padding = PaddingValues(16.dp)) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.School, if (e.str("jenis") == "MILITER") Sx.Green else if (e.str("jenis") == "DIKLAT") Sx.Amber else Sx.Purple)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text("${e.str("jenjang")} ${e.str("program_studi")}".trim(), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        Text(e.str("nama_institusi", "-"), style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    }
                    Badge(if (verified) "Terverifikasi" else "Menunggu", if (verified) Tone.Green else Tone.Amber)
                }
                Spacer(Modifier.height(8.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Badge(Fmt.title(e.str("jenis")), Tone.Gray)
                    if (e.str("tahun_lulus").isNotBlank()) Badge("Lulus ${e.str("tahun_lulus")}", Tone.Blue)
                    e.strOrNull("suffix_gelar")?.let { Badge(it, Tone.Purple) }
                }
                FileLink("Ijazah", e.strOrNull("file_ijazah_path"), Modifier.padding(top = 10.dp))
                FileLink("Sertifikat", e.strOrNull("file_sertifikat_path"), Modifier.padding(top = 6.dp))
                Spacer(Modifier.height(10.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    SoftButton("Ubah", { editing = e }, Modifier.weight(1f), icon = Icons.Outlined.Edit)
                    SoftButton("Hapus", { deleting = e }, Modifier.weight(1f), icon = Icons.Outlined.Delete, tone = Tone.Red)
                }
                if (canVerify && !verified) {
                    Spacer(Modifier.height(8.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        SoftButton("Tolak", { rejecting = e }, Modifier.weight(1f), icon = Icons.Outlined.Cancel, tone = Tone.Red)
                        SoftButton("Verifikasi", {
                            scope.launch {
                                if (Ui.report(Api.send("/admin/pendidikan/${e.str("id")}/verify"), "Pendidikan diverifikasi.")) loader.refresh()
                            }
                        }, Modifier.weight(1f), icon = Icons.Outlined.CheckCircle, tone = Tone.Green)
                    }
                }
            }
        }
    }

    if (creating || editing != null) {
        EducationForm(editing, onDismiss = { creating = false; editing = null }) { form ->
            val e = editing
            val r = form.submit(if (e == null) storePath else "$itemPrefix/${e.str("id")}", method = if (e == null) "POST" else "PUT")
            if (Ui.report(r)) { creating = false; editing = null; loader.refresh() }
        }
    }
    deleting?.let { e ->
        SxDialog(
            onDismiss = { deleting = null }, title = "Hapus riwayat pendidikan?", message = "${e.str("jenjang")} — ${e.str("nama_institusi")}",
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = "Hapus", confirmColor = Sx.Red, loading = busy,
            onConfirm = {
                scope.launch {
                    busy = true
                    val r = Api.send("$itemPrefix/${e.str("id")}", method = "DELETE")
                    busy = false
                    if (Ui.report(r)) { deleting = null; loader.refresh() }
                }
            },
        )
    }
    rejecting?.let { e ->
        ReasonDialog("Tolak pendidikan?", label = "Alasan penolakan", loading = busy, onDismiss = { rejecting = null }) { reason ->
            scope.launch {
                busy = true
                val r = Api.send("/admin/pendidikan/${e.str("id")}/reject", mapOf("reason" to reason))
                busy = false
                if (Ui.report(r)) { rejecting = null; loader.refresh() }
            }
        }
    }
}

@Composable
private fun EducationForm(item: JsonObject?, onDismiss: () -> Unit, onSubmit: suspend (id.my.sisfoperskc.app.ui.components.FormState) -> Unit) {
    val scope = rememberCoroutineScope()
    val form = rememberForm(
        "jenis" to (item?.str("jenis") ?: "AKADEMIK"),
        "jenjang" to item?.str("jenjang"),
        "program_studi" to item?.str("program_studi"),
        "nama_institusi" to item?.str("nama_institusi"),
        "tahun_lulus" to item?.str("tahun_lulus"),
        "nomor_ijazah" to item?.str("nomor_ijazah"),
        "front_title" to item?.str("front_title"),
        "suffix_gelar" to item?.str("suffix_gelar"),
    )
    SxSheet(onDismiss) {
        Text(if (item == null) "Tambah Pendidikan" else "Ubah Pendidikan", style = MaterialTheme.typography.headlineSmall)
        Spacer(Modifier.height(16.dp))
        Column(verticalArrangement = Arrangement.spacedBy(14.dp)) {
            ChipsF(form, "jenis", "Jenis Pendidikan", opts("AKADEMIK" to "Akademik", "DIKLAT" to "Diklat", "MILITER" to "Militer"), required = true, onChanged = { form["jenjang"] = "" })
            SelectF(form, "jenjang", "Jenjang", jenjangFor(form["jenis"]).map { Opt(it, it) }, required = true)
            TextFieldF(form, "program_studi", if (form["jenis"] == "AKADEMIK") "Program Studi / Jurusan" else "Nama Program")
            TextFieldF(form, "nama_institusi", "Nama Institusi / Penyelenggara")
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                TextFieldF(form, "tahun_lulus", "Tahun Lulus", Modifier.weight(1f), keyboardType = KeyboardType.Number, maxLength = 4)
                TextFieldF(form, "nomor_ijazah", "No. Ijazah/Sertifikat", Modifier.weight(1.3f))
            }
            if (form["jenis"] == "AKADEMIK") {
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    TextFieldF(form, "front_title", "Gelar Depan", Modifier.weight(1f), placeholder = "Dr.")
                    TextFieldF(form, "suffix_gelar", "Gelar Belakang", Modifier.weight(1f), placeholder = "S.Kom.")
                }
            }
            FileF(form, "file_ijazah", "Berkas Ijazah", Accept.ImageOrPdf, helper = "PDF/JPG maks 4 MB", existingPath = item?.strOrNull("file_ijazah_path"))
            FileF(form, "file_sertifikat", "Berkas Sertifikat", Accept.ImageOrPdf, helper = "Opsional, PDF/JPG maks 4 MB", existingPath = item?.strOrNull("file_sertifikat_path"))
            PrimaryButton("Simpan", {
                if (!form.require("jenis" to "Jenis", "jenjang" to "Jenjang")) return@PrimaryButton
                scope.launch { onSubmit(form) }
            }, Modifier.fillMaxWidth(), loading = form.submitting)
        }
    }
}

@Composable
fun MyEducationScreen() {
    ShellScaffold(title = "Riwayat Pendidikan", subtitle = "Ijazah & sertifikat Anda") { inner ->
        Column(Modifier.padding(top = inner.calculateTopPadding())) {
            EducationManager("/personel/pendidikan", "/personel/pendidikan", "/personel/pendidikan", canVerify = false)
        }
    }
}

