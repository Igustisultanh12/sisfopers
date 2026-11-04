package id.my.sisfoperskc.app.ui.chat

import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.Send
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.AttachFile
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.Delete
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.LockOpen
import androidx.compose.material.icons.outlined.MoreVert
import androidx.compose.material.icons.outlined.Refresh
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.material.icons.outlined.Videocam
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableLongStateOf
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.call.CallActivity
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Files
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.UploadFile
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.push.AppVisibility
import id.my.sisfoperskc.app.push.ChatMemory
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.CenterLoading
import id.my.sisfoperskc.app.ui.components.CircleIconButton
import id.my.sisfoperskc.app.ui.components.EmptyState
import id.my.sisfoperskc.app.ui.components.ErrorState
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.ImageViewer
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PersonelRow
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.RemoteImage
import id.my.sisfoperskc.app.ui.components.SheetAction
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.filterChips
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.nav.AppState
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.NotificationBell
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonObject

@Composable
fun ChatThreadsScreen() {
    val nav = LocalNav.current
    val me by Session.me.collectAsState()
    val prefix = me?.chatPrefix ?: "/admin"
    val state = rememberPaged("$prefix/live-chat", "threads", mapOf("status" to "all"))
    var starting by remember { mutableStateOf(false) }

    // Segarkan daftar berkala agar pesan baru terlihat
    LaunchedEffect(state) {
        while (true) {
            delay(8000)
            if (!state.loading) state.reload(silent = true)
        }
    }
    LaunchedEffect(state.root) {
        state.root?.obj("stats")?.let { AppState.chatUnread.value = it.int("total_unread") }
    }

    ShellScaffold(
        title = "Layanan Informasi",
        subtitle = state.root?.obj("stats")?.let { "${it.int("total_open")} sesi aktif • ${it.int("total_unread")} belum dibaca" } ?: "Pusat layanan & konsultasi",
        topLevel = true,
        actions = { NotificationBell() },
        fab = {
            FloatingActionButton(onClick = { starting = true }, containerColor = Sx.Blue, contentColor = Color.White, modifier = Modifier.padding(bottom = 84.dp)) {
                Icon(Icons.Outlined.Add, "Mulai chat")
            }
        },
    ) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = "Cari nama personel...",
            filterBar = { state.filterChips("status", listOf(Opt("all", "Semua"), Opt("open", "Aktif"), Opt("closed", "Ditutup"))) },
            emptyTitle = "Belum ada percakapan", emptyMessage = "Mulai percakapan baru dengan personel melalui tombol +.",
            itemSpacing = 10.dp,
        ) { t, _ ->
            val p = t.obj("personel")
            val last = t.objOrNull("latest_message")
            val unread = t.int("unread_admin")
            SxCard(Modifier.fillMaxWidth(), padding = PaddingValues(14.dp), onClick = { nav.navigate(Rt.with(Rt.CHAT_ROOM, "uuid" to t.str("uuid"))) }) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box {
                        Avatar(p.strOrNull("photo_profile"), p.str("full_name"), 50.dp)
                        if (p.bool("is_online")) Box(Modifier.align(Alignment.BottomEnd).size(14.dp).clip(CircleShape).background(Color.White).padding(2.dp).clip(CircleShape).background(Sx.Green))
                    }
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text(p.str("full_name", "Personel"), style = MaterialTheme.typography.titleSmall.copy(fontSize = 14.sp), modifier = Modifier.weight(1f), maxLines = 1, overflow = TextOverflow.Ellipsis)
                            Text(Fmt.relative(t.str("last_message_at")), fontSize = 11.sp, color = Sx.Faint)
                        }
                        Spacer(Modifier.height(2.dp))
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text(
                                last?.let { (if (it.str("sender_type") != "PERSONEL") "Anda: " else "") + (it.strOrNull("message") ?: "📎 Lampiran") } ?: "Belum ada pesan",
                                style = MaterialTheme.typography.bodySmall.copy(color = if (unread > 0) Sx.Ink else Sx.Muted, fontWeight = if (unread > 0) FontWeight.Bold else FontWeight.Medium),
                                maxLines = 1, overflow = TextOverflow.Ellipsis, modifier = Modifier.weight(1f),
                            )
                            if (unread > 0) {
                                Box(Modifier.size(20.dp).clip(CircleShape).background(Sx.Blue), contentAlignment = Alignment.Center) {
                                    Text("$unread", color = Color.White, fontSize = 10.sp, fontWeight = FontWeight.ExtraBold)
                                }
                            } else if (t.str("status") == "CLOSED") Badge("Ditutup", Tone.Gray)
                        }
                    }
                }
            }
        }
    }
    if (starting) StartChatSheet(prefix, onDismiss = { starting = false }) { uuid ->
        starting = false
        nav.navigate(Rt.with(Rt.CHAT_ROOM, "uuid" to uuid))
    }
}

@Composable
private fun StartChatSheet(prefix: String, onDismiss: () -> Unit, onStarted: (String) -> Unit) {
    val scope = rememberCoroutineScope()
    var query by remember { mutableStateOf("") }
    var results by remember { mutableStateOf<List<JsonObject>>(emptyList()) }
    var loading by remember { mutableStateOf(false) }
    var selected by remember { mutableStateOf<JsonObject?>(null) }
    var message by remember { mutableStateOf("") }
    var sending by remember { mutableStateOf(false) }
    LaunchedEffect(query) {
        if (query.trim().length < 2) { results = emptyList(); return@LaunchedEffect }
        delay(400)
        loading = true
        val r = Api.get("$prefix/live-chat/search-personel", mapOf("query" to query.trim())) as? ApiResult.Ok
        results = r?.data?.objects("items") ?: emptyList()
        loading = false
    }
    SxSheet(onDismiss) {
        Text("Mulai Percakapan", style = MaterialTheme.typography.headlineSmall)
        Text("Cari personel berdasarkan nama atau NIKC.", style = MaterialTheme.typography.bodySmall)
        Spacer(Modifier.height(14.dp))
        val sel = selected
        if (sel == null) {
            PlainTextField(query, { query = it }, placeholder = "Ketik minimal 2 huruf...", icon = Icons.Outlined.Search)
            Spacer(Modifier.height(10.dp))
            if (loading) Text("Mencari...", style = MaterialTheme.typography.bodySmall)
            results.forEach { p ->
                Row(
                    Modifier.fillMaxWidth().clip(RoundedCornerShape(16.dp)).pressable({
                        if (p.bool("has_open_thread") && p.strOrNull("open_thread_uuid") != null) onStarted(p.str("open_thread_uuid")) else selected = p
                    }).padding(vertical = 8.dp, horizontal = 4.dp),
                ) {
                    PersonelRow(p, subtitle = "${p.str("pangkat")} • ${p.str("nikc")}", trailing = {
                        if (p.bool("has_open_thread")) Badge("Sesi aktif", Tone.Green) else MatraBadge(p.str("matra"))
                    })
                }
            }
        } else {
            PersonelRow(sel, subtitle = "${sel.str("pangkat")} • ${sel.str("nikc")}")
            Spacer(Modifier.height(14.dp))
            PlainTextField(message, { message = it }, label = "Pesan pembuka (opsional)", minLines = 3)
            Spacer(Modifier.height(14.dp))
            PrimaryButton("Mulai Chat", {
                scope.launch {
                    sending = true
                    val r = Api.send("$prefix/live-chat/start", mapOf("personel_id" to sel.int("id"), "message" to message.ifBlank { null }))
                    sending = false
                    if (r is ApiResult.Ok) {
                        Ui.success(r.message ?: "Sesi obrolan dibuka.")
                        onStarted(r.data.obj("thread").str("uuid"))
                    } else Ui.report(r)
                }
            }, Modifier.fillMaxWidth(), loading = sending, icon = Icons.AutoMirrored.Outlined.Send)
        }
    }
}

/** Ruang chat: operator (uuid tersedia) atau personel (uuid null). */
@Composable
fun ChatRoomScreen(uuidArg: String?) {
    val ctx = LocalContext.current
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val me by Session.me.collectAsState()
    val operator = uuidArg != null
    val prefix = if (operator) me?.chatPrefix ?: "/admin" else "/personel"

    var uuid by remember { mutableStateOf(uuidArg) }
    var status by remember { mutableStateOf("OPEN") }
    var peerName by remember { mutableStateOf(if (operator) "Personel" else "Pusat Layanan Informasi") }
    var peerSub by remember { mutableStateOf<String?>(null) }
    var peerPhoto by remember { mutableStateOf<String?>(null) }
    var online by remember { mutableStateOf(false) }
    var typingName by remember { mutableStateOf<String?>(null) }
    var error by remember { mutableStateOf<String?>(null) }
    var loaded by remember { mutableStateOf(false) }
    var text by remember { mutableStateOf("") }
    var sending by remember { mutableStateOf(false) }
    var lastTyping by remember { mutableLongStateOf(0L) }
    var menu by remember { mutableStateOf(false) }
    var confirmDelete by remember { mutableStateOf(false) }
    var confirmEnd by remember { mutableStateOf(false) }
    var reloadKey by remember { mutableStateOf(0) }
    val messages = remember { mutableStateListOf<JsonObject>() }
    val pending = remember { mutableStateListOf<UploadFile>() }
    val listState = rememberLazyListState()

    val picker = rememberLauncherForActivityResult(ActivityResultContracts.OpenMultipleDocuments()) { uris ->
        uris.take(5).forEach { pending.add(Files.describe(ctx, it)) }
    }

    fun merge(list: List<JsonObject>) {
        val ids = messages.map { it.int("id") }.toHashSet()
        val fresh = list.filter { it.int("id") !in ids }
        if (fresh.isNotEmpty()) messages.addAll(fresh)
    }

    // Muat awal
    LaunchedEffect(reloadKey) {
        error = null
        if (operator) {
            when (val r = Api.get("$prefix/live-chat", mapOf("thread" to uuidArg))) {
                is ApiResult.Ok -> {
                    val th = r.data.objOrNull("activeThread")
                    if (th == null) { error = "Percakapan tidak ditemukan."; return@LaunchedEffect }
                    val p = th.obj("personel")
                    peerName = p.str("full_name", "Personel")
                    peerSub = listOf(p.str("pangkat"), p.str("nikc")).filter { it.isNotBlank() }.joinToString(" • ")
                    peerPhoto = p.strOrNull("photo_profile")
                    online = p.bool("is_online")
                    status = th.str("status", "OPEN")
                    messages.clear(); merge(r.data.objects("activeMessages"))
                }
                is ApiResult.Err -> { error = r.message; return@LaunchedEffect }
            }
        } else {
            when (val r = Api.get("/personel/live-chat/thread")) {
                is ApiResult.Ok -> {
                    val th = r.data.obj("thread")
                    uuid = th.str("uuid")
                    ChatMemory.personelThread = uuid
                    status = th.str("status", "OPEN")
                    messages.clear(); merge(r.data.objects("messages"))
                }
                is ApiResult.Err -> { error = r.message; return@LaunchedEffect }
            }
        }
        loaded = true
    }

    // Tandai sedang dibuka (menekan notifikasi chat ganda)
    DisposableEffect(uuid) {
        AppVisibility.activeChatThread = uuid
        onDispose { if (AppVisibility.activeChatThread == uuid) AppVisibility.activeChatThread = null }
    }

    // Polling pesan baru
    LaunchedEffect(uuid, loaded) {
        val id = uuid ?: return@LaunchedEffect
        if (!loaded) return@LaunchedEffect
        while (true) {
            delay(2500)
            val lastId = messages.maxOfOrNull { it.int("id") } ?: 0
            val r = Api.get("$prefix/live-chat/$id/messages", mapOf("last_id" to lastId)) as? ApiResult.Ok ?: continue
            merge(r.data.objects("messages"))
            status = r.data.str("status", status)
            val typing = r.data.obj("typing")
            typingName = if (typing.bool("is_typing")) typing.strOrNull("name") ?: "Mengetik" else null
            r.data.objOrNull("presence")?.let { online = it.bool("is_online") }
        }
    }
    LaunchedEffect(messages.size) { if (messages.isNotEmpty()) listState.animateScrollToItem(messages.size - 1) }

    fun send() {
        val id = uuid ?: return
        if (text.isBlank() && pending.isEmpty()) return
        val body = text.trim()
        val files = pending.toList()
        scope.launch {
            sending = true
            val r = Api.send(
                "$prefix/live-chat/$id/send",
                mapOf("message" to body.ifBlank { null }),
                files = files.map { "attachments[]" to it },
            )
            sending = false
            when (r) {
                is ApiResult.Ok -> {
                    text = ""; pending.clear()
                    r.data.objOrNull("message")?.let { merge(listOf(it)) }
                }
                is ApiResult.Err -> Ui.error(r.message)
            }
        }
    }

    fun typing() {
        val id = uuid ?: return
        val now = System.currentTimeMillis()
        if (now - lastTyping < 3000) return
        lastTyping = now
        scope.launch { Api.send("$prefix/live-chat/$id/typing") }
    }

    ShellScaffold(
        title = peerName,
        subtitle = typingName?.let { "$it sedang mengetik..." } ?: peerSub ?: if (online) "Online" else if (operator) "Personel" else "Petugas layanan siap membantu",
        topLevel = !operator,
        showBack = operator,
        bottomBar = false,
        actions = {
            if (status == "OPEN" && uuid != null) {
                CircleIconButton(Icons.Outlined.Videocam, {
                    CallActivity.start(ctx, uuid!!, prefix, outgoing = true, peerName = peerName, peerPhoto = peerPhoto)
                }, tint = Sx.Blue, contentDescription = "Panggilan video")
            }
            CircleIconButton(Icons.Outlined.MoreVert, { menu = true }, contentDescription = "Opsi")
        },
    ) { inner ->
        Column(Modifier.fillMaxSize().padding(top = inner.calculateTopPadding()).imePadding()) {
            when {
                error != null -> Box(Modifier.weight(1f)) { ErrorState(error ?: "", { reloadKey++ }) }
                !loaded -> Box(Modifier.weight(1f)) { CenterLoading() }
                else -> {
                    LazyColumn(
                        Modifier.weight(1f).fillMaxWidth(),
                        state = listState,
                        contentPadding = PaddingValues(horizontal = 14.dp, vertical = 10.dp),
                        verticalArrangement = Arrangement.spacedBy(6.dp),
                    ) {
                        item(key = "intro") { ChatIntro(operator) }
                        items(messages, key = { it.int("id") }) { m ->
                            val mine = if (operator) m.str("sender_type") != "PERSONEL" else m.str("sender_type") == "PERSONEL"
                            MessageBubble(m, mine)
                        }
                        if (typingName != null) item(key = "typing") { TypingBubble() }
                    }
                }
            }
            if (status != "OPEN") {
                Row(
                    Modifier.fillMaxWidth().background(Sx.LineSoft).padding(14.dp).navigationBarsPadding(),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Icon(Icons.Outlined.Lock, null, tint = Sx.Muted, modifier = Modifier.size(18.dp))
                    Spacer(Modifier.width(8.dp))
                    Text("Sesi percakapan telah ditutup.", style = MaterialTheme.typography.bodySmall, modifier = Modifier.weight(1f))
                    if (!operator) {
                        Text("Mulai baru", color = Sx.Blue, fontWeight = FontWeight.Bold, fontSize = 13.sp, modifier = Modifier.pressable({
                            scope.launch { if (Ui.report(Api.send("/personel/live-chat/new"), "Sesi baru dibuka.")) reloadKey++ }
                        }))
                    } else {
                        Text("Buka kembali", color = Sx.Blue, fontWeight = FontWeight.Bold, fontSize = 13.sp, modifier = Modifier.pressable({
                            scope.launch { if (Ui.report(Api.send("$prefix/live-chat/${uuid}/status"))) status = "OPEN" }
                        }))
                    }
                }
            } else {
                Composer(
                    text = text, onText = { text = it; typing() }, pending = pending,
                    sending = sending, onAttach = { picker.launch(arrayOf("image/*", "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) },
                    onRemove = { pending.remove(it) }, onSend = { send() },
                )
            }
        }
    }

    if (menu) {
        SxSheet({ menu = false }) {
            Text("Opsi Percakapan", style = MaterialTheme.typography.titleLarge)
            Spacer(Modifier.height(10.dp))
            SheetAction(Icons.Outlined.Refresh, "Muat ulang") { menu = false; reloadKey++ }
            if (operator) {
                SheetAction(if (status == "OPEN") Icons.Outlined.Lock else Icons.Outlined.LockOpen, if (status == "OPEN") "Tutup sesi" else "Buka kembali sesi", Tone.Amber,
                    if (status == "OPEN") "Lampiran akan dihapus otomatis" else null) {
                    menu = false
                    scope.launch {
                        val r = Api.send("$prefix/live-chat/$uuid/status")
                        if (Ui.report(r)) status = if (status == "OPEN") "CLOSED" else "OPEN"
                    }
                }
                SheetAction(Icons.Outlined.Delete, "Hapus percakapan", Tone.Red) { menu = false; confirmDelete = true }
            } else if (status == "OPEN") {
                SheetAction(Icons.Outlined.Close, "Akhiri sesi", Tone.Red, "Lampiran akan dihapus otomatis") { menu = false; confirmEnd = true }
            }
        }
    }
    if (confirmDelete) {
        SxDialog(onDismiss = { confirmDelete = false }, title = "Hapus percakapan?", message = "Seluruh pesan dan lampiran akan dihapus permanen.",
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = "Hapus", confirmColor = Sx.Red,
            onConfirm = {
                confirmDelete = false
                scope.launch { if (Ui.report(Api.send("$prefix/live-chat/$uuid", method = "DELETE"), "Percakapan dihapus.")) nav.popBackStack() }
            })
    }
    if (confirmEnd) {
        SxDialog(onDismiss = { confirmEnd = false }, title = "Akhiri sesi obrolan?", message = "Anda dapat memulai sesi baru kapan saja.",
            icon = Icons.Outlined.WarningAmber, tone = Tone.Red, confirmText = "Akhiri", confirmColor = Sx.Red,
            onConfirm = {
                confirmEnd = false
                scope.launch { if (Ui.report(Api.send("/personel/live-chat/$uuid/end"))) status = "CLOSED" }
            })
    }
}

@Composable
private fun ChatIntro(operator: Boolean) {
    Column(Modifier.fillMaxWidth().padding(vertical = 12.dp), horizontalAlignment = Alignment.CenterHorizontally) {
        Box(Modifier.size(56.dp).clip(RoundedCornerShape(18.dp)).background(Sx.BlueSoft), contentAlignment = Alignment.Center) {
            Icon(Icons.Outlined.SupportAgent, null, tint = Sx.Blue)
        }
        Spacer(Modifier.height(8.dp))
        Text(
            if (operator) "Percakapan dinas dengan personel. Jaga kerahasiaan data." else "Sampaikan pertanyaan Anda, petugas akan segera membalas.",
            style = MaterialTheme.typography.bodySmall, textAlign = androidx.compose.ui.text.style.TextAlign.Center, modifier = Modifier.padding(horizontal = 32.dp),
        )
    }
}

@Composable
private fun MessageBubble(m: JsonObject, mine: Boolean) {
    val system = m.str("sender_type") == "SYSTEM"
    if (system) {
        Box(Modifier.fillMaxWidth().padding(vertical = 4.dp), contentAlignment = Alignment.Center) {
            Text(m.str("message"), fontSize = 11.sp, color = Sx.Muted, modifier = Modifier.clip(RoundedCornerShape(50)).background(Sx.LineSoft).padding(horizontal = 12.dp, vertical = 5.dp))
        }
        return
    }
    var viewImage by remember { mutableStateOf<String?>(null) }
    Row(Modifier.fillMaxWidth().appear(0, key = m.int("id"), distance = 20f), horizontalArrangement = if (mine) Arrangement.End else Arrangement.Start) {
        Column(
            Modifier
                .widthIn(max = 300.dp)
                .clip(RoundedCornerShape(topStart = 20.dp, topEnd = 20.dp, bottomStart = if (mine) 20.dp else 6.dp, bottomEnd = if (mine) 6.dp else 20.dp))
                .background(if (mine) Brush.linearGradient(listOf(Sx.Blue, Sx.BlueDark)) else Brush.linearGradient(listOf(Color.White, Color.White)))
                .then(if (mine) Modifier else Modifier.border(1.dp, Sx.Line, RoundedCornerShape(topStart = 20.dp, topEnd = 20.dp, bottomStart = 6.dp, bottomEnd = 20.dp)))
                .padding(horizontal = 14.dp, vertical = 10.dp),
        ) {
            if (!mine) Text(m.str("sender_name"), fontSize = 11.sp, fontWeight = FontWeight.Bold, color = Sx.Blue)
            val atts = (m["attachments"] as? JsonArray)?.mapNotNull { it as? JsonObject }.orEmpty()
            atts.forEach { a ->
                if (a.bool("is_image") || a.str("mime_type").startsWith("image")) {
                    Box(Modifier.padding(vertical = 4.dp).size(200.dp, 150.dp).clip(RoundedCornerShape(14.dp)).background(Sx.LineSoft).pressable({ viewImage = a.str("file_path") })) {
                        RemoteImage(a.str("file_path"), Modifier.fillMaxSize())
                    }
                } else {
                    FileLink(a.str("original_name", "Lampiran"), a.str("file_path"), Modifier.padding(vertical = 4.dp))
                }
            }
            m.strOrNull("message")?.let { Text(it, color = if (mine) Color.White else Sx.Ink, fontSize = 14.sp, lineHeight = 20.sp) }
            Row(Modifier.align(Alignment.End), verticalAlignment = Alignment.CenterVertically) {
                Text(Fmt.time(m.str("created_at")), fontSize = 10.sp, color = if (mine) Color.White.copy(0.75f) else Sx.Faint)
                if (mine) Text(if (m.bool("is_read")) "  ✓✓" else "  ✓", fontSize = 10.sp, color = Color.White.copy(0.85f))
            }
        }
    }
    viewImage?.let { ImageViewer(it) { viewImage = null } }
}

@Composable
private fun TypingBubble() {
    val t = rememberInfiniteTransition(label = "typing")
    Row(Modifier.padding(vertical = 4.dp).clip(RoundedCornerShape(18.dp)).background(Color.White).border(1.dp, Sx.Line, RoundedCornerShape(18.dp)).padding(horizontal = 16.dp, vertical = 12.dp)) {
        repeat(3) { i ->
            val a by t.animateFloat(0.2f, 1f, infiniteRepeatable(tween(500, delayMillis = i * 150), RepeatMode.Reverse), label = "d$i")
            Box(Modifier.padding(horizontal = 2.dp).size(7.dp).clip(CircleShape).background(Sx.Muted.copy(alpha = a)))
        }
    }
}

@Composable
private fun Composer(
    text: String,
    onText: (String) -> Unit,
    pending: List<UploadFile>,
    sending: Boolean,
    onAttach: () -> Unit,
    onRemove: (UploadFile) -> Unit,
    onSend: () -> Unit,
) {
    Column(Modifier.fillMaxWidth().background(Color.White).navigationBarsPadding()) {
        AnimatedVisibility(pending.isNotEmpty(), enter = fadeIn(), exit = fadeOut()) {
            Row(Modifier.fillMaxWidth().padding(horizontal = 12.dp, vertical = 8.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                pending.forEach { f ->
                    Row(Modifier.clip(RoundedCornerShape(12.dp)).background(Sx.BlueSoft).padding(horizontal = 10.dp, vertical = 6.dp), verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Outlined.Description, null, tint = Sx.Blue, modifier = Modifier.size(14.dp))
                        Spacer(Modifier.width(4.dp))
                        Text(f.name, fontSize = 11.sp, maxLines = 1, overflow = TextOverflow.Ellipsis, modifier = Modifier.widthIn(max = 90.dp))
                        Icon(Icons.Outlined.Close, "Hapus", tint = Sx.Muted, modifier = Modifier.size(16.dp).pressable({ onRemove(f) }))
                    }
                }
            }
        }
        Row(Modifier.fillMaxWidth().padding(10.dp), verticalAlignment = Alignment.Bottom) {
            Box(Modifier.size(46.dp).clip(CircleShape).background(Sx.Bg).pressable(onAttach), contentAlignment = Alignment.Center) {
                Icon(Icons.Outlined.AttachFile, "Lampirkan", tint = Sx.Muted)
            }
            Spacer(Modifier.width(8.dp))
            Box(
                Modifier.weight(1f).heightIn(min = 46.dp).clip(RoundedCornerShape(24.dp)).background(Sx.Bg).border(1.dp, Sx.Line, RoundedCornerShape(24.dp)).padding(horizontal = 16.dp, vertical = 12.dp),
                contentAlignment = Alignment.CenterStart,
            ) {
                if (text.isEmpty()) Text("Tulis pesan...", color = Sx.Faint, fontSize = 14.sp)
                BasicTextField(text, onText, textStyle = TextStyle(fontSize = 14.sp, color = Sx.Ink), maxLines = 5, modifier = Modifier.fillMaxWidth())
            }
            Spacer(Modifier.width(8.dp))
            Box(
                Modifier.size(46.dp).clip(CircleShape).background(if (text.isNotBlank() || pending.isNotEmpty()) Sx.Blue else Sx.Faint).pressable(if (!sending) onSend else null, pressedScale = 0.88f),
                contentAlignment = Alignment.Center,
            ) {
                if (sending) androidx.compose.material3.CircularProgressIndicator(Modifier.size(18.dp), color = Color.White, strokeWidth = 2.dp)
                else Icon(Icons.AutoMirrored.Outlined.Send, "Kirim", tint = Color.White, modifier = Modifier.size(20.dp))
            }
        }
    }
}

