package id.my.sisfoperskc.app.call

import android.Manifest
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.media.AudioManager
import android.media.ToneGenerator
import android.os.Bundle
import android.view.WindowManager
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Cameraswitch
import androidx.compose.material.icons.outlined.CallEnd
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Mic
import androidx.compose.material.icons.outlined.MicOff
import androidx.compose.material.icons.outlined.Videocam
import androidx.compose.material.icons.outlined.VideocamOff
import androidx.compose.material.icons.automirrored.outlined.VolumeUp
import androidx.compose.material.icons.outlined.PhoneInTalk
import androidx.compose.material3.Icon
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableLongStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.core.content.ContextCompat
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.push.AppVisibility
import id.my.sisfoperskc.app.push.CallCoordinator
import id.my.sisfoperskc.app.push.Notifier
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.theme.SisfoTheme
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

/** Panggilan video 1-1 Pusat Layanan Informasi (Agora RTC, kompatibel dengan versi web). */
class CallActivity : ComponentActivity() {

    private var rtc: Rtc? = null
    private var ended = false
    private var threadUuid = ""
    private var prefix = ""
    private var startedAt = 0L

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        AppVisibility.inCall = true
        Notifier.cancelCall(this)

        threadUuid = intent.getStringExtra(EXTRA_THREAD).orEmpty()
        prefix = intent.getStringExtra(EXTRA_PREFIX).orEmpty()
        val outgoing = intent.getBooleanExtra(EXTRA_OUTGOING, false)
        val peerName = intent.getStringExtra(EXTRA_PEER_NAME) ?: "Layanan Informasi"
        val peerPhoto = intent.getStringExtra(EXTRA_PEER_PHOTO)
        if (threadUuid.isBlank() || prefix.isBlank()) { finish(); return }

        setContent {
            SisfoTheme {
                CallScreen(outgoing, peerName, peerPhoto)
            }
        }
    }

    @Composable
    private fun CallScreen(outgoing: Boolean, peerName: String, peerPhoto: String?) {
        val scope = rememberCoroutineScope()
        var status by remember { mutableStateOf(if (outgoing) "Memanggil..." else "Menyambungkan...") }
        var phase by remember { mutableStateOf(if (outgoing) Phase.Outgoing else Phase.Connecting) }
        var micOn by remember { mutableStateOf(true) }
        var camOn by remember { mutableStateOf(true) }
        var speakerOn by remember { mutableStateOf(true) }
        var elapsed by remember { mutableLongStateOf(0L) }
        var permsGranted by remember { mutableStateOf(hasPerms()) }
        var rtcState by remember { mutableStateOf<Rtc?>(null) }

        val permLauncher = androidx.activity.compose.rememberLauncherForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { res ->
            val mic = res[Manifest.permission.RECORD_AUDIO] == true || has(Manifest.permission.RECORD_AUDIO)
            if (!mic) {
                status = "Izin mikrofon diperlukan untuk panggilan."
                scope.launch { delay(1500); hangUp("canceled") }
            } else permsGranted = true
        }

        LaunchedEffect(Unit) {
            if (!permsGranted) permLauncher.launch(arrayOf(Manifest.permission.CAMERA, Manifest.permission.RECORD_AUDIO, *btPerm()))
        }

        suspend fun joinMedia() {
            if (rtc != null) return
            val uid = callUid()
            val tokenRes = Api.get("$prefix/live-chat/$threadUuid/call/agora-token", mapOf("uid" to uid))
            val data = (tokenRes as? ApiResult.Ok)?.data
            val appId = data?.strOrNull("appId") ?: Session.agoraAppId
            val channel = data?.strOrNull("channel") ?: ("SISFOPERSKC_" + threadUuid.replace(Regex("[^a-zA-Z0-9]"), ""))
            val token = data?.strOrNull("token")
            val engine = Rtc(this@CallActivity, appId)
            if (!engine.available) {
                status = engine.lastError.value ?: "Modul video tidak tersedia."
                return
            }
            engine.onRemoteJoined = {
                phase = Phase.Connected
                status = "Tersambung"
                if (startedAt == 0L) startedAt = System.currentTimeMillis()
                scope.launch { Api.send("$prefix/live-chat/$threadUuid/call/signal", mapOf("action" to "connected", "sender" to if (outgoing) "caller" else "callee")) }
            }
            engine.onRemoteLeft = {
                if (engine.remoteUids.isEmpty()) scope.launch { hangUp("ended") }
            }
            rtc = engine
            rtcState = engine
            engine.join(token, channel, data?.int("uid", uid) ?: uid, withVideo = has(Manifest.permission.CAMERA))
            camOn = has(Manifest.permission.CAMERA)
        }

        // Alur sinyal panggilan
        LaunchedEffect(permsGranted) {
            if (!permsGranted) return@LaunchedEffect
            if (outgoing) {
                val channel = "SISFOPERSKC_" + threadUuid.replace(Regex("[^a-zA-Z0-9]"), "")
                val r = Api.send("$prefix/live-chat/$threadUuid/call/initiate", mapOf("call_type" to "video", "channel" to channel))
                if (r is ApiResult.Err) {
                    status = r.message
                    delay(1800); finishCall(); return@LaunchedEffect
                }
                val tone = try { ToneGenerator(AudioManager.STREAM_VOICE_CALL, 70) } catch (_: Exception) { null }
                val started = System.currentTimeMillis()
                while (phase == Phase.Outgoing && !ended) {
                    tone?.startTone(ToneGenerator.TONE_SUP_RINGTONE, 1200)
                    val s = Api.get("$prefix/live-chat/$threadUuid/call/signal") as? ApiResult.Ok
                    val call = s?.data?.objOrNull("call")
                    when (call?.str("status")) {
                        "ACCEPTED", "CONNECTED" -> { phase = Phase.Connecting; status = "Menyambungkan..." }
                        "REJECTED" -> { status = "Panggilan ditolak"; tone?.release(); delay(1500); finishCall(); return@LaunchedEffect }
                        "ENDED" -> { status = "Panggilan berakhir"; tone?.release(); delay(1200); finishCall(); return@LaunchedEffect }
                    }
                    if (System.currentTimeMillis() - started > 45_000) {
                        status = "Tidak ada jawaban"
                        tone?.release()
                        hangUp("missed")
                        return@LaunchedEffect
                    }
                    delay(1500)
                }
                tone?.release()
                joinMedia()
            } else {
                Api.send("$prefix/live-chat/$threadUuid/call/signal", mapOf("action" to "accept", "sender" to "callee"))
                joinMedia()
            }
            // Pantau berakhirnya panggilan dari pihak lain
            while (!ended) {
                delay(2500)
                val s = Api.get("$prefix/live-chat/$threadUuid/call/signal") as? ApiResult.Ok ?: continue
                val st = s.data.objOrNull("call")?.str("status")
                if (st == null || st == "ENDED" || st == "REJECTED") {
                    if (phase != Phase.Outgoing) {
                        status = "Panggilan berakhir"
                        delay(800)
                        finishCall()
                    }
                    break
                }
            }
        }

        LaunchedEffect(phase) {
            while (phase == Phase.Connected) {
                elapsed = (System.currentTimeMillis() - startedAt) / 1000
                delay(1000)
            }
        }

        // Batas waktu menyambungkan
        LaunchedEffect(phase) {
            if (phase == Phase.Connecting) {
                delay(40_000)
                if (phase == Phase.Connecting) {
                    status = "Koneksi gagal. Coba lagi."
                    hangUp("missed")
                }
            }
        }

        BackHandler { scope.launch { hangUp("ended") } }

        val engine = rtcState
        Box(Modifier.fillMaxSize().background(Sx.NightGradient)) {
            // Video lawan bicara
            val remote = engine?.remoteUids?.firstOrNull()
            if (engine != null && remote != null && engine.remoteVideoOff[remote] != true) {
                androidx.compose.runtime.key(remote) {
                    AndroidView(factory = { engine.remoteView(remote) }, modifier = Modifier.fillMaxSize())
                }
            } else {
                PeerPlaceholder(peerName, peerPhoto, status, ringing = phase != Phase.Connected)
            }

            // Header
            Column(Modifier.fillMaxWidth().statusBarsPadding().padding(20.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                Row(
                    Modifier.clip(RoundedCornerShape(50)).background(Color.Black.copy(0.35f)).padding(horizontal = 12.dp, vertical = 6.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Icon(Icons.Outlined.Lock, null, tint = Color.White.copy(0.8f), modifier = Modifier.size(13.dp))
                    Spacer(Modifier.width(6.dp))
                    Text(
                        if (phase == Phase.Connected) "${Fmt.duration(elapsed)} • Terenkripsi" else status,
                        color = Color.White, fontSize = 12.sp, fontWeight = FontWeight.SemiBold,
                    )
                }
                if (engine?.connectionLost?.value == true) {
                    Spacer(Modifier.height(8.dp))
                    Text("Menyambungkan ulang...", color = Sx.Amber, fontSize = 12.sp, fontWeight = FontWeight.Bold)
                }
            }

            // Pratinjau kamera sendiri
            if (engine != null && camOn) {
                Box(
                    Modifier
                        .align(Alignment.TopEnd)
                        .statusBarsPadding()
                        .padding(top = 64.dp, end = 16.dp)
                        .size(width = 110.dp, height = 160.dp)
                        .clip(RoundedCornerShape(20.dp))
                        .border(2.dp, Color.White.copy(0.5f), RoundedCornerShape(20.dp)),
                ) { AndroidView(factory = { engine.localView() }, modifier = Modifier.fillMaxSize()) }
            }

            // Kontrol
            Row(
                Modifier
                    .align(Alignment.BottomCenter)
                    .navigationBarsPadding()
                    .padding(bottom = 28.dp)
                    .clip(RoundedCornerShape(32.dp))
                    .background(Color.Black.copy(0.4f))
                    .padding(horizontal = 14.dp, vertical = 12.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                CallButton(if (micOn) Icons.Outlined.Mic else Icons.Outlined.MicOff, active = micOn) {
                    micOn = !micOn; rtc?.muteMic(!micOn)
                }
                CallButton(if (camOn) Icons.Outlined.Videocam else Icons.Outlined.VideocamOff, active = camOn) {
                    if (!has(Manifest.permission.CAMERA)) return@CallButton
                    camOn = !camOn; rtc?.videoEnabled(camOn)
                }
                CallButton(Icons.Outlined.Cameraswitch, active = true) { rtc?.switchCamera() }
                CallButton(Icons.AutoMirrored.Outlined.VolumeUp, active = speakerOn) {
                    speakerOn = !speakerOn; rtc?.speaker(speakerOn)
                }
                CallButton(Icons.Outlined.CallEnd, active = true, danger = true, size = 62.dp) {
                    scope.launch { hangUp(if (phase == Phase.Outgoing) "canceled" else "ended") }
                }
            }
        }
    }

    private enum class Phase { Outgoing, Connecting, Connected }

    private fun has(p: String) = ContextCompat.checkSelfPermission(this, p) == PackageManager.PERMISSION_GRANTED
    private fun hasPerms() = has(Manifest.permission.RECORD_AUDIO)
    private fun btPerm(): Array<String> = if (android.os.Build.VERSION.SDK_INT >= 31) arrayOf(Manifest.permission.BLUETOOTH_CONNECT) else emptyArray()

    private fun callUid(): Int {
        val me = Session.me.value ?: return (100000..999999).random()
        return if (me.isPju) 500000 + me.id else me.id
    }

    private suspend fun hangUp(reason: String) {
        if (ended) return
        ended = true
        val duration = if (startedAt > 0) (System.currentTimeMillis() - startedAt) / 1000 else 0
        CallCoordinator.markHandled(threadUuid)
        Api.send("$prefix/live-chat/$threadUuid/call/end", mapOf("reason" to reason, "duration_seconds" to duration))
        finishCall()
    }

    private fun finishCall() {
        ended = true
        rtc?.destroy()
        rtc = null
        AppVisibility.inCall = false
        if (!isFinishing) finish()
    }

    override fun onDestroy() {
        if (!ended) {
            val t = threadUuid; val p = prefix
            val duration = if (startedAt > 0) (System.currentTimeMillis() - startedAt) / 1000 else 0
            CoroutineScope(Dispatchers.IO).launch {
                Api.send("$p/live-chat/$t/call/end", mapOf("reason" to "ended", "duration_seconds" to duration))
            }
        }
        rtc?.destroy()
        rtc = null
        AppVisibility.inCall = false
        super.onDestroy()
    }

    companion object {
        const val EXTRA_THREAD = "thread_uuid"
        const val EXTRA_PREFIX = "prefix"
        const val EXTRA_OUTGOING = "outgoing"
        const val EXTRA_PEER_NAME = "peer_name"
        const val EXTRA_PEER_PHOTO = "peer_photo"

        fun start(context: Context, threadUuid: String, prefix: String, outgoing: Boolean, peerName: String, peerPhoto: String?) {
            context.startActivity(Intent(context, CallActivity::class.java).apply {
                putExtra(EXTRA_THREAD, threadUuid)
                putExtra(EXTRA_PREFIX, prefix)
                putExtra(EXTRA_OUTGOING, outgoing)
                putExtra(EXTRA_PEER_NAME, peerName)
                putExtra(EXTRA_PEER_PHOTO, peerPhoto)
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            })
        }
    }
}

@Composable
fun PeerPlaceholder(name: String, photo: String?, status: String, ringing: Boolean) {
    val t = rememberInfiniteTransition(label = "ring")
    val pulse by t.animateFloat(1f, 1.6f, infiniteRepeatable(tween(1400), RepeatMode.Restart), label = "p")
    val alpha by t.animateFloat(0.45f, 0f, infiniteRepeatable(tween(1400), RepeatMode.Restart), label = "a")
    Column(Modifier.fillMaxSize(), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
        Box(contentAlignment = Alignment.Center) {
            if (ringing) {
                Box(Modifier.size(150.dp).scale(pulse).clip(CircleShape).background(Sx.Blue.copy(alpha)))
                Box(Modifier.size(150.dp).scale(1f + (pulse - 1f) / 2).clip(CircleShape).background(Sx.Blue.copy(alpha * 0.8f)))
            }
            Avatar(photo, name, 128.dp, ring = Color.White.copy(0.7f))
        }
        Spacer(Modifier.height(26.dp))
        Text(name, color = Color.White, fontSize = 24.sp, fontWeight = FontWeight.ExtraBold, textAlign = TextAlign.Center, modifier = Modifier.padding(horizontal = 24.dp))
        Spacer(Modifier.height(6.dp))
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(Icons.Outlined.PhoneInTalk, null, tint = Color.White.copy(0.7f), modifier = Modifier.size(16.dp))
            Spacer(Modifier.width(6.dp))
            Text(status, color = Color.White.copy(0.75f), fontSize = 14.sp)
        }
    }
}

@Composable
fun CallButton(icon: ImageVector, active: Boolean, danger: Boolean = false, size: Dp = 54.dp, onClick: () -> Unit) {
    val bg = when {
        danger -> Brush.linearGradient(listOf(Color(0xFFEF4444), Color(0xFFDC2626)))
        active -> Brush.linearGradient(listOf(Color.White.copy(0.22f), Color.White.copy(0.12f)))
        else -> Brush.linearGradient(listOf(Color.White, Color.White))
    }
    Box(
        Modifier.size(size).clip(CircleShape).background(bg).pressable(onClick, pressedScale = 0.88f),
        contentAlignment = Alignment.Center,
    ) {
        Icon(icon, null, tint = if (!active && !danger) Sx.Ink else Color.White, modifier = Modifier.size(size * 0.45f))
    }
}
