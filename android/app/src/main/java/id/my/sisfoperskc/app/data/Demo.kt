package id.my.sisfoperskc.app.data

import android.content.Context
import id.my.sisfoperskc.app.BuildConfig
import kotlinx.serialization.json.JsonObject
import okhttp3.Interceptor
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.Protocol
import okhttp3.Response
import okhttp3.ResponseBody.Companion.toResponseBody

/**
 * Mode demo khusus build tangkapan layar (BuildConfig.DEMO = true, tidak pernah aktif
 * di APK rilis). Semua permintaan /api/m dijawab dari berkas contoh di assets/demo/<peran>/
 * sehingga layar dapat dipotret tanpa server.
 */
object Demo {
    @Volatile var role: String = "guest"
    private lateinit var appContext: Context
    private val json = "application/json; charset=utf-8".toMediaType()

    val enabled: Boolean get() = BuildConfig.DEMO

    fun init(context: Context) {
        appContext = context.applicationContext
    }

    fun key(path: String, query: List<Pair<String, String>>): String {
        var k = path.trim('/').replace("/", "__").ifBlank { "root" }
        if (query.isNotEmpty()) k += ".q." + query.sortedWith(compareBy({ it.first }, { it.second })).joinToString("_") { "${it.first}-${it.second}" }
        return k.replace(Regex("[^A-Za-z0-9._-]"), "_")
    }

    private fun asset(role: String, key: String): String? = try {
        appContext.assets.open("demo/$role/$key.json").bufferedReader().use { it.readText() }
    } catch (_: Exception) {
        null
    }

    /** Masuk sebagai peran contoh (admin, kmatra, pju, personel) atau "guest". */
    fun signIn(role: String) {
        if (role == "fresh") {
            this.role = "guest"
            Session.clear()
            Session.onboardingSeen = false
            return
        }
        this.role = role
        Session.onboardingSeen = true
        if (role == "guest") {
            Session.clear()
            return
        }
        val me = asset(role, "app__me")?.let { parseJson(it) as? JsonObject }?.objOrNull("me") ?: return
        Session.saveLogin("demo-$role", me)
    }

    val interceptor = Interceptor { chain ->
        val req = chain.request()
        val url = req.url
        val encoded = url.encodedPath
        val body: String
        val code: Int
        if (!encoded.startsWith("/api/m")) {
            body = "{}"
            code = 404
        } else {
            val path = encoded.removePrefix("/api/m")
            val query = (0 until url.querySize).map { url.queryParameterName(it) to (url.queryParameterValue(it) ?: "") }
            val found = if (req.method == "GET") asset(role, key(path, query)) ?: asset(role, key(path, emptyList())) else null
            when {
                found != null -> { body = found; code = 200 }
                req.method == "GET" -> { body = "{\"success\":true}"; code = 200 }
                else -> { body = "{\"success\":true,\"message\":\"Tersimpan (mode demo).\"}"; code = 200 }
            }
        }
        Response.Builder()
            .request(req)
            .protocol(Protocol.HTTP_1_1)
            .code(code)
            .message(if (code == 200) "OK" else "Not Found")
            .body(body.toResponseBody(json))
            .build()
    }
}
