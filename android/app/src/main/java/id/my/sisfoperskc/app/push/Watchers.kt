package id.my.sisfoperskc.app.push

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import androidx.lifecycle.DefaultLifecycleObserver
import androidx.lifecycle.LifecycleOwner
import androidx.lifecycle.ProcessLifecycleOwner
import androidx.work.Constraints
import androidx.work.CoroutineWorker
import androidx.work.ExistingPeriodicWorkPolicy
import androidx.work.NetworkType
import androidx.work.PeriodicWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters
import id.my.sisfoperskc.app.call.IncomingCallActivity
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.MsgKind
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.nav.AppState
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.delay
import kotlinx.coroutines.isActive
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject
import java.util.concurrent.TimeUnit

/** Status visibilitas aplikasi & layar chat yang sedang dibuka. */
object AppVisibility {
    @Volatile var foreground: Boolean = false
    @Volatile var activeChatThread: String? = null
    @Volatile var inCall: Boolean = false
}

/** Koordinasi panggilan masuk agar tidak dobel (FCM + polling). */
object CallCoordinator {
    private var lastCallId: String? = null
    private var lastAt = 0L
    val handledCalls = mutableSetOf<String>()

    @Synchronized
    fun onIncoming(context: Context, call: IncomingCall) {
        if (AppVisibility.inCall) return
        val id = call.callId.ifBlank { call.threadUuid }
        if (id in handledCalls) return
        if (id == lastCallId && System.currentTimeMillis() - lastAt < 60_000) return
        lastCallId = id
        lastAt = System.currentTimeMillis()
        Notifier.showIncomingCall(context, call)
        if (AppVisibility.foreground) {
            try {
                context.startActivity(IncomingCallActivity.intent(context, call).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
            } catch (_: Exception) {
            }
        }
    }

    @Synchronized
    fun markHandled(callId: String) {
        handledCalls.add(callId)
        if (handledCalls.size > 50) handledCalls.remove(handledCalls.first())
    }
}

/** Tombol "Tolak" pada notifikasi panggilan. */
class CallActionReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        val thread = intent.getStringExtra("thread_uuid") ?: return
        val prefix = intent.getStringExtra("prefix") ?: return
        val callId = intent.getStringExtra("call_id").orEmpty()
        Notifier.cancelCall(context)
        CallCoordinator.markHandled(callId.ifBlank { thread })
        val pending = goAsync()
        CoroutineScope(Dispatchers.IO).launch {
            try {
                Api.send("$prefix/live-chat/$thread/call/end", mapOf("reason" to "rejected"))
            } finally {
                pending.finish()
            }
        }
    }

    companion object {
        fun declineIntent(context: Context, call: IncomingCall) = Intent(context, CallActionReceiver::class.java).apply {
            putExtra("thread_uuid", call.threadUuid)
            putExtra("prefix", call.prefix)
            putExtra("call_id", call.callId)
        }
    }
}

/** Sinkronisasi notifikasi berkala (cadangan bila FCM belum dikonfigurasi). */
class NotificationSyncWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {
    override suspend fun doWork(): Result {
        if (!Session.isLoggedIn) return Result.success()
        NotificationSync.sync(applicationContext, showSystem = true)
        return Result.success()
    }

    companion object {
        fun schedule(context: Context) {
            val req = PeriodicWorkRequestBuilder<NotificationSyncWorker>(15, TimeUnit.MINUTES)
                .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build())
                .build()
            WorkManager.getInstance(context).enqueueUniquePeriodicWork("notif-sync", ExistingPeriodicWorkPolicy.KEEP, req)
        }

        fun cancel(context: Context) {
            WorkManager.getInstance(context).cancelUniqueWork("notif-sync")
        }
    }
}

object NotificationSync {
    /** Ambil notifikasi terbaru; tampilkan yang belum pernah ditampilkan. */
    suspend fun sync(context: Context, showSystem: Boolean): Int {
        val first = Session.lastNotificationSync == null
        val r = Api.appGet("/notifications", mapOf("limit" to 20))
        val ok = r as? ApiResult.Ok ?: return AppState.unread.value
        val unread = ok.data.int("unread_count")
        AppState.unread.value = unread
        val shown = Session.shownNotificationIds()
        val fresh = ok.data.objects("items").filter { it.strOrNull("read_at") == null && it.str("id") !in shown }
        fresh.forEach { n -> Session.markNotificationShown(n.str("id")) }
        if (!first) {
            fresh.take(5).forEach { n ->
                if (showSystem) {
                    Notifier.show(
                        context, key = n.str("id"), title = n.str("title", "SISFOPERS KC"), body = n.str("message"),
                        channel = Notifier.channelFor(null, n.strOrNull("icon")), url = n.strOrNull("url"), notificationId = n.str("id"),
                    )
                } else {
                    Ui.toast("${n.str("title")}: ${n.str("message")}", MsgKind.Info)
                }
            }
        }
        Session.lastNotificationSync = ok.data.str("server_time", System.currentTimeMillis().toString())
        return unread
    }
}

/** Polling ringan saat aplikasi di layar depan: badge notifikasi & panggilan masuk. */
object ForegroundWatcher : DefaultLifecycleObserver {
    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)
    private var notifJob: Job? = null
    private var callJob: Job? = null
    private lateinit var appContext: Context

    fun install(context: Context) {
        appContext = context.applicationContext
        ProcessLifecycleOwner.get().lifecycle.addObserver(this)
    }

    override fun onStart(owner: LifecycleOwner) {
        AppVisibility.foreground = true
        start()
    }

    override fun onStop(owner: LifecycleOwner) {
        AppVisibility.foreground = false
        notifJob?.cancel(); callJob?.cancel()
    }

    fun start() {
        if (!Session.isLoggedIn) return
        if (notifJob?.isActive != true) {
            notifJob = scope.launch {
                while (isActive) {
                    if (Session.isLoggedIn) {
                        try {
                            NotificationSync.sync(appContext, showSystem = false)
                        } catch (_: Exception) {
                        }
                    }
                    delay(25_000)
                }
            }
        }
        if (callJob?.isActive != true) {
            callJob = scope.launch {
                delay(3_000)
                while (isActive) {
                    try {
                        pollCalls()
                    } catch (_: Exception) {
                    }
                    delay(if (PushManager.isReady()) 9_000 else 5_000)
                }
            }
        }
    }

    private suspend fun pollCalls() {
        val me = Session.me.value ?: return
        if (AppVisibility.inCall || !Session.isLoggedIn) return
        if (me.isChatOperator) {
            val r = Api.get("${me.chatPrefix}/live-chat/sync") as? ApiResult.Ok ?: return
            AppState.chatUnread.value = r.data.obj("stats").int("total_unread")
            val c = r.data.objOrNull("incoming_call") ?: return
            if (c.str("status") != "RINGING") return
            dispatch(c, me.chatPrefix)
        } else if (me.hasPersonel && !me.needsOtp) {
            val thread = ChatMemory.personelThread ?: return
            if (AppVisibility.activeChatThread == thread) return
            val r = Api.get("/personel/live-chat/$thread/call/signal") as? ApiResult.Ok ?: return
            val c = r.data.objOrNull("call") ?: return
            if (c.str("status") != "RINGING" || c.obj("caller").str("type") != "OPERATOR") return
            dispatch(c, "/personel")
        }
    }

    private fun dispatch(c: JsonObject, prefix: String) {
        val caller = c.obj("caller")
        CallCoordinator.onIncoming(
            appContext,
            IncomingCall(
                threadUuid = c.str("thread_uuid"),
                callId = c.str("call_id"),
                callerName = caller.str("name", "Petugas Layanan"),
                callerRank = caller.str("pangkat"),
                callType = c.str("call_type", "video"),
                prefix = prefix,
                callerPhoto = caller.strOrNull("photo"),
            ),
        )
    }
}

/** Mengingat utas chat personel untuk deteksi panggilan masuk. */
object ChatMemory {
    private const val KEY = "personel_thread"
    var personelThread: String?
        get() = Api.appContext.getSharedPreferences("sisfopers_chat", Context.MODE_PRIVATE).getString(KEY, null)
        set(v) = Api.appContext.getSharedPreferences("sisfopers_chat", Context.MODE_PRIVATE).edit().putString(KEY, v).apply()
}
