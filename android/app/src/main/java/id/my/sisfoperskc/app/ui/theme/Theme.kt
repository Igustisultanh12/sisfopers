package id.my.sisfoperskc.app.ui.theme

import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Shapes
import androidx.compose.material3.Typography
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.Font
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.R

/** Palet mengikuti antarmuka web SISFOPERSKC (Tailwind slate + blue-600). */
object Sx {
    val Blue = Color(0xFF2563EB)
    val BlueDark = Color(0xFF1D4ED8)
    val BlueDeep = Color(0xFF1E3A8A)
    val BlueSoft = Color(0xFFEFF6FF)
    val BlueLine = Color(0xFFBFDBFE)
    val Indigo = Color(0xFF4F46E5)
    val Bg = Color(0xFFF8FAFC)
    val Card = Color(0xFFFFFFFF)
    val Line = Color(0xFFE2E8F0)
    val LineSoft = Color(0xFFF1F5F9)
    val Ink = Color(0xFF0F172A)
    val Ink2 = Color(0xFF334155)
    val Muted = Color(0xFF64748B)
    val Faint = Color(0xFF94A3B8)
    val Green = Color(0xFF10B981)
    val GreenSoft = Color(0xFFECFDF5)
    val GreenInk = Color(0xFF047857)
    val Amber = Color(0xFFF59E0B)
    val AmberSoft = Color(0xFFFFFBEB)
    val AmberInk = Color(0xFFB45309)
    val Red = Color(0xFFEF4444)
    val RedSoft = Color(0xFFFEF2F2)
    val RedInk = Color(0xFFB91C1C)
    val Purple = Color(0xFF8B5CF6)
    val PurpleSoft = Color(0xFFF5F3FF)
    val Cyan = Color(0xFF06B6D4)
    val CyanSoft = Color(0xFFECFEFF)
    val Slate900 = Color(0xFF0B1220)

    val HeroGradient = Brush.linearGradient(listOf(Color(0xFF1E40AF), Color(0xFF2563EB), Color(0xFF3B82F6)))
    val NightGradient = Brush.verticalGradient(listOf(Color(0xFF0F172A), Color(0xFF1E293B)))

    fun matraColor(m: String?): Color = when (m?.uppercase()) {
        "AD" -> Color(0xFF16A34A)
        "AL" -> Color(0xFF2563EB)
        "AU" -> Color(0xFF0EA5E9)
        else -> Muted
    }
}

val Figtree = FontFamily(
    Font(R.font.figtree_regular, FontWeight.Normal),
    Font(R.font.figtree_medium, FontWeight.Medium),
    Font(R.font.figtree_semibold, FontWeight.SemiBold),
    Font(R.font.figtree_bold, FontWeight.Bold),
    Font(R.font.figtree_extrabold, FontWeight.ExtraBold),
    Font(R.font.figtree_extrabold, FontWeight.Black),
)

private val base = TextStyle(fontFamily = Figtree, color = Sx.Ink)

val SxTypography = Typography(
    displayLarge = base.copy(fontSize = 40.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = (-1).sp),
    displayMedium = base.copy(fontSize = 34.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.8).sp),
    displaySmall = base.copy(fontSize = 28.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.5).sp),
    headlineLarge = base.copy(fontSize = 26.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.5).sp),
    headlineMedium = base.copy(fontSize = 22.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = (-0.3).sp),
    headlineSmall = base.copy(fontSize = 19.sp, fontWeight = FontWeight.Bold, letterSpacing = (-0.2).sp),
    titleLarge = base.copy(fontSize = 18.sp, fontWeight = FontWeight.Bold),
    titleMedium = base.copy(fontSize = 15.sp, fontWeight = FontWeight.Bold),
    titleSmall = base.copy(fontSize = 13.sp, fontWeight = FontWeight.Bold),
    bodyLarge = base.copy(fontSize = 15.sp, fontWeight = FontWeight.Normal, lineHeight = 22.sp),
    bodyMedium = base.copy(fontSize = 14.sp, fontWeight = FontWeight.Normal, lineHeight = 20.sp, color = Sx.Ink2),
    bodySmall = base.copy(fontSize = 12.sp, fontWeight = FontWeight.Medium, lineHeight = 17.sp, color = Sx.Muted),
    labelLarge = base.copy(fontSize = 14.sp, fontWeight = FontWeight.Bold),
    labelMedium = base.copy(fontSize = 12.sp, fontWeight = FontWeight.Bold),
    labelSmall = base.copy(fontSize = 10.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = 0.8.sp),
)

private val colors = lightColorScheme(
    primary = Sx.Blue,
    onPrimary = Color.White,
    primaryContainer = Sx.BlueSoft,
    onPrimaryContainer = Sx.BlueDeep,
    secondary = Sx.Indigo,
    onSecondary = Color.White,
    tertiary = Sx.Green,
    background = Sx.Bg,
    onBackground = Sx.Ink,
    surface = Sx.Card,
    onSurface = Sx.Ink,
    surfaceVariant = Sx.LineSoft,
    onSurfaceVariant = Sx.Muted,
    surfaceContainer = Sx.Card,
    surfaceContainerLow = Sx.Card,
    surfaceContainerHigh = Sx.Card,
    surfaceContainerHighest = Sx.LineSoft,
    surfaceContainerLowest = Sx.Card,
    outline = Sx.Line,
    outlineVariant = Sx.LineSoft,
    error = Sx.Red,
    onError = Color.White,
)

val SxShapes = Shapes(
    extraSmall = RoundedCornerShape(8.dp),
    small = RoundedCornerShape(12.dp),
    medium = RoundedCornerShape(16.dp),
    large = RoundedCornerShape(22.dp),
    extraLarge = RoundedCornerShape(28.dp),
)

@Composable
fun SisfoTheme(content: @Composable () -> Unit) {
    MaterialTheme(colorScheme = colors, typography = SxTypography, shapes = SxShapes, content = content)
}
