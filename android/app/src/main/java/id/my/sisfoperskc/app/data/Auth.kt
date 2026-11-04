package id.my.sisfoperskc.app.data

import android.content.Context
import id.my.sisfoperskc.app.push.NotificationSyncWorker
import id.my.sisfoperskc.app.push.Notifier
import id.my.sisfoperskc.app.push.PushManager
import id.my.sisfoperskc.app.ui.nav.AppState
import id.my.sisfoperskc.app.ui.nav.Rt
import kotlinx.serialization.json.JsonObject

object Auth {
    /** Muat ulang profil sesi dari server; null bila sesi tidak valid. */
    suspend fun refreshMe(context: Context): ApiResult {
        val r = Api.appGet("/me")
        if (r is ApiResult.Ok) {
            val me = r.data.objOrNull("me") ?: return r
            Session.updateMe(me)
            AppState.unread.value = me.int("unread_notifications")
            val app = me.obj("app")
            app.strOrNull("agora_app_id")?.let { Session.agoraAppId = it }
            PushManager.applyServerConfig(context, app.objOrNull("firebase"))
            PushManager.registerDevice(context)
        }
        return r
    }

    suspend fun loadConfig(context: Context): JsonObject? {
        val r = Api.appGet("/config") as? ApiResult.Ok ?: return null
        r.data.strOrNull("agora_app_id")?.let { Session.agoraAppId = it }
        PushManager.applyServerConfig(context, r.data.objOrNull("firebase"))
        return r.data
    }

    fun routeFor(me: Me?): String = when {
        me == null -> Rt.LOGIN
        me.needsOtp -> Rt.GATE_OTP
        me.needsSinyalmen -> Rt.GATE_SINYALMEN
        me.needsKewilayahan -> Rt.GATE_WILAYAH
        else -> Rt.HOME
    }

    /** ID perangkat yang stabil (tetap sama walau aplikasi dipasang ulang) untuk aturan satu akun satu HP. */
    fun deviceId(context: Context): String {
        @Suppress("HardwareIds")
        val raw = android.provider.Settings.Secure.getString(context.contentResolver, android.provider.Settings.Secure.ANDROID_ID)
            ?: (android.os.Build.MANUFACTURER + android.os.Build.MODEL + android.os.Build.FINGERPRINT)
        val digest = java.security.MessageDigest.getInstance("SHA-256").digest(("sisfoperskc:" + raw).toByteArray())
        return digest.take(16).joinToString("") { "%02x".format(it) }
    }

    fun deviceName(): String = "${android.os.Build.MANUFACTURER.replaceFirstChar { it.uppercase() }} ${android.os.Build.MODEL}"

    suspend fun login(context: Context, username: String, password: String, logoutWeb: Boolean = false): ApiResult {
        val r = Api.appSend(
            "/login",
            mapOf(
                "username" to username.trim(), "password" to password,
                "device_name" to deviceName(), "device_id" to deviceId(context),
                "logout_web" to logoutWeb,
            ),
        )
        if (r is ApiResult.Ok) {
            val token = r.data.strOrNull("token")
            val me = r.data.objOrNull("me")
            if (token == null || me == null) return ApiResult.Err(0, "invalid", "Respons login tidak valid.")
            Session.saveLogin(token, me)
            Session.lastUsername = username.trim()
            AppState.unread.value = me.int("unread_notifications")
            val app = me.obj("app")
            app.strOrNull("agora_app_id")?.let { Session.agoraAppId = it }
            PushManager.applyServerConfig(context, app.objOrNull("firebase"))
            PushManager.registerDevice(context, force = true)
            NotificationSyncWorker.schedule(context)
        }
        return r
    }

    suspend fun logout(context: Context) {
        try {
            Api.appSend("/logout", mapOf("fcm_token" to Session.fcmToken))
        } catch (_: Exception) {
        }
        clearLocal(context)
    }

    fun clearLocal(context: Context) {
        Session.clear()
        AppState.unread.value = 0
        AppState.chatUnread.value = 0
        NotificationSyncWorker.cancel(context)
        Notifier.cancelAll(context)
    }
}
