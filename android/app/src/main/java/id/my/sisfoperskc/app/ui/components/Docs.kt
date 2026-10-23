package id.my.sisfoperskc.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.gestures.rememberTransformableState
import androidx.compose.foundation.gestures.transformable
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.Download
import androidx.compose.material.icons.outlined.OpenInNew
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableFloatStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Files
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import java.net.URLEncoder

/** Pengunduh dokumen (PDF/Excel) terautentikasi; hasil disimpan ke Download lalu dibuka. */
@Composable
fun rememberDownloader(): (path: String, name: String) -> Unit {
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    return remember {
        { path: String, name: String ->
            scope.launch {
                Ui.toast("Menyiapkan $name ...")
                Api.download(path, fallbackName = name)
                    .onSuccess { Files.saveAndOpen(ctx, it) }
                    .onFailure { Ui.error(it.message ?: "Gagal mengunduh dokumen.") }
            }
            Unit
        }
    }
}

fun isPdfPath(path: String?) = path?.lowercase()?.substringBefore('?')?.endsWith(".pdf") == true

/** Path stream privat untuk dokumen (dipakai saat unduh dokumen pendaftar). */
fun privateStreamPath(path: String) = "/documents/private-stream?path=" + URLEncoder.encode(path, "UTF-8")

@Composable
fun ImageViewer(path: String, onDismiss: () -> Unit) {
    var scale by remember { mutableFloatStateOf(1f) }
    var offset by remember { mutableStateOf(Offset.Zero) }
    val state = rememberTransformableState { zoom, pan, _ ->
        scale = (scale * zoom).coerceIn(1f, 5f)
        offset = if (scale == 1f) Offset.Zero else offset + pan
    }
    Dialog(onDismissRequest = onDismiss, properties = DialogProperties(usePlatformDefaultWidth = false)) {
        Box(Modifier.fillMaxSize().background(Color.Black)) {
            RemoteImage(
                path,
                Modifier.fillMaxSize().transformable(state).graphicsLayer {
                    scaleX = scale; scaleY = scale; translationX = offset.x; translationY = offset.y
                },
                contentScale = ContentScale.Fit,
            )
            Box(
                Modifier.align(Alignment.TopEnd).statusBarsPadding().padding(16.dp).size(44.dp).clip(CircleShape).background(Color.White.copy(0.2f)).pressable(onDismiss),
                contentAlignment = Alignment.Center,
            ) { Icon(Icons.Outlined.Close, "Tutup", tint = Color.White) }
        }
    }
}

/** Kartu pratinjau dokumen: gambar dibuka penuh, PDF diunduh & dibuka. */
@Composable
fun DocumentTile(label: String, path: String?, modifier: Modifier = Modifier) {
    val download = rememberDownloader()
    var view by remember { mutableStateOf(false) }
    val has = !path.isNullOrBlank() && path != "null"
    val pdf = isPdfPath(path)
    Column(modifier) {
        Box(
            Modifier
                .fillMaxWidth()
                .height(130.dp)
                .clip(RoundedCornerShape(18.dp))
                .background(Sx.LineSoft)
                .border(1.dp, Sx.Line, RoundedCornerShape(18.dp))
                .pressable(
                    if (has) {
                        { if (pdf) { download(privateStreamPath(path!!), label.replace(' ', '_') + ".pdf") } else { view = true } }
                    } else null,
                ),
            contentAlignment = Alignment.Center,
        ) {
            when {
                !has -> Text("Tidak ada berkas", style = MaterialTheme.typography.bodySmall)
                pdf -> Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Icon(Icons.Outlined.Description, null, tint = Sx.Red, modifier = Modifier.size(38.dp))
                    Spacer(Modifier.height(4.dp))
                    Text("Dokumen PDF", fontSize = 12.sp, fontWeight = FontWeight.Bold, color = Sx.Ink2)
                }
                else -> RemoteImage(path, Modifier.fillMaxSize())
            }
            if (has) {
                Box(
                    Modifier.align(Alignment.BottomEnd).padding(8.dp).size(30.dp).clip(CircleShape).background(Color.White),
                    contentAlignment = Alignment.Center,
                ) { Icon(if (pdf) Icons.Outlined.Download else Icons.Outlined.OpenInNew, null, tint = Sx.Blue, modifier = Modifier.size(16.dp)) }
            }
        }
        Spacer(Modifier.height(6.dp))
        Text(label, fontSize = 12.sp, fontWeight = FontWeight.SemiBold, color = Sx.Ink2, maxLines = 1, overflow = TextOverflow.Ellipsis)
    }
    if (view && path != null) ImageViewer(path) { view = false }
}

@Composable
fun FileLink(label: String, path: String?, modifier: Modifier = Modifier) {
    val download = rememberDownloader()
    var view by remember { mutableStateOf(false) }
    if (path.isNullOrBlank() || path == "null") return
    Row(
        modifier.fillMaxWidth().clip(RoundedCornerShape(14.dp)).background(Sx.BlueSoft)
            .pressable({ if (isPdfPath(path) || !path.contains('.')) { download(privateStreamPath(path), path.substringAfterLast('/')) } else { view = true } })
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Icon(Icons.Outlined.Description, null, tint = Sx.Blue, modifier = Modifier.size(20.dp))
        Spacer(Modifier.width(10.dp))
        Text(label, color = Sx.BlueDark, fontWeight = FontWeight.Bold, fontSize = 13.sp, modifier = Modifier.weight(1f), maxLines = 1, overflow = TextOverflow.Ellipsis)
        Icon(Icons.Outlined.OpenInNew, null, tint = Sx.Blue, modifier = Modifier.size(18.dp))
    }
    if (view) ImageViewer(path) { view = false }
}

@Composable
fun PersonelRow(
    p: kotlinx.serialization.json.JsonObject,
    modifier: Modifier = Modifier,
    trailing: (@Composable () -> Unit)? = null,
    subtitle: String? = null,
) {
    Row(modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
        Avatar(p.strOrNull("photo_profile"), p.str("full_name"), 46.dp)
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) {
            Text(p.str("full_name", "-"), style = MaterialTheme.typography.titleSmall.copy(fontSize = 14.sp), maxLines = 1, overflow = TextOverflow.Ellipsis)
            Text(
                subtitle ?: listOf(p.str("pangkat"), p.str("nikc")).filter { it.isNotBlank() }.joinToString(" • "),
                style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis,
            )
        }
        if (trailing != null) {
            Spacer(Modifier.width(8.dp))
            trailing()
        }
    }
}
