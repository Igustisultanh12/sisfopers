package id.my.sisfoperskc.app.ui.components

import android.Manifest
import android.content.pm.PackageManager
import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.animateColorAsState
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ExperimentalLayoutApi
import androidx.compose.foundation.layout.FlowRow
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AttachFile
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.CameraAlt
import androidx.compose.material.icons.outlined.Check
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.ExpandMore
import androidx.compose.material.icons.outlined.Image
import androidx.compose.material.icons.outlined.Schedule
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.Visibility
import androidx.compose.material.icons.outlined.VisibilityOff
import androidx.compose.material3.DatePicker
import androidx.compose.material3.DatePickerDialog
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Switch
import androidx.compose.material3.SwitchDefaults
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TimePicker
import androidx.compose.material3.rememberDatePickerState
import androidx.compose.material3.rememberTimePickerState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateMapOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.core.content.ContextCompat
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.Files
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.UploadFile
import id.my.sisfoperskc.app.ui.theme.Sx

/** Status formulir: nilai teks, berkas, galat validasi dari server. */
class FormState(initial: Map<String, String> = emptyMap()) {
    val values = mutableStateMapOf<String, String>().apply { putAll(initial) }
    val files = mutableStateMapOf<String, UploadFile>()
    var errors by mutableStateOf<Map<String, String>>(emptyMap())
    var submitting by mutableStateOf(false)

    operator fun get(key: String): String = values[key] ?: ""
    operator fun set(key: String, value: String) {
        values[key] = value
        if (errors.containsKey(key)) errors = errors - key
    }

    fun setAll(map: Map<String, String?>) {
        map.forEach { (k, v) -> values[k] = v ?: "" }
    }

    fun file(key: String): UploadFile? = files[key]
    fun setFile(key: String, f: UploadFile?) {
        if (f == null) files.remove(key) else files[key] = f
        if (errors.containsKey(key)) errors = errors - key
    }

    fun error(key: String): String? = errors[key]

    fun body(keys: Collection<String>? = null): Map<String, Any?> {
        val src = if (keys == null) values.toMap() else values.filterKeys { it in keys }
        return src.mapValues { (_, v) -> v.ifBlank { null } }
    }

    fun fileParts(): List<Pair<String, UploadFile>> = files.map { it.key to it.value }

    /** Validasi sisi klien sederhana untuk field wajib. */
    fun require(vararg pairs: Pair<String, String>): Boolean {
        val missing = pairs.filter { (k, _) -> this[k].isBlank() && files[k] == null }
        if (missing.isNotEmpty()) {
            errors = errors + missing.associate { (k, label) -> k to "$label wajib diisi." }
            Ui.error("Lengkapi isian yang wajib: ${missing.joinToString { it.second }}")
            return false
        }
        return true
    }

    suspend fun submit(
        path: String,
        method: String = "POST",
        body: Map<String, Any?> = body(),
        withFiles: Boolean = true,
    ): ApiResult {
        submitting = true
        val r = Api.send(path, body, method, if (withFiles) fileParts() else emptyList())
        submitting = false
        if (r is ApiResult.Err) errors = r.errors
        return r
    }
}

@Composable
fun rememberForm(vararg initial: Pair<String, String?>): FormState =
    remember { FormState(initial.associate { (k, v) -> k to (v ?: "") }) }

// ------------------------------------------------------------------ Field dasar

@Composable
fun FieldLabel(text: String, required: Boolean = false) {
    Row(Modifier.padding(bottom = 6.dp, start = 2.dp)) {
        Text(text, style = MaterialTheme.typography.labelMedium.copy(color = Sx.Ink2))
        if (required) Text(" *", color = Sx.Red, style = MaterialTheme.typography.labelMedium)
    }
}

@Composable
fun FieldError(text: String?) {
    AnimatedSection(text != null) {
        Text(text ?: "", color = Sx.Red, fontSize = 12.sp, fontWeight = FontWeight.Medium, modifier = Modifier.padding(top = 4.dp, start = 4.dp))
    }
}

@Composable
private fun fieldColors() = OutlinedTextFieldDefaults.colors(
    focusedBorderColor = Sx.Blue,
    unfocusedBorderColor = Sx.Line,
    errorBorderColor = Sx.Red,
    focusedContainerColor = Color.White,
    unfocusedContainerColor = Sx.Bg,
    disabledContainerColor = Sx.LineSoft,
    disabledBorderColor = Sx.Line,
    cursorColor = Sx.Blue,
    focusedLeadingIconColor = Sx.Blue,
    unfocusedLeadingIconColor = Sx.Faint,
)

@Composable
fun PlainTextField(
    value: String,
    onChange: (String) -> Unit,
    modifier: Modifier = Modifier,
    label: String? = null,
    placeholder: String? = null,
    required: Boolean = false,
    error: String? = null,
    icon: ImageVector? = null,
    keyboardType: KeyboardType = KeyboardType.Text,
    password: Boolean = false,
    minLines: Int = 1,
    maxLength: Int? = null,
    enabled: Boolean = true,
    helper: String? = null,
    imeAction: ImeAction = if (minLines > 1) ImeAction.Default else ImeAction.Next,
    onDone: (() -> Unit)? = null,
    trailing: (@Composable () -> Unit)? = null,
) {
    var visible by remember { mutableStateOf(false) }
    Column(modifier.fillMaxWidth()) {
        if (label != null) FieldLabel(label, required)
        OutlinedTextField(
            value = value,
            onValueChange = { v ->
                var nv = v
                if (keyboardType == KeyboardType.Number || keyboardType == KeyboardType.NumberPassword) nv = nv.filter { it.isDigit() }
                if (maxLength != null) nv = nv.take(maxLength)
                onChange(nv)
            },
            modifier = Modifier.fillMaxWidth(),
            placeholder = placeholder?.let { { Text(it, color = Sx.Faint, fontSize = 14.sp) } },
            leadingIcon = icon?.let { { Icon(it, null, modifier = Modifier.size(20.dp)) } },
            trailingIcon = when {
                password -> {
                    {
                        Icon(
                            if (visible) Icons.Outlined.VisibilityOff else Icons.Outlined.Visibility, null,
                            tint = Sx.Faint,
                            modifier = Modifier.clip(RoundedCornerShape(50)).clickable { visible = !visible }.padding(6.dp),
                        )
                    }
                }
                trailing != null -> trailing
                else -> null
            },
            isError = error != null,
            singleLine = minLines == 1,
            minLines = minLines,
            maxLines = if (minLines == 1) 1 else 12,
            enabled = enabled,
            visualTransformation = if (password && !visible) PasswordVisualTransformation() else VisualTransformation.None,
            keyboardOptions = KeyboardOptions(keyboardType = keyboardType, imeAction = imeAction),
            keyboardActions = KeyboardActions(onDone = { onDone?.invoke() }, onGo = { onDone?.invoke() }),
            shape = RoundedCornerShape(16.dp),
            colors = fieldColors(),
            textStyle = MaterialTheme.typography.bodyLarge.copy(fontSize = 15.sp, color = Sx.Ink),
        )
        FieldError(error)
        if (helper != null && error == null) {
            Text(helper, style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(top = 4.dp, start = 4.dp))
        }
    }
}

@Composable
fun TextFieldF(
    form: FormState,
    key: String,
    label: String,
    modifier: Modifier = Modifier,
    placeholder: String? = null,
    required: Boolean = false,
    icon: ImageVector? = null,
    keyboardType: KeyboardType = KeyboardType.Text,
    password: Boolean = false,
    minLines: Int = 1,
    maxLength: Int? = null,
    enabled: Boolean = true,
    helper: String? = null,
) {
    PlainTextField(
        value = form[key], onChange = { form[key] = it }, modifier = modifier, label = label,
        placeholder = placeholder, required = required, error = form.error(key), icon = icon,
        keyboardType = keyboardType, password = password, minLines = minLines, maxLength = maxLength,
        enabled = enabled, helper = helper,
    )
}

// ------------------------------------------------------------------ Pilihan

data class Opt(val value: String, val label: String, val sub: String? = null)

fun opts(vararg pairs: Pair<String, String>) = pairs.map { Opt(it.first, it.second) }
fun optsOf(values: List<String>) = values.map { Opt(it, it) }

@Composable
fun SelectField(
    value: String,
    onChange: (String) -> Unit,
    options: List<Opt>,
    label: String? = null,
    modifier: Modifier = Modifier,
    placeholder: String = "Pilih...",
    required: Boolean = false,
    error: String? = null,
    icon: ImageVector? = null,
    enabled: Boolean = true,
    searchable: Boolean = options.size > 8,
) {
    var open by remember { mutableStateOf(false) }
    val selected = options.firstOrNull { it.value == value }
    Column(modifier.fillMaxWidth()) {
        if (label != null) FieldLabel(label, required)
        val border by animateColorAsState(if (error != null) Sx.Red else if (open) Sx.Blue else Sx.Line, label = "b")
        Row(
            Modifier
                .fillMaxWidth()
                .heightIn(min = 56.dp)
                .clip(RoundedCornerShape(16.dp))
                .background(if (enabled) Sx.Bg else Sx.LineSoft)
                .border(1.dp, border, RoundedCornerShape(16.dp))
                .clickable(enabled = enabled) { open = true }
                .padding(horizontal = 16.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            if (icon != null) {
                Icon(icon, null, tint = Sx.Faint, modifier = Modifier.size(20.dp))
                Spacer(Modifier.width(12.dp))
            }
            Text(
                selected?.label ?: value.ifBlank { placeholder },
                color = if (selected != null || value.isNotBlank()) Sx.Ink else Sx.Faint,
                fontSize = 15.sp,
                modifier = Modifier.weight(1f),
                maxLines = 2,
                overflow = TextOverflow.Ellipsis,
            )
            Icon(Icons.Outlined.ExpandMore, null, tint = Sx.Muted)
        }
        FieldError(error)
    }
    if (open) {
        var query by remember { mutableStateOf("") }
        SxSheet(onDismiss = { open = false }) {
            Text(label ?: "Pilih", style = MaterialTheme.typography.titleLarge, modifier = Modifier.padding(bottom = 12.dp))
            if (searchable) {
                PlainTextField(query, { query = it }, placeholder = "Cari...", icon = Icons.Outlined.Search, imeAction = ImeAction.Search)
                Spacer(Modifier.height(10.dp))
            }
            val filtered = options.filter { query.isBlank() || it.label.contains(query, true) || (it.sub?.contains(query, true) == true) }
            Column(Modifier.heightIn(max = 520.dp)) {
                LazyColumn {
                    items(filtered, key = { it.value + it.label }) { o ->
                        val isSel = o.value == value
                        Row(
                            Modifier
                                .fillMaxWidth()
                                .clip(RoundedCornerShape(14.dp))
                                .background(if (isSel) Sx.BlueSoft else Color.Transparent)
                                .clickable { onChange(o.value); open = false }
                                .padding(horizontal = 14.dp, vertical = 13.dp),
                            verticalAlignment = Alignment.CenterVertically,
                        ) {
                            Column(Modifier.weight(1f)) {
                                Text(o.label, fontSize = 15.sp, fontWeight = if (isSel) FontWeight.Bold else FontWeight.Medium, color = if (isSel) Sx.BlueDark else Sx.Ink)
                                if (o.sub != null) Text(o.sub, style = MaterialTheme.typography.bodySmall)
                            }
                            if (isSel) Icon(Icons.Outlined.Check, null, tint = Sx.Blue)
                        }
                    }
                }
                if (filtered.isEmpty()) Text("Tidak ada pilihan yang cocok.", style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(16.dp))
            }
        }
    }
}

@Composable
fun SelectF(
    form: FormState,
    key: String,
    label: String,
    options: List<Opt>,
    modifier: Modifier = Modifier,
    required: Boolean = false,
    placeholder: String = "Pilih...",
    icon: ImageVector? = null,
    enabled: Boolean = true,
    onChanged: ((String) -> Unit)? = null,
) {
    SelectField(
        value = form[key], onChange = { form[key] = it; onChanged?.invoke(it) }, options = options, label = label,
        modifier = modifier, placeholder = placeholder, required = required, error = form.error(key), icon = icon, enabled = enabled,
    )
}

@OptIn(ExperimentalLayoutApi::class)
@Composable
fun ChoiceChips(
    value: String,
    onChange: (String) -> Unit,
    options: List<Opt>,
    label: String? = null,
    required: Boolean = false,
    error: String? = null,
) {
    Column(Modifier.fillMaxWidth()) {
        if (label != null) FieldLabel(label, required)
        FlowRow(horizontalArrangement = Arrangement.spacedBy(8.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            options.forEach { o -> Pill(o.label, o.value == value, { onChange(o.value) }) }
        }
        FieldError(error)
    }
}

@Composable
fun ChipsF(form: FormState, key: String, label: String, options: List<Opt>, required: Boolean = false, onChanged: ((String) -> Unit)? = null) {
    ChoiceChips(form[key], { form[key] = it; onChanged?.invoke(it) }, options, label, required, form.error(key))
}

@Composable
fun SwitchRow(
    checked: Boolean,
    onChange: (Boolean) -> Unit,
    title: String,
    subtitle: String? = null,
    icon: ImageVector? = null,
) {
    Row(
        Modifier.fillMaxWidth().clip(RoundedCornerShape(16.dp)).background(Sx.Bg).border(1.dp, Sx.Line, RoundedCornerShape(16.dp))
            .clickable { onChange(!checked) }.padding(horizontal = 14.dp, vertical = 12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        if (icon != null) {
            IconBox(icon, size = 38.dp, iconSize = 19.dp, radius = 12.dp)
            Spacer(Modifier.width(12.dp))
        }
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.titleSmall.copy(fontSize = 14.sp))
            if (subtitle != null) Text(subtitle, style = MaterialTheme.typography.bodySmall)
        }
        Switch(
            checked = checked, onCheckedChange = onChange,
            colors = SwitchDefaults.colors(checkedTrackColor = Sx.Blue, checkedThumbColor = Color.White, uncheckedTrackColor = Sx.Line, uncheckedBorderColor = Sx.Line, uncheckedThumbColor = Color.White),
        )
    }
}

@Composable
fun SwitchF(form: FormState, key: String, title: String, subtitle: String? = null, icon: ImageVector? = null) {
    SwitchRow(form[key] == "1", { form[key] = if (it) "1" else "0" }, title, subtitle, icon)
}

// ------------------------------------------------------------------ Tanggal & waktu

@Composable
fun DateField(
    value: String,
    onChange: (String) -> Unit,
    label: String? = null,
    modifier: Modifier = Modifier,
    required: Boolean = false,
    error: String? = null,
    withTime: Boolean = false,
    placeholder: String = if (withTime) "Pilih tanggal & jam" else "Pilih tanggal",
) {
    var showDate by remember { mutableStateOf(false) }
    var showTime by remember { mutableStateOf(false) }
    var pendingDate by remember { mutableStateOf("") }
    val display = when {
        value.isBlank() -> placeholder
        withTime -> Fmt.dateTime(value)
        else -> Fmt.dateLong(value)
    }
    Column(modifier.fillMaxWidth()) {
        if (label != null) FieldLabel(label, required)
        Row(
            Modifier
                .fillMaxWidth()
                .heightIn(min = 56.dp)
                .clip(RoundedCornerShape(16.dp))
                .background(Sx.Bg)
                .border(1.dp, if (error != null) Sx.Red else Sx.Line, RoundedCornerShape(16.dp))
                .clickable { showDate = true }
                .padding(horizontal = 16.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Icon(Icons.Outlined.CalendarMonth, null, tint = Sx.Faint, modifier = Modifier.size(20.dp))
            Spacer(Modifier.width(12.dp))
            Text(display, color = if (value.isBlank()) Sx.Faint else Sx.Ink, fontSize = 15.sp, modifier = Modifier.weight(1f))
            if (value.isNotBlank() && !required) {
                Icon(Icons.Outlined.Close, null, tint = Sx.Faint, modifier = Modifier.size(18.dp).clickable { onChange("") })
            }
        }
        FieldError(error)
    }
    if (showDate) {
        val state = rememberDatePickerState(initialSelectedDateMillis = Fmt.toMillisDate(value.take(10)))
        DatePickerDialog(
            onDismissRequest = { showDate = false },
            confirmButton = {
                TextButton(onClick = {
                    val millis = state.selectedDateMillis
                    showDate = false
                    if (millis != null) {
                        val d = Fmt.fromMillisDate(millis)
                        if (withTime) {
                            pendingDate = d; showTime = true
                        } else onChange(d)
                    }
                }) { Text("Pilih", fontWeight = FontWeight.Bold) }
            },
            dismissButton = { TextButton(onClick = { showDate = false }) { Text("Batal") } },
        ) { DatePicker(state = state) }
    }
    if (showTime) {
        val init = value.substringAfter('T', "").take(5)
        val ts = rememberTimePickerState(
            initialHour = init.take(2).toIntOrNull() ?: 8,
            initialMinute = init.drop(3).take(2).toIntOrNull() ?: 0,
            is24Hour = true,
        )
        SxDialog(
            onDismiss = { showTime = false },
            title = "Pilih Jam",
            icon = Icons.Outlined.Schedule,
            confirmText = "Simpan",
            onConfirm = {
                showTime = false
                onChange(String.format("%sT%02d:%02d", pendingDate, ts.hour, ts.minute))
            },
        ) { Box(Modifier.fillMaxWidth(), contentAlignment = Alignment.Center) { TimePicker(state = ts) } }
    }
}

@Composable
fun DateF(form: FormState, key: String, label: String, required: Boolean = false, withTime: Boolean = false, modifier: Modifier = Modifier) {
    DateField(form[key], { form[key] = it }, label, modifier, required, form.error(key), withTime)
}

@Composable
fun TimeField(value: String, onChange: (String) -> Unit, label: String, required: Boolean = false, error: String? = null) {
    var show by remember { mutableStateOf(false) }
    Column(Modifier.fillMaxWidth()) {
        FieldLabel(label, required)
        Row(
            Modifier.fillMaxWidth().heightIn(min = 56.dp).clip(RoundedCornerShape(16.dp)).background(Sx.Bg)
                .border(1.dp, if (error != null) Sx.Red else Sx.Line, RoundedCornerShape(16.dp))
                .clickable { show = true }.padding(horizontal = 16.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Icon(Icons.Outlined.Schedule, null, tint = Sx.Faint, modifier = Modifier.size(20.dp))
            Spacer(Modifier.width(12.dp))
            Text(value.ifBlank { "Pilih jam" }, color = if (value.isBlank()) Sx.Faint else Sx.Ink, fontSize = 15.sp)
        }
        FieldError(error)
    }
    if (show) {
        val ts = rememberTimePickerState(value.take(2).toIntOrNull() ?: 8, value.drop(3).take(2).toIntOrNull() ?: 0, true)
        SxDialog(onDismiss = { show = false }, title = label, icon = Icons.Outlined.Schedule, confirmText = "Simpan", onConfirm = {
            show = false
            onChange(String.format("%02d:%02d", ts.hour, ts.minute))
        }) { Box(Modifier.fillMaxWidth(), contentAlignment = Alignment.Center) { TimePicker(state = ts) } }
    }
}

// ------------------------------------------------------------------ Berkas

enum class Accept(val mimes: Array<String>, val label: String) {
    Image(arrayOf("image/*"), "Gambar (JPG/PNG)"),
    Pdf(arrayOf("application/pdf"), "Dokumen PDF"),
    ImageOrPdf(arrayOf("image/*", "application/pdf"), "Gambar atau PDF"),
    Excel(arrayOf("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.ms-excel"), "Berkas Excel"),
    Json(arrayOf("application/json", "text/plain", "application/octet-stream"), "Berkas JSON"),
    Any(arrayOf("*/*"), "Semua berkas"),
}

@Composable
fun FilePickerField(
    file: UploadFile?,
    onPick: (UploadFile?) -> Unit,
    label: String,
    accept: Accept = Accept.ImageOrPdf,
    required: Boolean = false,
    error: String? = null,
    helper: String? = null,
    allowCamera: Boolean = accept == Accept.Image || accept == Accept.ImageOrPdf,
    existingPath: String? = null,
) {
    val ctx = LocalContext.current
    var cameraUri by remember { mutableStateOf<Uri?>(null) }
    val docLauncher = rememberLauncherForActivityResult(ActivityResultContracts.OpenDocument()) { uri ->
        if (uri != null) onPick(Files.describe(ctx, uri))
    }
    val cameraLauncher = rememberLauncherForActivityResult(ActivityResultContracts.TakePicture()) { ok ->
        val u = cameraUri
        if (ok && u != null) onPick(Files.describe(ctx, u))
    }
    val permLauncher = rememberLauncherForActivityResult(ActivityResultContracts.RequestPermission()) { granted ->
        if (granted) {
            val u = Files.newCameraUri(ctx); cameraUri = u; cameraLauncher.launch(u)
        } else Ui.error("Izin kamera diperlukan untuk mengambil foto.")
    }
    Column(Modifier.fillMaxWidth()) {
        FieldLabel(label, required)
        Row(
            Modifier
                .fillMaxWidth()
                .clip(RoundedCornerShape(16.dp))
                .background(if (file != null) Sx.BlueSoft else Sx.Bg)
                .border(1.dp, if (error != null) Sx.Red else if (file != null) Sx.BlueLine else Sx.Line, RoundedCornerShape(16.dp))
                .clickable { docLauncher.launch(accept.mimes) }
                .padding(12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            val isImage = file?.mime?.startsWith("image") == true
            Box(Modifier.size(48.dp).clip(RoundedCornerShape(12.dp)).background(Color.White), contentAlignment = Alignment.Center) {
                if (file != null && isImage) {
                    coil.compose.AsyncImage(model = file.uri, contentDescription = null, modifier = Modifier.size(48.dp), contentScale = androidx.compose.ui.layout.ContentScale.Crop)
                } else if (file == null && existingPath != null) {
                    RemoteImage(existingPath, Modifier.size(48.dp))
                } else {
                    Icon(
                        when {
                            file == null -> Icons.Outlined.AttachFile
                            file.mime.contains("pdf") -> Icons.Outlined.Description
                            else -> Icons.Outlined.Image
                        }, null, tint = Sx.Blue,
                    )
                }
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(
                    file?.name ?: if (existingPath != null) "Berkas tersimpan — ketuk untuk mengganti" else "Ketuk untuk memilih berkas",
                    fontWeight = FontWeight.SemiBold, fontSize = 14.sp, color = if (file != null) Sx.BlueDark else Sx.Ink2,
                    maxLines = 1, overflow = TextOverflow.Ellipsis,
                )
                Text(
                    if (file != null) Files.humanSize(file.size) else (helper ?: accept.label),
                    style = MaterialTheme.typography.bodySmall,
                )
            }
            if (file != null) {
                Icon(Icons.Outlined.Close, null, tint = Sx.Muted, modifier = Modifier.clip(RoundedCornerShape(50)).clickable { onPick(null) }.padding(6.dp))
            } else if (allowCamera) {
                Box(
                    Modifier.size(40.dp).clip(RoundedCornerShape(12.dp)).background(Color.White).border(1.dp, Sx.Line, RoundedCornerShape(12.dp)).clickable {
                        if (ContextCompat.checkSelfPermission(ctx, Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED) {
                            val u = Files.newCameraUri(ctx); cameraUri = u; cameraLauncher.launch(u)
                        } else permLauncher.launch(Manifest.permission.CAMERA)
                    },
                    contentAlignment = Alignment.Center,
                ) { Icon(Icons.Outlined.CameraAlt, "Kamera", tint = Sx.Blue, modifier = Modifier.size(20.dp)) }
            }
        }
        FieldError(error)
    }
}

@Composable
fun FileF(
    form: FormState,
    key: String,
    label: String,
    accept: Accept = Accept.ImageOrPdf,
    required: Boolean = false,
    helper: String? = null,
    existingPath: String? = null,
) {
    FilePickerField(form.file(key), { form.setFile(key, it) }, label, accept, required, form.error(key), helper, existingPath = existingPath)
}

@Composable
fun FormSection(title: String, subtitle: String? = null, icon: ImageVector? = null, content: @Composable () -> Unit) {
    SxCard(Modifier.fillMaxWidth()) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            if (icon != null) {
                IconBox(icon, size = 38.dp, iconSize = 19.dp, radius = 12.dp)
                Spacer(Modifier.width(12.dp))
            }
            Column {
                Text(title, style = MaterialTheme.typography.titleMedium)
                if (subtitle != null) Text(subtitle, style = MaterialTheme.typography.bodySmall)
            }
        }
        Spacer(Modifier.height(16.dp))
        Column(verticalArrangement = Arrangement.spacedBy(14.dp)) { content() }
    }
}
