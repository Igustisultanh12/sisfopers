package id.my.sisfoperskc.app.ui.components

import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.geometry.CornerRadius
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.ui.theme.Sx

val ChartPalette = listOf(Sx.Blue, Sx.Green, Sx.Cyan, Sx.Amber, Sx.Purple, Sx.Red, Color(0xFFEC4899), Color(0xFF14B8A6))

@Composable
fun CountUp(target: Int, style: androidx.compose.ui.text.TextStyle = MaterialTheme.typography.headlineMedium, color: Color = Sx.Ink) {
    val anim = remember { Animatable(0f) }
    LaunchedEffect(target) { anim.animateTo(target.toFloat(), tween(900, easing = FastOutSlowInEasing)) }
    Text(Fmt.number(anim.value.toInt()), style = style, color = color)
}

@Composable
fun StatCard(
    label: String,
    value: Int,
    icon: ImageVector,
    color: Color = Sx.Blue,
    modifier: Modifier = Modifier,
    caption: String? = null,
    onClick: (() -> Unit)? = null,
) {
    SxCard(modifier, onClick = onClick, padding = androidx.compose.foundation.layout.PaddingValues(16.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(icon, tint = color, size = 40.dp, iconSize = 20.dp, radius = 13.dp)
            Spacer(Modifier.weight(1f))
            if (caption != null) Text(caption, fontSize = 10.sp, color = color, fontWeight = FontWeight.Bold)
        }
        Spacer(Modifier.height(14.dp))
        CountUp(value)
        Text(label, style = MaterialTheme.typography.bodySmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
    }
}

/** Kartu statistik gradien besar untuk hero dashboard. */
@Composable
fun HeroStat(label: String, value: Int, icon: ImageVector, modifier: Modifier = Modifier) {
    Column(
        modifier.clip(RoundedCornerShape(20.dp)).background(Color.White.copy(alpha = 0.14f)).padding(14.dp),
    ) {
        Icon(icon, null, tint = Color.White.copy(0.9f), modifier = Modifier.size(20.dp))
        Spacer(Modifier.height(10.dp))
        CountUp(value, MaterialTheme.typography.headlineSmall, Color.White)
        Text(label, color = Color.White.copy(0.75f), fontSize = 11.sp, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
    }
}

@Composable
fun BarChart(
    labels: List<String>,
    values: List<Int>,
    modifier: Modifier = Modifier,
    height: Dp = 180.dp,
    colors: List<Color> = ChartPalette,
) {
    val progress = remember(values) { Animatable(0f) }
    LaunchedEffect(values) { progress.animateTo(1f, tween(1000, easing = FastOutSlowInEasing)) }
    val max = (values.maxOrNull() ?: 0).coerceAtLeast(1)
    Column(modifier.fillMaxWidth()) {
        Row(Modifier.fillMaxWidth().height(height), horizontalArrangement = Arrangement.SpaceEvenly, verticalAlignment = Alignment.Bottom) {
            values.forEachIndexed { i, v ->
                Column(Modifier.weight(1f), horizontalAlignment = Alignment.CenterHorizontally) {
                    Text(v.toString(), fontSize = 12.sp, fontWeight = FontWeight.ExtraBold, color = Sx.Ink2)
                    Spacer(Modifier.height(6.dp))
                    val frac = (v.toFloat() / max) * progress.value
                    val c = colors[i % colors.size]
                    Box(
                        Modifier
                            .width(34.dp)
                            .height((height - 30.dp) * frac.coerceAtLeast(0.02f))
                            .clip(RoundedCornerShape(topStart = 12.dp, topEnd = 12.dp, bottomStart = 4.dp, bottomEnd = 4.dp))
                            .background(Brush.verticalGradient(listOf(c, c.copy(alpha = 0.55f)))),
                    )
                }
            }
        }
        Spacer(Modifier.height(8.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceEvenly) {
            labels.forEach { l ->
                Text(l, Modifier.weight(1f), fontSize = 11.sp, color = Sx.Muted, fontWeight = FontWeight.SemiBold, maxLines = 1, overflow = TextOverflow.Ellipsis, textAlign = androidx.compose.ui.text.style.TextAlign.Center)
            }
        }
    }
}

@Composable
fun DonutChart(
    labels: List<String>,
    values: List<Int>,
    modifier: Modifier = Modifier,
    size: Dp = 150.dp,
    centerLabel: String = "Total",
    colors: List<Color> = ChartPalette,
) {
    val progress = remember(values) { Animatable(0f) }
    LaunchedEffect(values) { progress.animateTo(1f, tween(1100, easing = FastOutSlowInEasing)) }
    val total = values.sum().coerceAtLeast(1)
    Row(modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
        Box(Modifier.size(size), contentAlignment = Alignment.Center) {
            Canvas(Modifier.fillMaxSize().padding(8.dp)) {
                val stroke = this.size.minDimension * 0.16f
                var start = -90f
                drawArc(Sx.LineSoft, 0f, 360f, false, style = Stroke(stroke))
                values.forEachIndexed { i, v ->
                    val sweep = 360f * v / total * progress.value
                    drawArc(colors[i % colors.size], start, (sweep - 2f).coerceAtLeast(0f), false, style = Stroke(stroke, cap = StrokeCap.Round))
                    start += sweep
                }
            }
            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                CountUp(values.sum(), MaterialTheme.typography.headlineSmall)
                Text(centerLabel, fontSize = 11.sp, color = Sx.Muted, fontWeight = FontWeight.SemiBold)
            }
        }
        Spacer(Modifier.width(16.dp))
        Column(verticalArrangement = Arrangement.spacedBy(10.dp), modifier = Modifier.weight(1f)) {
            labels.forEachIndexed { i, l ->
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(Modifier.size(10.dp).clip(CircleShape).background(colors[i % colors.size]))
                    Spacer(Modifier.width(8.dp))
                    Text(l, fontSize = 13.sp, color = Sx.Ink2, fontWeight = FontWeight.SemiBold, modifier = Modifier.weight(1f))
                    val v = values.getOrNull(i) ?: 0
                    Text("$v", fontSize = 13.sp, fontWeight = FontWeight.ExtraBold)
                    Text("  ${v * 100 / total}%", fontSize = 11.sp, color = Sx.Muted)
                }
            }
        }
    }
}

@Composable
fun AreaChart(labels: List<String>, values: List<Int>, modifier: Modifier = Modifier, height: Dp = 150.dp, color: Color = Sx.Blue) {
    val progress = remember(values) { Animatable(0f) }
    LaunchedEffect(values) { progress.animateTo(1f, tween(1200, easing = FastOutSlowInEasing)) }
    val max = (values.maxOrNull() ?: 0).coerceAtLeast(1).toFloat()
    Column(modifier.fillMaxWidth()) {
        Canvas(Modifier.fillMaxWidth().height(height)) {
            if (values.isEmpty()) return@Canvas
            val w = size.width
            val h = size.height - 12f
            val step = if (values.size > 1) w / (values.size - 1) else w
            val pts = values.mapIndexed { i, v -> Offset(i * step, h - (v / max) * h * progress.value + 6f) }
            for (g in 0..3) {
                val y = h * g / 3f + 6f
                drawLine(Sx.LineSoft, Offset(0f, y), Offset(w, y), strokeWidth = 2f)
            }
            val line = Path().apply {
                moveTo(pts.first().x, pts.first().y)
                for (i in 1 until pts.size) {
                    val p0 = pts[i - 1]; val p1 = pts[i]
                    val cx = (p0.x + p1.x) / 2
                    cubicTo(cx, p0.y, cx, p1.y, p1.x, p1.y)
                }
            }
            val area = Path().apply {
                addPath(line)
                lineTo(pts.last().x, size.height)
                lineTo(pts.first().x, size.height)
                close()
            }
            drawPath(area, Brush.verticalGradient(listOf(color.copy(0.28f), color.copy(0f))))
            drawPath(line, color, style = Stroke(width = 6f, cap = StrokeCap.Round))
            pts.forEach {
                drawCircle(Color.White, 9f, it)
                drawCircle(color, 6f, it)
            }
        }
        Spacer(Modifier.height(6.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
            labels.forEach { Text(it, fontSize = 10.sp, color = Sx.Muted, fontWeight = FontWeight.SemiBold) }
        }
    }
}

@Composable
fun ProgressBar(fraction: Float, color: Color = Sx.Blue, modifier: Modifier = Modifier, height: Dp = 8.dp) {
    val anim = remember { Animatable(0f) }
    LaunchedEffect(fraction) { anim.animateTo(fraction.coerceIn(0f, 1f), tween(900, easing = FastOutSlowInEasing)) }
    Canvas(modifier.fillMaxWidth().height(height)) {
        drawRoundRect(Sx.LineSoft, size = size, cornerRadius = CornerRadius(size.height / 2))
        drawRoundRect(color, size = Size(size.width * anim.value, size.height), cornerRadius = CornerRadius(size.height / 2))
    }
}

@Composable
fun AttendanceSummary(hadir: Int, izin: Int, tidak: Int, total: Int, modifier: Modifier = Modifier) {
    val belum = (total - hadir - izin - tidak).coerceAtLeast(0)
    val t = total.coerceAtLeast(1).toFloat()
    Column(modifier, verticalArrangement = Arrangement.spacedBy(10.dp)) {
        listOf(
            Triple("Hadir", hadir, Sx.Green),
            Triple("Izin", izin, Sx.Amber),
            Triple("Tidak Hadir", tidak, Sx.Red),
            Triple("Belum Merespons", belum, Sx.Faint),
        ).forEach { (l, v, c) ->
            Column {
                Row {
                    Text(l, fontSize = 12.sp, color = Sx.Ink2, fontWeight = FontWeight.SemiBold, modifier = Modifier.weight(1f))
                    Text("$v", fontSize = 12.sp, fontWeight = FontWeight.ExtraBold)
                }
                Spacer(Modifier.height(4.dp))
                ProgressBar(v / t, c)
            }
        }
    }
}

@Composable
fun SparkCounter(label: String, value: Int, color: Color, modifier: Modifier = Modifier) {
    var shown by remember { mutableIntStateOf(0) }
    LaunchedEffect(value) { shown = value }
    Column(modifier.clip(RoundedCornerShape(16.dp)).background(color.copy(0.08f)).padding(12.dp), horizontalAlignment = Alignment.CenterHorizontally) {
        CountUp(shown, MaterialTheme.typography.titleLarge, color)
        Text(label, fontSize = 11.sp, color = Sx.Muted, fontWeight = FontWeight.SemiBold, maxLines = 1)
    }
}
