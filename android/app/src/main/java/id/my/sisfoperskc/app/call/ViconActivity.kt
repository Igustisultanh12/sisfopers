package id.my.sisfoperskc.app.call

import android.Manifest
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.view.WindowManager
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.slideInVertically
import androidx.compose.animation.slideOutVertically
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.Chat
import androidx.compose.material.icons.automirrored.outlined.Send
import androidx.compose.material.icons.outlined.CallEnd
import androidx.compose.material.icons.outlined.Cameraswitch
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.Groups
import androidx.compose.material.icons.outlined.Mic
import androidx.compose.material.icons.outlined.MicOff
import androidx.compose.material.icons.outlined.StopCircle
import androidx.compose.material.icons.outlined.Videocam
import androidx.compose.material.icons.outlined.VideocamOff
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.key
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateMapOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.core.content.ContextCompat
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
import id.my.sisfoperskc.app.push.AppVisibility
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.MessageHost
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.theme.SisfoTheme
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

/** Rapat dinas daring multi-peserta (Vicon) berbasis Agora RTC. */
class ViconActivity : ComponentActivity() {
    private var rtc: Rtc? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        val roomPath = intent.getStringExtra(EXTRA_PATH)
        if (roomPath.isNullOrBlank()) { finish(); return }
        AppVisibility.inCall = true
        setContent { SisfoTheme { Box { ViconScreen(roomPath); MessageHost() } } }
    }

    private fun has(p: String) = ContextCompat.checkSelfPermission(this, p) == PackageManager.PERMISSION_GRANTED

    @Composable
    private fun ViconScreen(roomPath: String) {
        val scope = rememberCoroutineScope()
        var room by remember { mutableStateOf<JsonObject?>(null) }
        var isHost by remember { mutableStateOf(false) }
        var myUid by remember { mutableIntStateOf(0) }
        var myName by remember { mutableStateOf("") }
        var error by remember { mutableStateOf<String?>(null) }
        var engineState by remember { mutableStateOf<Rtc?>(null) }
        var permsAsked by remember { mutableStateOf(false) }
        var micOn by remember { mutableStateOf(true) }
        var camOn by remember { mutableStateOf(true) }
        var showChat by remember { mutableStateOf(false) }
        var showPeople by remember { mutableStateOf(false) }
        var confirmEnd by remember { mutableStateOf(false) }
        var unreadChat by remember { mutableIntStateOf(0) }
        val names = remember { mutableStateMapOf<Int, String>() }
        val photos = remember { mutableStateMapOf<Int, String?>() }
        val participants = remember { mutableStateListOf<JsonObject>() }
        val messages = remember { mutableStateListOf<JsonObject>() }

        val perm = rememberLauncherForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { permsAsked = true }
        LaunchedEffect(Unit) {
            val needed = buildList {
                if (!has(Manifest.permission.CAMERA)) add(Manifest.permission.CAMERA)
                if (!has(Manifest.permission.RECORD_AUDIO)) add(Manifest.permission.RECORD_AUDIO)
                if (android.os.Build.VERSION.SDK_INT >= 31 && !has(Manifest.permission.BLUETOOTH_CONNECT)) add(Manifest.permission.BLUETOOTH_CONNECT)
            }
            if (needed.isEmpty()) permsAsked = true else perm.launch(needed.toTypedArray())
        }

        LaunchedEffect(permsAsked) {
            if (!permsAsked) return@LaunchedEffect
            when (val r = Api.get(roomPath)) {
                is ApiResult.Err -> { error = r.message; return@LaunchedEffect }
                is ApiResult.Ok -> {
                    val rm = r.data.obj("room")
                    room = rm
                    isHost = r.data.bool("isHost")
                    val cp = r.data.obj("currentParticipant")
                    myUid = cp.int("agora_uid")
                    myName = cp.str("display_name", Session.me.value?.fullName ?: "Peserta")
                    if (rm.str("status") == "ENDED") { error = "Rapat dinas ini telah berakhir."; return@LaunchedEffect }
                    val uuid = rm.str("uuid")
                    val tk = Api.get("/vicon/$uuid/token", mapOf("uid" to myUid)) as? ApiResult.Ok
                    val appId = tk?.data?.strOrNull("appId") ?: r.data.strOrNull("agoraAppId") ?: Session.agoraAppId
                    val engine = Rtc(this@ViconActivity, appId)
                    if (!engine.available) { error = engine.lastError.value; return@LaunchedEffect }
                    rtc = engine
                    engineState = engine
                    val withVideo = has(Manifest.permission.CAMERA)
                    camOn = withVideo
                    if (!has(Manifest.permission.RECORD_AUDIO)) { micOn = false }
                    engine.join(tk?.data?.strOrNull("token"), tk?.data?.strOrNull("channel") ?: rm.str("agora_channel"), tk?.data?.int("uid", myUid) ?: myUid, withVideo)
                    if (!micOn) engine.muteMic(true)
                }
            }
        }

        // Sinkronisasi peserta, status rapat, dan obrolan
        LaunchedEffect(room?.str("uuid")) {
            val uuid = room?.str("uuid") ?: return@LaunchedEffect
            var lastMsg = 0
            while (true) {
                val s = Api.get("/vicon/$uuid/sync", mapOf("last_msg_id" to lastMsg)) as? ApiResult.Ok
                if (s != null) {
                    if (s.data.str("status") == "ENDED") {
                        Ui.toast("Rapat dinas telah diakhiri oleh host.")
                        delay(1200); leave(); break
                    }
                    val ps = s.data.objects("participants")
                    participants.clear(); participants.addAll(ps)
                    ps.forEach { p -> names[p.int("agora_uid")] = p.str("display_name"); photos[p.int("agora_uid")] = p.strOrNull("photo") }
                    val ms = s.data.objects("messages")
                    if (ms.isNotEmpty()) {
                        messages.addAll(ms.filter { m -> messages.none { it.int("id") == m.int("id") } })
                        lastMsg = ms.maxOf { it.int("id") }
                        if (!showChat) unreadChat += ms.size
                    }
                }
                delay(3500)
            }
        }

        BackHandler { leave() }

        Box(Modifier.fillMaxSize().background(Sx.Slate900)) {
            val engine = engineState
            when {
                error != null -> Column(Modifier.fillMaxSize().padding(32.dp), verticalArrangement = Arrangement.Center, horizontalAlignment = Alignment.CenterHorizontally) {
                    Text(error ?: "", color = Color.White, fontSize = 16.sp, fontWeight = FontWeight.Bold)
                    Spacer(Modifier.height(16.dp))
                    CallButton(Icons.Outlined.Close, active = true) { leave() }
                }
                engine == null -> Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        CircularProgressIndicator(color = Color.White)
                        Spacer(Modifier.height(14.dp))
                        Text("Menyiapkan ruang rapat...", color = Color.White.copy(0.8f))
                    }
                }
                else -> {
                    val tiles = listOf(Rtc.LOCAL) + engine.remoteUids
                    val cols = if (tiles.size <= 2) 1 else 2
                    LazyVerticalGrid(
                        columns = GridCells.Fixed(cols),
                        modifier = Modifier.fillMaxSize().statusBarsPadding().padding(top = 64.dp, bottom = 110.dp),
                        contentPadding = PaddingValues(10.dp),
                        horizontalArrangement = Arrangement.spacedBy(10.dp),
                        verticalArrangement = Arrangement.spacedBy(10.dp),
                    ) {
                        items(tiles, key = { it }) { uid ->
                            val isLocal = uid == Rtc.LOCAL
                            val name = if (isLocal) "$myName (Anda)" else names[uid] ?: "Peserta $uid"
                            val videoOff = if (isLocal) !camOn else engine.remoteVideoOff[uid] == true
                            val talking = engine.speaking[uid] == true
                            Box(
                                Modifier
                                    .fillMaxWidth()
                                    .aspectRatio(if (cols == 1) 1.3f else 0.78f)
                                    .clip(RoundedCornerShape(22.dp))
                                    .background(Color(0xFF1E293B))
                                    .border(if (talking) 3.dp else 1.dp, if (talking) Sx.Green else Color.White.copy(0.08f), RoundedCornerShape(22.dp)),
                            ) {
                                if (!videoOff) {
                                    key(uid) {
                                        AndroidView(factory = { if (isLocal) engine.localView() else engine.remoteView(uid) }, modifier = Modifier.fillMaxSize())
                                    }
                                } else {
                                    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                                        Avatar(if (isLocal) Session.me.value?.photo else photos[uid], name, 72.dp)
                                    }
                                }
                                Row(
                                    Modifier.align(Alignment.BottomStart).padding(10.dp).clip(RoundedCornerShape(10.dp)).background(Color.Black.copy(0.5f)).padding(horizontal = 8.dp, vertical = 4.dp),
                                    verticalAlignment = Alignment.CenterVertically,
                                ) {
                                    val muted = if (isLocal) !micOn else engine.remoteAudioOff[uid] == true
                                    Icon(if (muted) Icons.Outlined.MicOff else Icons.Outlined.Mic, null, tint = if (muted) Sx.Red else Color.White, modifier = Modifier.size(13.dp))
                                    Spacer(Modifier.width(4.dp))
                                    Text(name, color = Color.White, fontSize = 11.sp, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                                }
                            }
                        }
                    }
                }
            }

            // Header
            Row(
                Modifier.fillMaxWidth().statusBarsPadding().padding(horizontal = 16.dp, vertical = 10.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Column(Modifier.weight(1f)) {
                    Text(room?.str("title") ?: "Vicon Dinas", color = Color.White, fontWeight = FontWeight.ExtraBold, fontSize = 16.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    Text("Kode: ${room?.str("room_code") ?: "-"} • ${(engineState?.remoteUids?.size ?: 0) + 1} peserta", color = Color.White.copy(0.65f), fontSize = 12.sp)
                }
                Box(Modifier.size(42.dp).clip(CircleShape).background(Color.White.copy(0.12f)).pressable({ showPeople = true }), contentAlignment = Alignment.Center) {
                    Icon(Icons.Outlined.Groups, "Peserta", tint = Color.White)
                }
            }

            // Kontrol
            Row(
                Modifier.align(Alignment.BottomCenter).navigationBarsPadding().padding(bottom = 22.dp)
                    .clip(RoundedCornerShape(32.dp)).background(Color.Black.copy(0.45f)).padding(horizontal = 12.dp, vertical = 10.dp),
                horizontalArrangement = Arrangement.spacedBy(10.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                CallButton(if (micOn) Icons.Outlined.Mic else Icons.Outlined.MicOff, active = micOn) {
                    if (!has(Manifest.permission.RECORD_AUDIO)) { Ui.error("Izin mikrofon belum diberikan."); return@CallButton }
                    micOn = !micOn; rtc?.muteMic(!micOn)
                }
                CallButton(if (camOn) Icons.Outlined.Videocam else Icons.Outlined.VideocamOff, active = camOn) {
                    if (!has(Manifest.permission.CAMERA)) { Ui.error("Izin kamera belum diberikan."); return@CallButton }
                    camOn = !camOn; rtc?.videoEnabled(camOn)
                }
                CallButton(Icons.Outlined.Cameraswitch, active = true) { rtc?.switchCamera() }
                Box {
                    CallButton(Icons.AutoMirrored.Outlined.Chat, active = true) { showChat = true; unreadChat = 0 }
                    if (unreadChat > 0) Box(Modifier.align(Alignment.TopEnd).size(14.dp).clip(CircleShape).background(Sx.Red))
                }
                if (isHost && Session.me.value?.isAdmin == true) {
                    CallButton(Icons.Outlined.StopCircle, active = true, danger = true) { confirmEnd = true }
                }
                CallButton(Icons.Outlined.CallEnd, active = true, danger = true, size = 58.dp) { leave() }
            }

            AnimatedVisibility(showChat, enter = slideInVertically { it }, exit = slideOutVertically { it }, modifier = Modifier.align(Alignment.BottomCenter)) {
                ChatPanel(messages, onClose = { showChat = false }) { text ->
                    val uuid = room?.str("uuid") ?: return@ChatPanel
                    scope.launch {
                        val r = Api.send("/vicon/$uuid/chat", mapOf("message" to text, "sender_name" to myName))
                        if (r is ApiResult.Ok) {
                            r.data.obj("message").takeIf { it.isNotEmpty() }?.let { m -> if (messages.none { it.int("id") == m.int("id") }) messages.add(m) }
                        } else Ui.error(r.errMessage() ?: "Gagal mengirim pesan")
                    }
                }
            }
            AnimatedVisibility(showPeople, enter = slideInVertically { it }, exit = slideOutVertically { it }, modifier = Modifier.align(Alignment.BottomCenter)) {
                PeoplePanel(participants, onClose = { showPeople = false })
            }
        }

        if (confirmEnd) {
            SxDialog(
                onDismiss = { confirmEnd = false },
                title = "Akhiri rapat untuk semua?",
                message = "Seluruh peserta akan dikeluarkan dan rapat ditandai selesai.",
                icon = Icons.Outlined.StopCircle,
                tone = Tone.Red,
                confirmText = "Akhiri Rapat",
                confirmColor = Sx.Red,
                onConfirm = {
                    confirmEnd = false
                    val uuid = room?.str("uuid") ?: return@SxDialog
                    scope.launch {
                        val r = Api.send("/admin/vicon/$uuid/end")
                        if (Ui.report(r, "Rapat diakhiri.")) leave()
                    }
                },
            )
        }
    }

    @Composable
    private fun ChatPanel(messages: List<JsonObject>, onClose: () -> Unit, onSend: (String) -> Unit) {
        var text by remember { mutableStateOf("") }
        val listState = rememberLazyListState()
        LaunchedEffect(messages.size) { if (messages.isNotEmpty()) listState.animateScrollToItem(messages.size - 1) }
        Column(
            Modifier.fillMaxWidth().fillMaxHeight(0.62f).clip(RoundedCornerShape(topStart = 28.dp, topEnd = 28.dp)).background(Color.White).navigationBarsPadding().imePadding(),
        ) {
            Row(Modifier.fillMaxWidth().padding(18.dp), verticalAlignment = Alignment.CenterVertically) {
                Text("Obrolan Rapat", fontWeight = FontWeight.ExtraBold, fontSize = 17.sp, modifier = Modifier.weight(1f))
                Icon(Icons.Outlined.Close, "Tutup", modifier = Modifier.clip(CircleShape).pressable(onClose).padding(6.dp))
            }
            LazyColumn(Modifier.weight(1f).padding(horizontal = 16.dp), state = listState, verticalArrangement = Arrangement.spacedBy(10.dp)) {
                if (messages.isEmpty()) item { Text("Belum ada pesan.", color = Sx.Muted, fontSize = 13.sp, modifier = Modifier.padding(8.dp)) }
                items(messages, key = { it.int("id") }) { m ->
                    Column(Modifier.clip(RoundedCornerShape(16.dp)).background(Sx.Bg).padding(12.dp).fillMaxWidth()) {
                        Row {
                            Text(m.str("sender_name"), fontWeight = FontWeight.Bold, fontSize = 12.sp, color = Sx.Blue, modifier = Modifier.weight(1f))
                            Text(Fmt.time(m.str("created_at")), fontSize = 11.sp, color = Sx.Faint)
                        }
                        Text(m.str("message"), fontSize = 14.sp, color = Sx.Ink)
                    }
                }
            }
            Row(Modifier.fillMaxWidth().padding(12.dp), verticalAlignment = Alignment.CenterVertically) {
                id.my.sisfoperskc.app.ui.components.PlainTextField(text, { text = it }, Modifier.weight(1f), placeholder = "Tulis pesan...")
                Spacer(Modifier.width(8.dp))
                Box(Modifier.size(50.dp).clip(CircleShape).background(Sx.Blue).pressable({ if (text.isNotBlank()) { onSend(text.trim()); text = "" } }), contentAlignment = Alignment.Center) {
                    Icon(Icons.AutoMirrored.Outlined.Send, "Kirim", tint = Color.White)
                }
            }
        }
    }

    @Composable
    private fun PeoplePanel(participants: List<JsonObject>, onClose: () -> Unit) {
        Column(Modifier.fillMaxWidth().fillMaxHeight(0.55f).clip(RoundedCornerShape(topStart = 28.dp, topEnd = 28.dp)).background(Color.White).navigationBarsPadding()) {
            Row(Modifier.fillMaxWidth().padding(18.dp), verticalAlignment = Alignment.CenterVertically) {
                Text("Peserta (${participants.count { it.str("status") == "JOINED" }} hadir)", fontWeight = FontWeight.ExtraBold, fontSize = 17.sp, modifier = Modifier.weight(1f))
                Icon(Icons.Outlined.Close, "Tutup", modifier = Modifier.clip(CircleShape).pressable(onClose).padding(6.dp))
            }
            LazyColumn(Modifier.weight(1f).padding(horizontal = 16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                items(participants, key = { it.int("id") }) { p ->
                    Row(verticalAlignment = Alignment.CenterVertically, modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp)) {
                        Avatar(p.strOrNull("photo"), p.str("display_name"), 40.dp)
                        Spacer(Modifier.width(12.dp))
                        Column(Modifier.weight(1f)) {
                            Text(p.str("display_name"), fontWeight = FontWeight.Bold, fontSize = 14.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
                            Text(if (p.str("role") == "HOST") "Host Rapat" else "Peserta", fontSize = 12.sp, color = Sx.Muted)
                        }
                        id.my.sisfoperskc.app.ui.components.StatusBadge(p.str("status"))
                    }
                }
            }
        }
    }

    private fun leave() {
        rtc?.destroy()
        rtc = null
        AppVisibility.inCall = false
        if (!isFinishing) finish()
    }

    override fun onDestroy() {
        rtc?.destroy()
        rtc = null
        AppVisibility.inCall = false
        super.onDestroy()
    }

    companion object {
        const val EXTRA_PATH = "room_path"
        fun start(context: Context, roomPath: String) {
            context.startActivity(Intent(context, ViconActivity::class.java).putExtra(EXTRA_PATH, roomPath).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
        }
    }
}
