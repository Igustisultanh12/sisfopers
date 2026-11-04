package id.my.sisfoperskc.app.data

import android.content.Context
import android.net.Uri
import android.os.Build
import id.my.sisfoperskc.app.BuildConfig
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.JsonPrimitive
import okhttp3.HttpUrl.Companion.toHttpUrlOrNull
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import okhttp3.Response
import okio.BufferedSink
import okio.source
import java.io.File
import java.io.IOException
import java.net.SocketTimeoutException
import java.net.URLEncoder
import java.net.UnknownHostException
import java.util.concurrent.TimeUnit

sealed class ApiResult {
    data class Ok(
        val data: JsonObject,
        val component: String? = null,
        val message: String? = null,
    ) : ApiResult()

    data class Err(
        val status: Int,
        val code: String? = null,
        val message: String,
        val errors: Map<String, String> = emptyMap(),
        val data: JsonObject? = null,
    ) : ApiResult()

    val isOk get() = this is Ok
    fun okOrNull(): Ok? = this as? Ok
    fun errMessage(): String? = (this as? Err)?.message
}

/** Berkas yang akan diunggah dari Uri (galeri, kamera, dokumen). */
data class UploadFile(val uri: Uri, val name: String, val mime: String, val size: Long = -1)

sealed class ApiEvent {
    data object Unauthenticated : ApiEvent()
    data object Maintenance : ApiEvent()
    data class Gate(val code: String, val message: String) : ApiEvent()
}

object Api {
    lateinit var appContext: Context
        private set

    val events = MutableSharedFlow<ApiEvent>(extraBufferCapacity = 16)

    private val jsonType = "application/json; charset=utf-8".toMediaType()

    val userAgent: String
        get() = "SISFOPERSKC-Android/${BuildConfig.VERSION_NAME} (Android ${Build.VERSION.RELEASE}; ${Build.MANUFACTURER} ${Build.MODEL})"

    val client: OkHttpClient by lazy {
        OkHttpClient.Builder()
            .connectTimeout(20, TimeUnit.SECONDS)
            .readTimeout(90, TimeUnit.SECONDS)
            .writeTimeout(120, TimeUnit.SECONDS)
            .retryOnConnectionFailure(true)
            .addInterceptor { chain ->
                val req = chain.request()
                val base = Session.baseUrl.toHttpUrlOrNull()
                val builder = req.newBuilder().header("User-Agent", userAgent)
                val token = Session.token
                if (token != null && base != null && req.url.host == base.host && req.header("Authorization") == null) {
                    builder.header("Authorization", "Bearer $token")
                }
                chain.proceed(builder.build())
            }
            .apply { if (Demo.enabled) addInterceptor(Demo.interceptor) }
            .build()
    }

    fun init(context: Context) {
        appContext = context.applicationContext
    }

    fun webUrl(path: String): String = Session.baseUrl + "/api/m" + ensureSlash(path)
    fun appUrl(path: String): String = Session.baseUrl + "/api/m/app" + ensureSlash(path)

    private fun ensureSlash(p: String) = if (p.startsWith("/")) p else "/$p"

    /** URL gambar/berkas: privat lewat stream terautentikasi, publik lewat /storage. */
    fun fileUrl(path: String?): String? {
        if (path.isNullOrBlank() || path == "null" || path == "-") return null
        val p = path.trim()
        return when {
            p.startsWith("data:") -> p
            p.startsWith("http://") || p.startsWith("https://") -> p
            p.startsWith("/storage/") -> Session.baseUrl + p
            p.startsWith("storage/") -> Session.baseUrl + "/" + p
            p.startsWith("settings/") -> Session.baseUrl + "/storage/" + p
            else -> Session.baseUrl + "/api/m/documents/private-stream?path=" + URLEncoder.encode(p, "UTF-8")
        }
    }

    // ------------------------------------------------------------------ Permintaan

    suspend fun get(path: String, query: Map<String, Any?> = emptyMap()): ApiResult =
        execute(buildGet(webUrl(path), query))

    suspend fun send(
        path: String,
        body: Map<String, Any?> = emptyMap(),
        method: String = "POST",
        files: List<Pair<String, UploadFile>> = emptyList(),
    ): ApiResult = execute(buildSend(webUrl(path), body, method, files))

    suspend fun appGet(path: String, query: Map<String, Any?> = emptyMap()): ApiResult =
        execute(buildGet(appUrl(path), query))

    suspend fun appSend(path: String, body: Map<String, Any?> = emptyMap(), method: String = "POST"): ApiResult =
        execute(buildSend(appUrl(path), body, method, emptyList()))

    private fun baseRequest(url: String): Request.Builder = Request.Builder()
        .url(url)
        .header("Accept", "application/json")
        .header("X-Requested-With", "XMLHttpRequest")

    private fun buildGet(url: String, query: Map<String, Any?>): Request {
        val httpUrl = url.toHttpUrlOrNull()?.newBuilder()
            ?: throw IllegalArgumentException("URL tidak valid: $url")
        val pairs = mutableListOf<Pair<String, String>>()
        query.forEach { (k, v) -> flatten(k, v, pairs) }
        pairs.forEach { (k, v) -> httpUrl.addQueryParameter(k, v) }
        return baseRequest(httpUrl.build().toString()).get().build()
    }

    private fun buildSend(url: String, body: Map<String, Any?>, method: String, files: List<Pair<String, UploadFile>>): Request {
        val m = method.uppercase()
        return if (files.isEmpty()) {
            val json = body.filterValues { it != null }.toJsonElement().toString()
            val rb = json.toRequestBody(jsonType)
            baseRequest(url).method(m, rb).build()
        } else {
            val mb = MultipartBody.Builder().setType(MultipartBody.FORM)
            val pairs = mutableListOf<Pair<String, String>>()
            body.forEach { (k, v) -> flatten(k, v, pairs) }
            pairs.forEach { (k, v) -> mb.addFormDataPart(k, v) }
            files.forEach { (field, file) -> mb.addFormDataPart(field, file.name, UriRequestBody(appContext, file)) }
            if (m != "POST") mb.addFormDataPart("_method", m)
            baseRequest(url).post(mb.build()).build()
        }
    }

    private fun flatten(key: String, value: Any?, out: MutableList<Pair<String, String>>) {
        when (value) {
            null -> Unit
            is Map<*, *> -> value.forEach { (k, v) -> flatten("$key[$k]", v, out) }
            is Iterable<*> -> value.forEachIndexed { i, v -> flatten("$key[$i]", v, out) }
            is Boolean -> out += key to if (value) "1" else "0"
            is JsonPrimitive -> out += key to value.content
            is JsonObject -> value.forEach { (k, v) -> flatten("$key[$k]", v, out) }
            is JsonArray -> value.forEachIndexed { i, v -> flatten("$key[$i]", v, out) }
            else -> out += key to value.toString()
        }
    }

    private suspend fun execute(request: Request): ApiResult = withContext(Dispatchers.IO) {
        try {
            client.newCall(request).execute().use { resp -> parse(resp, request) }
        } catch (e: UnknownHostException) {
            ApiResult.Err(0, "network", "Tidak dapat terhubung ke server. Periksa koneksi internet Anda.")
        } catch (e: SocketTimeoutException) {
            ApiResult.Err(0, "timeout", "Server terlalu lama merespons. Silakan coba lagi.")
        } catch (e: IOException) {
            ApiResult.Err(0, "network", "Koneksi terputus. Periksa jaringan Anda lalu coba lagi.")
        } catch (e: Exception) {
            ApiResult.Err(0, "client", e.message ?: "Terjadi kesalahan pada aplikasi.")
        }
    }

    private fun parse(resp: Response, request: Request): ApiResult {
        val status = resp.code
        val text = resp.body?.string().orEmpty()
        val json = parseJson(text)
        val obj = json as? JsonObject
        val serverMessage = (obj?.get("message") as? JsonPrimitive)?.content?.takeIf { it.isNotBlank() && it != "null" }
            ?: (obj?.get("error") as? JsonPrimitive)?.content?.takeIf { it.isNotBlank() && it != "null" }
        val code = obj.strOrNull("code")

        when {
            status == 401 -> {
                if (request.header("Authorization") != null || Session.token != null) {
                    events.tryEmit(ApiEvent.Unauthenticated)
                }
                return ApiResult.Err(401, code ?: "unauthenticated", serverMessage ?: "Sesi Anda telah berakhir. Silakan login kembali.")
            }
            status == 503 || code == "maintenance" -> {
                if (code == "maintenance") events.tryEmit(ApiEvent.Maintenance)
                return ApiResult.Err(503, code ?: "unavailable", serverMessage ?: "Sistem sedang dalam pemeliharaan.")
            }
            status == 409 && (code == "needs_otp" || code == "needs_sinyalmen") -> {
                events.tryEmit(ApiEvent.Gate(code, serverMessage ?: ""))
                return ApiResult.Err(409, code, serverMessage ?: "Tindakan diperlukan.")
            }
        }

        if (status in 200..299) {
            if (json is JsonArray) return ApiResult.Ok(JsonObject(mapOf("items" to json)))
            if (obj == null) {
                return if (text.isBlank()) ApiResult.Ok(EmptyObject) else ApiResult.Ok(JsonObject(mapOf("raw" to JsonPrimitive(text))))
            }
            if (obj.containsKey("component") && obj.containsKey("props")) {
                return ApiResult.Ok(obj.obj("props"), component = obj.strOrNull("component"))
            }
            val successFlag = obj["success"] as? JsonPrimitive
            if (successFlag != null && !obj.bool("success", true)) {
                return ApiResult.Err(status, code, serverMessage ?: "Permintaan tidak dapat diproses.", errorsOf(obj), obj)
            }
            return ApiResult.Ok(obj, message = serverMessage)
        }

        val errors = errorsOf(obj)
        val message = when (status) {
            422 -> serverMessage?.takeIf { !it.startsWith("The given data") } ?: errors.values.firstOrNull() ?: "Periksa kembali isian Anda."
            403 -> serverMessage ?: "Anda tidak memiliki akses ke fitur ini."
            404 -> serverMessage?.takeIf { it.length < 200 } ?: "Data tidak ditemukan."
            413 -> "Ukuran berkas terlalu besar."
            419 -> "Sesi kedaluwarsa. Silakan ulangi."
            423 -> serverMessage ?: "Akses dikunci sementara."
            429 -> serverMessage ?: "Terlalu banyak percobaan. Silakan tunggu sebentar."
            in 500..599 -> serverMessage?.takeIf { it.length < 200 && !it.contains("SQLSTATE") } ?: "Terjadi gangguan pada server ($status)."
            else -> serverMessage ?: "Permintaan gagal ($status)."
        }
        return ApiResult.Err(status, code, message, errors, obj)
    }

    private fun errorsOf(obj: JsonObject?): Map<String, String> {
        val e = obj?.get("errors") as? JsonObject ?: return emptyMap()
        return e.mapNotNull { (k, v) ->
            val msg = when (v) {
                is JsonArray -> v.firstOrNull()?.asStr()
                else -> v.asStr()
            }
            msg?.let { k to it }
        }.toMap()
    }

    // ------------------------------------------------------------------ Unduhan

    data class Downloaded(val file: File, val mime: String, val name: String)

    suspend fun download(path: String, query: Map<String, Any?> = emptyMap(), fallbackName: String = "dokumen"): Result<Downloaded> =
        withContext(Dispatchers.IO) {
            try {
                val req = buildGet(webUrl(path), query).newBuilder()
                    .header("Accept", "application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,*/*")
                    .build()
                client.newCall(req).execute().use { resp ->
                    val mime = resp.header("Content-Type")?.substringBefore(';')?.trim() ?: "application/octet-stream"
                    if (!resp.isSuccessful || mime.contains("json")) {
                        val result = parse(resp, req)
                        val msg = (result as? ApiResult.Err)?.message ?: "Dokumen tidak tersedia."
                        return@withContext Result.failure(IOException(msg))
                    }
                    val disposition = resp.header("Content-Disposition").orEmpty()
                    var name = Regex("filename\\*=UTF-8''([^;]+)").find(disposition)?.groupValues?.get(1)
                        ?.let { java.net.URLDecoder.decode(it, "UTF-8") }
                        ?: Regex("filename=\"?([^\";]+)\"?").find(disposition)?.groupValues?.get(1)
                        ?: fallbackName
                    if (!name.contains('.')) {
                        name += when {
                            mime.contains("pdf") -> ".pdf"
                            mime.contains("sheet") || mime.contains("excel") -> ".xlsx"
                            mime.contains("png") -> ".png"
                            mime.contains("jpeg") -> ".jpg"
                            mime.contains("html") -> ".html"
                            else -> ""
                        }
                    }
                    if (mime.contains("html") && !name.endsWith(".html")) name = name.substringBeforeLast('.') + ".html"
                    val dir = File(appContext.cacheDir, "downloads").apply { mkdirs() }
                    val file = File(dir, name.replace(Regex("[\\\\/:*?\"<>|]"), "_"))
                    resp.body?.byteStream()?.use { input -> file.outputStream().use { input.copyTo(it) } }
                    Result.success(Downloaded(file, mime, file.name))
                }
            } catch (e: Exception) {
                Result.failure(IOException(e.message ?: "Gagal mengunduh dokumen."))
            }
        }
}

/** RequestBody streaming dari content Uri agar berkas besar tidak dimuat penuh ke memori. */
private class UriRequestBody(private val context: Context, private val file: UploadFile) : RequestBody() {
    override fun contentType() = file.mime.toMediaTypeOrNull()
    override fun contentLength(): Long = file.size
    override fun writeTo(sink: BufferedSink) {
        context.contentResolver.openInputStream(file.uri)?.use { input ->
            sink.writeAll(input.source())
        } ?: throw IOException("Berkas tidak dapat dibaca.")
    }
}
