package id.my.sisfoperskc.app.push

import android.content.Context
import android.os.Build
import android.util.Log
import com.google.firebase.FirebaseApp
import com.google.firebase.FirebaseOptions
import com.google.firebase.messaging.FirebaseMessaging
import id.my.sisfoperskc.app.BuildConfig
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.parseJson
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import kotlinx.coroutines.suspendCancellableCoroutine
import kotlinx.serialization.json.JsonObject
import kotlin.coroutines.resume

/**
 * Firebase Cloud Messaging yang dikonfigurasi dari server (Pengaturan Sistem admin),
 * sehingga APK tidak perlu google-services.json.
 */
object PushManager {
    private const val TAG = "PushManager"

    fun isReady(): Boolean = try {
        FirebaseApp.getApps(Api.appContext).isNotEmpty()
    } catch (_: Throwable) {
        false
    }

    fun initFromCache(context: Context) {
        val cfg = Session.firebaseConfig?.let { parseJson(it) as? JsonObject } ?: return
        initialize(context, cfg)
    }

    private fun initialize(context: Context, cfg: JsonObject): Boolean {
        return try {
            if (FirebaseApp.getApps(context).isNotEmpty()) return true
            val appId = cfg.strOrNull("application_id") ?: return false
            val apiKey = cfg.strOrNull("api_key") ?: return false
            val projectId = cfg.strOrNull("project_id") ?: return false
            val options = FirebaseOptions.Builder()
                .setApplicationId(appId)
                .setApiKey(apiKey)
                .setProjectId(projectId)
                .apply {
                    cfg.strOrNull("sender_id")?.let { setGcmSenderId(it) }
                    cfg.strOrNull("storage_bucket")?.let { setStorageBucket(it) }
                }
                .build()
            FirebaseApp.initializeApp(context.applicationContext, options)
            FirebaseMessaging.getInstance().isAutoInitEnabled = true
            true
        } catch (e: Throwable) {
            Log.w(TAG, "Firebase gagal diinisialisasi: ${e.message}")
            false
        }
    }

    /** Terapkan konfigurasi Firebase dari /api/m/app/config atau /me. */
    fun applyServerConfig(context: Context, firebase: JsonObject?) {
        if (firebase == null) return
        val serialized = firebase.toString()
        val previous = Session.firebaseConfig?.let { parseJson(it) as? JsonObject }
        Session.firebaseConfig = serialized
        if (previous != null && previous.str("project_id") != firebase.str("project_id") && isReady()) {
            // Proyek berubah: berlaku setelah aplikasi dibuka ulang.
            Session.fcmRegisteredFor = null
            return
        }
        initialize(context, firebase)
    }

    private suspend fun fetchToken(): String? = suspendCancellableCoroutine { cont ->
        try {
            FirebaseMessaging.getInstance().token
                .addOnCompleteListener { task -> if (cont.isActive) cont.resume(if (task.isSuccessful) task.result else null) }
        } catch (e: Throwable) {
            if (cont.isActive) cont.resume(null)
        }
    }

    /** Daftarkan token perangkat ke server untuk akun yang sedang login. */
    suspend fun registerDevice(context: Context, force: Boolean = false) {
        if (!Session.isLoggedIn || !isReady()) return
        val token = fetchToken() ?: Session.fcmToken ?: return
        Session.fcmToken = token
        val me = Session.me.value ?: return
        val marker = "${me.type}:${me.id}:$token"
        if (!force && Session.fcmRegisteredFor == marker) return
        val r = Api.appSend(
            "/device",
            mapOf(
                "fcm_token" to token,
                "platform" to "android",
                "device_name" to "${Build.MANUFACTURER} ${Build.MODEL}".trim(),
                "app_version" to BuildConfig.VERSION_NAME,
            ),
        )
        if (r is ApiResult.Ok) Session.fcmRegisteredFor = marker
    }

    suspend fun onNewToken(context: Context, token: String) {
        Session.fcmToken = token
        Session.fcmRegisteredFor = null
        registerDevice(context, force = true)
    }
}
