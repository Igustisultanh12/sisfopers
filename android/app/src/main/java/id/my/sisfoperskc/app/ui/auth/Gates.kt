package id.my.sisfoperskc.app.ui.auth

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.outlined.Accessibility
import androidx.compose.material.icons.outlined.Map
import androidx.compose.material.icons.outlined.MarkEmailRead
import androidx.compose.material.icons.outlined.Print
import androidx.compose.material.icons.outlined.Send
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.focus.FocusRequester
import androidx.compose.ui.focus.focusRequester
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.IntOffset
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Auth
import id.my.sisfoperskc.app.data.JsonLenient
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.Pill
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SecondaryButton
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.nav.AppState
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

/** Input OTP 6 kotak beranimasi dengan efek getar saat salah. */
@Composable
fun OtpBoxes(value: String, onChange: (String) -> Unit, length: Int = 6, error: Boolean = false, onComplete: (String) -> Unit = {}) {
    val focus = remember { FocusRequester() }
    val shake = remember { Animatable(0f) }
    LaunchedEffect(error) {
        if (error) {
            repeat(3) { shake.animateTo(14f, tween(50)); shake.animateTo(-14f, tween(50)) }
            shake.animateTo(0f, tween(50))
        }
    }
    LaunchedEffect(Unit) { delay(300); try { focus.requestFocus() } catch (_: Exception) {} }
    val caret = rememberInfiniteTransition(label = "caret")
    val caretAlpha by caret.animateFloat(1f, 0f, infiniteRepeatable(tween(520), RepeatMode.Reverse), label = "c")
    BasicTextField(
        value = value,
        onValueChange = { v ->
            val digits = v.filter { it.isDigit() }.take(length)
            onChange(digits)
            if (digits.length == length) onComplete(digits)
        },
        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.NumberPassword),
        modifier = Modifier.focusRequester(focus).offset { IntOffset(shake.value.toInt(), 0) },
        textStyle = TextStyle(color = Color.Transparent),
        decorationBox = { _ ->
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp), modifier = Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                repeat(length) { i ->
                    val ch = value.getOrNull(i)?.toString() ?: ""
                    val active = i == value.length
                    val border by animateColorAsState(
                        when {
                            error -> Sx.Red
                            active -> Sx.Blue
                            ch.isNotEmpty() -> Sx.BlueLine
                            else -> Sx.Line
                        }, label = "b",
                    )
                    Box(
                        Modifier.weight(1f).height(58.dp).clip(RoundedCornerShape(16.dp))
                            .background(if (ch.isNotEmpty()) Sx.BlueSoft else Color.White)
                            .border(if (active) 2.dp else 1.dp, border, RoundedCornerShape(16.dp)),
                        contentAlignment = Alignment.Center,
                    ) {
                        if (ch.isNotEmpty()) Text(ch, fontSize = 24.sp, fontWeight = FontWeight.ExtraBold, color = Sx.BlueDeep, modifier = Modifier.appear(0, key = ch + i))
                        else if (active) Box(Modifier.width(2.dp).height(24.dp).background(Sx.Blue.copy(alpha = caretAlpha)))
                    }
                }
            }
        },
    )
}

@Composable
private fun GateFrame(title: String, subtitle: String, content: @Composable () -> Unit) {
    Column(Modifier.fillMaxSize().background(Sx.Bg)) {
        AuthHero(title, subtitle)
        Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding().padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
            content()
            Spacer(Modifier.height(8.dp))
            SecondaryButton("Keluar dari akun", { AppState.logoutRequest.value++ }, Modifier.fillMaxWidth(), icon = Icons.AutoMirrored.Outlined.Logout, color = Sx.Red)
            Text("Masuk sebagai ${Session.me.value?.fullName ?: ""}", style = MaterialTheme.typography.bodySmall, textAlign = TextAlign.Center, modifier = Modifier.fillMaxWidth())
        }
    }
}

private suspend fun proceedAfterGate(ctx: android.content.Context, nav: androidx.navigation.NavHostController) {
    Auth.refreshMe(ctx)
    val target = Auth.routeFor(Session.me.value)
    nav.navigate(target) { popUpTo(0) { inclusive = true } }
}

@Composable
fun OtpGateScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    var mode by remember { mutableStateOf("auto") }
    var otp by remember { mutableStateOf("") }
    var error by remember { mutableStateOf(false) }
    var sending by remember { mutableStateOf(false) }
    var verifying by remember { mutableStateOf(false) }
    var sent by remember { mutableStateOf<String?>(null) }
    var cooldown by remember { mutableIntStateOf(0) }
    var info by remember { mutableStateOf<JsonObject?>(null) }

    LaunchedEffect(Unit) {
        val r = Api.get("/personel/face-verification")
        if (r is ApiResult.Ok) {
            if (r.data.containsKey("masked_phone") || r.data.containsKey("disable_whatsapp_otp")) info = r.data
            else proceedAfterGate(ctx, nav)
        }
    }
    LaunchedEffect(cooldown) { if (cooldown > 0) { delay(1000); cooldown-- } }

    fun verify(code: String) {
        if (code.length != 6) return
        scope.launch {
            verifying = true; error = false
            val path = if (mode == "manual") "/personel/face-verification/otp-manual" else "/personel/face-verification/verify-otp"
            when (val r = Api.send(path, mapOf("otp" to code))) {
                is ApiResult.Ok -> { Ui.success("Akun berhasil diverifikasi."); proceedAfterGate(ctx, nav) }
                is ApiResult.Err -> { error = true; otp = ""; Ui.error(r.message) }
            }
            verifying = false
        }
    }

    GateFrame("Verifikasi Akun", "Satu langkah lagi untuk mengamankan akun personel Anda.") {
        SxCard(Modifier.fillMaxWidth().appear(0)) {
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                Pill("Kirim OTP", mode == "auto", { mode = "auto"; otp = ""; error = false })
                Pill("OTP Cetak dari Admin", mode == "manual", { mode = "manual"; otp = ""; error = false })
            }
            Spacer(Modifier.height(16.dp))
            if (mode == "auto") {
                val disabledWa = info.bool("disable_whatsapp_otp")
                Text(
                    if (disabledWa) "Kode OTP 6 digit akan dikirim ke email akun Anda." else "Kode OTP 6 digit akan dikirim ke WhatsApp ${info.str("masked_phone", "terdaftar")} (atau email bila WA tidak tersedia).",
                    style = MaterialTheme.typography.bodyMedium,
                )
                Spacer(Modifier.height(12.dp))
                PrimaryButton(
                    if (cooldown > 0) "Kirim ulang dalam ${cooldown}s" else if (sent != null) "Kirim Ulang OTP" else "Kirim Kode OTP",
                    {
                        scope.launch {
                            sending = true
                            when (val r = Api.send("/personel/face-verification/request-otp")) {
                                is ApiResult.Ok -> { sent = r.message ?: "Kode OTP terkirim."; cooldown = 60; Ui.success(sent ?: "") }
                                is ApiResult.Err -> Ui.error(r.message)
                            }
                            sending = false
                        }
                    },
                    Modifier.fillMaxWidth(), loading = sending, enabled = cooldown == 0, icon = Icons.Outlined.Send,
                )
                if (sent != null) {
                    Spacer(Modifier.height(10.dp))
                    Notice(sent ?: "", Tone.Green, Icons.Outlined.MarkEmailRead)
                }
            } else {
                Notice("Masukkan kode OTP 6 digit yang tercetak pada lembar OTP dari admin/koordinator satuan Anda.", Tone.Blue, Icons.Outlined.Print)
            }
            Spacer(Modifier.height(18.dp))
            Text("Masukkan Kode OTP", style = MaterialTheme.typography.titleSmall)
            Spacer(Modifier.height(10.dp))
            OtpBoxes(otp, { otp = it; error = false }, error = error, onComplete = { verify(it) })
            Spacer(Modifier.height(16.dp))
            PrimaryButton("Verifikasi", { verify(otp) }, Modifier.fillMaxWidth(), loading = verifying, enabled = otp.length == 6, icon = Icons.Outlined.VerifiedUser)
        }
    }
}

@Composable
fun SinyalmenGateScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val form = rememberForm("ciri_khas" to "-", "cacat_tubuh" to "-")
    GateFrame("Data Ciri Fisik", "Lengkapi sinyalmen (ciri fisik) Anda sebagai data wajib personel.") {
        FormSection("Sinyalmen", "Isi sesuai kondisi sebenarnya.", Icons.Outlined.Accessibility) {
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                TextFieldF(form, "tinggi_badan", "Tinggi (cm)", Modifier.weight(1f), required = true, keyboardType = KeyboardType.Number, maxLength = 3)
                TextFieldF(form, "berat_badan", "Berat (kg)", Modifier.weight(1f), required = true, keyboardType = KeyboardType.Number, maxLength = 3)
            }
            ChipsF(form, "golongan_darah", "Golongan Darah", opts("A" to "A", "B" to "B", "AB" to "AB", "O" to "O"), required = true)
            SelectF(form, "rambut", "Rambut", listOf("Hitam Lurus", "Hitam Ikal", "Hitam Keriting", "Hitam Bergelombang", "Beruban", "Botak", "Lainnya").map { Opt(it, it) }, required = true)
            SelectF(form, "mata", "Warna Mata", listOf("Hitam", "Coklat", "Coklat Tua", "Biru", "Abu-abu", "Lainnya").map { Opt(it, it) }, required = true)
            TextFieldF(form, "ciri_khas", "Ciri Khas", required = true, helper = "Isi \"-\" bila tidak ada")
            TextFieldF(form, "cacat_tubuh", "Cacat Tubuh", required = true, helper = "Isi \"-\" bila tidak ada")
        }
        PrimaryButton("Simpan & Lanjutkan", {
            if (!form.require("tinggi_badan" to "Tinggi", "berat_badan" to "Berat", "golongan_darah" to "Golongan darah", "rambut" to "Rambut", "mata" to "Mata", "ciri_khas" to "Ciri khas", "cacat_tubuh" to "Cacat tubuh")) return@PrimaryButton
            scope.launch {
                val r = form.submit("/personel/sinyalmen/lengkap")
                if (r is ApiResult.Ok) { Ui.success("Data sinyalmen tersimpan."); proceedAfterGate(ctx, nav) } else Ui.error(r.errMessage() ?: "Gagal")
            }
        }, Modifier.fillMaxWidth(), loading = form.submitting)
    }
}

/** Data Kotama & satuan kewilayahan per matra (sama dengan versi web). */
object UnitsData {
    private var cache: JsonObject? = null
    fun load(ctx: android.content.Context): JsonObject {
        cache?.let { return it }
        val text = ctx.assets.open("kewilayahan.json").bufferedReader().use { it.readText() }
        val obj = JsonLenient.parseToJsonElement(text) as JsonObject
        cache = obj
        return obj
    }
}

@Composable
fun KewilayahanGateScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val me = Session.me.value
    val matra = me?.matra ?: "AD"
    val units = remember { UnitsData.load(ctx).obj(matra).takeIf { it.isNotEmpty() } ?: UnitsData.load(ctx).obj("AD") }
    val kotamaMap = units.obj("kotama")
    val form = rememberForm("kotama" to me?.personel.strOrNull("kotama"), "satuan_kewilayahan" to me?.personel.strOrNull("satuan_kewilayahan"))
    GateFrame("Data Kewilayahan", "Pilih Komando Utama dan satuan kewilayahan pembina Anda.") {
        FormSection("Kewilayahan ${id.my.sisfoperskc.app.data.Fmt.matra(matra)}", icon = Icons.Outlined.Map) {
            SelectF(form, "kotama", units.str("label_kotama", "Kotama"), kotamaMap.keys.map { Opt(it, it) }, required = true, onChanged = { form["satuan_kewilayahan"] = "" })
            val satuan = kotamaMap[form["kotama"]]?.let { el -> (el as? kotlinx.serialization.json.JsonArray)?.mapNotNull { (it as? kotlinx.serialization.json.JsonPrimitive)?.content } } ?: emptyList()
            SelectF(form, "satuan_kewilayahan", units.str("label_satuan", "Satuan Kewilayahan"), satuan.map { Opt(it, it) }, required = true, enabled = form["kotama"].isNotBlank())
        }
        PrimaryButton("Simpan Data Kewilayahan", {
            if (!form.require("kotama" to "Kotama", "satuan_kewilayahan" to "Satuan")) return@PrimaryButton
            scope.launch {
                val r = form.submit("/personel/update-kewilayahan")
                if (r is ApiResult.Ok) { Ui.success("Data kewilayahan tersimpan."); proceedAfterGate(ctx, nav) } else Ui.error(r.errMessage() ?: "Gagal")
            }
        }, Modifier.fillMaxWidth(), loading = form.submitting)
    }
}
