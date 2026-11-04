package id.my.sisfoperskc.app.ui.auth

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.Check
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Phone
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.WorkOutline
import androidx.compose.material3.Icon
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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SecondaryButton
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SwitchF
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.WilayahFields
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

val MatraOpts = opts("AD" to "TNI AD (Matra Darat)", "AL" to "TNI AL (Matra Laut)", "AU" to "TNI AU (Matra Udara)")
val GenderOpts = opts("L" to "Laki-laki", "P" to "Perempuan")
val AsnJenisOpts = opts("CPNS" to "CPNS", "PNS" to "PNS", "P3K" to "P3K", "P3K Paruh Waktu" to "P3K Paruh Waktu")

/** Ambil opsi pangkat dari halaman pendaftaran (master kepangkatan aktif). */
suspend fun loadPangkatOptions(): List<Opt> {
    val r = Api.get("/register") as? ApiResult.Ok ?: return emptyList()
    return r.data.objects("pangkatOptions").map { Opt(it.str("nama"), it.str("nama"), "Kelompok NIKC ${it.str("kelompok_nikc")}") }
}

@Composable
fun StepIndicator(steps: List<String>, current: Int) {
    Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
        steps.forEachIndexed { i, label ->
            val done = i < current
            val active = i == current
            Column(horizontalAlignment = Alignment.CenterHorizontally, modifier = Modifier.weight(1f)) {
                Box(
                    Modifier.size(34.dp).clip(CircleShape).background(
                        when {
                            done -> Sx.Green
                            active -> Sx.Blue
                            else -> Sx.LineSoft
                        },
                    ),
                    contentAlignment = Alignment.Center,
                ) {
                    if (done) Icon(Icons.Outlined.Check, null, tint = Color.White, modifier = Modifier.size(18.dp))
                    else Text("${i + 1}", color = if (active) Color.White else Sx.Muted, fontWeight = FontWeight.ExtraBold, fontSize = 13.sp)
                }
                Spacer(Modifier.height(4.dp))
                Text(label, fontSize = 10.sp, color = if (active) Sx.Blue else Sx.Muted, fontWeight = FontWeight.Bold, textAlign = TextAlign.Center, maxLines = 1)
            }
        }
    }
}

@Composable
fun RegisterScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    var step by remember { mutableIntStateOf(0) }
    var skep by remember { mutableStateOf<JsonObject?>(null) }
    var checking by remember { mutableStateOf(false) }
    var checkResult by remember { mutableStateOf<String?>(null) }
    var notFound by remember { mutableStateOf(false) }
    var done by remember { mutableStateOf(false) }
    var pangkatOptions by remember { mutableStateOf<List<Opt>>(emptyList()) }
    val form = rememberForm("is_asn" to "0", "sumber_rekrutmen" to "Reguler", "gender" to "L")

    LaunchedEffect(Unit) { pangkatOptions = loadPangkatOptions() }

    val steps = listOf("Cek NIKC", "Data Diri", "Alamat", "Berkas")

    fun checkNikc() {
        if (!form.require("nikc" to "NIKC", "dob" to "Tanggal lahir")) return
        if (form["nikc"].length != 17) { form.errors = form.errors + ("nikc" to "NIKC harus 17 digit angka."); return }
        scope.launch {
            checking = true; checkResult = null; notFound = false
            when (val r = Api.send("/skep/check", mapOf("nikc" to form["nikc"], "dob" to form["dob"]))) {
                is ApiResult.Ok -> {
                    val d = r.data
                    when {
                        d.bool("registered") -> checkResult = "NIKC ini sudah memiliki akun. Silakan login atau gunakan menu lupa kata sandi."
                        d.bool("exists") -> {
                            val s = d.obj("data")
                            skep = s
                            form.setAll(mapOf("full_name" to s.str("nama_lengkap"), "pangkat" to s.str("pangkat"), "matra" to s.str("matra"), "angkatan" to s.str("angkatan")))
                            step = 1
                        }
                        else -> { notFound = true; checkResult = "NIKC belum tercantum di database SKEP. Ajukan verifikasi berkas SKEP terlebih dahulu." }
                    }
                }
                is ApiResult.Err -> { form.errors = r.errors; Ui.error(r.message) }
            }
            checking = false
        }
    }

    fun next() {
        when (step) {
            1 -> {
                val ok = form.require("full_name" to "Nama lengkap", "nik" to "NIK", "pob" to "Tempat lahir", "email" to "Email", "phone_number" to "Nomor HP", "pangkat" to "Pangkat") &&
                    (form["is_asn"] != "1" || form.require("asn_nip" to "NIP", "asn_jenis" to "Jenis ASN", "asn_tmt" to "TMT ASN", "asn_nama_instansi" to "Instansi"))
                if (ok && form["nik"].length != 16) { form.errors = form.errors + ("nik" to "NIK harus 16 digit."); return }
                if (ok) step = 2
            }
            2 -> if (form.require("province" to "Provinsi", "address" to "Alamat lengkap")) step = 3
        }
    }

    fun submit() {
        if (!form.require("photo_profile" to "Pas foto", "ktp_document" to "Foto KTP", "password" to "Kata sandi", "password_confirmation" to "Konfirmasi kata sandi")) return
        if (form["password"].length < 8) { form.errors = form.errors + ("password" to "Minimal 8 karakter."); return }
        if (form["password"] != form["password_confirmation"]) { form.errors = form.errors + ("password_confirmation" to "Konfirmasi kata sandi tidak cocok."); return }
        scope.launch {
            val r = form.submit("/register")
            when (r) {
                is ApiResult.Ok -> done = true
                is ApiResult.Err -> {
                    Ui.error(r.message)
                    val keys = r.errors.keys
                    step = when {
                        keys.any { it in setOf("nikc", "dob") } -> 0
                        keys.any { it in setOf("full_name", "nik", "pob", "email", "phone_number", "pangkat", "matra", "angkatan", "gender", "sumber_rekrutmen") || it.startsWith("asn") } -> 1
                        keys.any { it in setOf("province", "city", "district", "village", "postal_code", "address") } -> 2
                        else -> 3
                    }
                }
            }
        }
    }

    Column(Modifier.fillMaxSize().background(Sx.Bg)) {
        AuthHero(
            title = if (done) "Pendaftaran Terkirim" else "Daftar Akun Personel",
            subtitle = if (done) "Berkas Anda sedang menunggu verifikasi admin." else "Lengkapi data sesuai SKEP dan KTP Anda.",
            onBack = { if (step > 0 && !done) step-- else nav.popBackStack() },
        )
        if (done) {
            SuccessPanel(
                "Pendaftaran berhasil dikirim",
                "Akun Anda akan aktif setelah diverifikasi oleh admin/koordinator. Pemberitahuan dikirim melalui WhatsApp atau email.",
            ) { nav.navigate(Rt.LOGIN) { popUpTo(Rt.LOGIN) { inclusive = true } } }
            return@Column
        }
        Column(Modifier.padding(horizontal = 20.dp, vertical = 16.dp)) { StepIndicator(steps, step) }
        AnimatedContent(
            step,
            transitionSpec = {
                if (targetState > initialState) (slideInHorizontally(tween(320)) { it } + fadeIn()) togetherWith (slideOutHorizontally(tween(320)) { -it } + fadeOut())
                else (slideInHorizontally(tween(320)) { -it } + fadeIn()) togetherWith (slideOutHorizontally(tween(320)) { it } + fadeOut())
            },
            label = "step",
            modifier = Modifier.weight(1f),
        ) { s ->
            Column(
                Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding().padding(horizontal = 20.dp).padding(bottom = 32.dp),
                verticalArrangement = Arrangement.spacedBy(14.dp),
            ) {
                when (s) {
                    0 -> {
                        FormSection("Verifikasi NIKC", "NIKC 17 digit dan tanggal lahir harus sesuai database SKEP.", Icons.Outlined.Search) {
                            TextFieldF(form, "nikc", "NIKC", required = true, keyboardType = KeyboardType.Number, maxLength = 17, icon = Icons.Outlined.Badge, placeholder = "17 digit angka")
                            DateF(form, "dob", "Tanggal Lahir", required = true)
                        }
                        if (checkResult != null) Notice(checkResult ?: "", if (notFound) Tone.Amber else Tone.Blue)
                        if (notFound) SecondaryButton("Ajukan Verifikasi SKEP", { nav.navigate(Rt.SKEP_REQUEST) }, Modifier.fillMaxWidth(), icon = Icons.Outlined.UploadFile, color = Sx.Blue)
                        PrimaryButton("Periksa NIKC", { checkNikc() }, Modifier.fillMaxWidth(), loading = checking, icon = Icons.Outlined.Search)
                    }
                    1 -> {
                        val sk = skep
                        if (sk != null) {
                            SxCard(Modifier.fillMaxWidth().appear(0), color = Sx.GreenSoft, border = Sx.Green.copy(0.25f)) {
                                Row(verticalAlignment = Alignment.CenterVertically) {
                                    Icon(Icons.Outlined.CheckCircle, null, tint = Sx.Green)
                                    Spacer(Modifier.width(8.dp))
                                    Text("Data SKEP ditemukan", fontWeight = FontWeight.Bold, color = Sx.GreenInk)
                                }
                                InfoRow("Nama", sk.str("nama_lengkap"))
                                InfoRow("Pangkat", sk.str("pangkat"))
                                InfoRow("Matra", id.my.sisfoperskc.app.data.Fmt.matra(sk.str("matra")))
                                InfoRow("Angkatan", sk.str("angkatan"))
                            }
                        }
                        FormSection("Identitas", icon = Icons.Outlined.Person) {
                            TextFieldF(form, "full_name", "Nama Lengkap (sesuai SKEP)", required = true, icon = Icons.Outlined.Person)
                            TextFieldF(form, "nik", "NIK (KTP)", required = true, keyboardType = KeyboardType.Number, maxLength = 16, icon = Icons.Outlined.Badge)
                            TextFieldF(form, "pob", "Tempat Lahir", required = true, icon = Icons.Outlined.Home)
                            ChipsF(form, "gender", "Jenis Kelamin", GenderOpts, required = true)
                            SelectF(form, "pangkat", "Pangkat", pangkatOptions, required = true)
                            SelectF(form, "matra", "Matra", MatraOpts, required = true, enabled = skep == null)
                            TextFieldF(form, "angkatan", "Angkatan", required = true, keyboardType = KeyboardType.Number, maxLength = 4, enabled = skep == null)
                            ChipsF(form, "sumber_rekrutmen", "Sumber Rekrutmen", opts("Reguler" to "Reguler", "SPPI" to "SPPI", "PNS" to "PNS"), required = true)
                        }
                        FormSection("Kontak", icon = Icons.Outlined.Phone) {
                            TextFieldF(form, "email", "Email", required = true, keyboardType = KeyboardType.Email, icon = Icons.Outlined.Email)
                            TextFieldF(form, "phone_number", "Nomor HP / WhatsApp", required = true, keyboardType = KeyboardType.Phone, icon = Icons.Outlined.Phone, placeholder = "08xxxxxxxxxx")
                        }
                        FormSection("Status ASN", icon = Icons.Outlined.WorkOutline) {
                            SwitchF(form, "is_asn", "Saya seorang ASN", "Aktifkan bila Anda berstatus CPNS/PNS/P3K")
                            if (form["is_asn"] == "1") {
                                TextFieldF(form, "asn_nip", "NIP", required = true, keyboardType = KeyboardType.Number)
                                SelectF(form, "asn_jenis", "Jenis ASN", AsnJenisOpts, required = true)
                                DateF(form, "asn_tmt", "TMT ASN", required = true)
                                TextFieldF(form, "asn_nama_instansi", "Nama Instansi", required = true)
                                FileF(form, "asn_sk", "SK ASN (opsional)", Accept.ImageOrPdf, helper = "PDF/JPG maks 2 MB")
                            }
                        }
                        PrimaryButton("Lanjut ke Alamat", { next() }, Modifier.fillMaxWidth())
                    }
                    2 -> {
                        FormSection("Alamat Domisili", "Provinsi menentukan validasi format NIKC.", Icons.Outlined.Home) {
                            WilayahFields(form)
                            TextFieldF(form, "postal_code", "Kode Pos", keyboardType = KeyboardType.Number, maxLength = 10)
                            TextFieldF(form, "address", "Alamat Lengkap", required = true, minLines = 3)
                        }
                        PrimaryButton("Lanjut ke Berkas", { next() }, Modifier.fillMaxWidth())
                    }
                    else -> {
                        FormSection("Berkas Pendaftaran", "Gunakan foto yang jelas dan terbaru.", Icons.Outlined.UploadFile) {
                            FileF(form, "photo_profile", "Pas Foto", Accept.Image, required = true, helper = "JPG/PNG, latar polos, maks 10 MB")
                            FileF(form, "ktp_document", "Foto KTP", Accept.ImageOrPdf, required = true, helper = "JPG/PNG/PDF maks 10 MB")
                        }
                        FormSection("Kata Sandi Akun", "Username akun Anda adalah NIKC.", Icons.Outlined.Lock) {
                            TextFieldF(form, "password", "Kata Sandi", required = true, password = true, icon = Icons.Outlined.Lock, helper = "Minimal 8 karakter")
                            TextFieldF(form, "password_confirmation", "Konfirmasi Kata Sandi", required = true, password = true, icon = Icons.Outlined.Lock)
                        }
                        Notice("Dengan mendaftar, Anda menyatakan data yang diisi benar dan dapat dipertanggungjawabkan.", Tone.Blue)
                        PrimaryButton("Kirim Pendaftaran", { submit() }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.CheckCircle)
                    }
                }
            }
        }
    }
}

@Composable
fun SuccessPanel(title: String, message: String, buttonText: String = "Kembali ke Login", onDone: () -> Unit) {
    Column(Modifier.fillMaxSize().padding(28.dp).navigationBarsPadding(), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
        SuccessCheck()
        Spacer(Modifier.height(24.dp))
        Text(title, style = MaterialTheme.typography.headlineSmall, textAlign = TextAlign.Center)
        Spacer(Modifier.height(8.dp))
        Text(message, style = MaterialTheme.typography.bodyMedium.copy(color = Sx.Muted), textAlign = TextAlign.Center)
        Spacer(Modifier.height(28.dp))
        PrimaryButton(buttonText, onDone, Modifier.fillMaxWidth())
    }
}

/** Animasi centang sukses (lingkaran + goresan). */
@Composable
fun SuccessCheck(size: androidx.compose.ui.unit.Dp = 120.dp) {
    val circle = remember { androidx.compose.animation.core.Animatable(0f) }
    val check = remember { androidx.compose.animation.core.Animatable(0f) }
    LaunchedEffect(Unit) {
        circle.animateTo(1f, tween(500))
        check.animateTo(1f, tween(420))
    }
    androidx.compose.foundation.Canvas(Modifier.size(size)) {
        val stroke = this.size.minDimension * 0.07f
        drawCircle(Sx.Green.copy(alpha = 0.12f * circle.value), radius = this.size.minDimension / 2 * circle.value)
        drawArc(Sx.Green, -90f, 360f * circle.value, false, style = androidx.compose.ui.graphics.drawscope.Stroke(stroke, cap = androidx.compose.ui.graphics.StrokeCap.Round), topLeft = androidx.compose.ui.geometry.Offset(stroke, stroke), size = androidx.compose.ui.geometry.Size(this.size.width - stroke * 2, this.size.height - stroke * 2))
        val p1 = androidx.compose.ui.geometry.Offset(this.size.width * 0.30f, this.size.height * 0.52f)
        val p2 = androidx.compose.ui.geometry.Offset(this.size.width * 0.45f, this.size.height * 0.66f)
        val p3 = androidx.compose.ui.geometry.Offset(this.size.width * 0.72f, this.size.height * 0.38f)
        val t = check.value
        if (t > 0f) {
            val first = (t / 0.4f).coerceAtMost(1f)
            drawLine(Sx.Green, p1, p1 + (p2 - p1) * first, stroke, androidx.compose.ui.graphics.StrokeCap.Round)
            if (t > 0.4f) {
                val second = ((t - 0.4f) / 0.6f).coerceAtMost(1f)
                drawLine(Sx.Green, p2, p2 + (p3 - p2) * second, stroke, androidx.compose.ui.graphics.StrokeCap.Round)
            }
        }
    }
}

@Composable
fun SkepRequestScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    var done by remember { mutableStateOf(false) }
    var pangkatOptions by remember { mutableStateOf<List<Opt>>(emptyList()) }
    val form = rememberForm()
    LaunchedEffect(Unit) { pangkatOptions = loadPangkatOptions() }

    Column(Modifier.fillMaxSize().background(Sx.Bg)) {
        AuthHero(
            if (done) "Pengajuan Terkirim" else "Verifikasi Berkas SKEP",
            if (done) "Admin akan meninjau berkas SKEP Anda." else "Unggah SKEP bila NIKC Anda belum terdaftar di sistem.",
            onBack = { nav.popBackStack() },
        )
        if (done) {
            SuccessPanel("Pengajuan SKEP diterima", "Hasil verifikasi akan diinformasikan melalui WhatsApp. Setelah disetujui, Anda dapat mendaftar akun.") { nav.popBackStack() }
            return@Column
        }
        Column(
            Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding().padding(20.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            FormSection("Data Sesuai SKEP", icon = Icons.Outlined.Badge) {
                TextFieldF(form, "nama_lengkap", "Nama Lengkap", required = true, icon = Icons.Outlined.Person)
                TextFieldF(form, "nik", "NIK (KTP)", required = true, keyboardType = KeyboardType.Number, maxLength = 16)
                TextFieldF(form, "nikc", "NIKC", required = true, keyboardType = KeyboardType.Number, maxLength = 17, helper = "17 digit sesuai SKEP")
                DateF(form, "dob", "Tanggal Lahir", required = true)
                SelectF(form, "pangkat", "Pangkat", pangkatOptions, required = true)
                SelectF(form, "matra", "Matra", MatraOpts, required = true)
                TextFieldF(form, "angkatan", "Angkatan", required = true, keyboardType = KeyboardType.Number, maxLength = 4, icon = Icons.Outlined.CalendarMonth)
                TextFieldF(form, "phone_number", "Nomor WhatsApp", required = true, keyboardType = KeyboardType.Phone, icon = Icons.Outlined.Phone)
                FileF(form, "skep_file", "Berkas SKEP", Accept.Pdf, required = true, helper = "Khusus PDF, maks 2 MB")
            }
            PrimaryButton("Kirim Pengajuan", {
                if (!form.require("nama_lengkap" to "Nama", "nik" to "NIK", "nikc" to "NIKC", "dob" to "Tanggal lahir", "pangkat" to "Pangkat", "matra" to "Matra", "angkatan" to "Angkatan", "phone_number" to "Nomor WA", "skep_file" to "Berkas SKEP")) return@PrimaryButton
                scope.launch {
                    val r = form.submit("/skep/request")
                    if (r is ApiResult.Ok) done = true else Ui.error(r.errMessage() ?: "Gagal mengirim")
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.UploadFile)
        }
    }
}
