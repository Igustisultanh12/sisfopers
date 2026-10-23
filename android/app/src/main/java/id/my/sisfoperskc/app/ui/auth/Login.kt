package id.my.sisfoperskc.app.ui.auth

import androidx.compose.foundation.ExperimentalFoundationApi
import androidx.compose.ui.res.painterResource
import id.my.sisfoperskc.app.R
import androidx.compose.foundation.background
import androidx.compose.foundation.combinedClickable
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
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Dns
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.automirrored.outlined.Login
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.BuildConfig
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Auth
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.components.AppLogo
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.RemoteImage
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

@OptIn(ExperimentalFoundationApi::class)
@Composable
fun LoginScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val focus = LocalFocusManager.current
    val scope = rememberCoroutineScope()
    var username by remember { mutableStateOf(Session.lastUsername.orEmpty()) }
    var password by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var errors by remember { mutableStateOf<Map<String, String>>(emptyMap()) }
    var config by remember { mutableStateOf<JsonObject?>(null) }
    var serverDialog by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) { config = Auth.loadConfig(ctx) }

    fun submit() {
        val e = mutableMapOf<String, String>()
        if (username.isBlank()) e["username"] = "NIKC / username wajib diisi."
        if (password.isBlank()) e["password"] = "Kata sandi wajib diisi."
        errors = e
        if (e.isNotEmpty()) return
        focus.clearFocus()
        scope.launch {
            loading = true
            when (val r = Auth.login(ctx, username, password)) {
                is ApiResult.Ok -> {
                    val me = Session.me.value
                    Ui.success("Selamat datang, ${me?.fullName ?: ""}")
                    nav.navigate(Auth.routeFor(me)) { popUpTo(0) { inclusive = true } }
                }
                is ApiResult.Err -> {
                    errors = r.errors
                    Ui.error(r.message)
                }
            }
            loading = false
        }
    }

    Box(Modifier.fillMaxSize().background(Sx.Bg)) {
        Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding()) {
            Box(Modifier.fillMaxWidth().clip(RoundedCornerShape(bottomStart = 44.dp, bottomEnd = 44.dp)).background(Sx.HeroGradient)) {
                AnimatedBlobs(Modifier.matchParentSize())
                Column(
                    // ruang bawah = tumpang tindih kartu login (52dp) + jarak, agar logo matra tidak terpotong
                    Modifier.fillMaxWidth().statusBarsPadding().padding(top = 32.dp, bottom = 76.dp),
                    horizontalAlignment = Alignment.CenterHorizontally,
                ) {
                    Box(
                        Modifier
                            .size(96.dp)
                            .shadow(20.dp, RoundedCornerShape(30.dp))
                            .clip(RoundedCornerShape(30.dp))
                            .background(Color.White)
                            .combinedClickable(onClick = {}, onLongClick = { serverDialog = true })
                            .appear(0),
                        contentAlignment = Alignment.Center,
                    ) { AppLogo(70.dp) }
                    Spacer(Modifier.height(18.dp))
                    Text("SISFOPERS KC", color = Color.White, fontSize = 26.sp, fontWeight = FontWeight.ExtraBold, modifier = Modifier.appear(1))
                    Text(
                        config?.strOrNull("app_title") ?: "Sistem Informasi Personel Komponen Cadangan",
                        color = Color.White.copy(0.82f), fontSize = 13.sp, textAlign = TextAlign.Center,
                        modifier = Modifier.padding(horizontal = 32.dp).appear(2),
                    )
                    Spacer(Modifier.height(14.dp))
                    // Lambang Tri Matra: TNI AD, TNI AL, TNI AU
                    Row(horizontalArrangement = Arrangement.spacedBy(14.dp), modifier = Modifier.appear(3)) {
                        listOf("logo_ad" to R.drawable.logo_ad, "logo_al" to R.drawable.logo_al, "logo_au" to R.drawable.logo_au).forEach { (k, res) ->
                            Box(
                                Modifier.size(46.dp).clip(RoundedCornerShape(14.dp)).background(Color.White.copy(0.95f)).padding(6.dp),
                                contentAlignment = Alignment.Center,
                            ) {
                                RemoteImage(
                                    config?.strOrNull(k), Modifier.fillMaxSize(),
                                    contentScale = androidx.compose.ui.layout.ContentScale.Fit,
                                    fallback = painterResource(res),
                                )
                            }
                        }
                    }
                }
            }

            Column(
                Modifier
                    .padding(horizontal = 20.dp)
                    .offset(y = (-52).dp)
                    .shadow(24.dp, RoundedCornerShape(30.dp), ambientColor = Sx.Blue.copy(0.2f), spotColor = Sx.Blue.copy(0.2f))
                    .clip(RoundedCornerShape(30.dp))
                    .background(Color.White)
                    .padding(22.dp)
                    .appear(2),
                verticalArrangement = Arrangement.spacedBy(14.dp),
            ) {
                Text("Masuk ke Akun", style = MaterialTheme.typography.headlineSmall)
                Text("Gunakan NIKC (personel), username, atau email akun PJU/admin.", style = MaterialTheme.typography.bodySmall)
                if (config?.bool("under_maintenance") == true) {
                    Notice("Sistem sedang dalam pemeliharaan. Akses personel sementara dibatasi.", Tone.Amber)
                }
                PlainTextField(
                    username, { username = it; errors = errors - "username" },
                    label = "NIKC / Username / Email", placeholder = "Masukkan NIKC atau username",
                    icon = Icons.Outlined.Person, error = errors["username"],
                )
                PlainTextField(
                    password, { password = it; errors = errors - "password" },
                    label = "Kata Sandi", placeholder = "Masukkan kata sandi", icon = Icons.Outlined.Lock,
                    password = true, error = errors["password"], imeAction = ImeAction.Done, onDone = { submit() },
                )
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                    LinkText("Lupa NIKC?") { nav.navigate(Rt.FORGOT_NIKC) }
                    LinkText("Lupa kata sandi?") { nav.navigate(Rt.RESET_PASSWORD) }
                }
                PrimaryButton("Masuk", { submit() }, Modifier.fillMaxWidth(), loading = loading, icon = Icons.AutoMirrored.Outlined.Login)
            }

            Column(Modifier.padding(horizontal = 20.dp).offset(y = (-36).dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                AuthOption(Icons.Outlined.Badge, "Daftar Akun Personel", "Personel KC yang NIKC-nya sudah tercantum di SKEP") { nav.navigate(Rt.REGISTER) }
                AuthOption(Icons.Outlined.UploadFile, "Ajukan Verifikasi SKEP", "NIKC belum terdaftar? Unggah berkas SKEP Anda") { nav.navigate(Rt.SKEP_REQUEST) }
                Spacer(Modifier.height(4.dp))
                Text(
                    "SISFOPERS KC v${BuildConfig.VERSION_NAME}\n© Komponen Cadangan Tri Matra",
                    style = MaterialTheme.typography.bodySmall, textAlign = TextAlign.Center,
                    modifier = Modifier.fillMaxWidth().navigationBarsPadding(),
                )
            }
        }
    }

    if (serverDialog) {
        var url by remember { mutableStateOf(Session.baseUrl) }
        SxDialog(
            onDismiss = { serverDialog = false },
            title = "Alamat Server",
            message = "Ubah hanya bila diarahkan administrator (mis. server uji).",
            icon = Icons.Outlined.Dns,
            confirmText = "Simpan",
            onConfirm = {
                val u = url.trim().trimEnd('/')
                if (u.startsWith("https://") || u.startsWith("http://")) {
                    Session.baseUrl = u
                    serverDialog = false
                    Ui.success("Server diubah ke $u")
                    scope.launch { config = Auth.loadConfig(ctx) }
                } else Ui.error("Alamat harus diawali https://")
            },
        ) {
            PlainTextField(url, { url = it }, placeholder = BuildConfig.BASE_URL)
            Spacer(Modifier.height(8.dp))
            Text("Bawaan: ${BuildConfig.BASE_URL}", style = MaterialTheme.typography.bodySmall, modifier = Modifier.pressable({ url = BuildConfig.BASE_URL }))
        }
    }
}

@Composable
fun LinkText(text: String, onClick: () -> Unit) {
    Text(
        text, color = Sx.Blue, fontWeight = FontWeight.Bold, fontSize = 13.sp,
        modifier = Modifier.clip(RoundedCornerShape(8.dp)).pressable(onClick).padding(horizontal = 4.dp, vertical = 4.dp),
    )
}

@Composable
private fun AuthOption(icon: androidx.compose.ui.graphics.vector.ImageVector, title: String, subtitle: String, onClick: () -> Unit) {
    Row(
        Modifier.fillMaxWidth().clip(RoundedCornerShape(22.dp)).background(Color.White).pressable(onClick).padding(16.dp).appear(4),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        id.my.sisfoperskc.app.ui.components.IconBox(icon)
        Spacer(Modifier.width(14.dp))
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.titleMedium)
            Text(subtitle, style = MaterialTheme.typography.bodySmall)
        }
        androidx.compose.material3.Icon(Icons.AutoMirrored.Outlined.Login, null, tint = Sx.Faint, modifier = Modifier.size(18.dp))
    }
}
