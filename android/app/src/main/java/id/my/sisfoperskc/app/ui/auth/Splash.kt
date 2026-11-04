package id.my.sisfoperskc.app.ui.auth

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.Spring
import androidx.compose.animation.core.animateDpAsState
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.spring
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInVertically
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.ExperimentalFoundationApi
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxScope
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.pager.HorizontalPager
import androidx.compose.foundation.pager.rememberPagerState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ArrowBack
import androidx.compose.material.icons.outlined.Build
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.Groups
import androidx.compose.material.icons.outlined.NotificationsActive
import androidx.compose.material.icons.outlined.Shield
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material.icons.outlined.VideoCall
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.rotate
import androidx.compose.ui.draw.scale
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.BuildConfig
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Auth
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.ui.components.AppLogo
import id.my.sisfoperskc.app.ui.components.Floating
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SecondaryButton
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.async
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

/** Latar animasi gelembung bercahaya untuk layar autentikasi. */
@Composable
fun AnimatedBlobs(modifier: Modifier = Modifier, dark: Boolean = true) {
    val t = rememberInfiniteTransition(label = "blobs")
    val a by t.animateFloat(0f, 1f, infiniteRepeatable(tween(9000, easing = LinearEasing), RepeatMode.Reverse), label = "a")
    val b by t.animateFloat(0f, 1f, infiniteRepeatable(tween(12000, easing = LinearEasing), RepeatMode.Reverse), label = "b")
    Canvas(modifier.fillMaxSize()) {
        val w = size.width; val h = size.height
        val c1 = if (dark) Color(0xFF60A5FA) else Color(0xFFBFDBFE)
        val c2 = if (dark) Color(0xFF818CF8) else Color(0xFFC7D2FE)
        val c3 = if (dark) Color(0xFF22D3EE) else Color(0xFFA5F3FC)
        drawCircle(Brush.radialGradient(listOf(c1.copy(0.55f), Color.Transparent), Offset(w * (0.15f + 0.25f * a), h * (0.2f + 0.1f * b)), w * 0.6f), w * 0.6f, Offset(w * (0.15f + 0.25f * a), h * (0.2f + 0.1f * b)))
        drawCircle(Brush.radialGradient(listOf(c2.copy(0.5f), Color.Transparent), Offset(w * (0.85f - 0.2f * b), h * (0.35f + 0.15f * a)), w * 0.55f), w * 0.55f, Offset(w * (0.85f - 0.2f * b), h * (0.35f + 0.15f * a)))
        drawCircle(Brush.radialGradient(listOf(c3.copy(0.35f), Color.Transparent), Offset(w * (0.5f + 0.2f * a), h * (0.75f - 0.1f * b)), w * 0.5f), w * 0.5f, Offset(w * (0.5f + 0.2f * a), h * (0.75f - 0.1f * b)))
    }
}

@Composable
fun SplashScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val logoScale = remember { Animatable(0.3f) }
    val textAlpha = remember { Animatable(0f) }
    val t = rememberInfiniteTransition(label = "ring")
    val rot by t.animateFloat(0f, 360f, infiniteRepeatable(tween(2400, easing = LinearEasing)), label = "rot")

    LaunchedEffect(Unit) {
        launch { logoScale.animateTo(1f, spring(dampingRatio = 0.45f, stiffness = Spring.StiffnessLow)) }
        launch { delay(350); textAlpha.animateTo(1f, tween(600)) }
        val minDelay = async { delay(1600) }
        val cfg = try { Auth.loadConfig(ctx) } catch (_: Exception) { null }
        val target = when {
            !Session.onboardingSeen -> Rt.ONBOARD
            !Session.isLoggedIn -> Rt.LOGIN
            else -> {
                val r = Auth.refreshMe(ctx)
                val me = Session.me.value
                when {
                    r is ApiResult.Err && r.status == 401 -> { Auth.clearLocal(ctx); Rt.LOGIN }
                    cfg?.bool("under_maintenance") == true && me?.isPersonel == true -> Rt.MAINTENANCE
                    else -> Auth.routeFor(me)
                }
            }
        }
        minDelay.await()
        nav.navigate(target) { popUpTo(Rt.SPLASH) { inclusive = true } }
    }

    Box(Modifier.fillMaxSize().background(Sx.HeroGradient), contentAlignment = Alignment.Center) {
        AnimatedBlobs(Modifier.matchParentSize())
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Box(contentAlignment = Alignment.Center, modifier = Modifier.scale(logoScale.value)) {
                Canvas(Modifier.size(150.dp).rotate(rot)) {
                    drawArc(
                        Brush.sweepGradient(listOf(Color.Transparent, Color.White.copy(0.9f), Color.Transparent)),
                        0f, 300f, false, style = Stroke(5f, cap = StrokeCap.Round),
                    )
                }
                Box(Modifier.size(118.dp).clip(RoundedCornerShape(36.dp)).background(Color.White), contentAlignment = Alignment.Center) {
                    AppLogo(84.dp)
                }
            }
            Spacer(Modifier.height(28.dp))
            Column(horizontalAlignment = Alignment.CenterHorizontally, modifier = Modifier.graphicsLayer { alpha = textAlpha.value; translationY = (1 - textAlpha.value) * 30f }) {
                Text("SISFOPERS KC", color = Color.White, fontSize = 30.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = 1.sp)
                Spacer(Modifier.height(4.dp))
                Text("Sistem Informasi Personel\nKomponen Cadangan Tri Matra", color = Color.White.copy(0.8f), fontSize = 14.sp, textAlign = TextAlign.Center, lineHeight = 20.sp)
            }
        }
        Text(
            "Versi ${BuildConfig.VERSION_NAME}",
            color = Color.White.copy(0.6f), fontSize = 12.sp,
            modifier = Modifier.align(Alignment.BottomCenter).navigationBarsPadding().padding(bottom = 24.dp),
        )
    }
}

private data class OnboardPage(val title: String, val body: String, val icon: ImageVector, val accents: List<ImageVector>, val color: Color)

@OptIn(ExperimentalFoundationApi::class)
@Composable
fun OnboardingScreen() {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val pages = remember {
        listOf(
            OnboardPage(
                "Satu Aplikasi untuk\nSeluruh Personel KC",
                "Data personel, riwayat pendidikan, pekerjaan, dan pengkinian status tersimpan aman dan selalu terbarui.",
                Icons.Outlined.Groups, listOf(Icons.Outlined.VerifiedUser, Icons.Outlined.Shield), Sx.Blue,
            ),
            OnboardPage(
                "Instruksi & Kegiatan\nLangsung di Genggaman",
                "Terima broadcast apel, latihan, dan mobilisasi, lalu konfirmasi kehadiran hanya dengan satu ketukan.",
                Icons.Outlined.Campaign, listOf(Icons.Outlined.NotificationsActive, Icons.Outlined.VerifiedUser), Sx.Indigo,
            ),
            OnboardPage(
                "Layanan Informasi &\nRapat Dinas Daring",
                "Berkonsultasi dengan petugas melalui chat dan panggilan video, serta ikuti vicon rapat dinas dari mana saja.",
                Icons.Outlined.SupportAgent, listOf(Icons.Outlined.VideoCall, Icons.Outlined.NotificationsActive), Sx.Cyan,
            ),
        )
    }
    val pager = rememberPagerState { pages.size }
    fun finish() {
        Session.onboardingSeen = true
        nav.navigate(Rt.LOGIN) { popUpTo(Rt.ONBOARD) { inclusive = true } }
    }
    Box(Modifier.fillMaxSize().background(Sx.Bg)) {
        AnimatedBlobs(Modifier.matchParentSize(), dark = false)
        Column(Modifier.fillMaxSize().statusBarsPadding().navigationBarsPadding()) {
            Row(Modifier.fillMaxWidth().padding(20.dp), verticalAlignment = Alignment.CenterVertically) {
                AppLogo(36.dp)
                Spacer(Modifier.width(10.dp))
                Text("SISFOPERS KC", style = MaterialTheme.typography.titleMedium, modifier = Modifier.weight(1f))
                Text("Lewati", color = Sx.Muted, fontWeight = FontWeight.Bold, modifier = Modifier.clip(RoundedCornerShape(10.dp)).pressable({ finish() }).padding(8.dp))
            }
            HorizontalPager(pager, Modifier.weight(1f)) { i ->
                val p = pages[i]
                Column(Modifier.fillMaxSize().padding(horizontal = 28.dp), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                    OnboardIllustration(p.icon, p.accents, p.color)
                    Spacer(Modifier.height(40.dp))
                    Text(p.title, style = MaterialTheme.typography.headlineLarge, textAlign = TextAlign.Center, modifier = Modifier.appear(0, key = i))
                    Spacer(Modifier.height(14.dp))
                    Text(p.body, style = MaterialTheme.typography.bodyLarge.copy(color = Sx.Muted), textAlign = TextAlign.Center, modifier = Modifier.appear(1, key = i))
                }
            }
            Row(Modifier.fillMaxWidth().padding(horizontal = 28.dp), horizontalArrangement = Arrangement.Center) {
                pages.indices.forEach { i ->
                    val w by animateDpAsState(if (pager.currentPage == i) 28.dp else 8.dp, spring(dampingRatio = 0.6f), label = "dot")
                    Box(Modifier.padding(4.dp).height(8.dp).width(w).clip(CircleShape).background(if (pager.currentPage == i) Sx.Blue else Sx.Line))
                }
            }
            Spacer(Modifier.height(24.dp))
            Row(Modifier.fillMaxWidth().padding(horizontal = 24.dp).padding(bottom = 20.dp), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                if (pager.currentPage > 0) {
                    SecondaryButton("Kembali", { scope.launch { pager.animateScrollToPage(pager.currentPage - 1) } }, Modifier.weight(1f), height = 54.dp)
                }
                PrimaryButton(
                    if (pager.currentPage == pages.lastIndex) "Mulai Sekarang" else "Lanjut",
                    { if (pager.currentPage == pages.lastIndex) finish() else scope.launch { pager.animateScrollToPage(pager.currentPage + 1) } },
                    Modifier.weight(1.4f),
                )
            }
        }
    }
}

@Composable
private fun OnboardIllustration(icon: ImageVector, accents: List<ImageVector>, color: Color) {
    val t = rememberInfiniteTransition(label = "ill")
    val orbit by t.animateFloat(0f, 360f, infiniteRepeatable(tween(16000, easing = LinearEasing)), label = "o")
    val pulse by t.animateFloat(0.92f, 1.06f, infiniteRepeatable(tween(1600, easing = FastOutSlowInEasing), RepeatMode.Reverse), label = "p")
    Box(Modifier.size(260.dp), contentAlignment = Alignment.Center) {
        Box(Modifier.size(240.dp).scale(pulse).clip(CircleShape).background(color.copy(0.07f)))
        Box(Modifier.size(180.dp).clip(CircleShape).background(color.copy(0.10f)))
        Floating {
            Box(
                Modifier.size(124.dp).clip(RoundedCornerShape(40.dp)).background(Brush.linearGradient(listOf(color, color.copy(0.7f)))),
                contentAlignment = Alignment.Center,
            ) { Icon(icon, null, tint = Color.White, modifier = Modifier.size(62.dp)) }
        }
        accents.forEachIndexed { i, a ->
            val angle = Math.toRadians((orbit + i * 180).toDouble())
            Box(
                Modifier.offset(x = (110 * kotlin.math.cos(angle)).dp, y = (110 * kotlin.math.sin(angle)).dp)
                    .size(52.dp).clip(RoundedCornerShape(18.dp)).background(Color.White),
                contentAlignment = Alignment.Center,
            ) { Icon(a, null, tint = color, modifier = Modifier.size(26.dp)) }
        }
    }
}

@Composable
fun MaintenanceScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    Box(Modifier.fillMaxSize().background(Sx.Bg)) {
        AnimatedBlobs(Modifier.matchParentSize(), dark = false)
        Column(Modifier.fillMaxSize().padding(28.dp), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
            Floating {
                Box(Modifier.size(120.dp).clip(RoundedCornerShape(38.dp)).background(Brush.linearGradient(listOf(Sx.Amber, Color(0xFFF97316)))), contentAlignment = Alignment.Center) {
                    Icon(Icons.Outlined.Build, null, tint = Color.White, modifier = Modifier.size(56.dp))
                }
            }
            Spacer(Modifier.height(30.dp))
            Text("Sistem Dalam Pemeliharaan", style = MaterialTheme.typography.headlineMedium, textAlign = TextAlign.Center)
            Spacer(Modifier.height(10.dp))
            Text(
                "Kami sedang melakukan peningkatan layanan SISFOPERS KC. Silakan coba kembali beberapa saat lagi.",
                style = MaterialTheme.typography.bodyLarge.copy(color = Sx.Muted), textAlign = TextAlign.Center,
            )
            Spacer(Modifier.height(28.dp))
            PrimaryButton("Periksa Kembali", {
                scope.launch {
                    val cfg = Auth.loadConfig(ctx)
                    if (cfg != null && !cfg.bool("under_maintenance")) {
                        val target = if (Session.isLoggedIn) Auth.routeFor(Session.me.value) else Rt.LOGIN
                        nav.navigate(target) { popUpTo(0) { inclusive = true } }
                    } else id.my.sisfoperskc.app.ui.components.Ui.toast("Sistem masih dalam pemeliharaan.")
                }
            }, Modifier.fillMaxWidth())
            Spacer(Modifier.height(10.dp))
            SecondaryButton("Masuk dengan akun lain", {
                scope.launch {
                    if (Session.isLoggedIn) Auth.logout(ctx)
                    nav.navigate(Rt.LOGIN) { popUpTo(0) { inclusive = true } }
                }
            }, Modifier.fillMaxWidth())
        }
    }
}

@Composable
fun AuthHero(title: String, subtitle: String, onBack: (() -> Unit)? = null, content: @Composable BoxScope.() -> Unit = {}) {
    Box(
        Modifier.fillMaxWidth().clip(RoundedCornerShape(bottomStart = 36.dp, bottomEnd = 36.dp)).background(Sx.HeroGradient),
    ) {
        AnimatedBlobs(Modifier.matchParentSize())
        Column(Modifier.fillMaxWidth().statusBarsPadding().padding(horizontal = 24.dp, vertical = 20.dp)) {
            if (onBack != null) {
                Box(Modifier.size(42.dp).clip(CircleShape).background(Color.White.copy(0.18f)).pressable(onBack), contentAlignment = Alignment.Center) {
                    Icon(Icons.AutoMirrored.Outlined.ArrowBack, "Kembali", tint = Color.White)
                }
                Spacer(Modifier.height(18.dp))
            }
            AnimatedContent(title, transitionSpec = { (fadeIn() + slideInVertically { it / 3 }) togetherWith fadeOut() }, label = "t") { tt ->
                Text(tt, color = Color.White, fontSize = 28.sp, fontWeight = FontWeight.ExtraBold, lineHeight = 34.sp)
            }
            Spacer(Modifier.height(6.dp))
            Text(subtitle, color = Color.White.copy(0.82f), fontSize = 14.sp, lineHeight = 20.sp)
            Spacer(Modifier.height(10.dp))
        }
        content()
    }
}
