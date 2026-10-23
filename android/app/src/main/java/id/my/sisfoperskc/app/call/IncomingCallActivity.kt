package id.my.sisfoperskc.app.call

import android.app.KeyguardManager
import android.content.Context
import android.content.Intent
import android.media.AudioAttributes
import android.media.Ringtone
import android.media.RingtoneManager
import android.os.Build
import android.os.Bundle
import android.os.VibrationEffect
import android.os.Vibrator
import android.os.VibratorManager
import android.view.WindowManager
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import id.my.sisfoperskc.app.ui.components.pressable
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Call
import androidx.compose.material.icons.outlined.CallEnd
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.material3.Icon
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.push.CallCoordinator
import id.my.sisfoperskc.app.push.IncomingCall
import id.my.sisfoperskc.app.push.Notifier
import id.my.sisfoperskc.app.ui.theme.SisfoTheme
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class IncomingCallActivity : ComponentActivity() {
    private var ringtone: Ringtone? = null
    private var vibrator: Vibrator? = null
    private lateinit var call: IncomingCall
    private var resolved = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
            setShowWhenLocked(true)
            setTurnScreenOn(true)
            (getSystemService(KEYGUARD_SERVICE) as? KeyguardManager)?.requestDismissKeyguard(this, null)
        } else {
            @Suppress("DEPRECATION")
            window.addFlags(
                WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED or WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON or
                    WindowManager.LayoutParams.FLAG_DISMISS_KEYGUARD,
            )
        }
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)

        call = IncomingCall(
            threadUuid = intent.getStringExtra("thread_uuid").orEmpty(),
            callId = intent.getStringExtra("call_id").orEmpty(),
            callerName = intent.getStringExtra("caller_name") ?: "Petugas Layanan",
            callerRank = intent.getStringExtra("caller_rank").orEmpty(),
            callType = intent.getStringExtra("call_type") ?: "video",
            prefix = intent.getStringExtra("prefix").orEmpty(),
            callerPhoto = intent.getStringExtra("caller_photo"),
        )
        if (call.threadUuid.isBlank() || call.prefix.isBlank()) { finish(); return }

        if (intent.getBooleanExtra("auto_accept", false)) {
            accept()
            return
        }
        startRinging()

        setContent { SisfoTheme { IncomingScreen() } }
    }

    @Composable
    private fun IncomingScreen() {
        LaunchedEffect(Unit) {
            val started = System.currentTimeMillis()
            while (!resolved) {
                delay(2000)
                val r = Api.get("${call.prefix}/live-chat/${call.threadUuid}/call/signal") as? ApiResult.Ok
                val st = r?.data?.objOrNull("call")?.str("status")
                if (r != null && (st == null || st == "ENDED" || st == "REJECTED")) {
                    dismiss(); break
                }
                if (System.currentTimeMillis() - started > 45_000) {
                    decline("missed"); break
                }
            }
        }
        val t = rememberInfiniteTransition(label = "in")
        val bounce by t.animateFloat(0f, -14f, infiniteRepeatable(tween(600), RepeatMode.Reverse), label = "b")
        Box(Modifier.fillMaxSize().background(Sx.NightGradient)) {
            PeerPlaceholder(call.callerName, call.callerPhoto, "Panggilan video masuk", ringing = true)
            Column(Modifier.fillMaxWidth().statusBarsPadding().padding(top = 28.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Outlined.SupportAgent, null, tint = Color.White.copy(0.8f))
                    Spacer(Modifier.padding(4.dp))
                    Text("Pusat Layanan Informasi", color = Color.White.copy(0.85f), fontWeight = FontWeight.Bold, fontSize = 14.sp)
                }
                if (call.callerRank.isNotBlank()) {
                    Spacer(Modifier.height(4.dp))
                    Text(call.callerRank, color = Color.White.copy(0.6f), fontSize = 12.sp)
                }
            }
            Row(
                Modifier.align(Alignment.BottomCenter).fillMaxWidth().navigationBarsPadding().padding(bottom = 56.dp, start = 48.dp, end = 48.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
            ) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    CallButton(Icons.Outlined.CallEnd, active = true, danger = true, size = 72.dp) { decline("rejected") }
                    Spacer(Modifier.height(10.dp))
                    Text("Tolak", color = Color.White, fontWeight = FontWeight.Bold)
                }
                Column(horizontalAlignment = Alignment.CenterHorizontally, modifier = Modifier.offset(y = bounce.dp)) {
                    AcceptButton { accept() }
                    Spacer(Modifier.height(10.dp))
                    Text("Terima", color = Color.White, fontWeight = FontWeight.Bold)
                }
            }
        }
    }

    @Composable
    private fun AcceptButton(onClick: () -> Unit) {
        Box(
            Modifier
                .size(72.dp)
                .clip(CircleShape)
                .background(Brush.linearGradient(listOf(Color(0xFF22C55E), Color(0xFF16A34A))))
                .pressable(onClick, pressedScale = 0.88f),
            contentAlignment = Alignment.Center,
        ) { Icon(Icons.Outlined.Call, "Terima", tint = Color.White, modifier = Modifier.size(32.dp)) }
    }

    private fun startRinging() {
        try {
            val uri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_RINGTONE)
            ringtone = RingtoneManager.getRingtone(this, uri)?.apply {
                audioAttributes = AudioAttributes.Builder().setUsage(AudioAttributes.USAGE_NOTIFICATION_RINGTONE).build()
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) isLooping = true
                play()
            }
        } catch (_: Exception) {
        }
        try {
            vibrator = if (Build.VERSION.SDK_INT >= 31) {
                (getSystemService(VIBRATOR_MANAGER_SERVICE) as VibratorManager).defaultVibrator
            } else {
                @Suppress("DEPRECATION")
                getSystemService(VIBRATOR_SERVICE) as Vibrator
            }
            val pattern = longArrayOf(0, 800, 700)
            if (Build.VERSION.SDK_INT >= 26) vibrator?.vibrate(VibrationEffect.createWaveform(pattern, 0))
            else @Suppress("DEPRECATION") vibrator?.vibrate(pattern, 0)
        } catch (_: Exception) {
        }
    }

    private fun stopRinging() {
        try { ringtone?.stop() } catch (_: Exception) {}
        try { vibrator?.cancel() } catch (_: Exception) {}
        ringtone = null
    }

    private fun accept() {
        if (resolved) return
        resolved = true
        stopRinging()
        Notifier.cancelCall(this)
        CallCoordinator.markHandled(call.callId.ifBlank { call.threadUuid })
        CallActivity.start(this, call.threadUuid, call.prefix, outgoing = false, peerName = call.callerName, peerPhoto = call.callerPhoto)
        finish()
    }

    private fun decline(reason: String) {
        if (resolved) return
        resolved = true
        stopRinging()
        Notifier.cancelCall(this)
        CallCoordinator.markHandled(call.callId.ifBlank { call.threadUuid })
        val c = call
        CoroutineScope(Dispatchers.IO).launch {
            Api.send("${c.prefix}/live-chat/${c.threadUuid}/call/end", mapOf("reason" to reason))
        }
        finish()
    }

    private fun dismiss() {
        resolved = true
        stopRinging()
        Notifier.cancelCall(this)
        finish()
    }

    override fun onDestroy() {
        stopRinging()
        super.onDestroy()
    }

    companion object {
        fun intent(context: Context, call: IncomingCall, autoAccept: Boolean = false) =
            Intent(context, IncomingCallActivity::class.java).apply {
                putExtra("thread_uuid", call.threadUuid)
                putExtra("call_id", call.callId)
                putExtra("caller_name", call.callerName)
                putExtra("caller_rank", call.callerRank)
                putExtra("call_type", call.callType)
                putExtra("prefix", call.prefix)
                putExtra("caller_photo", call.callerPhoto)
                putExtra("auto_accept", autoAccept)
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_NO_USER_ACTION)
            }
    }
}
