package id.my.sisfoperskc.app.ui.components

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.LocationCity
import androidx.compose.material.icons.outlined.Map
import androidx.compose.material.icons.outlined.Place
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.unit.dp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import kotlinx.serialization.json.JsonObject

/** Cache sederhana referensi wilayah. */
object WilayahCache {
    var provinces: List<JsonObject>? = null
    val regencies = HashMap<String, List<JsonObject>>()
    val districts = HashMap<String, List<JsonObject>>()
    val villages = HashMap<String, List<JsonObject>>()

    suspend fun load(path: String): List<JsonObject> {
        val r = Api.get(path) as? ApiResult.Ok ?: return emptyList()
        return r.data.objects("items")
    }

    suspend fun provinces(): List<JsonObject> {
        provinces?.let { if (it.isNotEmpty()) return it }
        val list = load("/api/wilayah/provinces")
        provinces = list
        return list
    }
}

/**
 * Isian provinsi → kab/kota → kecamatan → desa. Provinsi wajib dari master data;
 * level di bawahnya memakai referensi wilayah bila tersedia, atau isian bebas.
 */
@Composable
fun WilayahFields(
    form: FormState,
    provinceKey: String = "province",
    cityKey: String? = "city",
    districtKey: String? = "district",
    villageKey: String? = "village",
    required: Boolean = true,
    cityRequired: Boolean = false,
) {
    var provinces by remember { mutableStateOf<List<JsonObject>>(emptyList()) }
    var regencies by remember { mutableStateOf<List<JsonObject>>(emptyList()) }
    var districts by remember { mutableStateOf<List<JsonObject>>(emptyList()) }
    var villages by remember { mutableStateOf<List<JsonObject>>(emptyList()) }
    var provinceCode by remember { mutableStateOf<String?>(null) }
    var regencyCode by remember { mutableStateOf<String?>(null) }
    var districtCode by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        provinces = WilayahCache.provinces()
        provinceCode = provinces.firstOrNull { it.str("name").equals(form[provinceKey], true) }?.str("code")
    }
    LaunchedEffect(provinceCode) {
        val c = provinceCode ?: return@LaunchedEffect
        regencies = WilayahCache.regencies.getOrPut(c) { WilayahCache.load("/api/wilayah/regencies/$c") }
        regencyCode = regencies.firstOrNull { it.str("name").equals(form[cityKey ?: ""], true) }?.str("code")
    }
    LaunchedEffect(regencyCode) {
        val c = regencyCode ?: return@LaunchedEffect
        districts = WilayahCache.districts.getOrPut(c) { WilayahCache.load("/api/wilayah/districts/$c") }
        districtCode = districts.firstOrNull { it.str("name").equals(form[districtKey ?: ""], true) }?.str("code")
    }
    LaunchedEffect(districtCode) {
        val c = districtCode ?: return@LaunchedEffect
        villages = WilayahCache.villages.getOrPut(c) { WilayahCache.load("/api/wilayah/villages/$c") }
    }

    Column(verticalArrangement = Arrangement.spacedBy(14.dp)) {
        if (provinces.isNotEmpty()) {
            SelectF(
                form, provinceKey, "Provinsi", provinces.map { Opt(it.str("name"), it.str("name")) },
                required = required, icon = Icons.Outlined.Map, placeholder = "Pilih provinsi",
                onChanged = { name ->
                    provinceCode = provinces.firstOrNull { it.str("name") == name }?.str("code")
                    cityKey?.let { form[it] = "" }; districtKey?.let { form[it] = "" }; villageKey?.let { form[it] = "" }
                    regencies = emptyList(); districts = emptyList(); villages = emptyList()
                },
            )
        } else {
            TextFieldF(form, provinceKey, "Provinsi", required = required, icon = Icons.Outlined.Map)
        }
        if (cityKey != null) {
            if (regencies.isNotEmpty()) {
                SelectF(form, cityKey, "Kabupaten / Kota", regencies.map { Opt(it.str("name"), it.str("name")) }, required = cityRequired, icon = Icons.Outlined.LocationCity,
                    onChanged = { name ->
                        regencyCode = regencies.firstOrNull { it.str("name") == name }?.str("code")
                        districtKey?.let { form[it] = "" }; villageKey?.let { form[it] = "" }
                        districts = emptyList(); villages = emptyList()
                    })
            } else TextFieldF(form, cityKey, "Kabupaten / Kota", required = cityRequired, icon = Icons.Outlined.LocationCity)
        }
        if (districtKey != null) {
            if (districts.isNotEmpty()) {
                SelectF(form, districtKey, "Kecamatan", districts.map { Opt(it.str("name"), it.str("name")) }, required = cityRequired, icon = Icons.Outlined.Place,
                    onChanged = { name ->
                        districtCode = districts.firstOrNull { it.str("name") == name }?.str("code")
                        villageKey?.let { form[it] = "" }
                        villages = emptyList()
                    })
            } else TextFieldF(form, districtKey, "Kecamatan", required = cityRequired, icon = Icons.Outlined.Place)
        }
        if (villageKey != null) {
            if (villages.isNotEmpty()) {
                SelectF(form, villageKey, "Kelurahan / Desa", villages.map { Opt(it.str("name"), it.str("name")) }, icon = Icons.Outlined.Place)
            } else TextFieldF(form, villageKey, "Kelurahan / Desa", icon = Icons.Outlined.Place)
        }
    }
}
