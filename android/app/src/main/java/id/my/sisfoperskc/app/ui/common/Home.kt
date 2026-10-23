package id.my.sisfoperskc.app.ui.common

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ExperimentalLayoutApi
import androidx.compose.foundation.layout.FlowRow
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ArrowForward
import androidx.compose.material.icons.outlined.Apps
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.EditNote
import androidx.compose.material.icons.outlined.EventAvailable
import androidx.compose.material.icons.outlined.EventBusy
import androidx.compose.material.icons.outlined.Groups
import androidx.compose.material.icons.outlined.HourglassTop
import androidx.compose.material.icons.outlined.Login
import androidx.compose.material.icons.outlined.MilitaryTech
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material.icons.outlined.PersonAdd
import androidx.compose.material.icons.outlined.Place
import androidx.compose.material.icons.outlined.Shield
import androidx.compose.material.icons.outlined.TaskAlt
import androidx.compose.material.icons.outlined.Timeline
import androidx.compose.material.icons.outlined.VerifiedUser
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.Fmt
import id.my.sisfoperskc.app.data.Me
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.data.int
import id.my.sisfoperskc.app.data.obj
import id.my.sisfoperskc.app.data.objects
import id.my.sisfoperskc.app.data.str
import id.my.sisfoperskc.app.data.strOrNull
import id.my.sisfoperskc.app.data.strings
import id.my.sisfoperskc.app.data.toPage
import id.my.sisfoperskc.app.ui.components.AreaChart
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.Badge
import id.my.sisfoperskc.app.ui.components.BarChart
import id.my.sisfoperskc.app.ui.components.DonutChart
import id.my.sisfoperskc.app.ui.components.HeroStat
import id.my.sisfoperskc.app.ui.components.IconBox
import id.my.sisfoperskc.app.ui.components.MatraBadge
import id.my.sisfoperskc.app.ui.components.Notice
import id.my.sisfoperskc.app.ui.components.PageContent
import id.my.sisfoperskc.app.ui.components.ReasonDialog
import id.my.sisfoperskc.app.ui.components.SectionTitle
import id.my.sisfoperskc.app.ui.components.StatCard
import id.my.sisfoperskc.app.ui.components.StatRow
import id.my.sisfoperskc.app.ui.components.StatusBadge
import id.my.sisfoperskc.app.ui.components.SxCard
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.Ui
import id.my.sisfoperskc.app.ui.components.appear
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.components.rememberPage
import id.my.sisfoperskc.app.ui.nav.LocalNav
import id.my.sisfoperskc.app.ui.nav.MenuItem
import id.my.sisfoperskc.app.ui.nav.Menus
import id.my.sisfoperskc.app.ui.nav.NotificationBell
import id.my.sisfoperskc.app.ui.nav.Rt
import id.my.sisfoperskc.app.ui.nav.ShellScaffold
import id.my.sisfoperskc.app.ui.nav.go
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.launch
import kotlinx.serialization.json.JsonObject

@Composable
fun HomeScreen() {
    val me by Session.me.collectAsState()
    val m = me ?: return
    val path = when (m.roleGroup) {
        "admin" -> "/admin/dashboard"
        "kordinator" -> "/kordinator/dashboard"
        "pju" -> "/pju/dashboard"
        "personel" -> "/personel/dashboard"
        else -> null
    }
    ShellScaffold(
        title = Fmt.greeting(),
        subtitle = m.fullName,
        topLevel = true,
        actions = { NotificationBell() },
    ) { inner ->
        if (path == null) {
            Column(Modifier.padding(top = inner.calculateTopPadding()).padding(16.dp), verticalArrangement = Arrangement.spacedBy(14.dp)) {
                HeroCard(m, emptyList())
                Notice("Dashboard ${m.roleLabel} tersedia pada versi web. Gunakan menu di bawah untuk data mandiri Anda.", Tone.Blue)
                QuickMenu(m)
            }
            return@ShellScaffold
        }
        val loader = rememberPage(path)
        PageContent(loader, Modifier.padding(top = inner.calculateTopPadding())) { data ->
            when (m.roleGroup) {
                "admin" -> AdminHome(m, data)
                "kordinator" -> KordinatorHome(m, data, onRefresh = { loader.refresh() })
                "pju" -> PjuHome(m, data)
                else -> PersonelHome(m, data)
            }
        }
    }
}

@Composable
fun HeroCard(me: Me, stats: List<Triple<String, Int, androidx.compose.ui.graphics.vector.ImageVector>>, extra: String? = null) {
    Box(Modifier.fillMaxWidth().appear(0).clip(RoundedCornerShape(28.dp)).background(Sx.HeroGradient)) {
        id.my.sisfoperskc.app.ui.auth.AnimatedBlobs()
        Column(Modifier.padding(20.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Avatar(me.photo, me.fullName, 54.dp, ring = Color.White.copy(0.6f))
                Spacer(Modifier.width(14.dp))
                Column(Modifier.weight(1f)) {
                    Text(me.fullName, color = Color.White, fontSize = 18.sp, fontWeight = FontWeight.ExtraBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    Text(
                        listOfNotNull(me.pangkatLabel.takeIf { it.isNotBlank() }, me.roleLabel).joinToString(" • "),
                        color = Color.White.copy(0.8f), fontSize = 12.sp, maxLines = 1, overflow = TextOverflow.Ellipsis,
                    )
                }
            }
            Spacer(Modifier.height(12.dp))
            Row(
                Modifier.clip(RoundedCornerShape(50)).background(Color.White.copy(0.16f)).padding(horizontal = 12.dp, vertical = 6.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Icon(Icons.Outlined.Shield, null, tint = Color.White, modifier = Modifier.size(14.dp))
                Spacer(Modifier.width(6.dp))
                Text(extra ?: Fmt.dayDate(java.text.SimpleDateFormat("yyyy-MM-dd", java.util.Locale.US).format(java.util.Date())), color = Color.White, fontSize = 12.sp, fontWeight = FontWeight.SemiBold)
            }
            if (stats.isNotEmpty()) {
                Spacer(Modifier.height(16.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    stats.forEach { (l, v, i) -> HeroStat(l, v, i, Modifier.weight(1f)) }
                }
            }
        }
    }
}

@OptIn(ExperimentalLayoutApi::class)
@Composable
fun QuickMenu(me: Me, max: Int = 7) {
    val nav = LocalNav.current
    val items = remember(me.roleGroup) { Menus.groups(me).flatMap { it.items }.filter { it.route != Rt.HOME }.distinctBy { it.route } }
    val shown = items.take(max)
    SectionTitle("Menu Cepat", Modifier.appear(1), action = "Semua", onAction = { nav.go(Rt.MENU) })
    FlowRow(
        Modifier.fillMaxWidth().appear(2),
        maxItemsInEachRow = 4,
        horizontalArrangement = Arrangement.spacedBy(10.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        shown.forEach { item -> QuickTile(item, Modifier.weight(1f)) { nav.go(item.route) } }
        QuickTile(MenuItem(Rt.MENU, "Semua Menu", Icons.Outlined.Apps, Sx.Ink2), Modifier.weight(1f)) { nav.go(Rt.MENU) }
    }
}

@Composable
private fun QuickTile(item: MenuItem, modifier: Modifier, onClick: () -> Unit) {
    Column(modifier.clip(RoundedCornerShape(18.dp)).pressable(onClick).padding(vertical = 6.dp), horizontalAlignment = Alignment.CenterHorizontally) {
        IconBox(item.icon, tint = item.color, size = 54.dp, iconSize = 25.dp, radius = 18.dp)
        Spacer(Modifier.height(6.dp))
        Text(item.label, fontSize = 11.sp, fontWeight = FontWeight.SemiBold, color = Sx.Ink2, maxLines = 2, overflow = TextOverflow.Ellipsis, textAlign = androidx.compose.ui.text.style.TextAlign.Center, lineHeight = 13.sp)
    }
}

// ------------------------------------------------------------------ Admin

@Composable
private fun AdminHome(me: Me, data: JsonObject) {
    val nav = LocalNav.current
    val s = data.obj("stats")
    HeroCard(
        me,
        listOf(
            Triple("Personel", s.int("total_personel"), Icons.Outlined.Groups),
            Triple("Menunggu", s.int("pending_verification"), Icons.Outlined.HourglassTop),
            Triple("Login hari ini", s.int("login_hari_ini"), Icons.Outlined.Login),
        ),
    )
    QuickMenu(me)
    SectionTitle("Ringkasan Sistem", Modifier.appear(3))
    StatRow {
        StatCard("Personel Aktif", s.int("personel_aktif"), Icons.Outlined.VerifiedUser, Sx.Green, Modifier.weight(1f).appear(3)) { nav.go(Rt.PERSONEL) }
        StatCard("Perlu Verifikasi", s.int("pending_verification"), Icons.Outlined.PersonAdd, Sx.Amber, Modifier.weight(1f).appear(4)) { nav.go(Rt.VERIF) }
    }
    StatRow {
        StatCard("Broadcast Aktif", s.int("broadcast_aktif"), Icons.Outlined.Campaign, Sx.Blue, Modifier.weight(1f).appear(5)) { nav.go(Rt.BROADCAST) }
        StatCard("Broadcast Selesai", s.int("broadcast_selesai"), Icons.Outlined.TaskAlt, Sx.Purple, Modifier.weight(1f).appear(6)) { nav.go(Rt.BROADCAST) }
    }
    StatRow {
        StatCard("Respons Hadir", s.int("respon_hadir"), Icons.Outlined.EventAvailable, Sx.Green, Modifier.weight(1f).appear(7))
        StatCard("Respons Izin", s.int("respon_izin"), Icons.Outlined.EventBusy, Sx.Amber, Modifier.weight(1f).appear(8))
    }
    val matra = data.obj("charts").obj("matra")
    SxCard(Modifier.fillMaxWidth().appear(8)) {
        SectionTitle("Komposisi Matra", subtitle = "Sebaran personel per matra")
        Spacer(Modifier.height(14.dp))
        DonutChart(matra.strings("labels"), matra.strings("data").map { it.toIntOrNull() ?: 0 }, centerLabel = "Personel")
    }
    val trend = data.obj("charts").obj("trend")
    SxCard(Modifier.fillMaxWidth().appear(9)) {
        SectionTitle("Tren Login", subtitle = "6 hari terakhir")
        Spacer(Modifier.height(14.dp))
        AreaChart(trend.strings("labels"), trend.strings("data").map { it.toIntOrNull() ?: 0 })
    }
    SectionTitle("Aktivitas Terbaru", Modifier.appear(10), action = "Lihat semua", onAction = { nav.go(Rt.ACTIVITY) })
    data.objects("recentActivities").forEachIndexed { i, a -> ActivityRow(a, Modifier.appear(10 + i)) }
}

@Composable
fun ActivityRow(a: JsonObject, modifier: Modifier = Modifier) {
    val action = a.str("action")
    val tone = when (action.uppercase()) {
        "CREATE", "SUBMIT_CUSTOM_FORM", "APPROVE" -> Tone.Green
        "DELETE", "REJECT" -> Tone.Red
        "UPDATE" -> Tone.Amber
        else -> Tone.Blue
    }
    SxCard(modifier.fillMaxWidth(), padding = androidx.compose.foundation.layout.PaddingValues(14.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            IconBox(Icons.Outlined.Timeline, tint = tone.fg, bg = tone.bg, size = 40.dp, iconSize = 20.dp)
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(
                    a.strOrNull("full_name") ?: a.path2("user", "username") ?: "Sistem",
                    style = MaterialTheme.typography.titleSmall, maxLines = 1, overflow = TextOverflow.Ellipsis,
                )
                Text(
                    "${Fmt.title(action)} • ${a.str("model_type").substringAfterLast('\\')}",
                    style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis,
                )
            }
            Text(Fmt.relative(a.str("created_at")), fontSize = 11.sp, color = Sx.Faint)
        }
    }
}

private fun JsonObject.path2(a: String, b: String): String? = (this[a] as? JsonObject)?.strOrNull(b)

// ------------------------------------------------------------------ Kordinator

@Composable
private fun KordinatorHome(me: Me, data: JsonObject, onRefresh: () -> Unit) {
    val nav = LocalNav.current
    val scope = rememberCoroutineScope()
    val s = data.obj("stats")
    var noteFor by remember { mutableStateOf<JsonObject?>(null) }
    var saving by remember { mutableStateOf(false) }
    HeroCard(
        me,
        listOf(
            Triple("Jajaran", s.int("total_personel"), Icons.Outlined.Groups),
            Triple("Aktif", s.int("total_aktif"), Icons.Outlined.CheckCircle),
            Triple("Terverifikasi", s.int("total_verified"), Icons.Outlined.VerifiedUser),
        ),
        extra = if (data.str("role") == "kordinator_matra") "Koordinator ${Fmt.matraLong(me.matra)}" else "Koordinator Angkatan ${me.personel.str("angkatan")}",
    )
    QuickMenu(me)
    val dist = data.obj("distribution")
    SxCard(Modifier.fillMaxWidth().appear(3)) {
        SectionTitle("Distribusi Jajaran", subtitle = if (data.str("role") == "kordinator_matra") "Per angkatan" else "Per matra")
        Spacer(Modifier.height(16.dp))
        BarChart(dist.strings("labels"), dist.strings("data").map { it.toIntOrNull() ?: 0 })
    }
    SectionTitle("Personel Jajaran", Modifier.appear(4), action = "Kelola", onAction = { nav.go(Rt.PERSONEL) })
    val page = data["jajaran"].toPage()
    page.items.take(12).forEachIndexed { i, p ->
        SxCard(Modifier.fillMaxWidth().appear(5 + i), padding = androidx.compose.foundation.layout.PaddingValues(14.dp), onClick = { noteFor = p }) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Avatar(p.strOrNull("photo_profile"), p.str("full_name"), 44.dp)
                Spacer(Modifier.width(12.dp))
                Column(Modifier.weight(1f)) {
                    Text(p.str("full_name"), style = MaterialTheme.typography.titleSmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
                    Text("${p.str("pangkat")} • ${p.str("nikc")}", style = MaterialTheme.typography.bodySmall, maxLines = 1)
                }
                MatraBadge(p.str("matra"))
            }
            val responses = p.objects("broadcast_responses")
            if (responses.isNotEmpty() || p.strOrNull("catatan_pembinaan") != null) {
                Spacer(Modifier.height(8.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    responses.take(3).forEach { r -> StatusBadge(r.str("status_attendance")) }
                    if (p.strOrNull("catatan_pembinaan") != null) Badge("Ada catatan", Tone.Purple)
                }
            }
        }
    }
    if (page.total > 12) Text("Menampilkan 12 dari ${page.total} personel. Buka Master Personel untuk selengkapnya.", style = MaterialTheme.typography.bodySmall)

    noteFor?.let { p ->
        ReasonDialog(
            title = "Catatan Pembinaan",
            label = "Catatan untuk ${p.str("full_name")}",
            message = p.strOrNull("catatan_pembinaan")?.let { "Catatan saat ini: $it" },
            required = false,
            confirmText = "Simpan",
            tone = Tone.Blue,
            loading = saving,
            onDismiss = { noteFor = null },
        ) { note ->
            scope.launch {
                saving = true
                val r = Api.send("/kordinator/personel/${p.str("uuid")}/catatan", mapOf("notes" to note))
                saving = false
                if (Ui.report(r, "Catatan pembinaan disimpan.")) { noteFor = null; onRefresh() }
            }
        }
    }
}

// ------------------------------------------------------------------ PJU

@Composable
private fun PjuHome(me: Me, data: JsonObject) {
    val nav = LocalNav.current
    val s = data.obj("stats")
    val u = data.obj("user")
    HeroCard(
        me,
        listOf(
            Triple("Personel", s.int("total_personel"), Icons.Outlined.Groups),
            Triple("Broadcast", s.int("total_broadcast"), Icons.Outlined.Campaign),
        ),
        extra = listOfNotNull(u.strOrNull("jabatan_pju"), u.strOrNull("satuan_wilayah")).joinToString(" • ").ifBlank { null },
    )
    QuickMenu(me)
    SxCard(Modifier.fillMaxWidth().appear(3)) {
        SectionTitle("Kekuatan Personel per Matra", subtitle = "Seluruh personel Komcad terdaftar")
        Spacer(Modifier.height(16.dp))
        BarChart(listOf("TNI AD", "TNI AL", "TNI AU"), listOf(s.int("personel_ad"), s.int("personel_al"), s.int("personel_au")))
    }
    SxCard(Modifier.fillMaxWidth().appear(4)) {
        SectionTitle("Profil Pejabat")
        Spacer(Modifier.height(8.dp))
        id.my.sisfoperskc.app.ui.components.InfoRow("Nama", u.str("full_name"), icon = Icons.Outlined.Person)
        id.my.sisfoperskc.app.ui.components.InfoRow("NRP", u.str("nrp"), icon = Icons.Outlined.Badge)
        id.my.sisfoperskc.app.ui.components.InfoRow("Jabatan", u.str("jabatan_pju"), icon = Icons.Outlined.MilitaryTech)
        id.my.sisfoperskc.app.ui.components.InfoRow("Matra", Fmt.matra(u.strOrNull("matra")), icon = Icons.Outlined.Shield)
        id.my.sisfoperskc.app.ui.components.InfoRow("Satuan", u.str("satuan_wilayah"), icon = Icons.Outlined.Place)
    }
    StatRow {
        StatCard("Personel Jajaran", s.int("total_personel"), Icons.Outlined.Groups, Sx.Blue, Modifier.weight(1f).appear(5)) { nav.go(Rt.PJU_PERSONEL) }
        StatCard("Broadcast", s.int("total_broadcast"), Icons.Outlined.Campaign, Sx.Purple, Modifier.weight(1f).appear(6)) { nav.go(Rt.BROADCAST) }
    }
}

// ------------------------------------------------------------------ Personel

@Composable
private fun PersonelHome(me: Me, data: JsonObject) {
    val nav = LocalNav.current
    val p = data.obj("personel")
    val s = data.obj("stats")
    HeroCard(
        me,
        listOf(
            Triple("Kegiatan", s.int("total_kegiatan"), Icons.Outlined.Campaign),
            Triple("Hadir", s.int("total_hadir"), Icons.Outlined.EventAvailable),
            Triple("Izin", s.int("total_izin"), Icons.Outlined.EventBusy),
        ),
        extra = listOfNotNull(p.strOrNull("kotama"), p.strOrNull("satuan_kewilayahan")).joinToString(" • ").ifBlank { null },
    )
    // Kartu identitas digital
    SxCard(Modifier.fillMaxWidth().appear(1), onClick = { nav.go(Rt.MY_EKTA) }) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Avatar(p.strOrNull("photo_profile"), p.str("full_name"), 62.dp, radius = 18.dp)
            Spacer(Modifier.width(14.dp))
            Column(Modifier.weight(1f)) {
                Text(p.str("full_name"), style = MaterialTheme.typography.titleMedium, maxLines = 1, overflow = TextOverflow.Ellipsis)
                Text("NIKC ${p.str("nikc")}", style = MaterialTheme.typography.bodySmall)
                Spacer(Modifier.height(6.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    MatraBadge(p.str("matra"))
                    Badge("Angkatan ${p.str("angkatan")}", Tone.Gray)
                    StatusBadge(p.strOrNull("status_keaktifan") ?: "AKTIF")
                }
            }
            Icon(Icons.AutoMirrored.Outlined.ArrowForward, null, tint = Sx.Faint)
        }
    }
    QuickMenu(me)
    SectionTitle("Broadcast Terbaru", Modifier.appear(3), action = "Semua", onAction = { nav.go(Rt.MY_BROADCAST) })
    val broadcasts = data.objects("latestBroadcasts")
    if (broadcasts.isEmpty()) Notice("Belum ada broadcast kegiatan untuk Anda.", Tone.Gray)
    broadcasts.forEachIndexed { i, b -> BroadcastMini(b, Modifier.appear(4 + i)) { nav.go(Rt.with(Rt.MY_BROADCAST_DETAIL, "uuid" to b.str("uuid"))) } }
    val forms = data.objects("activeForms")
    if (forms.isNotEmpty()) {
        SectionTitle("Formulir Aktif", Modifier.appear(8), action = "Semua", onAction = { nav.go(Rt.MY_FORMS) })
        forms.forEachIndexed { i, f ->
            SxCard(Modifier.fillMaxWidth().appear(9 + i), onClick = { nav.go(Rt.with(Rt.MY_FORM, "uuid" to f.str("uuid"))) }, padding = androidx.compose.foundation.layout.PaddingValues(14.dp)) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    IconBox(Icons.Outlined.EditNote, Sx.Green)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(f.str("title"), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        Text("Batas: ${Fmt.dateTime(f.str("deadline"))}", style = MaterialTheme.typography.bodySmall)
                    }
                    Badge(Fmt.title(f.str("category")), Tone.Green)
                }
            }
        }
    }
}

@Composable
fun BroadcastMini(b: JsonObject, modifier: Modifier = Modifier, onClick: () -> Unit) {
    val responded = b.objects("responses").firstOrNull()
    val past = Fmt.isPast(b.str("deadline"))
    SxCard(modifier.fillMaxWidth(), onClick = onClick, padding = androidx.compose.foundation.layout.PaddingValues(14.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Column(
                Modifier.size(54.dp).clip(RoundedCornerShape(16.dp)).background(Sx.BlueSoft),
                horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center,
            ) {
                Text(Fmt.date(b.str("event_date")).substringBefore(' '), fontSize = 18.sp, fontWeight = FontWeight.ExtraBold, color = Sx.BlueDark)
                Text(Fmt.date(b.str("event_date")).split(' ').getOrNull(1).orEmpty().uppercase(), fontSize = 10.sp, fontWeight = FontWeight.Bold, color = Sx.Blue)
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(b.str("title"), style = MaterialTheme.typography.titleSmall, maxLines = 2, overflow = TextOverflow.Ellipsis)
                Text("${Fmt.category(b.str("category"))} • ${b.str("event_time")} • ${b.str("location")}", style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
            }
            Spacer(Modifier.width(8.dp))
            when {
                responded != null -> StatusBadge(responded.str("status_attendance"))
                past -> Badge("Ditutup", Tone.Gray)
                else -> Badge("Respons", Tone.Amber)
            }
        }
    }
}

@Composable
fun MenuScreen() {
    val me by Session.me.collectAsState()
    val m = me ?: return
    val nav = LocalNav.current
    ShellScaffold(title = "Semua Menu", subtitle = m.roleLabel, topLevel = true, actions = { NotificationBell() }) { inner ->
        androidx.compose.foundation.lazy.LazyColumn(
            Modifier.fillMaxSize().padding(top = inner.calculateTopPadding()),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 120.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            var idx = 0
            Menus.groups(m).forEach { g ->
                item(key = "g_" + g.title) {
                    Text(g.title.uppercase(), style = MaterialTheme.typography.labelSmall, color = Sx.Faint, modifier = Modifier.padding(start = 4.dp, top = 12.dp))
                }
                g.items.forEach { item ->
                    val i = idx++
                    item(key = g.title + item.route) {
                        SxCard(Modifier.fillMaxWidth().appear(i % 10), onClick = { nav.go(item.route) }, padding = androidx.compose.foundation.layout.PaddingValues(14.dp)) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                IconBox(item.icon, tint = item.color)
                                Spacer(Modifier.width(14.dp))
                                Column(Modifier.weight(1f)) {
                                    Text(item.label, style = MaterialTheme.typography.titleSmall.copy(fontSize = 14.sp))
                                    if (item.desc != null) Text(item.desc, style = MaterialTheme.typography.bodySmall)
                                }
                                Icon(Icons.AutoMirrored.Outlined.ArrowForward, null, tint = Sx.Faint, modifier = Modifier.size(18.dp))
                            }
                        }
                    }
                }
            }
        }
    }
}

