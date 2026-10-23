package id.my.sisfoperskc.app.push

import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.ui.nav.AppState
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch

class SisfoMessagingService : FirebaseMessagingService() {
    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    override fun onNewToken(token: String) {
        scope.launch { PushManager.onNewToken(applicationContext, token) }
    }

    override fun onMessageReceived(message: RemoteMessage) {
        if (!Session.isLoggedIn) return
        val data = message.data
        val type = data["type"] ?: "notification"
        val title = data["title"] ?: message.notification?.title ?: "SISFOPERS KC"
        val body = data["body"] ?: message.notification?.body ?: ""
        val url = data["url"]

        when (type) {
            "call" -> handleCall(data)
            "chat" -> {
                val thread = data["thread_uuid"].orEmpty()
                if (AppVisibility.foreground && AppVisibility.activeChatThread == thread) return
                AppState.chatUnread.value = AppState.chatUnread.value + 1
                Notifier.showChat(applicationContext, thread, title.removePrefix("Pesan baru dari ").ifBlank { title }, body, url)
            }
            else -> {
                val nid = data["notification_id"]
                if (nid != null) {
                    if (nid in Session.shownNotificationIds()) return
                    Session.markNotificationShown(nid)
                    AppState.unread.value = AppState.unread.value + 1
                }
                Notifier.show(
                    applicationContext,
                    key = nid ?: (title + body),
                    title = title,
                    body = body,
                    channel = Notifier.channelFor(type, data["icon"]),
                    url = url,
                    notificationId = nid,
                )
            }
        }
    }

    private fun handleCall(data: Map<String, String>) {
        val sentAt = Fmt.parse(data["sent_at"])?.time ?: System.currentTimeMillis()
        if (System.currentTimeMillis() - sentAt > 50_000) return
        val me = Session.me.value ?: return
        val call = IncomingCall(
            threadUuid = data["thread_uuid"].orEmpty(),
            callId = data["call_id"].orEmpty(),
            callerName = data["caller_name"] ?: "Petugas Layanan",
            callerRank = data["caller_rank"].orEmpty(),
            callType = data["call_type"] ?: "video",
            prefix = me.chatPrefix,
        )
        if (call.threadUuid.isBlank()) return
        CallCoordinator.onIncoming(applicationContext, call)
    }
}
