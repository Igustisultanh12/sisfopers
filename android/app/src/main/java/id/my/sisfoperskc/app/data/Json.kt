package id.my.sisfoperskc.app.data

import kotlinx.serialization.json.Json
import kotlinx.serialization.json.JsonArray
import kotlinx.serialization.json.JsonElement
import kotlinx.serialization.json.JsonNull
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.JsonPrimitive
import kotlinx.serialization.json.booleanOrNull
import kotlinx.serialization.json.contentOrNull
import kotlinx.serialization.json.doubleOrNull
import kotlinx.serialization.json.longOrNull

/**
 * Pembacaan JSON yang toleran: server Laravel kadang mengirim angka sebagai string,
 * boolean sebagai 0/1, atau objek kosong sebagai array kosong.
 */
val JsonLenient = Json {
    ignoreUnknownKeys = true
    isLenient = true
    explicitNulls = false
    coerceInputValues = true
}

val EmptyObject = JsonObject(emptyMap())
val EmptyArray = JsonArray(emptyList())

fun parseJson(text: String): JsonElement? = try {
    JsonLenient.parseToJsonElement(text)
} catch (_: Throwable) {
    null
}

fun JsonElement?.asObj(): JsonObject = this as? JsonObject ?: EmptyObject
fun JsonElement?.asArr(): JsonArray = when (this) {
    is JsonArray -> this
    is JsonObject -> JsonArray(this.values.toList())
    else -> EmptyArray
}

fun JsonElement?.asStr(): String? = when (this) {
    null, JsonNull -> null
    is JsonPrimitive -> this.contentOrNull
    else -> this.toString()
}

fun JsonObject?.str(key: String, default: String = ""): String {
    val v = this?.get(key) ?: return default
    return v.asStr() ?: default
}

fun JsonObject?.strOrNull(key: String): String? {
    val v = this?.get(key) ?: return null
    val s = v.asStr()
    return if (s.isNullOrBlank() || s == "null") null else s
}

fun JsonObject?.int(key: String, default: Int = 0): Int {
    val p = this?.get(key) as? JsonPrimitive ?: return default
    return p.longOrNull?.toInt() ?: p.doubleOrNull?.toInt() ?: p.contentOrNull?.toIntOrNull() ?: default
}

fun JsonObject?.long(key: String, default: Long = 0): Long {
    val p = this?.get(key) as? JsonPrimitive ?: return default
    return p.longOrNull ?: p.doubleOrNull?.toLong() ?: p.contentOrNull?.toLongOrNull() ?: default
}

fun JsonObject?.bool(key: String, default: Boolean = false): Boolean {
    val p = this?.get(key) as? JsonPrimitive ?: return default
    p.booleanOrNull?.let { return it }
    return when (p.contentOrNull?.lowercase()) {
        "1", "true", "yes", "on" -> true
        "0", "false", "no", "off", "" -> false
        else -> default
    }
}

fun JsonObject?.obj(key: String): JsonObject = this?.get(key).asObj()
fun JsonObject?.objOrNull(key: String): JsonObject? = this?.get(key) as? JsonObject
fun JsonObject?.arr(key: String): JsonArray = this?.get(key).asArr()
fun JsonObject?.objects(key: String): List<JsonObject> = arr(key).mapNotNull { it as? JsonObject }
fun JsonObject?.strings(key: String): List<String> = arr(key).mapNotNull { it.asStr() }

/** Navigasi jalur bertitik, mis. "personel.user.email". */
fun JsonObject?.path(dotted: String): JsonElement? {
    var cur: JsonElement? = this
    for (part in dotted.split('.')) {
        cur = (cur as? JsonObject)?.get(part) ?: return null
    }
    return cur
}

fun JsonObject?.pathStr(dotted: String, default: String = ""): String = path(dotted).asStr()?.takeIf { it != "null" } ?: default

/** Paginator Laravel (LengthAwarePaginator) */
data class Page(
    val items: List<JsonObject>,
    val currentPage: Int,
    val lastPage: Int,
    val total: Int,
) {
    val hasMore: Boolean get() = currentPage < lastPage
}

fun JsonElement?.toPage(): Page {
    return when (this) {
        is JsonArray -> Page(this.mapNotNull { it as? JsonObject }, 1, 1, this.size)
        is JsonObject -> {
            if (this.containsKey("data") && (this["data"] is JsonArray)) {
                val items = this.objects("data")
                val meta = this.objOrNull("meta")
                Page(
                    items = items,
                    currentPage = (meta ?: this).int("current_page", 1),
                    lastPage = (meta ?: this).int("last_page", 1),
                    total = (meta ?: this).int("total", items.size),
                )
            } else {
                Page(emptyList(), 1, 1, 0)
            }
        }
        else -> Page(emptyList(), 1, 1, 0)
    }
}

/** Konversi nilai Kotlin menjadi JsonElement untuk badan permintaan JSON. */
fun Any?.toJsonElement(): JsonElement = when (this) {
    null -> JsonNull
    is JsonElement -> this
    is String -> JsonPrimitive(this)
    is Number -> JsonPrimitive(this)
    is Boolean -> JsonPrimitive(this)
    is Map<*, *> -> JsonObject(this.entries.associate { (k, v) -> k.toString() to v.toJsonElement() })
    is Iterable<*> -> JsonArray(this.map { it.toJsonElement() })
    is Array<*> -> JsonArray(this.map { it.toJsonElement() })
    else -> JsonPrimitive(this.toString())
}

/** Nilai teks untuk isian formulir; null → "". */
fun asStrOrEmpty(e: JsonElement?): String = e.asStr()?.takeIf { it != "null" } ?: ""
