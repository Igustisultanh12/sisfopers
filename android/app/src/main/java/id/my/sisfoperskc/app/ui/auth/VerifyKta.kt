package id.my.sisfoperskc.app.ui.auth

import android.net.Uri
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.ErrorOutline
import androidx.compose.material.icons.outlined.QrCodeScanner
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.Shield
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.google.mlkit.vision.barcode.common.Barcode
import com.google.mlkit.vision.codescanner.GmsBarcodeScannerOptions
import com.google.mlkit.vision.codescanner.GmsBarcodeScanning
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiResult
import id.my.sisfoperskc.app.data.bool
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.PlainTextField
import id.my.sisfoperskc.app.ui.components.PrimaryButton
import id.my.sisfoperskc.app.ui.components.SecondaryButton
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

/*
 * Verifikasi keaslian E-KTA dengan memindai QR pada kartu.
 * QR kartu berisi URL {server}/verifikasi/ekta/{kode}. Aplikasi mengambil kodenya lalu
 * memeriksa ke rute publik yang sama (lewat jembatan /api/m). Server sudah menyensor
 * nama & NIKC, sehingga aplikasi hanya menampilkan data tersensor.
 */

/** Ambil kode verifikasi dari isi QR E-KTA: URL .../verifikasi/ekta/{kode} atau kode "KTA-..." langsung. */
internal fun extractKtaCode(raw: String?): String? {
    val t = raw?.trim().orEmpty()
    if (t.isEmpty() || t.length > 300) return null
    Regex("/verifikasi/ekta/([A-Za-z0-9-]{4,64})(?:[/?#].*)?$").find(t)?.let { return it.groupValues[1] }
    return t.takeIf { Regex("^KTA-[A-Za-z0-9-]{4,60}$", RegexOption.IGNORE_CASE).matches(it) }
}

private data class VerifyAlert(val title: String, val message: String)

@Composable
fun VerifyKtaScreen() {
    val nav = LocalNav.current
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    var loading by remember { mutableStateOf(false) }
    var manual by rememberSaveable { mutableStateOf("") }
    var valid by remember { mutableStateOf<JsonObject?>(null) }
    var alert by remember { mutableStateOf<VerifyAlert?>(null) }

    fun check(raw: String?) {
        val code = extractKtaCode(raw)
        if (code == null) {
            valid = null
            alert = VerifyAlert("QR Tidak Valid", "QR yang dipindai bukan QR verifikasi E-KTA SISFOPERS KC.")
            return
        }
        loading = true
        valid = null
        scope.launch {
            when (val r = Api.get("/verifikasi/ekta/" + Uri.encode(code))) {
                is ApiResult.Ok -> {
                    if (r.data.bool("is_valid")) {
                        valid = r.data.obj("doc")
                    } else {
                        alert = VerifyAlert(
                            "E-KTA Tidak Valid",
                            r.data.str("message", "Data E-KTA tidak ditemukan atau belum disahkan oleh Pejabat Berwenang."),
                        )
                    }
                }
                is ApiResult.Err -> {
                    alert = if (r.status == 404) {
                        VerifyAlert("E-KTA Tidak Valid", "Data E-KTA dengan kode tersebut tidak ditemukan.")
                    } else {
                        VerifyAlert("Gagal Memverifikasi", r.message)
                    }
                }
            }
            loading = false
        }
    }

    fun scan() {
        val options = GmsBarcodeScannerOptions.Builder()
            .setBarcodeFormats(Barcode.FORMAT_QR_CODE)
            .enableAutoZoom()
            .build()
        GmsBarcodeScanning.getClient(ctx, options).startScan()
            .addOnSuccessListener { barcode -> check(barcode.rawValue) }
            .addOnFailureListener { e ->
                alert = VerifyAlert(
                    "Pemindai Tidak Tersedia",
                    "Pemindai QR tidak dapat dibuka (${e.message ?: "layanan Google Play"}). Masukkan kode verifikasi secara manual.",
                )
            }
    }

    Column(Modifier.fillMaxSize().background(Sx.Bg)) {
        AuthHero(
            "Verifikasi E-KTA",
            "Pindai QR pada kartu E-KTA untuk memeriksa keasliannya.",
            onBack = { nav.popBackStack() },
        )
        Column(
            Modifier.fillMaxSize().verticalScroll(rememberScrollState()).imePadding().padding(20.dp).navigationBarsPadding(),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            SxCard(Modifier.fillMaxWidth().appear(0)) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.QrCodeScanner, Sx.Green)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text("Pindai QR Kartu", style = MaterialTheme.typography.titleMedium)
                        Text("Arahkan kamera ke QR di sisi depan kartu E-KTA.", style = MaterialTheme.typography.bodySmall)
                    }
                }
                Spacer(Modifier.height(14.dp))
                PrimaryButton("Pindai QR E-KTA", { scan() }, Modifier.fillMaxWidth(), loading = loading, icon = Icons.Outlined.QrCodeScanner)
            }

            SxCard(Modifier.fillMaxWidth().appear(1)) {
                Text("Atau masukkan kode verifikasi", style = MaterialTheme.typography.titleSmall)
                Spacer(Modifier.height(10.dp))
                PlainTextField(
                    manual, { manual = it.trim() },
                    Modifier.fillMaxWidth(),
                    label = "Kode Verifikasi",
                    placeholder = "KTA-2026-XXXXXXXX",
                    maxLength = 64,
                    imeAction = ImeAction.Done,
                    onDone = { if (manual.isNotBlank() && !loading) check(manual) },
                )
                Spacer(Modifier.height(10.dp))
                SecondaryButton(
                    "Periksa Kode", { check(manual) }, Modifier.fillMaxWidth(),
                    icon = Icons.Outlined.Search, enabled = manual.isNotBlank() && !loading,
                )
            }

            valid?.let { ValidResult(it) }
        }
    }

    alert?.let { a ->
        SxDialog(
            onDismiss = { alert = null },
            title = a.title,
            message = a.message,
            icon = Icons.Outlined.ErrorOutline,
            tone = Tone.Red,
            dismissText = "Tutup",
            confirmText = "Pindai Ulang",
            confirmColor = Sx.Red,
            onConfirm = { alert = null; scan() },
        )
    }
}

@Composable
private fun ValidResult(doc: JsonObject) {
    SxCard(Modifier.fillMaxWidth().appear(0, key = doc), color = Sx.GreenSoft, border = Sx.Green.copy(alpha = 0.35f)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(Icons.Outlined.VerifiedUser, tint = Sx.GreenInk, bg = Color.White)
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text("E-KTA VALID", color = Sx.GreenInk, fontWeight = FontWeight.Black, fontSize = 16.sp)
                Text(doc.str("status", "ASLI & SAH TERDAFTAR RESMI"), style = MaterialTheme.typography.bodySmall, color = Sx.GreenInk)
            }
        }
    }

    SxCard(Modifier.fillMaxWidth().appear(1, key = doc)) {
        Text("Data Pemegang Kartu", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(12.dp))
        Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
            InfoRow("Nama Pemegang", doc.str("nama_personel", "-"))
            InfoRow("NIKC", doc.str("nikc", "-"))
            InfoRow("Nomor KTA", doc.str("nomor_kta", "-"))
            InfoRow("Pangkat", doc.str("pangkat", "-"))
            InfoRow("Jabatan", doc.str("jabatan", "-"))
            InfoRow("Kesatuan / Matra", doc.str("kesatuan_matra", "-"))
            InfoRow("Berlaku s/d", doc.str("berlaku_sampai", "-"))
            InfoRow("Tanggal Terbit", doc.str("tanggal_terbit", "-"))
            InfoRow(
                "Disahkan Oleh",
                listOf(doc.str("signer_name"), doc.str("signer_rank"), doc.str("signer_title"))
                    .filter { it.isNotBlank() }.joinToString("\n").ifBlank { "-" },
            )
            InfoRow("Kode Verifikasi", doc.str("verify_code", "-"))
        }
        Spacer(Modifier.height(14.dp))
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(Icons.Outlined.Shield, null, tint = Sx.Muted, modifier = Modifier.size(16.dp))
            Spacer(Modifier.width(8.dp))
            Text(
                "Nama dan NIKC disensor untuk melindungi privasi personel.",
                style = MaterialTheme.typography.bodySmall, color = Sx.Muted,
            )
        }
    }
}

@Composable
private fun InfoRow(label: String, value: String) {
    Column {
        Text(label.uppercase(), fontSize = 10.sp, fontWeight = FontWeight.Bold, color = Sx.Muted, letterSpacing = 0.6.sp)
        Text(value, fontSize = 14.sp, fontWeight = FontWeight.SemiBold, color = Sx.Ink)
    }
}
