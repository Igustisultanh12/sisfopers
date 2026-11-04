package id.my.sisfoperskc.app.ui.personel

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Add
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Business
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.ConfirmationNumber
import androidx.compose.material.icons.outlined.Construction
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.EventBusy
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Place
import androidx.compose.material.icons.outlined.Schedule
import androidx.compose.material.icons.outlined.Send
import androidx.compose.material.icons.outlined.UploadFile
import androidx.compose.material.icons.outlined.WarningAmber
import androidx.compose.material.icons.outlined.Work
import androidx.compose.material.icons.outlined.WorkOff
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
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.arr
import id.my.sisfoperskc.app.data.asStr
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.data.strings
import id.my.sisfoperskc.app.ui.auth.AsnJenisOpts
import id.my.sisfoperskc.app.ui.auth.OtpBoxes
import id.my.sisfoperskc.app.ui.common.BroadcastMini
import id.my.sisfoperskc.app.ui.components.Accept
import id.my.sisfoperskc.app.ui.components.AttendanceSummary
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.ChipsF
import id.my.sisfoperskc.app.ui.components.ChoiceChips
import id.my.sisfoperskc.app.ui.components.DateF
import id.my.sisfoperskc.app.ui.components.Divider
import id.my.sisfoperskc.app.ui.components.EmptyState
import id.my.sisfoperskc.app.ui.components.FileF
import id.my.sisfoperskc.app.ui.components.FileLink
import id.my.sisfoperskc.app.ui.components.FilePickerField
import id.my.sisfoperskc.app.ui.components.FilterRow
import id.my.sisfoperskc.app.ui.components.FormSection
import id.my.sisfoperskc.app.ui.components.FormState
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.InfoRow
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.Opt
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.PagedList
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.RemoteImage
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.SelectF
import id.my.sisfoperskc.app.ui.components.SoftButton
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SwitchRow
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.SxSheet
import id.my.sisfoperskc.app.ui.components.TextFieldF
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.WilayahFields
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.opts
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberForm
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.components.rememberPaged
import id.my.sisfoperskc.app.ui.components.statusLabel
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.NotificationBell
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

// ------------------------------------------------------------------ Broadcast & kegiatan

@Composable
fun MyBroadcastScreen() {
    val nav = LocalNav.current
    val state = rememberPaged("/personel/broadcast", "broadcasts")
    var filter by remember { mutableStateOf("") }
    ShellScaffold(title = "Tugas & Kegiatan", subtitle = "Konfirmasi kehadiran Anda", topLevel = true, actions = { NotificationBell() }) { inner ->
        PagedList(
            state, Modifier.padding(top = inner.calculateTopPadding()),
            searchHint = null,
            filterBar = {
                FilterRow(listOf(Opt("", "Semua"), Opt("pending", "Belum Respons"), Opt("done", "Sudah Respons")), filter) { filter = it }
            },
            emptyTitle = "Belum ada kegiatan", emptyMessage = "Broadcast kegiatan untuk Anda akan tampil di sini.",
        ) { b, _ ->
            val responded = b.objects("responses").isNotEmpty()
            val show = when (filter) {
                "pending" -> !responded
                "done" -> responded
                else -> true
            }
            if (show) BroadcastMini(b) { nav.navigate(Rt.with(Rt.MY_BROADCAST_DETAIL, "uuid" to b.str("uuid"))) }
        }
    }
}

@Composable
fun MyBroadcastDetailScreen(uuid: String) {
    val scope = rememberCoroutineScope()
    val loader = rememberPage("/personel/broadcast/$uuid")
    var choice by remember { mutableStateOf<String?>(null) }
    var notes by remember { mutableStateOf("") }
    var permit by remember { mutableStateOf(false) }
    var sending by remember { mutableStateOf(false) }
    ShellScaffold(title = "Detail Kegiatan") { inner ->
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding()).imePadding()) { data ->
            val b = data.obj("broadcast")
            val existing = data.objOrNull("existing_response")
            val closed = Fmt.isPast(b.str("deadline"))
            BroadcastHeader(b)
            SxCard(Modifier.fillMaxWidth().appear(2)) {
                SectionTitle("Uraian Kegiatan")
                Spacer(Modifier.height(8.dp))
                Text(b.str("description"), style = MaterialTheme.typography.bodyMedium)
                b.strOrNull("attachment")?.let { FileLink("Lampiran", it, Modifier.padding(top = 12.dp)) }
            }
            if (existing != null) {
                SxCard(Modifier.fillMaxWidth().appear(3), color = Sx.GreenSoft, border = Sx.Green.copy(0.25f)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Outlined.CheckCircle, null, tint = Sx.Green)
                        Spacer(Modifier.width(8.dp))
                        Text("Anda sudah merespons", fontWeight = FontWeight.Bold, color = Sx.GreenInk, modifier = Modifier.weight(1f))
                        StatusBadge(existing.str("status_attendance"))
                    }
                    existing.strOrNull("notes")?.let { Text("Catatan: $it", style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(top = 6.dp)) }
                    Text("Dikirim ${Fmt.dateTime(existing.str("responded_at"))}", style = MaterialTheme.typography.bodySmall)
                }
            } else if (closed) {
                Notice("Batas waktu konfirmasi kehadiran telah berakhir.", Tone.Gray, Icons.Outlined.EventBusy)
            } else {
                SxCard(Modifier.fillMaxWidth().appear(3)) {
                    SectionTitle("Konfirmasi Kehadiran", subtitle = "Batas: ${Fmt.dateTime(b.str("deadline"))}")
                    Spacer(Modifier.height(12.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        AttendChoice("Hadir", Icons.Outlined.CheckCircle, Sx.Green, choice == "HADIR", Modifier.weight(1f)) { choice = "HADIR" }
                        AttendChoice("Izin", Icons.Outlined.Schedule, Sx.Amber, choice == "IZIN", Modifier.weight(1f)) { choice = "IZIN" }
                        AttendChoice("Tidak Hadir", Icons.Outlined.Cancel, Sx.Red, choice == "TIDAK_HADIR", Modifier.weight(1f)) { choice = "TIDAK_HADIR" }
                    }
                    AnimatedVisibility(choice != null && choice != "HADIR") {
                        Column(Modifier.padding(top = 12.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                            PlainTextField(notes, { notes = it }, label = "Keterangan", placeholder = "Alasan izin / tidak hadir", minLines = 3, maxLength = 255)
                            SwitchRow(permit, { permit = it }, "Ajukan surat izin", "Satuan akan menindaklanjuti permohonan izin Anda")
                        }
                    }
                    Spacer(Modifier.height(14.dp))
                    PrimaryButton("Kirim Konfirmasi", {
                        val c = choice ?: return@PrimaryButton
                        scope.launch {
                            sending = true
                            val r = Api.send("/personel/broadcast/$uuid/respon", mapOf("status" to c, "notes" to notes.ifBlank { null }, "permit_letter" to permit))
                            sending = false
                            if (Ui.report(r, "Konfirmasi kehadiran terkirim.")) loader.refresh()
                        }
                    }, Modifier.fillMaxWidth(), loading = sending, enabled = choice != null, icon = Icons.Outlined.Send)
                }
            }
        }
    }
}

@Composable
fun BroadcastHeader(b: JsonObject) {
    Box(Modifier.fillMaxWidth().appear(0).clip(RoundedCornerShape(26.dp)).background(Sx.HeroGradient)) {
        id.my.sisfoperskc.app.ui.auth.AnimatedBlobs(Modifier.matchParentSize())
        Column(Modifier.padding(20.dp)) {
            Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                Box(Modifier.clip(RoundedCornerShape(50)).background(Color.White.copy(0.2f)).padding(horizontal = 10.dp, vertical = 4.dp)) {
                    Text(Fmt.category(b.str("category")), color = Color.White, fontSize = 11.sp, fontWeight = FontWeight.Bold)
                }
                Box(Modifier.clip(RoundedCornerShape(50)).background(Color.White.copy(0.2f)).padding(horizontal = 10.dp, vertical = 4.dp)) {
                    Text(Fmt.matra(b.strOrNull("matra") ?: b.strOrNull("target_value")), color = Color.White, fontSize = 11.sp, fontWeight = FontWeight.Bold)
                }
            }
            Spacer(Modifier.height(10.dp))
            Text(b.str("title"), color = Color.White, fontSize = 20.sp, fontWeight = FontWeight.ExtraBold, lineHeight = 26.sp)
            Spacer(Modifier.height(14.dp))
            HeaderLine(Icons.Outlined.CalendarMonth, Fmt.dayDate(b.str("event_date")))
            HeaderLine(Icons.Outlined.Schedule, "${b.str("event_time")} WIB")
            HeaderLine(Icons.Outlined.Place, b.str("location"))
        }
    }
}

@Composable
private fun HeaderLine(icon: androidx.compose.ui.graphics.vector.ImageVector, text: String) {
    Row(Modifier.padding(vertical = 3.dp), verticalAlignment = Alignment.CenterVertically) {
        Icon(icon, null, tint = Color.White.copy(0.85f), modifier = Modifier.size(16.dp))
        Spacer(Modifier.width(8.dp))
        Text(text, color = Color.White, fontSize = 13.sp, fontWeight = FontWeight.Medium)
    }
}

@Composable
private fun AttendChoice(label: String, icon: androidx.compose.ui.graphics.vector.ImageVector, color: Color, selected: Boolean, modifier: Modifier, onClick: () -> Unit) {
    Column(
        modifier
            .clip(RoundedCornerShape(18.dp))
            .background(if (selected) color.copy(0.12f) else Sx.Bg)
            .border(if (selected) 2.dp else 1.dp, if (selected) color else Sx.Line, RoundedCornerShape(18.dp))
            .pressable(onClick)
            .padding(vertical = 14.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Icon(icon, null, tint = color, modifier = Modifier.size(26.dp))
        Spacer(Modifier.height(6.dp))
        Text(label, fontSize = 12.sp, fontWeight = FontWeight.Bold, color = if (selected) color else Sx.Ink2)
    }
}
