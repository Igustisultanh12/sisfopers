package id.my.sisfoperskc.app.push

import android.Manifest
import android.app.NotificationChannel
import android.app.NotificationChannelGroup
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.media.AudioAttributes
import android.media.RingtoneManager
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.core.app.Person
import androidx.core.content.ContextCompat
import id.my.sisfoperskc.app.MainActivity
import id.my.sisfoperskc.app.R
import id.my.sisfoperskc.app.call.IncomingCallActivity

object Notifier {
    const val CH_GENERAL = "sisfopers_general"
    const val CH_BROADCAST = "sisfopers_broadcast"
    const val CH_CHAT = "sisfopers_chat"
    const val CH_CALL = "sisfopers_call_v2"
    const val CH_SILENT = "sisfopers_silent"

    const val EXTRA_URL = "nav_url"
    const val EXTRA_NOTIFICATION_ID = "notification_id"
    const val CALL_NOTIFICATION_ID = 7001

    fun createChannels(context: Context) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.O) return
        val nm = context.getSystemService(NotificationManager::class.java) ?: return
        nm.createNotificationChannelGroup(NotificationChannelGroup("sisfopers", "SISFOPERS KC"))
        val general = NotificationChannel(CH_GENERAL, "Pemberitahuan Umum", NotificationManager.IMPORTANCE_HIGH).apply {
            description = "Verifikasi, status pengajuan, formulir, dan pemberitahuan sistem"
            group = "sisfopers"
            enableVibration(true)
            enableLights(true)
            lightColor = 0xFF2563EB.toInt()
        }
        val broadcast = NotificationChannel(CH_BROADCAST, "Broadcast & Kegiatan", NotificationManager.IMPORTANCE_HIGH).apply {
            description = "Instruksi kegiatan, apel, latihan, dan undangan rapat dinas"
            group = "sisfopers"
            enableVibration(true)
        }
        val chat = NotificationChannel(CH_CHAT, "Pusat Layanan Informasi", NotificationManager.IMPORTANCE_HIGH).apply {
            description = "Pesan baru dari layanan informasi"
            group = "sisfopers"
            enableVibration(true)
        }
        val call = NotificationChannel(CH_CALL, "Panggilan Video", NotificationManager.IMPORTANCE_HIGH).apply {
            description = "Panggilan video masuk dari Pusat Layanan Informasi"
            group = "sisfopers"
            enableVibration(true)
            vibrationPattern = longArrayOf(0, 800, 600, 800, 600, 800)
            setSound(
                RingtoneManager.getDefaultUri(RingtoneManager.TYPE_RINGTONE),
                AudioAttributes.Builder()
                    .setUsage(AudioAttributes.USAGE_NOTIFICATION_RINGTONE)
                    .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                    .build(),
            )
            lockscreenVisibility = android.app.Notification.VISIBILITY_PUBLIC
        }
        val silent = NotificationChannel(CH_SILENT, "Proses Latar", NotificationManager.IMPORTANCE_LOW).apply {
            description = "Status proses yang berjalan di latar"
            group = "sisfopers"
        }
        nm.createNotificationChannels(listOf(general, broadcast, chat, call, silent))
    }

    fun canNotify(context: Context): Boolean {
        if (Build.VERSION.SDK_INT >= 33 &&
            ContextCompat.checkSelfPermission(context, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED
        ) return false
        return NotificationManagerCompat.from(context).areNotificationsEnabled()
    }

    private fun contentIntent(context: Context, url: String?, notificationId: String?, requestCode: Int): PendingIntent {
        val intent = Intent(context, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            putExtra(EXTRA_URL, url ?: "")
            if (notificationId != null) putExtra(EXTRA_NOTIFICATION_ID, notificationId)
        }
        return PendingIntent.getActivity(context, requestCode, intent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
    }

    fun channelFor(type: String?, icon: String?): String = when {
        type == "chat" -> CH_CHAT
        type == "vicon" || type == "broadcast" -> CH_BROADCAST
        icon == "megaphone" || icon == "broadcast" || icon == "calendar" -> CH_BROADCAST
        else -> CH_GENERAL
    }

    fun show(
        context: Context,
        key: String,
        title: String,
        body: String,
        channel: String = CH_GENERAL,
        url: String? = null,
        notificationId: String? = null,
    ) {
        if (!canNotify(context)) return
        val id = key.hashCode()
        val builder = NotificationCompat.Builder(context, channel)
            .setSmallIcon(R.drawable.ic_notification)
            .setColor(0xFF2563EB.toInt())
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(NotificationCompat.BigTextStyle().bigText(body))
            .setAutoCancel(true)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setCategory(if (channel == CH_CHAT) NotificationCompat.CATEGORY_MESSAGE else NotificationCompat.CATEGORY_EVENT)
            .setDefaults(NotificationCompat.DEFAULT_ALL)
            .setContentIntent(contentIntent(context, url, notificationId, id))
            .setGroup(channel)
        try {
            NotificationManagerCompat.from(context).notify(id, builder.build())
        } catch (_: SecurityException) {
        }
    }

    fun showChat(context: Context, threadUuid: String, sender: String, message: String, url: String?) {
        if (!canNotify(context)) return
        val id = ("chat:$threadUuid").hashCode()
        val person = Person.Builder().setName(sender).build()
        val style = NotificationCompat.MessagingStyle(Person.Builder().setName("Saya").build())
            .addMessage(message, System.currentTimeMillis(), person)
        val n = NotificationCompat.Builder(context, CH_CHAT)
            .setSmallIcon(R.drawable.ic_notification)
            .setColor(0xFF2563EB.toInt())
            .setContentTitle(sender)
            .setContentText(message)
            .setStyle(style)
            .setAutoCancel(true)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setCategory(NotificationCompat.CATEGORY_MESSAGE)
            .setContentIntent(contentIntent(context, url, null, id))
            .build()
        try {
            NotificationManagerCompat.from(context).notify(id, n)
        } catch (_: SecurityException) {
        }
    }

    fun showIncomingCall(context: Context, call: IncomingCall) {
        val fullIntent = IncomingCallActivity.intent(context, call)
        val fullPending = PendingIntent.getActivity(context, 9001, fullIntent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
        val acceptPending = PendingIntent.getActivity(
            context, 9002, IncomingCallActivity.intent(context, call, autoAccept = true),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
        )
        val declinePending = PendingIntent.getBroadcast(
            context, 9003, CallActionReceiver.declineIntent(context, call),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE,
        )
        val builder = NotificationCompat.Builder(context, CH_CALL)
            .setSmallIcon(R.drawable.ic_notification)
            .setColor(0xFF2563EB.toInt())
            .setContentTitle("Panggilan video masuk")
            .setContentText("${call.callerName} sedang memanggil Anda")
            .setPriority(NotificationCompat.PRIORITY_MAX)
            .setCategory(NotificationCompat.CATEGORY_CALL)
            .setOngoing(true)
            .setAutoCancel(false)
            .setTimeoutAfter(45_000)
            .setFullScreenIntent(fullPending, true)
            .setContentIntent(fullPending)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            val caller = Person.Builder().setName(call.callerName).setImportant(true).build()
            builder.setStyle(NotificationCompat.CallStyle.forIncomingCall(caller, declinePending, acceptPending))
        } else {
            builder.addAction(0, "Tolak", declinePending).addAction(0, "Terima", acceptPending)
        }
        try {
            if (canNotify(context)) NotificationManagerCompat.from(context).notify(CALL_NOTIFICATION_ID, builder.build())
        } catch (_: Exception) {
        }
    }

    fun cancelCall(context: Context) {
        NotificationManagerCompat.from(context).cancel(CALL_NOTIFICATION_ID)
    }

    fun cancelAll(context: Context) {
        NotificationManagerCompat.from(context).cancelAll()
    }
}

/** Data panggilan masuk (chat layanan informasi). */
data class IncomingCall(
    val threadUuid: String,
    val callId: String,
    val callerName: String,
    val callerRank: String,
    val callType: String,
    val prefix: String,
    val callerPhoto: String? = null,
)
