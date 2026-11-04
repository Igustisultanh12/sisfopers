package id.my.sisfoperskc.app.data

import android.content.Context
import android.content.SharedPreferences
import id.my.sisfoperskc.app.BuildConfig
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.serialization.json.JsonObject

/** Profil sesi yang diterima dari /api/m/app/me */
data class Me(val raw: JsonObject) {
    val type: String get() = raw.str("type", "user")
    val isPju: Boolean get() = type == "pju"
    val id: Int get() = raw.int("id")
    val username: String get() = raw.str("username")
    val email: String get() = raw.str("email")
    val fullName: String get() = raw.str("full_name", username)
    val role: String get() = raw.str("role", "personel")
    val roleGroup: String get() = raw.str("role_group", "personel")
    val roleLabel: String get() = raw.str("role_label", role)
    val jabatan: String? get() = raw.strOrNull("jabatan")
    val matra: String? get() = raw.strOrNull("matra")
    val canBroadcast: Boolean get() = raw.bool("can_broadcast")
    val personel: JsonObject? get() = raw.objOrNull("personel")
    val photo: String? get() = personel.strOrNull("photo_profile")
    val pangkatLabel: String get() = personel.str("pangkat_label", personel.str("pangkat"))
    val needsOtp: Boolean get() = raw.obj("gates").bool("needs_otp")
    val needsSinyalmen: Boolean get() = raw.obj("gates").bool("needs_sinyalmen")
    val needsKewilayahan: Boolean get() = raw.obj("gates").bool("needs_kewilayahan")
    val unread: Int get() = raw.int("unread_notifications")
    val hasPersonel: Boolean get() = personel != null

    val isAdmin get() = roleGroup == "admin"
    val isKordinator get() = roleGroup == "kordinator"
    val isPjuRole get() = roleGroup == "pju"
    val isPersonel get() = roleGroup == "personel"
    val isKomandan get() = roleGroup == "komandan"

    /** Prefix rute layanan informasi (live chat) sesuai peran */
    val chatPrefix: String
        get() = when (roleGroup) {
            "admin" -> "/admin"
            "kordinator" -> "/kordinator"
            "pju" -> "/pju"
            else -> "/personel"
        }
    val isChatOperator: Boolean get() = roleGroup in setOf("admin", "kordinator", "pju")
}

object Session {
    private lateinit var prefs: SharedPreferences

    private val _me = MutableStateFlow<Me?>(null)
    val me: StateFlow<Me?> = _me

    fun init(context: Context) {
        prefs = context.getSharedPreferences("sisfopers_session", Context.MODE_PRIVATE)
        _me.value = prefs.getString("me", null)?.let { parseJson(it) as? JsonObject }?.let { Me(it) }
    }

    var token: String?
        get() = prefs.getString("token", null)
        set(v) = prefs.edit().putString("token", v).apply()

    var baseUrl: String
        get() = prefs.getString("base_url", null)?.takeIf { it.isNotBlank() } ?: BuildConfig.BASE_URL
        set(v) = prefs.edit().putString("base_url", v.trim().trimEnd('/')).apply()

    var firebaseConfig: String?
        get() = prefs.getString("firebase_config", null)
        set(v) = prefs.edit().putString("firebase_config", v).apply()

    var fcmToken: String?
        get() = prefs.getString("fcm_token", null)
        set(v) = prefs.edit().putString("fcm_token", v).apply()

    var fcmRegisteredFor: String?
        get() = prefs.getString("fcm_registered_for", null)
        set(v) = prefs.edit().putString("fcm_registered_for", v).apply()

    var lastNotificationSync: String?
        get() = prefs.getString("last_notif_sync", null)
        set(v) = prefs.edit().putString("last_notif_sync", v).apply()

    var onboardingSeen: Boolean
        get() = prefs.getBoolean("onboarding_seen", false)
        set(v) = prefs.edit().putBoolean("onboarding_seen", v).apply()

    var lastUsername: String?
        get() = prefs.getString("last_username", null)
        set(v) = prefs.edit().putString("last_username", v).apply()

    var agoraAppId: String
        get() = prefs.getString("agora_app_id", null) ?: "19daeb63b0ec46f2b02197c9fbbe81d6"
        set(v) = prefs.edit().putString("agora_app_id", v).apply()

    fun shownNotificationIds(): Set<String> = prefs.getStringSet("shown_notifs", emptySet()) ?: emptySet()
    fun markNotificationShown(id: String) {
        val set = shownNotificationIds().toMutableSet()
        set.add(id)
        val trimmed = if (set.size > 300) set.toList().takeLast(200).toSet() else set
        prefs.edit().putStringSet("shown_notifs", trimmed).apply()
    }

    val isLoggedIn: Boolean get() = !token.isNullOrBlank() && _me.value != null

    fun saveLogin(token: String, me: JsonObject) {
        prefs.edit().putString("token", token).putString("me", me.toString()).apply()
        _me.value = Me(me)
    }

    fun updateMe(me: JsonObject) {
        prefs.edit().putString("me", me.toString()).apply()
        _me.value = Me(me)
    }

    fun clear() {
        prefs.edit()
            .remove("token").remove("me").remove("fcm_registered_for")
            .remove("last_notif_sync").remove("shown_notifs")
            .apply()
        _me.value = null
    }
}
