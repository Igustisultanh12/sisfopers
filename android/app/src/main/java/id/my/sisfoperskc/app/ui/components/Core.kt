package id.my.sisfoperskc.app.ui.components

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.Spring
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.spring
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.expandVertically
import androidx.compose.animation.shrinkVertically
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsPressedAsState
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxScope
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.RowScope
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.CloudOff
import androidx.compose.material.icons.outlined.Inbox
import androidx.compose.material.icons.outlined.Refresh
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.AsyncImage
import coil.request.ImageRequest
import id.my.sisfoperskc.app.R
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay

// ------------------------------------------------------------------ Animasi

/** Animasi masuk bertahap (fade + slide) ala Settigation untuk kartu & item daftar. */
@Composable
fun Modifier.appear(index: Int = 0, key: Any? = Unit, distance: Float = 46f): Modifier {
    val anim = remember(key) { Animatable(0f) }
    LaunchedEffect(key) {
        delay((index.coerceIn(0, 12)) * 45L)
        anim.animateTo(1f, tween(460, easing = FastOutSlowInEasing))
    }
    return this.graphicsLayer {
        alpha = anim.value
        translationY = (1f - anim.value) * distance
        val s = 0.96f + 0.04f * anim.value
        scaleX = s
        scaleY = s
    }
}

/** Efek mengecil saat ditekan. */
@Composable
fun Modifier.pressable(
    onClick: (() -> Unit)?,
    enabled: Boolean = true,
    pressedScale: Float = 0.97f,
): Modifier {
    if (onClick == null) return this
    val source = remember { MutableInteractionSource() }
    val pressed by source.collectIsPressedAsState()
    val scale by animateFloatAsState(
        if (pressed) pressedScale else 1f,
        spring(dampingRatio = Spring.DampingRatioMediumBouncy, stiffness = Spring.StiffnessMedium),
        label = "press",
    )
    return this
        .scale(scale)
        .clickable(interactionSource = source, indication = androidx.compose.material3.ripple(), enabled = enabled, onClick = onClick)
}

@Composable
fun Modifier.shimmer(): Modifier {
    val t = rememberInfiniteTransition(label = "shimmer")
    val x by t.animateFloat(
        initialValue = -600f, targetValue = 1400f,
        animationSpec = infiniteRepeatable(tween(1300, easing = LinearEasing)), label = "x",
    )
    return this.background(
        Brush.linearGradient(
            listOf(Sx.LineSoft, Color(0xFFE9EEF5), Sx.LineSoft),
            start = Offset(x, 0f), end = Offset(x + 500f, 200f),
        ),
    )
}

@Composable
fun Floating(content: @Composable BoxScope.() -> Unit) {
    val t = rememberInfiniteTransition(label = "float")
    val y by t.animateFloat(-6f, 6f, infiniteRepeatable(tween(1800, easing = FastOutSlowInEasing), RepeatMode.Reverse), label = "y")
    Box(Modifier.graphicsLayer { translationY = y }, content = content)
}

// ------------------------------------------------------------------ Kartu & tata letak

@Composable
fun SxCard(
    modifier: Modifier = Modifier,
    onClick: (() -> Unit)? = null,
    padding: PaddingValues = PaddingValues(18.dp),
    radius: Dp = 22.dp,
    color: Color = Sx.Card,
    border: Color = Sx.Line,
    elevation: Dp = 0.dp,
    content: @Composable ColumnScope.() -> Unit,
) {
    val shape = RoundedCornerShape(radius)
    Column(
        modifier
            .then(if (elevation > 0.dp) Modifier.shadow(elevation, shape, ambientColor = Sx.Blue.copy(0.12f), spotColor = Sx.Blue.copy(0.12f)) else Modifier)
            .clip(shape)
            .background(color)
            .border(1.dp, border, shape)
            .pressable(onClick)
            .padding(padding),
        content = content,
    )
}

@Composable
fun SectionTitle(
    title: String,
    modifier: Modifier = Modifier,
    subtitle: String? = null,
    action: String? = null,
    onAction: (() -> Unit)? = null,
) {
    Row(modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.titleLarge)
            if (subtitle != null) Text(subtitle, style = MaterialTheme.typography.bodySmall)
        }
        if (action != null && onAction != null) {
            Text(
                action,
                color = Sx.Blue,
                style = MaterialTheme.typography.labelLarge,
                modifier = Modifier.clip(RoundedCornerShape(10.dp)).clickable(onClick = onAction).padding(horizontal = 8.dp, vertical = 4.dp),
            )
        }
    }
}

@Composable
fun Eyebrow(text: String, color: Color = Sx.Blue, modifier: Modifier = Modifier) {
    Text(text.uppercase(), style = MaterialTheme.typography.labelSmall, color = color, modifier = modifier)
}

@Composable
fun IconBox(
    icon: ImageVector,
    tint: Color = Sx.Blue,
    bg: Color = tint.copy(alpha = 0.1f),
    size: Dp = 44.dp,
    iconSize: Dp = 22.dp,
    radius: Dp = 14.dp,
) {
    Box(
        Modifier.size(size).clip(RoundedCornerShape(radius)).background(bg),
        contentAlignment = Alignment.Center,
    ) { Icon(icon, null, tint = tint, modifier = Modifier.size(iconSize)) }
}

enum class Tone(val fg: Color, val bg: Color) {
    Blue(Sx.BlueDark, Sx.BlueSoft),
    Green(Sx.GreenInk, Sx.GreenSoft),
    Amber(Sx.AmberInk, Sx.AmberSoft),
    Red(Sx.RedInk, Sx.RedSoft),
    Gray(Sx.Ink2, Sx.LineSoft),
    Purple(Color(0xFF6D28D9), Sx.PurpleSoft),
    Cyan(Color(0xFF0E7490), Sx.CyanSoft),
}

fun toneOf(status: String?): Tone = when (status?.uppercase()?.replace(' ', '_')) {
    "APPROVED", "VERIFIED", "ACCEPTED", "DISETUJUI", "SELESAI", "HADIR", "ACTIVE", "OPEN", "LENGKAP",
    "AKTIF", "DONE", "TERBIT", "DITERBITKAN", "JOINED", "SUCCESS", "TERVERIFIKASI", "BERHASIL" -> Tone.Green
    "PENDING", "DIPROSES", "SUBMITTED", "SCHEDULED", "IZIN", "MENUNGGU", "INVITED", "BELUM_LENGKAP", "PROCESSING", "RINGING" -> Tone.Amber
    "REJECTED", "DITOLAK", "TIDAK_HADIR", "ABSEN", "FAILED", "GAGAL", "NONAKTIF", "INACTIVE", "MENINGGAL" -> Tone.Red
    "CLOSED", "ENDED", "EXPIRED", "LEFT", "DITUTUP", "BERAKHIR" -> Tone.Gray
    else -> Tone.Blue
}

fun statusLabel(status: String?): String = when (status?.uppercase()) {
    "APPROVED" -> "Disetujui"
    "REJECTED" -> "Ditolak"
    "PENDING" -> "Menunggu"
    "VERIFIED" -> "Terverifikasi"
    "ACCEPTED" -> "Diterima"
    "SUBMITTED" -> "Terkirim"
    "OPEN" -> "Aktif"
    "CLOSED" -> "Ditutup"
    "ACTIVE" -> "Berlangsung"
    "SCHEDULED" -> "Terjadwal"
    "ENDED" -> "Selesai"
    "TIDAK_HADIR" -> "Tidak Hadir"
    "DIPROSES" -> "Diproses"
    "DISETUJUI" -> "Disetujui"
    "DITOLAK" -> "Ditolak"
    "SELESAI" -> "Selesai"
    "HADIR" -> "Hadir"
    "IZIN" -> "Izin"
    "ABSEN" -> "Absen"
    "LENGKAP" -> "Lengkap"
    "BELUM_LENGKAP" -> "Belum Lengkap"
    null, "" -> "-"
    else -> Fmt.title(status)
}

@Composable
fun Badge(text: String, tone: Tone = Tone.Blue, modifier: Modifier = Modifier, icon: ImageVector? = null) {
    Row(
        modifier.clip(RoundedCornerShape(50)).background(tone.bg).padding(horizontal = 10.dp, vertical = 4.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        if (icon != null) {
            Icon(icon, null, tint = tone.fg, modifier = Modifier.size(12.dp))
            Spacer(Modifier.width(4.dp))
        }
        Text(text, color = tone.fg, fontSize = 11.sp, fontWeight = FontWeight.Bold, maxLines = 1)
    }
}

@Composable
fun StatusBadge(status: String?, modifier: Modifier = Modifier) = Badge(statusLabel(status), toneOf(status), modifier)

@Composable
fun MatraBadge(matra: String?) {
    val c = Sx.matraColor(matra)
    Row(
        Modifier.clip(RoundedCornerShape(8.dp)).background(c.copy(0.1f)).padding(horizontal = 8.dp, vertical = 3.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Box(Modifier.size(6.dp).clip(CircleShape).background(c))
        Spacer(Modifier.width(5.dp))
        Text(Fmt.matra(matra), color = c, fontSize = 11.sp, fontWeight = FontWeight.Bold)
    }
}

// ------------------------------------------------------------------ Gambar

@Composable
fun RemoteImage(
    path: String?,
    modifier: Modifier = Modifier,
    contentScale: ContentScale = ContentScale.Crop,
    contentDescription: String? = null,
) {
    val url = Api.fileUrl(path) ?: return
    if (url.startsWith("data:")) {
        val bmp = remember(url) { decodeDataUri(url) }
        if (bmp != null) Image(bmp, contentDescription, modifier, contentScale = contentScale)
        return
    }
    val ctx = LocalContext.current
    AsyncImage(
        model = ImageRequest.Builder(ctx).data(url).crossfade(true).build(),
        contentDescription = contentDescription,
        contentScale = contentScale,
        modifier = modifier,
    )
}

private fun decodeDataUri(uri: String): androidx.compose.ui.graphics.ImageBitmap? = try {
    val b64 = uri.substringAfter("base64,", "")
    val bytes = android.util.Base64.decode(b64, android.util.Base64.DEFAULT)
    android.graphics.BitmapFactory.decodeByteArray(bytes, 0, bytes.size)?.asImageBitmap()
} catch (_: Exception) {
    null
}

@Composable
fun Avatar(path: String?, name: String?, size: Dp = 44.dp, radius: Dp? = null, ring: Color? = null) {
    val shape = if (radius == null) CircleShape else RoundedCornerShape(radius)
    Box(
        Modifier
            .size(size)
            .then(if (ring != null) Modifier.border(2.dp, ring, shape) else Modifier)
            .clip(shape)
            .background(Brush.linearGradient(listOf(Color(0xFFDBEAFE), Color(0xFFE0E7FF)))),
        contentAlignment = Alignment.Center,
    ) {
        Text(
            Fmt.initials(name),
            color = Sx.BlueDark,
            fontWeight = FontWeight.ExtraBold,
            fontSize = (size.value * 0.36f).sp,
        )
        RemoteImage(path, Modifier.fillMaxSize())
    }
}

@Composable
fun AppLogo(size: Dp = 56.dp, modifier: Modifier = Modifier) {
    Image(painterResource(R.drawable.ic_logo), null, modifier.size(size))
}

// ------------------------------------------------------------------ Tombol

@Composable
fun PrimaryButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    loading: Boolean = false,
    enabled: Boolean = true,
    icon: ImageVector? = null,
    color: Color = Sx.Blue,
    height: Dp = 54.dp,
) {
    val active = enabled && !loading
    Box(
        modifier
            .height(height)
            .shadow(if (active) 10.dp else 0.dp, RoundedCornerShape(16.dp), ambientColor = color.copy(0.4f), spotColor = color.copy(0.4f))
            .clip(RoundedCornerShape(16.dp))
            .background(if (active) Brush.horizontalGradient(listOf(color, color.copy(red = (color.red * 0.85f)))) else Brush.horizontalGradient(listOf(Sx.Faint, Sx.Faint)))
            .pressable(if (active) onClick else null)
            .padding(horizontal = 20.dp),
        contentAlignment = Alignment.Center,
    ) {
        if (loading) {
            CircularProgressIndicator(color = Color.White, strokeWidth = 2.5.dp, modifier = Modifier.size(22.dp))
        } else {
            Row(verticalAlignment = Alignment.CenterVertically) {
                if (icon != null) {
                    Icon(icon, null, tint = Color.White, modifier = Modifier.size(20.dp))
                    Spacer(Modifier.width(8.dp))
                }
                Text(text, color = Color.White, fontWeight = FontWeight.Bold, fontSize = 15.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
            }
        }
    }
}

@Composable
fun SecondaryButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    icon: ImageVector? = null,
    color: Color = Sx.Ink2,
    enabled: Boolean = true,
    loading: Boolean = false,
    height: Dp = 50.dp,
) {
    Box(
        modifier
            .height(height)
            .clip(RoundedCornerShape(16.dp))
            .background(Sx.Card)
            .border(1.dp, Sx.Line, RoundedCornerShape(16.dp))
            .pressable(if (enabled && !loading) onClick else null)
            .padding(horizontal = 16.dp),
        contentAlignment = Alignment.Center,
    ) {
        if (loading) {
            CircularProgressIndicator(color = color, strokeWidth = 2.dp, modifier = Modifier.size(20.dp))
        } else {
            Row(verticalAlignment = Alignment.CenterVertically) {
                if (icon != null) {
                    Icon(icon, null, tint = if (enabled) color else Sx.Faint, modifier = Modifier.size(19.dp))
                    Spacer(Modifier.width(8.dp))
                }
                Text(text, color = if (enabled) color else Sx.Faint, fontWeight = FontWeight.Bold, fontSize = 14.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
            }
        }
    }
}

@Composable
fun SoftButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    icon: ImageVector? = null,
    tone: Tone = Tone.Blue,
    loading: Boolean = false,
    enabled: Boolean = true,
    height: Dp = 44.dp,
) {
    Box(
        modifier
            .height(height)
            .clip(RoundedCornerShape(14.dp))
            .background(if (enabled) tone.bg else Sx.LineSoft)
            .pressable(if (enabled && !loading) onClick else null)
            .padding(horizontal = 14.dp),
        contentAlignment = Alignment.Center,
    ) {
        if (loading) {
            CircularProgressIndicator(color = tone.fg, strokeWidth = 2.dp, modifier = Modifier.size(18.dp))
        } else {
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.Center) {
                if (icon != null) {
                    Icon(icon, null, tint = if (enabled) tone.fg else Sx.Faint, modifier = Modifier.size(17.dp))
                    Spacer(Modifier.width(6.dp))
                }
                Text(text, color = if (enabled) tone.fg else Sx.Faint, fontWeight = FontWeight.Bold, fontSize = 13.sp, maxLines = 1)
            }
        }
    }
}

@Composable
fun CircleIconButton(
    icon: ImageVector,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    tint: Color = Sx.Ink2,
    bg: Color = Sx.Card,
    size: Dp = 42.dp,
    badge: Int = 0,
    contentDescription: String? = null,
) {
    Box(modifier.size(size)) {
        Box(
            Modifier.fillMaxSize().clip(CircleShape).background(bg).border(1.dp, Sx.Line, CircleShape).pressable(onClick, pressedScale = 0.9f),
            contentAlignment = Alignment.Center,
        ) { Icon(icon, contentDescription, tint = tint, modifier = Modifier.size(size * 0.5f)) }
        if (badge > 0) {
            val pulse = rememberInfiniteTransition(label = "badge")
            val s by pulse.animateFloat(1f, 1.15f, infiniteRepeatable(tween(700), RepeatMode.Reverse), label = "s")
            Box(
                Modifier.align(Alignment.TopEnd).scale(s).widthIn(min = 18.dp).height(18.dp).clip(CircleShape).background(Sx.Red).border(2.dp, Color.White, CircleShape).padding(horizontal = 4.dp),
                contentAlignment = Alignment.Center,
            ) { Text(if (badge > 99) "99+" else badge.toString(), color = Color.White, fontSize = 9.sp, fontWeight = FontWeight.ExtraBold) }
        }
    }
}

// ------------------------------------------------------------------ Status kosong / galat / memuat

@Composable
fun EmptyState(
    title: String = "Belum ada data",
    message: String = "Data akan tampil di sini setelah tersedia.",
    icon: ImageVector = Icons.Outlined.Inbox,
    modifier: Modifier = Modifier,
    action: String? = null,
    onAction: (() -> Unit)? = null,
) {
    Column(modifier.fillMaxWidth().padding(vertical = 40.dp, horizontal = 24.dp), horizontalAlignment = Alignment.CenterHorizontally) {
        Floating {
            Box(
                Modifier.size(92.dp).clip(RoundedCornerShape(30.dp)).background(Brush.linearGradient(listOf(Sx.BlueSoft, Color(0xFFE0E7FF)))),
                contentAlignment = Alignment.Center,
            ) { Icon(icon, null, tint = Sx.Blue, modifier = Modifier.size(42.dp)) }
        }
        Spacer(Modifier.height(18.dp))
        Text(title, style = MaterialTheme.typography.titleMedium, textAlign = TextAlign.Center)
        Spacer(Modifier.height(4.dp))
        Text(message, style = MaterialTheme.typography.bodySmall, textAlign = TextAlign.Center)
        if (action != null && onAction != null) {
            Spacer(Modifier.height(16.dp))
            SoftButton(action, onAction)
        }
    }
}

@Composable
fun ErrorState(message: String, onRetry: () -> Unit, modifier: Modifier = Modifier) {
    EmptyState(
        title = "Gagal memuat data",
        message = message,
        icon = Icons.Outlined.CloudOff,
        modifier = modifier,
        action = "Coba Lagi",
        onAction = onRetry,
    )
}

@Composable
fun SkeletonList(count: Int = 5, modifier: Modifier = Modifier) {
    Column(modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        repeat(count) {
            Row(
                Modifier.fillMaxWidth().clip(RoundedCornerShape(20.dp)).background(Sx.Card).border(1.dp, Sx.Line, RoundedCornerShape(20.dp)).padding(16.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Box(Modifier.size(46.dp).clip(RoundedCornerShape(14.dp)).shimmer())
                Spacer(Modifier.width(14.dp))
                Column(Modifier.weight(1f)) {
                    Box(Modifier.fillMaxWidth(0.7f).height(14.dp).clip(RoundedCornerShape(6.dp)).shimmer())
                    Spacer(Modifier.height(8.dp))
                    Box(Modifier.fillMaxWidth(0.45f).height(11.dp).clip(RoundedCornerShape(6.dp)).shimmer())
                }
            }
        }
    }
}

@Composable
fun CenterLoading(modifier: Modifier = Modifier) {
    Box(modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
        CircularProgressIndicator(color = Sx.Blue, strokeWidth = 3.dp)
    }
}

// ------------------------------------------------------------------ Baris info

@Composable
fun InfoRow(label: String, value: String?, modifier: Modifier = Modifier, icon: ImageVector? = null, valueColor: Color = Sx.Ink) {
    Row(modifier.fillMaxWidth().padding(vertical = 7.dp), verticalAlignment = Alignment.Top) {
        if (icon != null) {
            Icon(icon, null, tint = Sx.Faint, modifier = Modifier.size(18.dp).padding(top = 1.dp))
            Spacer(Modifier.width(10.dp))
        }
        Text(label, style = MaterialTheme.typography.bodySmall, modifier = Modifier.weight(0.42f))
        Text(
            value?.takeIf { it.isNotBlank() && it != "null" } ?: "-",
            style = MaterialTheme.typography.bodyMedium.copy(fontWeight = FontWeight.SemiBold, color = valueColor),
            modifier = Modifier.weight(0.58f),
            textAlign = TextAlign.End,
        )
    }
}

@Composable
fun Divider(modifier: Modifier = Modifier) {
    Box(modifier.fillMaxWidth().height(1.dp).background(Sx.LineSoft))
}

@Composable
fun Notice(text: String, tone: Tone = Tone.Blue, icon: ImageVector? = null, modifier: Modifier = Modifier) {
    Row(
        modifier.fillMaxWidth().clip(RoundedCornerShape(16.dp)).background(tone.bg).border(1.dp, tone.fg.copy(0.15f), RoundedCornerShape(16.dp)).padding(14.dp),
        verticalAlignment = Alignment.Top,
    ) {
        if (icon != null) {
            Icon(icon, null, tint = tone.fg, modifier = Modifier.size(18.dp))
            Spacer(Modifier.width(10.dp))
        }
        Text(text, color = tone.fg, fontSize = 13.sp, fontWeight = FontWeight.Medium, lineHeight = 18.sp)
    }
}

@Composable
fun AnimatedSection(visible: Boolean, content: @Composable () -> Unit) {
    AnimatedVisibility(visible, enter = fadeIn() + expandVertically(), exit = fadeOut() + shrinkVertically()) { content() }
}

@Composable
fun Pill(text: String, selected: Boolean, onClick: () -> Unit, modifier: Modifier = Modifier, count: Int? = null) {
    val bg by androidx.compose.animation.animateColorAsState(if (selected) Sx.Blue else Sx.Card, label = "pill")
    val fg by androidx.compose.animation.animateColorAsState(if (selected) Color.White else Sx.Ink2, label = "pillfg")
    Row(
        modifier
            .clip(RoundedCornerShape(50))
            .background(bg)
            .border(1.dp, if (selected) Sx.Blue else Sx.Line, RoundedCornerShape(50))
            .pressable(onClick)
            .padding(horizontal = 14.dp, vertical = 8.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Text(text, color = fg, fontSize = 13.sp, fontWeight = FontWeight.Bold)
        if (count != null) {
            Spacer(Modifier.width(6.dp))
            Text(count.toString(), color = if (selected) Color.White.copy(0.85f) else Sx.Muted, fontSize = 12.sp, fontWeight = FontWeight.Bold)
        }
    }
}

@Composable
fun RowScope.Weighted(content: @Composable () -> Unit) {
    Box(Modifier.weight(1f)) { content() }
}

@Composable
fun SurfaceBox(modifier: Modifier = Modifier, content: @Composable () -> Unit) {
    Surface(modifier, color = Sx.Card, shape = RoundedCornerShape(22.dp), border = BorderStroke(1.dp, Sx.Line)) { content() }
}
