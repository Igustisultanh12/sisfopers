package id.my.sisfoperskc.app.ui.components

import android.content.Intent
import android.net.Uri
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Print
import androidx.compose.material.icons.outlined.Sync
import androidx.compose.material3.Icon
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
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.drawBehind
import androidx.compose.ui.draw.drawWithContent
import androidx.compose.ui.draw.rotate
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.geometry.CornerRadius
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.geometry.Size
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.ImageBitmap
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.PathEffect
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.asAndroidBitmap
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.drawscope.DrawScope
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.drawscope.rotate
import androidx.compose.ui.graphics.drawscope.scale
import androidx.compose.ui.graphics.drawscope.translate
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.layer.drawLayer
import androidx.compose.ui.graphics.rememberGraphicsLayer
import androidx.compose.ui.graphics.vector.PathParser
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.drawText
import androidx.compose.ui.text.font.Font
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.rememberTextMeasurer
import androidx.compose.ui.text.style.TextDecoration
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.TextUnit
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.em
import androidx.compose.ui.unit.sp
import androidx.print.PrintHelper
import com.google.zxing.BarcodeFormat
import com.google.zxing.EncodeHintType
import com.google.zxing.qrcode.QRCodeWriter
import id.my.sisfoperskc.app.R
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.objOrNull
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject
import java.text.SimpleDateFormat
import java.util.Locale

/*
 * Kartu Tanda Anggota Komcad — tiruan persis komponen web resources/js/Components/EktaCard.vue
 * (rasio CR80 85,6 x 54, latar hijau #94BA74, watermark "KOMCAD", lambang Komcad, sisi depan & belakang, QR verifikasi).
 * Semua ukuran memakai satuan "px web" dengan lebar acuan 500 px lalu diskalakan ke lebar kartu di layar.
 */

private const val REF_W = 500f
private val INK = Color(0xFF142C0A)
private val DOT = Color(0xFF244616).copy(alpha = 0.6f)
private val CARD_BG = Color(0xFF94BA74)

private val KtaSans = FontFamily(Font(R.font.kta_sans_regular, FontWeight.Normal), Font(R.font.kta_sans_bold, FontWeight.Bold))
private val KtaSerif = FontFamily(Font(R.font.kta_serif_regular, FontWeight.Normal), Font(R.font.kta_serif_bold, FontWeight.Bold))
private val KtaMono = FontFamily(Font(R.font.kta_mono_regular, FontWeight.Normal), Font(R.font.kta_mono_bold, FontWeight.Bold))

/** Data kartu, dihitung dengan aturan yang sama seperti computed() di EktaCard.vue. */
class KtaData(personel: JsonObject, ekta: JsonObject?, settings: JsonObject?) {
    private val p = personel
    private val e = ekta
    private val s = settings
    private val sin = personel.objOrNull("sinyalmen")

    val name = p.strOrNull("full_name") ?: "-"
    val nikc = p.strOrNull("nikc") ?: p.objOrNull("user").strOrNull("username") ?: "-"
    val photo = p.strOrNull("photo_profile")
    val matraCode = (p.strOrNull("matra") ?: "AD").uppercase()
    val nomor = e.strOrNull("nomor_kta") ?: "No. ...../KTA KC/${p.strOrNull("angkatan") ?: java.util.Calendar.getInstance().get(java.util.Calendar.YEAR)}"
    val pangkat = e.strOrNull("pangkat") ?: p.strOrNull("pangkat") ?: "Prajurit Komcad"
    val jabatan = e.strOrNull("jabatan") ?: pangkat.uppercase().let { if ("LET" in it || "KAP" in it || "MAY" in it || "KOL" in it) "Perwira Komcad" else "Anggota Komcad" }
    val matra = e.strOrNull("kesatuan_matra") ?: when (matraCode) { "AL" -> "Matra Laut"; "AU" -> "Matra Udara"; else -> "Matra Darat" }
    val photoBg = when (matraCode) { "AL" -> Color(0xFF1E3A8A); "AU" -> Color(0xFF0284C7); else -> Color(0xFF991B1B) }
    val berlaku = e.strOrNull("berlaku_sampai") ?: "Selama Menjadi Anggota Komcad"

    val signerName = s.strOrNull("signer_name") ?: "Sri Yanto, S.T."
    val signerRank = s.strOrNull("signer_rank") ?: "Laksamana Muda TNI"
    private val signerTitle = s.strOrNull("signer_title") ?: "Direktur Jenderal Potensi Pertahanan"
    val signerLine1 = if ("potensi" in signerTitle.lowercase()) "Direktur Jenderal" else signerTitle
    val signerLine2 = if ("potensi" in signerTitle.lowercase()) "Potensi Pertahanan" else ""
    val logoKomcad = s.strOrNull("logo_komcad")
    val signature = s.strOrNull("signature")
    val stamp = s.strOrNull("stamp")
    val fontFamily: FontFamily = when (s.strOrNull("font_family")) { "Times New Roman" -> KtaSerif; "Courier New" -> KtaMono; else -> KtaSans }
    val fontPx: Float = s.strOrNull("font_size")?.removeSuffix("px")?.trim()?.toFloatOrNull() ?: 11f

    val verifyCode = e.strOrNull("verify_code") ?: "KTA-${p.strOrNull("angkatan") ?: "2025"}-${p.strOrNull("nikc")?.takeLast(6) ?: "VERIF"}"
    val verificationUrl = Session.baseUrl.trimEnd('/') + "/verifikasi/ekta/" + verifyCode

    val tinggiBerat = e.strOrNull("tinggi_berat")
        ?: if (sin != null && sin.int("tinggi_badan") > 0 && sin.int("berat_badan") > 0) "${sin.str("tinggi_badan")}/${sin.str("berat_badan")}" else "165/60"
    val rambut = e.strOrNull("rambut") ?: sin.strOrNull("rambut") ?: "Bergelombang"
    val mata = e.strOrNull("mata") ?: sin.strOrNull("mata") ?: "Coklat"
    val darah = e.strOrNull("golongan_darah") ?: sin.strOrNull("golongan_darah") ?: "O"
    val tempatLahir = e.strOrNull("tempat_lahir") ?: p.strOrNull("pob") ?: "-"
    val tanggalLahir = e.strOrNull("tanggal_lahir") ?: p.strOrNull("dob")?.let { dob ->
        try {
            val d = SimpleDateFormat("yyyy-MM-dd", Locale.US).parse(dob.take(10))
            SimpleDateFormat("d MMMM yyyy", Locale("id", "ID")).format(d!!)
        } catch (_: Exception) { dob }
    } ?: "-"
    val agama = e.strOrNull("agama") ?: sin.strOrNull("religion") ?: "Islam"
    val tandaKehormatan = e.strOrNull("tanda_kehormatan")

    /** Alamat dipecah 24 karakter (baris 1) lalu 28 karakter per baris, sama seperti web. */
    val addressLines: List<String> = run {
        val raw = e.strOrNull("alamat") ?: p.strOrNull("address") ?: "-"
        if (raw.isBlank() || raw == "-") return@run listOf("-")
        val lines = mutableListOf<String>()
        var cur = ""
        raw.trim().split(Regex("\\s+")).forEach { w ->
            val limit = if (lines.isEmpty()) 24 else 28
            val test = if (cur.isEmpty()) w else "$cur $w"
            if (test.length <= limit) cur = test else { if (cur.isNotEmpty()) lines += cur; cur = w }
        }
        if (cur.isNotEmpty()) lines += cur
        lines.ifEmpty { listOf(raw) }
    }
}

/** Skala px web → dp di layar. */
private class Px(val k: Float, private val density: androidx.compose.ui.unit.Density) {
    fun dp(v: Float): Dp = (v * k).dp
    fun sp(v: Float): TextUnit = with(density) { (v * k).dp.toSp() }
}

/** Kartu lengkap dengan kontrol Tampak Depan/Belakang, Balik Kartu, dan Cetak — seperti di web. */
@Composable
fun KtaCardView(personel: JsonObject, ekta: JsonObject?, settings: JsonObject?, modifier: Modifier = Modifier, showVerifyLink: Boolean = false) {
    val data = remember(personel, ekta, settings) { KtaData(personel, ekta, settings) }
    var back by rememberSaveable { mutableStateOf(false) }
    val rot by animateFloatAsState(if (back) 180f else 0f, tween(520), label = "flip")
    val ctx = LocalContext.current
    val scope = rememberCoroutineScope()
    val layer = rememberGraphicsLayer()

    Column(modifier.fillMaxWidth(), verticalArrangement = Arrangement.spacedBy(10.dp)) {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            Row(Modifier.clip(RoundedCornerShape(12.dp)).background(Color(0xFFF1F5F9)).border(1.dp, Color(0xFFE2E8F0), RoundedCornerShape(12.dp)).padding(3.dp)) {
                SideTab("Tampak Depan", !back) { back = false }
                SideTab("Tampak Belakang", back) { back = true }
            }
            Spacer(Modifier.weight(1f))
        }
        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            SoftButton("Balik Kartu", { back = !back }, Modifier.weight(1f), icon = Icons.Outlined.Sync, tone = Tone.Gray, height = 40.dp)
            SoftButton("Cetak E-KTA", {
                scope.launch {
                    val bmp = layer.toImageBitmap().asAndroidBitmap()
                    PrintHelper(ctx).apply { scaleMode = PrintHelper.SCALE_MODE_FIT }
                        .printBitmap("E-KTA ${data.name} (${if (back) "belakang" else "depan"})", bmp)
                }
            }, Modifier.weight(1f), icon = Icons.Outlined.Print, height = 40.dp)
        }

        Box(
            Modifier.fillMaxWidth().aspectRatio(85.6f / 54f)
                .graphicsLayer { rotationY = rot; cameraDistance = 14f * density },
        ) {
            val showBack = rot > 90f
            Box(
                Modifier.fillMaxSize()
                    .graphicsLayer { if (showBack) rotationY = 180f }
                    .drawWithContent {
                        layer.record { this@drawWithContent.drawContent() }
                        drawLayer(layer)
                    },
            ) { KtaCardFace(data, showBack) }
        }
        if (showVerifyLink) {
            Text(
                "Buka Halaman Verifikasi Publik QR",
                color = Sx.Blue, fontSize = 13.sp, fontWeight = FontWeight.Bold,
                modifier = Modifier.clip(RoundedCornerShape(8.dp)).pressable({
                    ctx.startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(data.verificationUrl)).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK))
                }).padding(vertical = 6.dp),
            )
        }
    }
}

@Composable
private fun SideTab(text: String, selected: Boolean, onClick: () -> Unit) {
    Box(
        Modifier.clip(RoundedCornerShape(9.dp)).then(if (selected) Modifier.shadow(1.dp, RoundedCornerShape(9.dp)).background(Color.White) else Modifier)
            .pressable(onClick).padding(horizontal = 13.dp, vertical = 6.dp),
    ) {
        Text(text, fontSize = 12.sp, fontWeight = if (selected) FontWeight.Bold else FontWeight.Medium, color = if (selected) Color(0xFF0F172A) else Color(0xFF64748B))
    }
}

/** Satu sisi kartu (tanpa kontrol). */
@Composable
fun KtaCardFace(data: KtaData, back: Boolean, modifier: Modifier = Modifier) {
    BoxWithConstraints(
        modifier.fillMaxSize()
            .shadow(14.dp, RoundedCornerShape(16.dp))
            .clip(RoundedCornerShape(16.dp))
            .background(CARD_BG)
            .border(1.dp, Color(0xFF064E3B).copy(alpha = 0.3f), RoundedCornerShape(16.dp)),
    ) {
        val px = Px(maxWidth.value / REF_W, LocalDensity.current)
        KomcadWatermarkText(px)
        // lambang Komcad besar di tengah (w-64 x scale-125, opacity 30%)
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            Box(Modifier.size(px.dp(320f)).alpha(0.3f)) { KomcadLogo(data.logoKomcad) }
        }
        if (!back) FrontSide(data, px) else BackSide(data, px)
    }
}

@Composable
private fun KomcadLogo(path: String?) {
    if (path != null) RemoteImage(path, Modifier.fillMaxSize(), contentScale = ContentScale.Fit) else KomcadEmblem(Modifier.fillMaxSize())
}

// ------------------------------------------------------------------ sisi depan
@Composable
private fun FrontSide(d: KtaData, px: Px) {
    val font = d.fontFamily
    BoxWithConstraints(Modifier.fillMaxSize().padding(px.dp(14f))) {
        run {
            val inner = maxWidth
            Row(Modifier.fillMaxSize()) {
                // Kolom kiri: logo Komcad + pas foto (satu bingkai)
                Column(Modifier.width(inner * 0.23f).fillMaxHeight().border(1.dp, INK).background(Color.White)) {
                    Box(
                        Modifier.fillMaxWidth().weight(46f).drawBehind {
                            drawLine(INK, Offset(0f, size.height), Offset(size.width, size.height), 1.dp.toPx())
                        }.padding(px.dp(4f)),
                        contentAlignment = Alignment.Center,
                    ) { KomcadLogo(d.logoKomcad) }
                    Box(Modifier.fillMaxWidth().weight(54f).background(d.photoBg), contentAlignment = Alignment.Center) {
                        if (d.photo != null) {
                            RemoteImage(d.photo, Modifier.fillMaxSize(), contentScale = ContentScale.Crop, alignment = Alignment.TopCenter)
                        } else {
                            Column(horizontalAlignment = Alignment.CenterHorizontally) {
                                Canvas(Modifier.size(px.dp(28f))) {
                                    drawCircle(Color.White.copy(0.55f), size.width * 0.18f, Offset(size.width / 2, size.height * 0.33f))
                                    drawRoundRect(Color.White.copy(0.55f), Offset(size.width * 0.17f, size.height * 0.62f), Size(size.width * 0.66f, size.height * 0.26f), CornerRadius(size.width * 0.2f))
                                }
                                Text("FOTO PERS", color = Color.White.copy(0.8f), fontSize = px.sp(7.5f), fontWeight = FontWeight.Bold, fontFamily = font)
                            }
                        }
                    }
                }
                Spacer(Modifier.width(px.dp(10f + 2f)))
                Box(Modifier.width(px.dp(1.2f)).fillMaxHeight().background(INK))
                Spacer(Modifier.width(px.dp(2f + 10f)))
                // Kolom kanan
                Column(Modifier.weight(1f).fillMaxHeight().padding(start = px.dp(2f)), verticalArrangement = Arrangement.SpaceBetween) {
                    Column(Modifier.fillMaxWidth(), horizontalAlignment = Alignment.CenterHorizontally) {
                        Underlined("KARTU TANDA ANGGOTA KOMCAD", px, 13f, font)
                        Text(d.nomor, color = INK, fontSize = px.sp(11f), fontFamily = font, modifier = Modifier.padding(top = px.dp(2f)))
                    }
                    Column(verticalArrangement = Arrangement.spacedBy(px.dp(2f))) {
                        DataRow("Nama", d.name, px, 84f, font, d.fontPx)
                        DataRow("Pangkat", d.pangkat, px, 84f, font, d.fontPx)
                        DataRow("NIKC", d.nikc, px, 84f, font, d.fontPx)
                        DataRow("Jabatan", d.jabatan, px, 84f, font, d.fontPx)
                        DataRow("Kesatuan/Matra", d.matra, px, 84f, font, d.fontPx)
                        DataRow("Berlaku s/d", d.berlaku, px, 84f, font, d.fontPx)
                    }
                    Row(Modifier.fillMaxWidth().padding(top = px.dp(2f)), horizontalArrangement = Arrangement.End, verticalAlignment = Alignment.Bottom) {
                        Signer(d, px, font)
                        Spacer(Modifier.width(px.dp(12f)))
                        Box(Modifier.padding(bottom = px.dp(2f))) {
                            Box(Modifier.clip(RoundedCornerShape(px.dp(4f))).background(Color.White).border(1.dp, INK, RoundedCornerShape(px.dp(4f))).padding(px.dp(4f))) {
                                QrImage(d.verificationUrl, Modifier.size(px.dp(54f)))
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun Signer(d: KtaData, px: Px, font: FontFamily) {
    Column(Modifier.widthIn(min = px.dp(155f)), horizontalAlignment = Alignment.CenterHorizontally) {
        Text(d.signerLine1.uppercase(), color = INK, fontSize = px.sp(9.5f), fontFamily = font, lineHeight = px.sp(11.5f), letterSpacing = (-0.01).em)
        if (d.signerLine2.isNotEmpty()) Text(d.signerLine2.uppercase(), color = INK, fontSize = px.sp(9.5f), fontFamily = font, lineHeight = px.sp(11.5f), letterSpacing = (-0.01).em)
        Box(Modifier.width(px.dp(155f)).height(px.dp(36f)).padding(vertical = px.dp(2f)), contentAlignment = Alignment.Center) {
            // stempel dinas (kiri, miring -12°)
            Box(Modifier.align(Alignment.TopStart).offset(x = px.dp(-12f), y = px.dp(-4f)).size(px.dp(48f)).rotate(-12f).alpha(0.85f)) {
                if (d.stamp != null) RemoteImage(d.stamp, Modifier.fillMaxSize(), contentScale = ContentScale.Fit) else DefaultStamp(px)
            }
            Box(Modifier.padding(start = px.dp(16f)), contentAlignment = Alignment.Center) {
                if (d.signature != null) {
                    RemoteImage(d.signature, Modifier.height(px.dp(32f)).widthIn(max = px.dp(110f)), contentScale = ContentScale.Fit)
                } else {
                    SignatureScribble(
                        Modifier.width(px.dp(96f)).height(px.dp(28f)), Color(0xFF1E3A8A).copy(alpha = 0.9f),
                        listOf("M10 25 C20 10, 30 35, 40 15 C50 5, 55 25, 70 20 C80 18, 85 28, 95 22" to 1.8f, "M35 22 L65 24" to 1.2f),
                    )
                }
            }
        }
        Text(d.signerName.uppercase(), color = INK, fontSize = px.sp(10f), fontFamily = font, textDecoration = TextDecoration.Underline, lineHeight = px.sp(12f))
        Text(d.signerRank, color = INK, fontSize = px.sp(8.5f), fontFamily = font, lineHeight = px.sp(10.5f))
    }
}

@Composable
private fun DefaultStamp(px: Px) {
    val purple = Color(0xFF6B21A8)
    Box(Modifier.fillMaxSize().clip(CircleShape).border(2.dp, purple.copy(0.8f), CircleShape).padding(px.dp(2f))) {
        Column(
            Modifier.fillMaxSize().clip(CircleShape).border(1.dp, purple.copy(0.6f), CircleShape),
            horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center,
        ) {
            val c = Color(0xFF581C87)
            Text("KEMHAN RI", color = c, fontSize = px.sp(5f), fontWeight = FontWeight.ExtraBold, lineHeight = px.sp(6f))
            Text("* KOMCAD *", color = c, fontSize = px.sp(4f), fontWeight = FontWeight.ExtraBold, lineHeight = px.sp(6f))
            Text("DITJEN", color = c, fontSize = px.sp(5f), fontWeight = FontWeight.ExtraBold, lineHeight = px.sp(6f))
        }
    }
}

// ------------------------------------------------------------------ sisi belakang
@Composable
private fun BackSide(d: KtaData, px: Px) {
    val font = d.fontFamily
    BoxWithConstraints(Modifier.fillMaxSize().padding(px.dp(14f))) {
        val inner = maxWidth
        Row(Modifier.fillMaxSize()) {
            Column(Modifier.width(inner * 0.58f).fillMaxHeight().padding(end = px.dp(4f))) {
                Underlined("SINYALEMEN", px, 13f, font)
                Spacer(Modifier.height(px.dp(6f)))
                Column(verticalArrangement = Arrangement.spacedBy(px.dp(2f))) {
                    DataRow("Tinggi/berat Badan", d.tinggiBerat, px, 105f, font, d.fontPx)
                    DataRow("Rambut", d.rambut, px, 105f, font, d.fontPx)
                    DataRow("Mata", d.mata, px, 105f, font, d.fontPx)
                    DataRow("Darah", d.darah, px, 105f, font, d.fontPx)
                    DataRow("Tempat Lahir", d.tempatLahir, px, 105f, font, d.fontPx)
                    DataRow("Tgl Lahir", d.tanggalLahir, px, 105f, font, d.fontPx)
                    DataRow("Agama", d.agama, px, 105f, font, d.fontPx)
                    d.addressLines.forEachIndexed { i, line ->
                        DataRow(if (i == 0) "Alamat Rumah" else "", line, px, 105f, font, d.fontPx)
                    }
                }
            }
            Spacer(Modifier.width(px.dp(10f + 2f)))
            Box(Modifier.width(px.dp(1.2f)).fillMaxHeight().background(INK))
            Spacer(Modifier.width(px.dp(2f + 10f + 4f)))
            Column(Modifier.weight(1f).fillMaxHeight()) {
                Column(Modifier.fillMaxWidth().weight(45f)) {
                    Underlined("TANDA KEHORMATAN", px, 11f, font)
                    Spacer(Modifier.height(px.dp(6f)))
                    if (d.tandaKehormatan != null) {
                        Text(d.tandaKehormatan, color = INK, fontSize = px.sp(11f), fontFamily = font, lineHeight = px.sp(13f))
                    } else {
                        Column(verticalArrangement = Arrangement.spacedBy(px.dp(4f))) { repeat(3) { DottedLine(Modifier.fillMaxWidth().height(px.dp(12f)), px) } }
                    }
                }
                Box(Modifier.fillMaxWidth().padding(vertical = px.dp(4f)).height(px.dp(1f)).background(INK))
                Column(Modifier.fillMaxWidth().weight(50f)) {
                    Underlined("TANDA TANGAN PEMEGANG", px, 11f, font)
                    Box(Modifier.fillMaxWidth().weight(1f).padding(px.dp(4f)), contentAlignment = Alignment.Center) {
                        SignatureScribble(
                            Modifier.width(px.dp(112f)).height(px.dp(36f)), Color(0xFF0F172A),
                            listOf("M15 28 C25 8, 30 38, 45 18 C55 8, 60 22, 75 14 C85 10, 88 30, 92 24" to 1.8f, "M40 26 L80 28" to 1.2f),
                        )
                    }
                }
            }
        }
    }
}

// ------------------------------------------------------------------ elemen kecil
@Composable
private fun Underlined(text: String, px: Px, size: Float, font: FontFamily) {
    Text(
        text, color = INK, fontSize = px.sp(size), fontFamily = font, fontWeight = FontWeight.Bold, letterSpacing = 0.025.em,
        maxLines = 1, softWrap = false,
        modifier = Modifier.drawBehind {
            drawLine(INK, Offset(0f, size.height), Offset(size.width, size.height), 1.dp.toPx())
        }.padding(bottom = px.dp(2f)),
    )
}

@Composable
private fun DataRow(label: String, value: String, px: Px, labelW: Float, font: FontFamily, sizePx: Float) {
    val fs = px.sp(sizePx)
    Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.Bottom) {
        Text(label, color = INK, fontSize = fs, fontFamily = font, maxLines = 1, softWrap = false, overflow = TextOverflow.Clip, modifier = Modifier.width(px.dp(labelW)))
        Text(":", color = INK, fontSize = fs, fontFamily = font, modifier = Modifier.padding(end = px.dp(6f)))
        Text(value, color = INK, fontSize = fs, fontFamily = font, maxLines = 1, softWrap = false, overflow = TextOverflow.Clip, modifier = Modifier.weight(1f, fill = false))
        DottedLine(Modifier.weight(1f).padding(start = px.dp(4f), bottom = px.dp(sizePx * 0.28f)).height(px.dp(1.5f)), px)
    }
}

@Composable
private fun DottedLine(modifier: Modifier, px: Px) {
    Canvas(modifier) {
        val y = size.height - 0.5f
        val step = px.dp(2f).toPx()
        drawLine(
            DOT, Offset(0f, y), Offset(size.width, y), strokeWidth = 1.dp.toPx(),
            pathEffect = PathEffect.dashPathEffect(floatArrayOf(step * 0.5f, step * 0.5f)), cap = StrokeCap.Round,
        )
    }
}

@Composable
private fun SignatureScribble(modifier: Modifier, color: Color, paths: List<Pair<String, Float>>) {
    val parsed = remember(paths) { paths.map { PathParser().parsePathString(it.first).toPath() to it.second } }
    Canvas(modifier) {
        val k = size.width / 100f
        scale(k, k, pivot = Offset.Zero) {
            parsed.forEach { (p, w) -> drawPath(p, color, style = Stroke(w, cap = StrokeCap.Round)) }
        }
    }
}

@Composable
private fun QrImage(text: String, modifier: Modifier) {
    val bmp: ImageBitmap? = remember(text) {
        try {
            val m = QRCodeWriter().encode(text, BarcodeFormat.QR_CODE, 0, 0, mapOf(EncodeHintType.MARGIN to 0))
            val w = m.width
            val px = IntArray(w * w) { i -> if (m[i % w, i / w]) 0xFF000000.toInt() else 0xFFFFFFFF.toInt() }
            android.graphics.Bitmap.createBitmap(px, w, w, android.graphics.Bitmap.Config.ARGB_8888).asImageBitmap()
        } catch (_: Exception) {
            null
        }
    }
    if (bmp != null) {
        Image(bmp, "QR verifikasi E-KTA", modifier, filterQuality = androidx.compose.ui.graphics.FilterQuality.None)
    } else {
        Box(modifier.background(Color.White))
    }
}

/** Watermark teks "KOMCAD" miring dan berulang (opacity 20%). */
@Composable
private fun KomcadWatermarkText(px: Px) {
    val measurer = rememberTextMeasurer()
    val style = TextStyle(color = Color(0xFF244616), fontSize = px.sp(9f), fontWeight = FontWeight.Black, letterSpacing = 0.1.em, fontFamily = KtaSans)
    Canvas(Modifier.fillMaxSize().alpha(0.2f)) {
        val layout = measurer.measure("KOMCAD", style)
        val w = layout.size.width.toFloat()
        val h = layout.size.height.toFloat()
        val gx = px.dp(12f).toPx()
        val gy = px.dp(8f).toPx()
        rotate(-6f) {
            scale(1.1f) {
                val cols = ((size.width * 1.4f) / (w + gx)).toInt() + 2
                val rows = ((size.height * 1.5f) / (h + gy)).toInt() + 2
                val rowW = cols * w + (cols - 1) * gx
                val x0 = (size.width - rowW) / 2f
                val y0 = -size.height * 0.25f
                for (r in 0 until rows) for (c in 0 until cols) {
                    drawText(layout, topLeft = Offset(x0 + c * (w + gx), y0 + r * (h + gy)))
                }
            }
        }
    }
}

/** Port vektor KomcadEmblem.vue (viewBox 200 x 200). */
@Composable
fun KomcadEmblem(modifier: Modifier = Modifier) {
    val measurer = rememberTextMeasurer()
    val paths = remember {
        fun p(d: String): Path = PathParser().parsePathString(d).toPath()
        mapOf(
            "top" to p("M24 100 A76 76 0 0 1 176 100 Z"),
            "bottom" to p("M24 100 A76 76 0 0 0 176 100 Z"),
            "padiL" to p("M36 125 C30 85 45 45 80 30 C75 42 75 60 85 70 C70 75 55 95 60 115 Z"),
            "padiR" to p("M164 125 C170 85 155 45 120 30 C125 42 125 60 115 70 C130 75 145 95 140 115 Z"),
            "star" to p("M100,20 L105,34 L120,34 L108,43 L112,57 L100,48 L88,57 L92,43 L80,34 L95,34 Z"),
            "wing1" to p("M100 85 C70 65 30 78 28 92 C50 96 75 92 100 102 C125 92 150 96 172 92 C170 78 130 65 100 85 Z"),
            "wing2" to p("M100 95 C75 80 45 88 40 102 C60 104 80 100 100 112 C120 100 140 104 160 102 C155 88 125 80 100 95 Z"),
            "anchorArc" to p("M70 120 C70 146 130 146 130 120 C126 120 124 128 118 132 C108 136 92 136 82 132 C76 128 74 120 70 120 Z"),
            "sword" to p("M100,55 L103,130 L100,135 L97,130 Z"),
            "ribbon" to p("M40 156 Q100 178 160 156 L154 172 Q100 190 46 172 Z"),
            "tailL" to p("M40 156 L26 166 L42 174 L38 164 Z"),
            "tailR" to p("M160 156 L174 166 L158 174 L162 164 Z"),
        )
    }
    Canvas(modifier) {
        val k = minOf(size.width, size.height) / 200f
        translate((size.width - 200f * k) / 2f, (size.height - 200f * k) / 2f) {
            scale(k, k, pivot = Offset.Zero) { drawEmblem(paths, measurer) }
        }
    }
}

private fun DrawScope.drawEmblem(p: Map<String, Path>, measurer: androidx.compose.ui.text.TextMeasurer) {
    val gold = Color(0xFFF59E0B); val goldL = Color(0xFFFBBF24); val brown = Color(0xFFB45309)
    val red = Color(0xFFDC2626); val redD = Color(0xFF991B1B)
    val c = Offset(100f, 100f)
    drawCircle(Color.White.copy(alpha = 0.1f), 92f, c)
    drawCircle(Color(0xFFD97706), 92f, c, style = Stroke(3f, pathEffect = PathEffect.dashPathEffect(floatArrayOf(2f, 1f))))
    drawCircle(Color(0xFFF8FAFC), 76f, c)
    drawCircle(brown, 76f, c, style = Stroke(2f))
    drawPath(p.getValue("top"), red)
    drawPath(p.getValue("bottom"), Color.White)
    drawPath(p.getValue("padiL"), gold.copy(alpha = 0.9f))
    drawPath(p.getValue("padiR"), gold.copy(alpha = 0.9f))
    drawPath(p.getValue("star"), goldL); drawPath(p.getValue("star"), brown, style = Stroke(1f))
    drawPath(p.getValue("wing1"), goldL); drawPath(p.getValue("wing1"), brown, style = Stroke(1.5f))
    drawPath(p.getValue("wing2"), gold.copy(alpha = 0.85f))
    drawCircle(gold, 7f, Offset(100f, 72f), style = Stroke(3f))
    drawRoundRect(gold, Offset(96f, 78f), Size(8f, 62f), CornerRadius(3f)); drawRoundRect(brown, Offset(96f, 78f), Size(8f, 62f), CornerRadius(3f), style = Stroke(1f))
    drawRoundRect(gold, Offset(82f, 88f), Size(36f, 6f), CornerRadius(2f)); drawRoundRect(brown, Offset(82f, 88f), Size(36f, 6f), CornerRadius(2f), style = Stroke(1f))
    drawPath(p.getValue("anchorArc"), gold); drawPath(p.getValue("anchorArc"), brown, style = Stroke(1.5f))
    drawPath(p.getValue("sword"), red)
    drawPath(p.getValue("ribbon"), red); drawPath(p.getValue("ribbon"), redD, style = Stroke(1.5f))
    drawPath(p.getValue("tailL"), Color(0xFFB91C1C)); drawPath(p.getValue("tailR"), Color(0xFFB91C1C))
    // teks pita (font-size 7,5 di viewBox; diukur pada skala 1 lalu digambar di koordinat viewBox)
    val layout = measurer.measure(
        "KOMPONEN CADANGAN",
        TextStyle(color = Color.White, fontSize = 7.5.sp, fontWeight = FontWeight.Black, letterSpacing = 0.8.sp, fontFamily = KtaSans),
        density = androidx.compose.ui.unit.Density(1f, 1f),
    )
    drawText(layout, topLeft = Offset(100f - layout.size.width / 2f, 169f - layout.size.height * 0.78f))
}
