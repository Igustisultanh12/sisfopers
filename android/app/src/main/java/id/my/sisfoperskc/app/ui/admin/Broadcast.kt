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
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.Groups
import androidx.compose.material.icons.outlined.PictureAsPdf
import androidx.compose.material.icons.outlined.Send
import androidx.compose.material.icons.outlined.TableChart
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
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.AttendanceSummary
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.FilterRow
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.ProgressBar
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.SparkCounter
import id.my.sisfoperskc.app.ui.components.StatRow
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.TimeField
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberDownloader
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.personel.BroadcastHeader
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

val BroadcastCategoryOpts = opts("apel" to "Apel", "latihan" to "Latihan", "mobilisasi" to "Mobilisasi", "pengumuman" to "Pengumuman", "Tugas" to "Tugas")

@Composable
fun BroadcastListScreen() {
    val nav = LocalNav.current
    val me by Session.me.collectAsState()
    val group = me?.roleGroup ?: "admin"
    val path = when (group) {
        "kordinator" -> "/kordinator/broadcast"
        "pju" -> "/pju/broadcast"
        else -> "/admin/broadcast"
    }
    val state = rememberPaged(path, "broadcasts")
    val canCreate = when (group) {
        "pju" -> state.root?.bool("canCreate") == true
        else -> true
    }
    ShellScaffold(
        title = "Broadcast Kegiatan",
        subtitle = when (group) {
            "kordinator" -> if (me?.role == "kordinator_matra") "Instruksi untuk ${Fmt.matraLong(me?.matra)}" else "Instruksi untuk angkatan Anda"
            "pju" -> "Kegiatan jajaran"
            else -> "Instruksi & kegiatan seluruh personel"
        },
        fab = if (canCreate) ({
            FloatingActionButton(onClick = { nav.navigate(Rt.BROADCAST_NEW) }, containerColor = Sx.Blue, contentColor = Color.White) { Icon(Icons.Outlined.Add, "Buat broadcast") }
        }) else null,
    ) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari judul kegiatan...",
            emptyTitle = "Belum ada broadcast", emptyMessage = if (canCreate) "Buat broadcast kegiatan pertama melalui tombol +." else "Broadcast kegiatan akan tampil di sini.",
        ) { b, _ ->
            BroadcastCard(b, onClick = if (group == "admin") ({ nav.navigate(Rt.with(Rt.BROADCAST_DETAIL, "uuid" to b.str("uuid"))) }) else null)
        }
    }
}

@Composable
private fun BroadcastCard(b: JsonObject, onClick: (() -> Unit)?) {
    val targets = b.int("targets_count")
    val responses = if (b.containsKey("responses_count")) b.int("responses_count") else b.objects("responses").size
    val past = Fmt.isPast(b.str("deadline"))
    SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(16.dp), onClick = onClick) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(Icons.Outlined.Campaign, Sx.Blue)
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(b.str("title"), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                Text("${Fmt.dayDate(b.str("event_date"))} • ${b.str("event_time")}", style = MaterialTheme.typography.bodySmall, maxLines = 1)
            }
            Badge(if (past) "Selesai" else "Aktif", if (past) Tone.Gray else Tone.Green)
        }
        Spacer(Modifier.height(10.dp))
        Row(horizontalArrangement = Arrangement.spacedBy(6.dp), verticalAlignment = Alignment.CenterVertically) {
            Badge(Fmt.category(b.str("category")), Tone.Blue)
            MatraBadge(b.strOrNull("matra") ?: b.strOrNull("target_value") ?: "ALL")
            b.objOrNull("creator")?.strOrNull("username")?.let { Badge("oleh $it", Tone.Gray) }
        }
        if (targets > 0) {
            Spacer(Modifier.height(10.dp))
            Row { Text("Respons $responses dari $targets personel", style = MaterialTheme.typography.bodySmall, modifier = Modifier.weight(1f)); Text("${responses * 100 / targets}%", style = MaterialTheme.typography.labelMedium) }
            Spacer(Modifier.height(4.dp))
            ProgressBar(responses.toFloat() / targets, Sx.Green)
        }
    }
}

@Composable
fun BroadcastFormScreen(uuid: String?) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val me by Session.me.collectAsState()
    val group = me?.roleGroup ?: "admin"
    val isEdit = uuid != null
    val form = rememberForm("category" to "apel", "target_type" to "ALL")
    var ready by remember { mutableStateOf(!isEdit) }
    val loader = if (isEdit) rememberPage("/admin/broadcast/$uuid/edit") else null

    LaunchedEffect(loader?.data) {
        val b = loader?.data?.obj("broadcast") ?: return@LaunchedEffect
        form.setAll(
            mapOf(
                "title" to b.str("title"), "category" to b.str("category"), "event_date" to Fmt.isoDate(b.str("event_date")),
                "event_time" to b.str("event_time").take(5), "location" to b.str("location"), "description" to b.str("description"),
                "deadline" to Fmt.isoDateTimeLocal(b.str("deadline")), "target_type" to b.str("target_type", "ALL"), "target_value" to b.strOrNull("target_value"),
            ),
        )
        ready = true
    }

    ShellScaffold(title = if (isEdit) "Ubah Broadcast" else "Buat Broadcast", subtitle = if (group == "pju") "Broadcast jajaran PJU" else "Instruksi kegiatan personel") { inner ->
        Column(
            Modifier.padding(top = inner.calculateTopPadding()).imePadding().verticalScroll(rememberScrollState()).padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            if (!ready) { Notice("Memuat data broadcast...", Tone.Gray); return@Column }
            if (group == "pju") {
                FormSection("Isi Broadcast", icon = Icons.Outlined.Campaign) {
                    TextFieldF(form, "title", "Judul", required = true)
                    SelectF(form, "category", "Kategori", BroadcastCategoryOpts, required = true)
                    DateF(form, "event_date", "Tanggal Kegiatan")
                    TextFieldF(form, "content", "Isi Pesan", required = true, minLines = 5)
                }
            } else {
                FormSection("Detail Kegiatan", icon = Icons.Outlined.Campaign) {
                    TextFieldF(form, "title", "Judul Kegiatan", required = true)
                    ChipsF(form, "category", "Kategori", BroadcastCategoryOpts, required = true)
                    DateF(form, "event_date", "Tanggal Kegiatan", required = true)
                    TimeField(form["event_time"], { form["event_time"] = it }, "Jam Kegiatan", required = true, error = form.error("event_time"))
                    TextFieldF(form, "location", "Lokasi", required = true)
                    TextFieldF(form, "description", "Uraian / Instruksi", required = true, minLines = 5)
                    DateF(form, "deadline", "Batas Konfirmasi Kehadiran", required = true, withTime = true)
                }
                if (group == "admin") {
                    FormSection("Sasaran Penerima", icon = Icons.Outlined.Groups) {
                        ChipsF(form, "target_type", "Jenis Sasaran", opts("ALL" to "Seluruh Personel", "MATRA" to "Per Matra", "ANGKATAN" to "Per Angkatan"), required = true, onChanged = { form["target_value"] = "" })
                        when (form["target_type"]) {
                            "MATRA" -> SelectF(form, "target_value", "Matra", opts("AD" to "TNI AD", "AL" to "TNI AL", "AU" to "TNI AU"), required = true)
                            "ANGKATAN" -> TextFieldF(form, "target_value", "Tahun Angkatan", required = true, keyboardType = KeyboardType.Number, maxLength = 4)
                        }
                    }
                } else {
                    Notice("Broadcast otomatis dikirim ke personel jajaran Anda dan diberitahukan via notifikasi aplikasi.", Tone.Blue)
                }
            }
            PrimaryButton(if (isEdit) "Simpan Perubahan" else "Kirim Broadcast", {
                val ok = if (group == "pju") form.require("title" to "Judul", "category" to "Kategori", "content" to "Isi pesan")
                else form.require("title" to "Judul", "category" to "Kategori", "event_date" to "Tanggal", "event_time" to "Jam", "location" to "Lokasi", "description" to "Uraian", "deadline" to "Batas konfirmasi")
                if (!ok) return@PrimaryButton
                scope.launch {
                    val path = when {
                        isEdit -> "/admin/broadcast/$uuid"
                        group == "kordinator" -> "/kordinator/broadcast"
                        group == "pju" -> "/pju/broadcast"
                        else -> "/admin/broadcast"
                    }
                    val r = form.submit(path, method = if (isEdit) "PUT" else "POST")
                    if (Ui.report(r, "Broadcast berhasil dikirim.")) nav.popBackStack()
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Send)
            Spacer(Modifier.height(40.dp))
        }
    }
}

@Composable
fun BroadcastDetailScreen(uuid: String) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val download = rememberDownloader()
    val loader = rememberPage("/admin/broadcast/$uuid")
    var filter by remember { mutableStateOf("") }
    var confirmDelete by remember { mutableStateOf(false) }
    var deleting by remember { mutableStateOf(false) }
    ShellScaffold(title = "Detail Broadcast", subtitle = "Rekap kehadiran personel") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            val b = data.obj("broadcast")
            val st = data.obj("stats")
            BroadcastHeader(b)
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp), modifier = Modifier.appear(1)) {
                SoftButton("Ubah", { nav.navigate(Rt.with(Rt.BROADCAST_EDIT, "uuid" to uuid)) }, Modifier.weight(1f), icon = Icons.Outlined.Edit)
                SoftButton("Hapus", { confirmDelete = true }, Modifier.weight(1f), icon = Icons.Outlined.Delete, tone = Tone.Red)
            }
            StatRow {
                SparkCounter("Sasaran", st.int("total"), Sx.Blue, Modifier.weight(1f))
                SparkCounter("Hadir", st.int("hadir"), Sx.Green, Modifier.weight(1f))
                SparkCounter("Izin", st.int("izin"), Sx.Amber, Modifier.weight(1f))
                SparkCounter("Tidak", st.int("tidak_hadir"), Sx.Red, Modifier.weight(1f))
            }
            SxCard(Modifier.fillMaxWidth().appear(2)) {
                SectionTitle("Ringkasan Kehadiran")
                Spacer(Modifier.height(12.dp))
                AttendanceSummary(st.int("hadir"), st.int("izin"), st.int("tidak_hadir"), st.int("total"))
            }
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp), modifier = Modifier.appear(3)) {
                SoftButton("Laporan PDF", { download("/admin/laporan/broadcast/$uuid/pdf", "Laporan_Broadcast.pdf") }, Modifier.weight(1f), icon = Icons.Outlined.PictureAsPdf, tone = Tone.Red)
                SoftButton("Laporan Excel", { download("/admin/laporan/broadcast/$uuid/excel", "Laporan_Broadcast.xlsx") }, Modifier.weight(1f), icon = Icons.Outlined.TableChart, tone = Tone.Green)
            }
            SxCard(Modifier.fillMaxWidth().appear(4)) {
                SectionTitle("Uraian")
                Spacer(Modifier.height(6.dp))
                Text(b.str("description"), style = MaterialTheme.typography.bodyMedium)
            }
            SectionTitle("Respons Personel", Modifier.appear(5))
            FilterRow(listOf(Opt("", "Semua"), Opt("HADIR", "Hadir"), Opt("IZIN", "Izin"), Opt("TIDAK_HADIR", "Tidak Hadir")), filter) { filter = it }
            val responses = b.objects("responses").filter { filter.isBlank() || it.str("status_attendance") == filter }
            if (responses.isEmpty()) Notice("Belum ada respons.", Tone.Gray)
            responses.forEachIndexed { i, r ->
                SxCard(Modifier.fillMaxWidth().appear(6 + i % 8), padding = PaddingValues(14.dp)) {
                    PersonelRow(r.obj("personel"), trailing = { StatusBadge(r.str("status_attendance")) })
                    r.strOrNull("notes")?.let { Text(it, style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(top = 8.dp)) }
                    Text(Fmt.dateTime(r.str("responded_at")), style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(top = 4.dp))
                }
            }
        }
    }
    if (confirmDelete) {
        SxDialog(
            onDismiss = { confirmDelete = false }, title = "Hapus broadcast?", message = "Broadcast dan seluruh respons kehadiran akan dihapus.",
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = "Hapus", confirmColor = Sx.Red, loading = deleting,
            onConfirm = {
                scope.launch {
                    deleting = true
                    val r = Api.send("/admin/broadcast/$uuid", method = "DELETE")
                    deleting = false
                    if (Ui.report(r, "Broadcast dihapus.")) { confirmDelete = false; nav.popBackStack() }
                }
            },
        )
    }
}
