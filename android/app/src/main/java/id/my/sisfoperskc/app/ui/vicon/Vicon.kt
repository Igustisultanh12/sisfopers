package id.my.sisfoperskc.app.ui.vicon

import android.content.Intent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.CheckBox
import androidx.compose.material.icons.outlined.CheckBoxOutlineBlank
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.GroupAdd
import androidx.compose.material.icons.outlined.Key
import androidx.compose.material.icons.outlined.Login
import androidx.compose.material.icons.outlined.Share
import androidx.compose.material.icons.outlined.StopCircle
import androidx.compose.material.icons.outlined.VideoCall
import androidx.compose.material.icons.outlined.Videocam
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.call.ViconActivity
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
import id.my.sisfoperskc.app.data.toPage
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.EmptyState
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SectionTitle
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
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

@Composable
fun ViconListScreen() {
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val me by Session.me.collectAsState()
    val group = me?.roleGroup ?: "personel"
    val base = when (group) {
        "admin" -> "/admin/vicon"
        "kordinator" -> "/kordinator/vicon"
        "pju" -> "/pju/vicon"
        else -> "/personel/vicon"
    }
    val isAdmin = group == "admin"
    val loader = rememberPage(base)
    var creating by remember { mutableStateOf(false) }
    var manage by remember { mutableStateOf<JsonObject?>(null) }
    var inviteFor by remember { mutableStateOf<JsonObject?>(null) }
    var joinCode by remember { mutableStateOf("") }
    var joinPass by remember { mutableStateOf("") }
    var joining by remember { mutableStateOf(false) }
    var confirm by remember { mutableStateOf<Pair<String, JsonObject>?>(null) }

    fun open(room: JsonObject) = ViconActivity.start(ctx, "$base/${room.str("uuid")}")

    ShellScaffold(
        title = "Vicon & Rapat Dinas",
        subtitle = "Rapat daring terenkripsi",
        fab = if (isAdmin) ({
            FloatingActionButton(onClick = { creating = true }, containerColor = Sx.Blue, contentColor = Color.White) { Icon(Icons.Outlined.Add, "Buat rapat") }
        }) else null,
    ) { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            if (group == "personel") {
                SxCard(Modifier.fillMaxWidth().appear(0)) {
                    SectionTitle("Gabung dengan Kode", subtitle = "Masukkan kode rapat dari penyelenggara")
                    Spacer(Modifier.height(12.dp))
                    PlainTextField(joinCode, { joinCode = it.uppercase() }, placeholder = "KC-XXXXXX", icon = Icons.Outlined.Key)
                    Spacer(Modifier.height(10.dp))
                    PlainTextField(joinPass, { joinPass = it }, placeholder = "Kata sandi (bila ada)", password = true)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Gabung Rapat", {
                        if (joinCode.isBlank()) { Ui.error("Kode rapat wajib diisi."); return@PrimaryButton }
                        scope.launch {
                            joining = true
                            val r = Api.send("/personel/vicon/join", mapOf("room_code" to joinCode.trim(), "passcode" to joinPass.ifBlank { null }))
                            joining = false
                            if (r is ApiResult.Ok) {
                                val path = r.data.strOrNull("redirect")
                                if (path != null && path.contains("/personel/vicon/")) ViconActivity.start(ctx, path) else Ui.report(r)
                            } else Ui.report(r)
                        }
                    }, Modifier.fillMaxWidth(), loading = joining, icon = Icons.Outlined.Login)
                }
            }
            val active = data.objects("activeRooms")
            val scheduled = data.objects("scheduledRooms")
            val past = data["pastRooms"].toPage().items
            SectionTitle("Sedang Berlangsung", Modifier.appear(1))
            if (active.isEmpty()) Notice(if (group == "personel") "Belum ada rapat yang mengundang Anda saat ini." else "Tidak ada rapat yang sedang berlangsung.", Tone.Gray)
            active.forEachIndexed { i, r -> RoomCard(r, live = true, modifier = Modifier.appear(2 + i), onJoin = { open(r) }, onMore = if (isAdmin) ({ manage = r }) else null) }
            if (scheduled.isNotEmpty()) {
                SectionTitle("Terjadwal", Modifier.appear(4))
                scheduled.forEachIndexed { i, r -> RoomCard(r, live = false, modifier = Modifier.appear(5 + i), onJoin = { open(r) }, onMore = if (isAdmin) ({ manage = r }) else null) }
            }
            if (past.isNotEmpty()) {
                SectionTitle("Riwayat Rapat", Modifier.appear(7))
                past.forEachIndexed { i, r -> RoomCard(r, live = false, ended = true, modifier = Modifier.appear(8 + i), onJoin = null, onMore = if (isAdmin) ({ manage = r }) else null) }
            }
            if (active.isEmpty() && scheduled.isEmpty() && past.isEmpty() && group != "personel") EmptyState("Belum ada rapat dinas", "Rapat yang dibuat admin akan muncul di sini.", Icons.Outlined.VideoCall)

            if (creating) CreateRoomSheet(data.objects("personels"), onDismiss = { creating = false }) { room ->
                creating = false
                loader.refresh()
                if (room != null && room.str("status") == "ACTIVE") open(room)
            }
            inviteFor?.let { room ->
                InviteSheet(room, data.objects("personels"), onDismiss = { inviteFor = null }) { inviteFor = null; loader.refresh() }
            }
        }
    }

    manage?.let { r ->
        SxSheet({ manage = null }) {
            Text(r.str("title"), style = MaterialTheme.typography.titleLarge)
            Text("Kode: ${r.str("room_code")}", style = MaterialTheme.typography.bodySmall)
            Spacer(Modifier.height(12.dp))
            if (r.str("status") != "ENDED") {
                SheetAction(Icons.Outlined.Videocam, "Masuk ke rapat") { manage = null; open(r) }
                SheetAction(Icons.Outlined.GroupAdd, "Undang personel", Tone.Purple) { manage = null; inviteFor = r }
                SheetAction(Icons.Outlined.Share, "Bagikan kode rapat", Tone.Cyan) {
                    manage = null
                    val text = "Undangan Rapat Dinas SISFOPERS KC\n${r.str("title")}\nKode: ${r.str("room_code")}" +
                        (if (r.bool("allow_guest")) "\nTautan tamu: ${Session.baseUrl}/vicon/join/${r.str("room_code")}" else "")
                    ctx.startActivity(Intent.createChooser(Intent(Intent.ACTION_SEND).setType("text/plain").putExtra(Intent.EXTRA_TEXT, text), "Bagikan").addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
                }
                SheetAction(Icons.Outlined.StopCircle, "Akhiri rapat", Tone.Amber) { manage = null; confirm = "end" to r }
            }
            SheetAction(Icons.Outlined.Delete, "Hapus catatan rapat", Tone.Red) { manage = null; confirm = "delete" to r }
        }
    }
    confirm?.let { (kind, r) ->
        SxDialog(
            onDismiss = { confirm = null },
            title = if (kind == "end") "Akhiri rapat?" else "Hapus rapat?",
            message = r.str("title"),
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = if (kind == "end") "Akhiri" else "Hapus", confirmColor = Sx.Red,
            onConfirm = {
                confirm = null
                scope.launch {
                    val res = if (kind == "end") Api.send("/admin/vicon/${r.str("uuid")}/end") else Api.send("/admin/vicon/${r.str("uuid")}", method = "DELETE")
                    if (Ui.report(res)) loader.refresh()
                }
            },
        )
    }
}

@Composable
private fun RoomCard(r: JsonObject, live: Boolean, modifier: Modifier, ended: Boolean = false, onJoin: (() -> Unit)?, onMore: (() -> Unit)?) {
    SxCard(modifier.fillMaxWidth(), padding = PaddingValues(16.dp), onClick = onMore ?: onJoin) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(Icons.Outlined.VideoCall, if (live) Sx.Green else if (ended) Sx.Muted else Sx.Purple)
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(r.str("title"), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                Text(
                    when {
                        live -> "Mulai ${Fmt.dateTime(r.strOrNull("started_at") ?: r.str("created_at"))}"
                        ended -> "Selesai ${Fmt.dateTime(r.str("ended_at"))}"
                        else -> "Jadwal ${Fmt.dateTime(r.str("scheduled_at"))}"
                    },
                    style = MaterialTheme.typography.bodySmall,
                )
            }
            StatusBadge(r.str("status"))
        }
        Spacer(Modifier.height(10.dp))
        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            Badge(r.str("room_code"), Tone.Gray)
            if (r.bool("allow_guest")) Badge("Tamu diizinkan", Tone.Cyan)
            val count = r.objects("participants").count { it.str("status") == "JOINED" }
            if (count > 0) Badge("$count hadir", Tone.Green)
            Spacer(Modifier.weight(1f))
            if (onJoin != null && !ended) SoftButton(if (live) "Gabung" else "Masuk", onJoin, tone = if (live) Tone.Green else Tone.Blue, icon = Icons.Outlined.Videocam, height = 38.dp)
        }
    }
}

@Composable
private fun PersonelPicker(personels: List<JsonObject>, selected: MutableList<Int>) {
    var q by remember { mutableStateOf("") }
    PlainTextField(q, { q = it }, placeholder = "Cari personel untuk diundang...")
    Spacer(Modifier.height(8.dp))
    val filtered = personels.filter { q.isBlank() || it.str("full_name").contains(q, true) || it.str("nikc").contains(q) }
    LazyColumn(Modifier.fillMaxWidth().heightIn(max = 320.dp)) {
        items(filtered, key = { it.int("id") }) { p ->
            val id = p.int("id")
            val checked = id in selected
            Row(
                Modifier.fillMaxWidth().clip(RoundedCornerShape(12.dp)).background(if (checked) Sx.BlueSoft else Color.Transparent)
                    .pressable({ if (checked) selected.remove(id) else selected.add(id) }).padding(8.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Icon(if (checked) Icons.Outlined.CheckBox else Icons.Outlined.CheckBoxOutlineBlank, null, tint = if (checked) Sx.Blue else Sx.Faint)
                Spacer(Modifier.width(10.dp))
                Avatar(p.strOrNull("photo"), p.str("full_name"), 34.dp)
                Spacer(Modifier.width(10.dp))
                Column(Modifier.weight(1f)) {
                    Text(p.str("full_name"), fontSize = 13.sp, fontWeight = FontWeight.Bold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    Text("${p.str("pangkat")} • ${p.str("label")}", fontSize = 11.sp, color = Sx.Muted, maxLines = 1)
                }
            }
        }
    }
    Text("${selected.size} personel dipilih", style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(top = 6.dp))
}

@Composable
private fun CreateRoomSheet(personels: List<JsonObject>, onDismiss: () -> Unit, onCreated: (JsonObject?) -> Unit) {
    val scope = rememberCoroutineScope()
    val form = rememberForm("allow_guest" to "0", "is_scheduled" to "0")
    val invited = remember { mutableStateListOf<Int>() }
    SxSheet(onDismiss) {
        Text("Buat Rapat Dinas", style = MaterialTheme.typography.headlineSmall)
        Spacer(Modifier.height(14.dp))
        Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
            TextFieldF(form, "title", "Judul Rapat", required = true)
            TextFieldF(form, "description", "Deskripsi", minLines = 2)
            SwitchF(form, "is_scheduled", "Jadwalkan rapat", "Matikan untuk memulai sekarang")
            if (form["is_scheduled"] == "1") DateF(form, "scheduled_at", "Waktu Rapat", required = true, withTime = true)
            SwitchF(form, "allow_guest", "Izinkan tamu", "Peserta luar dapat bergabung dengan tautan")
            if (form["allow_guest"] == "1") TextFieldF(form, "guest_passcode", "Kata Sandi Tamu (opsional)")
            Text("Undang Personel", style = MaterialTheme.typography.titleSmall)
            PersonelPicker(personels, invited)
            PrimaryButton(if (form["is_scheduled"] == "1") "Jadwalkan Rapat" else "Mulai Rapat Sekarang", {
                if (!form.require("title" to "Judul")) return@PrimaryButton
                if (form["is_scheduled"] == "1" && form["scheduled_at"].isBlank()) { form.require("scheduled_at" to "Waktu rapat"); return@PrimaryButton }
                scope.launch {
                    val body = mapOf(
                        "title" to form["title"], "description" to form["description"].ifBlank { null },
                        "allow_guest" to (form["allow_guest"] == "1"), "guest_passcode" to form["guest_passcode"].ifBlank { null },
                        "is_scheduled" to (form["is_scheduled"] == "1"), "scheduled_at" to form["scheduled_at"].ifBlank { null },
                        "invited_personel_ids" to invited.toList(),
                    )
                    val r = form.submit("/admin/vicon", body = body)
                    if (r is ApiResult.Ok) { Ui.success("Ruang rapat berhasil dibuat."); onCreated(r.data.obj("room")) } else Ui.report(r)
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.VideoCall)
        }
    }
}

@Composable
private fun InviteSheet(room: JsonObject, personels: List<JsonObject>, onDismiss: () -> Unit, onDone: () -> Unit) {
    val scope = rememberCoroutineScope()
    val selected = remember { mutableStateListOf<Int>() }
    var busy by remember { mutableStateOf(false) }
    SxSheet(onDismiss) {
        Text("Undang ke ${room.str("title")}", style = MaterialTheme.typography.titleLarge)
        Spacer(Modifier.height(12.dp))
        PersonelPicker(personels, selected)
        Spacer(Modifier.height(12.dp))
        PrimaryButton("Kirim Undangan", {
            if (selected.isEmpty()) { Ui.error("Pilih minimal satu personel."); return@PrimaryButton }
            scope.launch {
                busy = true
                val r = Api.send("/admin/vicon/${room.str("uuid")}/invite", mapOf("personel_ids" to selected.toList()))
                busy = false
                if (Ui.report(r)) onDone()
            }
        }, Modifier.fillMaxWidth(), loading = busy, icon = Icons.Outlined.GroupAdd)
    }
}
