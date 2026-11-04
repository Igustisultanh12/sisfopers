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
import androidx.compose.material.icons.outlined.Assignment
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.QuestionAnswer
import androidx.compose.material.icons.outlined.Save
import androidx.compose.material.icons.outlined.ToggleOn
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.data.strings
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.ChoiceChips
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.FilterRow
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.ReasonDialog
import id.my.sisfoperskc.app.ui.components.SheetAction
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.SparkCounter
import id.my.sisfoperskc.app.ui.components.StatRow
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SwitchRow
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.filterChips
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.personel.FormAnswers
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

@Composable
fun formsBase(): String {
    val me by Session.me.collectAsState()
    return when (me?.roleGroup) {
        "kordinator" -> "/kordinator/formulir"
        "pju" -> "/pju/formulir"
        else -> "/admin/formulir"
    }
}

@Composable
fun FormListScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val base = formsBase()
    val state = rememberPaged(base, "forms")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    var deleting by remember { mutableStateOf<JsonObject?>(null) }
    ShellScaffold(
        title = "Formulir & Rekrutmen", subtitle = "Formulir dinamis untuk personel",
        fab = { FloatingActionButton(onClick = { nav.navigate(Rt.FORM_NEW) }, containerColor = Sx.Blue, contentColor = Color.White) { Icon(Icons.Outlined.Add, "Buat formulir") } },
    ) { inner ->
        PagedList(state, Modifier.padding(top = inner.calculateTopPadding()), searchHint = "Cari judul formulir...", emptyTitle = "Belum ada formulir") { f, _ ->
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(16.dp), onClick = { selected = f }) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.EditNote, if (f.bool("is_active")) Sx.Green else Sx.Muted)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(f.str("title"), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        Text("Batas ${Fmt.dateTime(f.strOrNull("deadline"), "tanpa batas")}", style = MaterialTheme.typography.bodySmall)
                    }
                    Badge(if (f.bool("is_active")) "Aktif" else "Nonaktif", if (f.bool("is_active")) Tone.Green else Tone.Gray)
                }
                Spacer(Modifier.height(10.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    Badge(Fmt.title(f.str("category")), Tone.Blue)
                    Badge("${f.int("responses_count")} respons", Tone.Purple)
                    Badge("${f.objects("questions").size} pertanyaan", Tone.Gray)
                }
            }
        }
    }
    selected?.let { f ->
        SxSheet({ selected = null }) {
            Text(f.str("title"), style = MaterialTheme.typography.titleLarge)
            f.strOrNull("description")?.let { Text(it, style = MaterialTheme.typography.bodySmall) }
            Spacer(Modifier.height(10.dp))
            SheetAction(Icons.Outlined.Assignment, "Lihat Respons (${f.int("responses_count")})", Tone.Purple) { selected = null; nav.navigate(Rt.with(Rt.FORM_RESPONSES, "uuid" to f.str("uuid"))) }
            SheetAction(Icons.Outlined.Edit, "Ubah Formulir") { selected = null; nav.navigate(Rt.with(Rt.FORM_EDIT, "uuid" to f.str("uuid"))) }
            SheetAction(Icons.Outlined.ToggleOn, if (f.bool("is_active")) "Nonaktifkan" else "Aktifkan", Tone.Amber) {
                selected = null
                scope.launch { if (Ui.report(Api.send("$base/${f.str("uuid")}/toggle-status", method = "PATCH"))) state.reload(silent = true) }
            }
            SheetAction(Icons.Outlined.Delete, "Hapus Formulir", Tone.Red) { selected = null; deleting = f }
        }
    }
    deleting?.let { f ->
        SxDialog(
            onDismiss = { deleting = null }, title = "Hapus formulir?", message = f.str("title"), icon = Icons.Outlined.WarningAmber, tone = Tone.Red,
            confirmText = "Hapus", confirmColor = Sx.Red,
            onConfirm = {
                deleting = null
                scope.launch { if (Ui.report(Api.send("$base/${f.str("uuid")}", method = "DELETE"))) state.reload(silent = true) }
            },
        )
    }
}

private class ReqItem(id: String, name: String, desc: String, required: Boolean, types: String) {
    var id by mutableStateOf(id)
    var name by mutableStateOf(name)
    var desc by mutableStateOf(desc)
    var required by mutableStateOf(required)
    var types by mutableStateOf(types)
}

private class QItem(id: String, question: String, type: String, options: String, required: Boolean) {
    var id by mutableStateOf(id)
    var question by mutableStateOf(question)
    var type by mutableStateOf(type)
    var options by mutableStateOf(options)
    var required by mutableStateOf(required)
}

@Composable
fun FormBuilderScreen(uuid: String?) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val base = formsBase()
    val isEdit = uuid != null
    val form = rememberForm("category" to "REKRUTMEN", "target_rank_category" to "ALL", "target_matra" to "ALL", "target_angkatan" to "ALL")
    val reqs = remember { mutableStateListOf<ReqItem>() }
    val qs = remember { mutableStateListOf<QItem>() }
    val loader = if (isEdit) rememberPage("$base/$uuid/edit") else null
    var loaded by remember { mutableStateOf(!isEdit) }

    LaunchedEffect(loader?.data) {
        val f = loader?.data?.obj("form") ?: return@LaunchedEffect
        form.setAll(
            mapOf(
                "title" to f.str("title"), "category" to f.str("category"), "description" to f.strOrNull("description"),
                "deadline" to Fmt.isoDate(f.strOrNull("deadline")), "target_rank_category" to f.str("target_rank_category", "ALL"),
                "target_matra" to f.str("target_matra", "ALL"), "target_angkatan" to f.str("target_angkatan", "ALL"),
            ),
        )
        reqs.clear(); qs.clear()
        f.objects("requirements").forEach { reqs.add(ReqItem(it.str("id"), it.str("name"), it.str("description"), it.bool("required"), it.strings("file_types").joinToString(","))) }
        f.objects("questions").forEach { qs.add(QItem(it.str("id"), it.str("question"), it.str("type", "text"), it.strings("options").joinToString("\n"), it.bool("required"))) }
        loaded = true
    }

    ShellScaffold(title = if (isEdit) "Ubah Formulir" else "Buat Formulir", subtitle = "Pertanyaan & berkas persyaratan") { inner ->
        Column(
            Modifier.padding(top = inner.calculateTopPadding()).imePadding().verticalScroll(rememberScrollState()).padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            if (!loaded) { Notice("Memuat formulir...", Tone.Gray); return@Column }
            FormSection("Informasi Formulir", icon = Icons.Outlined.EditNote) {
                TextFieldF(form, "title", "Judul Formulir", required = true)
                ChipsF(form, "category", "Kategori", opts("REKRUTMEN" to "Rekrutmen", "PENDATAAN" to "Pendataan", "SURVEI" to "Survei", "PENDAFTARAN" to "Pendaftaran", "LAINNYA" to "Lainnya"), required = true)
                TextFieldF(form, "description", "Deskripsi", minLines = 3)
                DateF(form, "deadline", "Batas Pengisian")
            }
            FormSection("Sasaran", icon = Icons.Outlined.Assignment) {
                ChipsF(form, "target_rank_category", "Golongan Pangkat", opts("ALL" to "Semua", "PERWIRA" to "Perwira", "BINTARA" to "Bintara", "TAMTAMA" to "Tamtama"), required = true)
                ChipsF(form, "target_matra", "Matra", opts("ALL" to "Semua", "AD" to "TNI AD", "AL" to "TNI AL", "AU" to "TNI AU"), required = true)
                TextFieldF(form, "target_angkatan", "Angkatan", helper = "Isi ALL untuk semua angkatan, atau tahun (mis. 2024)")
            }
            FormSection("Berkas Persyaratan", "Personel wajib mengunggah berkas sesuai daftar ini.", Icons.Outlined.UploadFile) {
                reqs.forEachIndexed { i, r ->
                    SxCard(Modifier.fillMaxWidth(), color = Sx.Bg, padding = PaddingValues(12.dp), radius = 16.dp) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text("Berkas ${i + 1}", style = MaterialTheme.typography.labelMedium, modifier = Modifier.weight(1f))
                            SoftButton("", { reqs.remove(r) }, icon = Icons.Outlined.Delete, tone = Tone.Red, height = 34.dp)
                        }
                        Spacer(Modifier.height(8.dp))
                        PlainTextField(r.name, { r.name = it }, label = "Nama Berkas", required = true)
                        Spacer(Modifier.height(8.dp))
                        PlainTextField(r.desc, { r.desc = it }, label = "Keterangan")
                        Spacer(Modifier.height(8.dp))
                        PlainTextField(r.types, { r.types = it }, label = "Format (pisahkan koma)", placeholder = "pdf,jpg,png")
                        Spacer(Modifier.height(8.dp))
                        SwitchRow(r.required, { r.required = it }, "Wajib diunggah")
                    }
                }
                SoftButton("Tambah Berkas", { reqs.add(ReqItem("r" + System.currentTimeMillis(), "", "", true, "pdf")) }, Modifier.fillMaxWidth(), icon = Icons.Outlined.Add)
            }
            FormSection("Pertanyaan", icon = Icons.Outlined.QuestionAnswer) {
                qs.forEachIndexed { i, q ->
                    SxCard(Modifier.fillMaxWidth(), color = Sx.Bg, padding = PaddingValues(12.dp), radius = 16.dp) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text("Pertanyaan ${i + 1}", style = MaterialTheme.typography.labelMedium, modifier = Modifier.weight(1f))
                            SoftButton("", { qs.remove(q) }, icon = Icons.Outlined.Delete, tone = Tone.Red, height = 34.dp)
                        }
                        Spacer(Modifier.height(8.dp))
                        PlainTextField(q.question, { q.question = it }, label = "Pertanyaan", required = true, minLines = 2)
                        Spacer(Modifier.height(8.dp))
                        ChoiceChips(q.type, { q.type = it }, opts("text" to "Isian Singkat", "textarea" to "Paragraf", "multiple_choice" to "Pilihan Ganda"), "Tipe Jawaban")
                        if (q.type == "multiple_choice") {
                            Spacer(Modifier.height(8.dp))
                            PlainTextField(q.options, { q.options = it }, label = "Pilihan (satu per baris)", minLines = 3)
                        }
                        Spacer(Modifier.height(8.dp))
                        SwitchRow(q.required, { q.required = it }, "Wajib dijawab")
                    }
                }
                SoftButton("Tambah Pertanyaan", { qs.add(QItem("q" + System.currentTimeMillis(), "", "text", "", true)) }, Modifier.fillMaxWidth(), icon = Icons.Outlined.Add)
            }
            PrimaryButton("Simpan Formulir", {
                if (!form.require("title" to "Judul", "category" to "Kategori")) return@PrimaryButton
                if (reqs.any { it.name.isBlank() } || qs.any { it.question.isBlank() }) { Ui.error("Lengkapi nama berkas dan teks pertanyaan."); return@PrimaryButton }
                if (qs.any { it.type == "multiple_choice" && it.options.lines().count { l -> l.isNotBlank() } < 2 }) { Ui.error("Pilihan ganda minimal 2 opsi."); return@PrimaryButton }
                val body = mapOf(
                    "title" to form["title"], "category" to form["category"], "description" to form["description"].ifBlank { null },
                    "deadline" to form["deadline"].ifBlank { null }, "target_rank_category" to form["target_rank_category"],
                    "target_matra" to form["target_matra"], "target_angkatan" to form["target_angkatan"].ifBlank { "ALL" },
                    "requirements" to reqs.map { r ->
                        mapOf("id" to r.id, "name" to r.name, "description" to r.desc.ifBlank { null }, "required" to r.required,
                            "file_types" to r.types.split(',').map { it.trim().lowercase() }.filter { it.isNotBlank() })
                    },
                    "questions" to qs.map { q ->
                        mapOf("id" to q.id, "question" to q.question, "type" to q.type, "required" to q.required,
                            "options" to if (q.type == "multiple_choice") q.options.lines().map { it.trim() }.filter { it.isNotBlank() } else emptyList())
                    },
                )
                scope.launch {
                    val r = form.submit(if (isEdit) "$base/$uuid" else base, method = if (isEdit) "PUT" else "POST", body = body, withFiles = false)
                    if (Ui.report(r, "Formulir tersimpan.")) nav.popBackStack()
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Save)
            Spacer(Modifier.height(40.dp))
        }
    }
}

@Composable
fun FormResponsesScreen(uuid: String) {
    val scope = rememberCoroutineScope()
    val base = formsBase()
    val state = rememberPaged("$base/$uuid/responses", "responses")
    var detail by remember { mutableStateOf<JsonObject?>(null) }
    var detailForm by remember { mutableStateOf<JsonObject?>(null) }
    var deciding by remember { mutableStateOf<Pair<String, JsonObject>?>(null) }
    var busy by remember { mutableStateOf(false) }
    ShellScaffold(title = "Respons Formulir", subtitle = state.root?.obj("form")?.str("title")) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama responden...",
            header = { root ->
                val st = root.obj("stats")
                if (root != null) item(key = "stats") {
                    StatRow {
                        SparkCounter("Total", st.int("total"), Sx.Blue, Modifier.weight(1f))
                        SparkCounter("Diverifikasi", st.int("verified"), Sx.Cyan, Modifier.weight(1f))
                        SparkCounter("Diterima", st.int("accepted"), Sx.Green, Modifier.weight(1f))
                        SparkCounter("Ditolak", st.int("rejected"), Sx.Red, Modifier.weight(1f))
                    }
                }
            },
            filterBar = { state.filterChips("status", listOf(Opt("", "Semua"), Opt("SUBMITTED", "Terkirim"), Opt("VERIFIED", "Diverifikasi"), Opt("ACCEPTED", "Diterima"), Opt("REJECTED", "Ditolak"))) },
            emptyTitle = "Belum ada respons",
        ) { r, _ ->
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = {
                scope.launch {
                    val res = Api.get("$base/$uuid/responses/${r.str("uuid")}")
                    if (res is ApiResult.Ok) { detailForm = res.data.obj("form"); detail = res.data.obj("response") } else Ui.report(res)
                }
            }) {
                PersonelRow(r.obj("personel"), trailing = { StatusBadge(r.str("status")) })
                Spacer(Modifier.height(6.dp))
                Text("Dikirim ${Fmt.dateTime(r.str("submitted_at"))}", style = MaterialTheme.typography.bodySmall)
            }
        }
    }
    detail?.let { resp ->
        SxSheet({ detail = null }) {
            PersonelRow(resp.obj("personel"), trailing = { StatusBadge(resp.str("status")) })
            Spacer(Modifier.height(10.dp))
            FormAnswers(detailForm ?: state.root.obj("form"), resp)
            Spacer(Modifier.height(14.dp))
            Text("Ubah Status", style = MaterialTheme.typography.titleSmall)
            Spacer(Modifier.height(8.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                SoftButton("Verifikasi", { deciding = "VERIFIED" to resp }, Modifier.weight(1f), tone = Tone.Cyan)
                SoftButton("Terima", { deciding = "ACCEPTED" to resp }, Modifier.weight(1f), tone = Tone.Green)
                SoftButton("Tolak", { deciding = "REJECTED" to resp }, Modifier.weight(1f), tone = Tone.Red)
            }
        }
    }
    deciding?.let { (status, resp) ->
        ReasonDialog(
            title = "Tandai ${id.my.sisfoperskc.app.ui.components.statusLabel(status)}?", label = "Catatan verifikasi", required = status == "REJECTED",
            confirmText = "Simpan", tone = if (status == "REJECTED") Tone.Red else Tone.Blue, loading = busy,
            onDismiss = { deciding = null },
        ) { notes ->
            scope.launch {
                busy = true
                val r = Api.send("$base/$uuid/responses/${resp.str("uuid")}/status", mapOf("status" to status, "verification_notes" to notes.ifBlank { null }))
                busy = false
                if (Ui.report(r)) { deciding = null; detail = null; state.reload(silent = true) }
            }
        }
    }
}

