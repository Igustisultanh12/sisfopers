package id.my.sisfoperskc.app.ui.auth

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
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.ContentCopy
import androidx.compose.material.icons.outlined.Key
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.platform.LocalClipboardManager
import androidx.compose.ui.text.AnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.RemoteImage
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

@Composable
fun ForgotNikcScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val clip = LocalClipboardManager.current
    val form = rememberForm()
    var result by remember { mutableStateOf<JsonObject?>(null) }

    Column(Modifier.fillMaxSize().background(Sx.Bg)) {
        AuthHero("Lupa NIKC", "Temukan NIKC Anda dengan nama lengkap, tanggal lahir, dan NIK.", onBack = { nav.popBackStack() })
        Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding().padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
            FormSection("Data Pencarian", icon = Icons.Outlined.Search) {
                TextFieldF(form, "full_name", "Nama Lengkap", required = true, icon = Icons.Outlined.Person, helper = "Sesuai KTP atau SKEP")
                DateF(form, "dob", "Tanggal Lahir", required = true)
                TextFieldF(form, "nik", "NIK (KTP)", required = true, keyboardType = KeyboardType.Number, maxLength = 16, icon = Icons.Outlined.Badge)
            }
            PrimaryButton("Cari NIKC", {
                if (!form.require("full_name" to "Nama lengkap", "dob" to "Tanggal lahir", "nik" to "NIK")) return@PrimaryButton
                scope.launch {
                    result = null
                    when (val r = form.submit("/lupa-nikc")) {
                        is ApiResult.Ok -> result = r.data.obj("result")
                        is ApiResult.Err -> Ui.error(r.message)
                    }
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting, icon = Icons.Outlined.Search)

            val res = result
            if (res != null && res.isNotEmpty()) {
                when (res.str("status")) {
                    "FOUND_WITH_ACCOUNT" -> SxCard(Modifier.fillMaxWidth().appear(0), elevation = 8.dp) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Box(Modifier.size(72.dp).clip(RoundedCornerShape(18.dp)).background(Sx.LineSoft)) {
                                RemoteImage(res.strOrNull("photo_url"), Modifier.fillMaxSize())
                            }
                            Spacer(Modifier.width(14.dp))
                            Column(Modifier.weight(1f)) {
                                Text(res.str("full_name"), style = MaterialTheme.typography.titleMedium)
                                Text("${res.str("pangkat")} • ${Fmt.matra(res.str("matra"))}", style = MaterialTheme.typography.bodySmall)
                            }
                        }
                        Spacer(Modifier.height(14.dp))
                        Row(
                            Modifier.fillMaxWidth().clip(RoundedCornerShape(16.dp)).background(Sx.BlueSoft).border(1.dp, Sx.BlueLine, RoundedCornerShape(16.dp))
                                .pressable({ clip.setText(AnnotatedString(res.str("nikc"))); Ui.success("NIKC disalin") }).padding(14.dp),
                            verticalAlignment = Alignment.CenterVertically,
                        ) {
                            Column(Modifier.weight(1f)) {
                                Text("NIKC Anda", fontSize = 11.sp, color = Sx.BlueDark, fontWeight = FontWeight.Bold)
                                Text(res.str("nikc"), fontSize = 20.sp, fontWeight = FontWeight.ExtraBold, color = Sx.BlueDeep, letterSpacing = 1.sp)
                            }
                            Icon(Icons.Outlined.ContentCopy, "Salin", tint = Sx.Blue)
                        }
                        Spacer(Modifier.height(8.dp))
                        InfoRow("Angkatan", res.str("angkatan"))
                        InfoRow("Nomor KTA", res.strOrNull("nomor_kta") ?: "-")
                        InfoRow("Alamat", res.str("address"))
                        Spacer(Modifier.height(10.dp))
                        PrimaryButton("Masuk dengan NIKC ini", {
                            id.my.sisfoperskc.app.data.Session.lastUsername = res.str("nikc")
                            nav.navigate(Rt.LOGIN) { popUpTo(Rt.LOGIN) { inclusive = true } }
                        }, Modifier.fillMaxWidth())
                    }
                    "FOUND_WITHOUT_ACCOUNT" -> SxCard(Modifier.fillMaxWidth().appear(0)) {
                        Text("Data ditemukan", style = MaterialTheme.typography.titleMedium)
                        InfoRow("Nama", res.str("masked_name"))
                        InfoRow("NIKC", res.str("masked_nikc"))
                        Spacer(Modifier.height(8.dp))
                        Notice(res.str("message"), Tone.Amber, Icons.Outlined.WarningAmber)
                        Spacer(Modifier.height(10.dp))
                        PrimaryButton("Daftar Akun", { nav.navigate(Rt.REGISTER) }, Modifier.fillMaxWidth())
                    }
                    else -> Notice(res.str("message", "Data tidak ditemukan."), Tone.Red, Icons.Outlined.WarningAmber)
                }
            }
        }
    }
}

@Composable
fun ResetPasswordScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val form = rememberForm()
    var done by remember { mutableStateOf(false) }
    Column(Modifier.fillMaxSize().background(Sx.Bg)) {
        AuthHero(
            if (done) "Kata Sandi Diperbarui" else "Reset Kata Sandi",
            if (done) "Silakan login dengan kata sandi baru." else "Gunakan kode OTP reset dari admin satuan Anda.",
            onBack = { nav.popBackStack() },
        )
        if (done) {
            SuccessPanel("Berhasil!", "Kata sandi akun Anda telah diperbarui.") {
                nav.navigate(Rt.LOGIN) { popUpTo(Rt.LOGIN) { inclusive = true } }
            }
            return@Column
        }
        Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding().padding(20.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
            Notice("Belum memiliki kode OTP? Hubungi admin/koordinator untuk mencetak OTP Reset Password atas nama Anda.", Tone.Blue)
            FormSection("Verifikasi", icon = Icons.Outlined.Key) {
                TextFieldF(form, "username", "NIKC", required = true, keyboardType = KeyboardType.Number, maxLength = 17, icon = Icons.Outlined.Badge)
                TextFieldF(form, "otp", "Kode OTP Reset", required = true, keyboardType = KeyboardType.Number, maxLength = 6, icon = Icons.Outlined.Key)
            }
            FormSection("Kata Sandi Baru", icon = Icons.Outlined.Lock) {
                TextFieldF(form, "password", "Kata Sandi Baru", required = true, password = true, helper = "Minimal 8 karakter")
                TextFieldF(form, "password_confirmation", "Konfirmasi Kata Sandi", required = true, password = true)
            }
            PrimaryButton("Simpan Kata Sandi", {
                if (!form.require("username" to "NIKC", "otp" to "Kode OTP", "password" to "Kata sandi", "password_confirmation" to "Konfirmasi")) return@PrimaryButton
                if (form["password"] != form["password_confirmation"]) { form.errors = form.errors + ("password_confirmation" to "Konfirmasi tidak cocok."); return@PrimaryButton }
                scope.launch {
                    val r = form.submit("/reset-password-otp")
                    if (r is ApiResult.Ok) done = true else Ui.error(r.errMessage() ?: "Gagal")
                }
            }, Modifier.fillMaxWidth(), loading = form.submitting)
        }
    }
}

