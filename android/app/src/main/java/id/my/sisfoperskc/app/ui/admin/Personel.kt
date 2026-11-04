package id.my.sisfoperskc.app.ui.admin

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Edit
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Phone
import androidx.compose.material.icons.outlined.Print
import androidx.compose.material.icons.outlined.School
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.Shield
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
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.auth.MatraOpts
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.Divider
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SheetAction
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
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
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.NotificationBell
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.personel.EducationManager
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

val RoleOpts = opts("personel" to "Personel", "kordinator_angkatan" to "Koordinator Angkatan", "kordinator_matra" to "Koordinator Matra", "admin" to "Administrator")

@Composable
fun PersonelListScreen() {
    val nav = LocalNav.current
    val me by Session.me.collectAsState()
    val isAdmin = me?.isAdmin == true
    val state = rememberPaged("/admin/personel", "personels")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    ShellScaffold(
        title = if (isAdmin) "Master Personel" else "Personel Jajaran",
        subtitle = "Data induk personel Komcad",
        topLevel = true,
        actions = { NotificationBell() },
        fab = if (isAdmin) ({
            FloatingActionButton(onClick = { nav.navigate(Rt.PERSONEL_NEW) }, containerColor = Sx.Blue, contentColor = Color.White, modifier = Modifier.padding(bottom = 84.dp)) {
                Icon(Icons.Outlined.Add, "Tambah personel")
            }
        }) else null,
    ) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama, NIKC, NIK...",
            filterBar = { state.filterChips("matra", MatraFilter) },
            emptyTitle = "Personel tidak ditemukan",
        ) { p, _ ->
            PersonelCard(p) { selected = p }
        }
    }
    selected?.let { p ->
        PersonelSheet(
            p, isAdmin = isAdmin, onDismiss = { selected = null },
            onChanged = { selected = null; state.reload(silent = true) },
        )
    }
}

@Composable
fun PersonelCard(p: JsonObject, onClick: () -> Unit) {
    SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = onClick) {
        PersonelRow(p, trailing = { MatraBadge(p.str("matra")) })
        Spacer(Modifier.height(10.dp))
        Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            Badge("Angkatan ${p.str("angkatan")}", Tone.Gray)
            StatusBadge(p.strOrNull("status_keaktifan") ?: if (p.obj("user").bool("is_active")) "AKTIF" else "NONAKTIF")
            if (p.objOrNull("sinyalmen") != null) Badge("Sinyalmen", Tone.Cyan)
            if (p.bool("face_verified")) Badge("Terverifikasi", Tone.Green)
        }
    }
}

@Composable
fun PersonelSheet(p: JsonObject, isAdmin: Boolean, onDismiss: () -> Unit, onChanged: () -> Unit) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val download = rememberDownloader()
    var confirmDelete by remember { mutableStateOf(false) }
    var deleting by remember { mutableStateOf(false) }
    SxSheet(onDismiss) {
        PersonelRow(p, trailing = { MatraBadge(p.str("matra")) })
        Spacer(Modifier.height(12.dp))
        InfoRow("NIKC", p.str("nikc"))
        InfoRow("Pangkat", p.str("pangkat"))
        InfoRow("Angkatan", p.str("angkatan"))
        InfoRow("Email", p.obj("user").str("email"))
        InfoRow("Nomor HP", p.str("phone_number"))
        InfoRow("Kotama", p.strOrNull("kotama"))
        InfoRow("Satuan", p.strOrNull("satuan_kewilayahan"))
        InfoRow("Status Profil", p.str("status_profile"))
        p.objOrNull("sinyalmen")?.let { s ->
            Spacer(Modifier.height(8.dp)); Divider(); Spacer(Modifier.height(8.dp))
            Text("Sinyalmen", style = MaterialTheme.typography.titleSmall)
            InfoRow("Tinggi / Berat", "${s.str("tinggi_badan")} cm / ${s.str("berat_badan")} kg")
            InfoRow("Gol. Darah", s.str("golongan_darah"))
            InfoRow("Rambut / Mata", "${s.str("rambut")} / ${s.str("mata")}")
            InfoRow("Ciri Khas", s.str("ciri_khas"))
        }
        val edu = p.objects("riwayat_pendidikan")
        if (edu.isNotEmpty()) {
            Spacer(Modifier.height(8.dp)); Divider(); Spacer(Modifier.height(8.dp))
            Text("Pendidikan", style = MaterialTheme.typography.titleSmall)
            edu.forEach { e -> InfoRow("${e.str("jenis")} ${e.str("jenjang")}", "${e.str("nama_institusi")} (${e.str("tahun_lulus")})") }
        }
        val jobs = p.objects("job_histories")
        if (jobs.isNotEmpty()) {
            Spacer(Modifier.height(8.dp)); Divider(); Spacer(Modifier.height(8.dp))
            Text("Pekerjaan", style = MaterialTheme.typography.titleSmall)
            jobs.forEach { j -> InfoRow(j.str("tipe_pekerjaan"), listOf(j.str("jabatan"), j.str("nama_perusahaan")).filter { it.isNotBlank() }.joinToString(" • ")) }
        }
        val resp = p.objects("broadcast_responses")
        if (resp.isNotEmpty()) {
            Spacer(Modifier.height(8.dp)); Divider(); Spacer(Modifier.height(8.dp))
            Text("Kehadiran Kegiatan", style = MaterialTheme.typography.titleSmall)
            resp.take(6).forEach { r -> InfoRow(r.obj("broadcast").str("title", "Kegiatan"), id.my.sisfoperskc.app.ui.components.statusLabel(r.str("status_attendance"))) }
        }
        if (isAdmin) {
            Spacer(Modifier.height(14.dp))
            SheetAction(Icons.Outlined.Edit, "Ubah Data Personel") { onDismiss(); nav.navigate(Rt.with(Rt.PERSONEL_EDIT, "uuid" to p.str("uuid"))) }
            SheetAction(Icons.Outlined.School, "Kelola Riwayat Pendidikan", Tone.Purple) { onDismiss(); nav.navigate(Rt.with(Rt.PERSONEL_EDU, "uuid" to p.str("uuid"))) }
            SheetAction(Icons.Outlined.Print, "Cetak Lembar Akun (PDF)", Tone.Green) {
                download("/admin/personel/${p.str("uuid")}/print-account", "Akun_${p.str("full_name").replace(' ', '_')}.pdf")
            }
            SheetAction(Icons.Outlined.Delete, "Hapus Personel", Tone.Red, "Menghapus akun & seluruh berkas secara permanen") { confirmDelete = true }
        }
    }
    if (confirmDelete) {
        SxDialog(
            onDismiss = { confirmDelete = false }, title = "Hapus ${p.str("full_name")}?",
            message = "Data personel, akun, dan seluruh berkas akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.",
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = "Hapus", confirmColor = Sx.Red, loading = deleting,
            onConfirm = {
                scope.launch {
                    deleting = true
                    val r = Api.send("/admin/personel/${p.str("uuid")}", method = "DELETE")
                    deleting = false
                    if (Ui.report(r, "Personel dihapus.")) { confirmDelete = false; onChanged() }
                }
            },
        )
    }
}

@Composable
fun PersonelFormScreen(uuid: String?) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val download = rememberDownloader()
    val isEdit = uuid != null
    val loader = rememberPage(if (isEdit) "/admin/personel/$uuid/edit" else "/admin/personel/create")
    val form = rememberForm("role" to "personel")
    var initialized by remember { mutableStateOf(false) }
    var created by remember { mutableStateOf<JsonObject?>(null) }
    var lookupMsg by remember { mutableStateOf<String?>(null) }

    ShellScaffold(title = if (isEdit) "Ubah Personel" else "Tambah Personel", subtitle = if (isEdit) "Perbarui data induk" else "Daftarkan personel langsung") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding()).imePadding()) { data ->
            val p = data.objOrNull("personel")
            LaunchedEffect(p) {
                if (!initialized && p != null) {
                    form.setAll(
                        mapOf(
                            "full_name" to p.str("full_name"), "email" to p.obj("user").str("email"), "phone_number" to p.str("phone_number"),
                            "matra" to p.str("matra"), "angkatan" to p.str("angkatan"), "pangkat" to p.str("pangkat"), "nikc" to p.str("nikc"),
                            "role" to (p.obj("user").obj("role").strOrNull("name") ?: "personel"),
                        ),
                    )
                }
                initialized = true
            }
            val pangkat = data.objects("pangkatOptions").map { Opt(it.str("nama"), it.str("nama")) }
            val provinces = data.objects("provinceOptions").map { Opt(it.str("nama"), it.str("nama")) }

            FormSection("Identitas Personel", icon = Icons.Outlined.Person) {
                Row(verticalAlignment = Alignment.Bottom, horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    TextFieldF(form, "nikc", "NIKC", Modifier.weight(1f), keyboardType = KeyboardType.Number, maxLength = 17, icon = Icons.Outlined.Badge, helper = if (isEdit) null else "Opsional, isi bila tercantum di SKEP")
                    if (!isEdit) {
                        SoftButton("Cek", {
                            scope.launch {
                                val r = Api.get("/admin/personel/lookup-skep", mapOf("nikc" to form["nikc"]))
                                if (r is ApiResult.Ok) {
                                    lookupMsg = r.data.str("message")
                                    if (r.data.bool("found")) {
                                        val d = r.data.obj("data")
                                        form.setAll(mapOf("full_name" to d.str("nama_lengkap"), "matra" to d.str("matra"), "angkatan" to d.str("angkatan"), "pangkat" to d.str("pangkat")))
                                    }
                                } else Ui.error(r.errMessage() ?: "Gagal")
                            }
                        }, icon = Icons.Outlined.Search, height = 56.dp)
                    }
                }
                if (lookupMsg != null) Notice(lookupMsg ?: "", Tone.Blue)
                TextFieldF(form, "full_name", "Nama Lengkap", required = true)
                if (!isEdit) TextFieldF(form, "nik", "NIK", required = true, keyboardType = KeyboardType.Number, maxLength = 16)
                if (!isEdit) ChipsF(form, "gender", "Jenis Kelamin", id.my.sisfoperskc.app.ui.auth.GenderOpts, required = true)
                SelectF(form, "pangkat", "Pangkat", pangkat, required = true)
                SelectF(form, "matra", "Matra", MatraOpts, required = true)
                TextFieldF(form, "angkatan", "Angkatan", required = true, keyboardType = KeyboardType.Number, maxLength = 4)
                if (!isEdit) {
                    DateF(form, "dob", "Tanggal Lahir", required = true)
                    SelectF(form, "province", "Provinsi", provinces, required = true)
                }
            }
            FormSection("Kontak & Akun", icon = Icons.Outlined.Email) {
                TextFieldF(form, "email", "Email", required = true, keyboardType = KeyboardType.Email, icon = Icons.Outlined.Email)
                TextFieldF(form, "phone_number", "Nomor HP", required = true, keyboardType = KeyboardType.Phone, icon = Icons.Outlined.Phone)
                SelectF(form, "role", "Peran Akun", RoleOpts, required = true, icon = Icons.Outlined.Shield)
                if (!isEdit) TextFieldF(form, "password", "Kata Sandi Awal", required = true, password = true, icon = Icons.Outlined.Lock, helper = "Minimal 8 karakter")
                FileF(form, "photo_profile", "Pas Foto", Accept.Image, required = !isEdit, helper = "JPG/PNG maks 2 MB", existingPath = p?.strOrNull("photo_profile"))
            }
            PrimaryButton(if (isEdit) "Simpan Perubahan" else "Daftarkan Personel", {
                val required = mutableListOf("full_name" to "Nama", "email" to "Email", "phone_number" to "Nomor HP", "matra" to "Matra", "angkatan" to "Angkatan", "pangkat" to "Pangkat")
                if (!isEdit) required += listOf("nik" to "NIK", "gender" to "Jenis kelamin", "dob" to "Tanggal lahir", "province" to "Provinsi", "password" to "Kata sandi", "photo_profile" to "Pas foto")
                if (!form.require(*required.toTypedArray())) return@PrimaryButton
                scope.launch {
                    val r = form.submit(if (isEdit) "/admin/personel/$uuid" else "/admin/personel", method = if (isEdit) "PUT" else "POST")
                    if (r is ApiResult.Ok) {
                        val cred = r.data.obj("flash").objOrNull("created_credentials")
                        if (cred != null) created = cred else { Ui.report(r); nav.popBackStack() }
                    } else Ui.report(r)
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting)
        }
    }
    created?.let { c ->
        SxDialog(
            onDismiss = { created = null; nav.popBackStack() },
            title = "Personel berhasil didaftarkan",
            message = "Simpan kredensial berikut dan serahkan kepada personel.",
            icon = Icons.Outlined.Badge, tone = Tone.Green, dismissText = "Selesai",
            confirmText = "Cetak PDF",
            onConfirm = {
                download("/admin/personel/${c.str("uuid")}/print-account?pwd=${java.net.URLEncoder.encode(c.str("password", form["password"]), "UTF-8")}", "Akun_${c.str("full_name").replace(' ', '_')}.pdf")
            },
        ) {
            InfoRow("Nama", c.str("full_name"))
            InfoRow("Username", c.str("nikc"))
            InfoRow("Pangkat", c.str("pangkat"))
            InfoRow("Kata Sandi", c.str("password", form["password"]))
        }
    }
}

@Composable
fun PersonelEducationScreen(uuid: String) {
    ShellScaffold(title = "Riwayat Pendidikan", subtitle = "Kelola pendidikan personel") { inner ->
        Column(Modifier.fillMaxSize().padding(top = inner.calculateTopPadding())) {
            EducationManager(path = "/admin/personel/$uuid/pendidikan", storePath = "/admin/personel/$uuid/pendidikan", itemPrefix = "/admin/pendidikan", canVerify = true)
        }
    }
}

@Composable
fun PjuPersonelListContent(path: String) {
    val state = rememberPaged(path, "personels")
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    PagedList(state, searchHint = "Cari nama, NIKC...", filterBar = { state.filterChips("matra", MatraFilter) }) { p, _ -> PersonelCard(p) { selected = p } }
    selected?.let { p -> PersonelSheet(p, isAdmin = false, onDismiss = { selected = null }, onChanged = { selected = null }) }
}

