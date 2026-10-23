package id.my.sisfoperskc.app.ui.components

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.Spring
import androidx.compose.animation.core.spring
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInVertically
import androidx.compose.animation.slideOutVertically
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.ErrorOutline
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalBottomSheet
import androidx.compose.material3.Text
import androidx.compose.material3.rememberModalBottomSheetState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableSharedFlow

enum class MsgKind { Success, Error, Info, Warning }
data class UiMessage(val text: String, val kind: MsgKind, val id: Long = System.nanoTime())

object Ui {
    val messages = MutableSharedFlow<UiMessage>(extraBufferCapacity = 8)
    fun toast(text: String, kind: MsgKind = MsgKind.Info) {
        if (text.isNotBlank()) messages.tryEmit(UiMessage(text, kind))
    }
    fun success(text: String) = toast(text, MsgKind.Success)
    fun error(text: String) = toast(text, MsgKind.Error)

    /** Tampilkan hasil aksi API sebagai toast; mengembalikan true bila sukses. */
    fun report(r: ApiResult, successFallback: String = "Berhasil disimpan."): Boolean = when (r) {
        is ApiResult.Ok -> {
            success(r.message?.takeIf { it.isNotBlank() } ?: r.data.let { d -> (d["message"] as? kotlinx.serialization.json.JsonPrimitive)?.content } ?: successFallback)
            true
        }
        is ApiResult.Err -> {
            if (r.status != 401 && r.status != 409) error(r.message)
            false
        }
    }
}

/** Toast animasi di bagian atas layar. */
@Composable
fun MessageHost() {
    var current by remember { mutableStateOf<UiMessage?>(null) }
    var visible by remember { mutableStateOf(false) }
    LaunchedEffect(Unit) {
        Ui.messages.collect { msg ->
            current = msg
            visible = true
        }
    }
    LaunchedEffect(current?.id) {
        if (current != null) {
            delay(if (current?.kind == MsgKind.Error) 4200 else 3000)
            visible = false
        }
    }
    Box(Modifier.fillMaxWidth().statusBarsPadding().padding(horizontal = 16.dp, vertical = 8.dp), contentAlignment = Alignment.TopCenter) {
        AnimatedVisibility(
            visible = visible,
            enter = slideInVertically(spring(dampingRatio = Spring.DampingRatioMediumBouncy)) { -it * 2 } + fadeIn(),
            exit = slideOutVertically { -it * 2 } + fadeOut(),
        ) {
            val m = current ?: return@AnimatedVisibility
            val (icon, tone) = when (m.kind) {
                MsgKind.Success -> Icons.Outlined.CheckCircle to Tone.Green
                MsgKind.Error -> Icons.Outlined.ErrorOutline to Tone.Red
                MsgKind.Warning -> Icons.Outlined.WarningAmber to Tone.Amber
                MsgKind.Info -> Icons.Outlined.Info to Tone.Blue
            }
            Row(
                Modifier
                    .fillMaxWidth()
                    .shadow(18.dp, RoundedCornerShape(18.dp), ambientColor = Color.Black.copy(0.2f), spotColor = Color.Black.copy(0.2f))
                    .clip(RoundedCornerShape(18.dp))
                    .background(Color.White)
                    .border(1.dp, tone.fg.copy(0.15f), RoundedCornerShape(18.dp))
                    .clickable { visible = false }
                    .padding(horizontal = 14.dp, vertical = 12.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                IconBox(icon, tint = tone.fg, bg = tone.bg, size = 36.dp, iconSize = 20.dp, radius = 12.dp)
                Spacer(Modifier.width(12.dp))
                Text(m.text, fontSize = 13.sp, fontWeight = FontWeight.SemiBold, color = Sx.Ink, lineHeight = 18.sp)
            }
        }
    }
}

@Composable
fun SxDialog(
    onDismiss: () -> Unit,
    title: String,
    message: String? = null,
    icon: ImageVector? = Icons.Outlined.Info,
    tone: Tone = Tone.Blue,
    confirmText: String = "Ya, Lanjutkan",
    onConfirm: (() -> Unit)? = null,
    dismissText: String = "Batal",
    loading: Boolean = false,
    confirmColor: Color = Sx.Blue,
    content: (@Composable ColumnScope.() -> Unit)? = null,
) {
    Dialog(onDismissRequest = { if (!loading) onDismiss() }, properties = DialogProperties(usePlatformDefaultWidth = false)) {
        Column(
            Modifier
                .fillMaxWidth()
                .padding(horizontal = 22.dp)
                .imePadding()
                .clip(RoundedCornerShape(28.dp))
                .background(Color.White)
                .padding(22.dp)
                .appear(),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            if (icon != null) {
                IconBox(icon, tint = tone.fg, bg = tone.bg, size = 60.dp, iconSize = 30.dp, radius = 20.dp)
                Spacer(Modifier.height(14.dp))
            }
            Text(title, style = MaterialTheme.typography.titleLarge, textAlign = TextAlign.Center)
            if (message != null) {
                Spacer(Modifier.height(6.dp))
                Text(message, style = MaterialTheme.typography.bodyMedium, textAlign = TextAlign.Center, color = Sx.Muted)
            }
            if (content != null) {
                Spacer(Modifier.height(14.dp))
                Column(Modifier.fillMaxWidth().verticalScroll(rememberScrollState()), content = content)
            }
            Spacer(Modifier.height(20.dp))
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                SecondaryButton(dismissText, onDismiss, Modifier.weight(1f), enabled = !loading)
                if (onConfirm != null) {
                    PrimaryButton(confirmText, onConfirm, Modifier.weight(1f), loading = loading, color = confirmColor, height = 50.dp)
                }
            }
        }
    }
}

/** Dialog meminta alasan/catatan (mis. penolakan). */
@Composable
fun ReasonDialog(
    title: String,
    label: String = "Alasan",
    message: String? = null,
    required: Boolean = true,
    confirmText: String = "Kirim",
    tone: Tone = Tone.Red,
    loading: Boolean = false,
    onDismiss: () -> Unit,
    onSubmit: (String) -> Unit,
) {
    var text by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    SxDialog(
        onDismiss = onDismiss,
        title = title,
        message = message,
        icon = Icons.Outlined.WarningAmber,
        tone = tone,
        confirmText = confirmText,
        loading = loading,
        confirmColor = if (tone == Tone.Red) Sx.Red else Sx.Blue,
        onConfirm = {
            if (required && text.isBlank()) error = "$label wajib diisi." else onSubmit(text.trim())
        },
    ) {
        PlainTextField(text, { text = it; error = null }, label = label, minLines = 3, error = error)
    }
}

@Composable
fun SxSheet(onDismiss: () -> Unit, content: @Composable ColumnScope.() -> Unit) {
    val state = rememberModalBottomSheetState(skipPartiallyExpanded = true)
    ModalBottomSheet(
        onDismissRequest = onDismiss,
        sheetState = state,
        containerColor = Color.White,
        shape = RoundedCornerShape(topStart = 30.dp, topEnd = 30.dp),
        contentWindowInsets = { WindowInsets(0, 0, 0, 0) },
    ) {
        Column(
            Modifier
                .fillMaxWidth()
                .verticalScroll(rememberScrollState())
                .padding(horizontal = 20.dp)
                .padding(bottom = 24.dp)
                .navigationBarsPadding()
                .imePadding(),
            content = content,
        )
    }
}

@Composable
fun SheetAction(icon: ImageVector, text: String, tone: Tone = Tone.Blue, subtitle: String? = null, onClick: () -> Unit) {
    Row(
        Modifier.fillMaxWidth().clip(RoundedCornerShape(16.dp)).pressable(onClick).padding(vertical = 10.dp, horizontal = 4.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        IconBox(icon, tint = tone.fg, bg = tone.bg, size = 42.dp, iconSize = 20.dp)
        Spacer(Modifier.width(14.dp))
        Column(Modifier.weight(1f)) {
            Text(text, style = MaterialTheme.typography.titleSmall.copy(fontSize = 14.sp))
            if (subtitle != null) Text(subtitle, style = MaterialTheme.typography.bodySmall)
        }
    }
}
