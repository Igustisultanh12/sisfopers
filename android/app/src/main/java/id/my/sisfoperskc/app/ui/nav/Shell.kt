package id.my.sisfoperskc.app.ui.nav

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.Spring
import androidx.compose.animation.core.animateDpAsState
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.spring
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.RowScope
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ArrowBack
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.outlined.Menu
import androidx.compose.material.icons.outlined.NotificationsNone
import androidx.compose.material3.DrawerValue
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalDrawerSheet
import androidx.compose.material3.ModalNavigationDrawer
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.rememberDrawerState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.CompositionLocalProvider
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.compositionLocalOf
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import androidx.navigation.compose.currentBackStackEntryAsState
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.ui.components.AppLogo
import id.my.sisfoperskc.app.ui.components.Avatar
import id.my.sisfoperskc.app.ui.components.CircleIconButton
import id.my.sisfoperskc.app.ui.components.Eyebrow
import id.my.sisfoperskc.app.ui.components.SxDialog
import id.my.sisfoperskc.app.ui.components.Tone
import id.my.sisfoperskc.app.ui.components.pressable
import id.my.sisfoperskc.app.ui.theme.Sx
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.launch

val LocalNav = compositionLocalOf<NavHostController> { error("NavController belum tersedia") }

object AppState {
    val unread = MutableStateFlow(0)
    val chatUnread = MutableStateFlow(0)
    val logoutRequest = MutableStateFlow(0)
}

fun NavHostController.go(route: String) {
    if (route == currentDestination?.route) return
    val top = route in Rt.topLevel
    navigate(route) {
        if (top) {
            popUpTo(Rt.HOME) { saveState = true }
            launchSingleTop = true
            restoreState = true
        } else {
            launchSingleTop = true
        }
    }
}

@Composable
fun ShellScaffold(
    title: String,
    modifier: Modifier = Modifier,
    subtitle: String? = null,
    topLevel: Boolean = false,
    showBack: Boolean = !topLevel,
    actions: @Composable RowScope.() -> Unit = {},
    fab: (@Composable () -> Unit)? = null,
    bottomBar: Boolean = topLevel,
    content: @Composable (PaddingValues) -> Unit,
) {
    val nav = LocalNav.current
    val me by Session.me.collectAsState()
    val drawer = rememberDrawerState(DrawerValue.Closed)
    val scope = rememberCoroutineScope()
    var confirmLogout by remember { mutableStateOf(false) }

    val body: @Composable () -> Unit = {
        Scaffold(
            modifier = modifier,
            containerColor = Sx.Bg,
            contentWindowInsets = WindowInsets(0, 0, 0, 0),
            topBar = {
                TopBar(
                    title = title,
                    subtitle = subtitle,
                    showBack = showBack,
                    showMenu = topLevel,
                    onBack = { if (!nav.popBackStack()) nav.go(Rt.HOME) },
                    onMenu = { scope.launch { drawer.open() } },
                    actions = actions,
                )
            },
            bottomBar = { if (bottomBar && me != null) BottomBar(nav) },
            floatingActionButton = { fab?.invoke() },
        ) { inner -> content(inner) }
    }

    val m = me
    if (topLevel && m != null) {
        ModalNavigationDrawer(
            drawerState = drawer,
            gesturesEnabled = drawer.isOpen,
            drawerContent = {
                ModalDrawerSheet(drawerContainerColor = Color.White, drawerShape = RoundedCornerShape(topEnd = 30.dp, bottomEnd = 30.dp), modifier = Modifier.width(320.dp)) {
                    DrawerContent(
                        onNavigate = { r -> scope.launch { drawer.close() }; nav.go(r) },
                        onLogout = { scope.launch { drawer.close() }; confirmLogout = true },
                    )
                }
            },
        ) { body() }
    } else body()

    if (confirmLogout) {
        SxDialog(
            onDismiss = { confirmLogout = false },
            title = "Keluar dari aplikasi?",
            message = "Sesi Anda pada perangkat ini akan diakhiri dan notifikasi tidak lagi diterima.",
            icon = Icons.AutoMirrored.Outlined.Logout,
            tone = Tone.Red,
            confirmText = "Keluar",
            confirmColor = Sx.Red,
            onConfirm = { confirmLogout = false; AppState.logoutRequest.value++ },
        )
    }
}

@Composable
private fun TopBar(
    title: String,
    subtitle: String?,
    showBack: Boolean,
    showMenu: Boolean,
    onBack: () -> Unit,
    onMenu: () -> Unit,
    actions: @Composable RowScope.() -> Unit,
) {
    Row(
        Modifier
            .fillMaxWidth()
            .background(Sx.Bg)
            .statusBarsPadding()
            .padding(horizontal = 16.dp, vertical = 10.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        when {
            showBack -> CircleIconButton(Icons.AutoMirrored.Outlined.ArrowBack, onBack, contentDescription = "Kembali")
            showMenu -> CircleIconButton(Icons.Outlined.Menu, onMenu, contentDescription = "Menu")
        }
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.headlineSmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
            if (subtitle != null) Text(subtitle, style = MaterialTheme.typography.bodySmall, maxLines = 1, overflow = TextOverflow.Ellipsis)
        }
        Row(horizontalArrangement = Arrangement.spacedBy(8.dp), verticalAlignment = Alignment.CenterVertically, content = actions)
    }
}

@Composable
fun NotificationBell() {
    val nav = LocalNav.current
    val unread by AppState.unread.collectAsState()
    CircleIconButton(Icons.Outlined.NotificationsNone, { nav.go(Rt.NOTIF) }, badge = unread, contentDescription = "Notifikasi")
}

@Composable
private fun BottomBar(nav: NavHostController) {
    val me by Session.me.collectAsState()
    val m = me ?: return
    val items = remember(m.roleGroup) { Menus.bottom(m) }
    val entry by nav.currentBackStackEntryAsState()
    val current = entry?.destination?.route
    val unread by AppState.unread.collectAsState()
    val chatUnread by AppState.chatUnread.collectAsState()
    val selectedIndex = items.indexOfFirst { it.route == current }.coerceAtLeast(0)

    Box(
        Modifier
            .fillMaxWidth()
            .navigationBarsPadding()
            .padding(horizontal = 14.dp, vertical = 10.dp),
    ) {
        BoxWithConstraints(
            Modifier
                .fillMaxWidth()
                .height(68.dp)
                .shadow(24.dp, RoundedCornerShape(26.dp), ambientColor = Sx.Blue.copy(0.25f), spotColor = Sx.Blue.copy(0.25f))
                .clip(RoundedCornerShape(26.dp))
                .background(Color.White)
                .border(1.dp, Sx.LineSoft, RoundedCornerShape(26.dp)),
        ) {
            val slot = maxWidth / items.size
            val indicatorX by animateDpAsState(slot * selectedIndex, spring(dampingRatio = 0.72f, stiffness = Spring.StiffnessMediumLow), label = "ind")
            Box(
                Modifier
                    .offset(x = indicatorX)
                    .width(slot)
                    .fillMaxHeight()
                    .padding(horizontal = 6.dp, vertical = 8.dp)
                    .clip(RoundedCornerShape(20.dp))
                    .background(Brush.verticalGradient(listOf(Sx.BlueSoft, Color(0xFFE0EAFF)))),
            )
            Row(Modifier.fillMaxSize()) {
                items.forEachIndexed { i, item ->
                    val selected = i == selectedIndex && item.route == current
                    val tint by animateColorAsState(if (selected) Sx.Blue else Sx.Faint, label = "tint")
                    val scale by animateFloatAsState(if (selected) 1.12f else 1f, spring(dampingRatio = Spring.DampingRatioMediumBouncy), label = "sc")
                    val badge = when (item.route) {
                        Rt.NOTIF -> unread
                        Rt.CHAT, Rt.MY_CHAT -> chatUnread
                        else -> 0
                    }
                    Column(
                        Modifier
                            .weight(1f)
                            .fillMaxHeight()
                            .clip(RoundedCornerShape(20.dp))
                            .clickable { nav.go(item.route) },
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.Center,
                    ) {
                        Box {
                            Icon(item.icon, item.label, tint = tint, modifier = Modifier.size(24.dp).scale(scale))
                            if (badge > 0) {
                                Box(
                                    Modifier.align(Alignment.TopEnd).offset(x = 6.dp, y = (-4).dp).size(16.dp).clip(CircleShape).background(Sx.Red).border(1.5.dp, Color.White, CircleShape),
                                    contentAlignment = Alignment.Center,
                                ) { Text(if (badge > 9) "9+" else "$badge", color = Color.White, fontSize = 8.sp, fontWeight = FontWeight.ExtraBold) }
                            }
                        }
                        Spacer(Modifier.height(3.dp))
                        Text(item.label, color = tint, fontSize = 10.sp, fontWeight = if (selected) FontWeight.ExtraBold else FontWeight.SemiBold, maxLines = 1)
                    }
                }
            }
        }
    }
}

@Composable
private fun DrawerContent(onNavigate: (String) -> Unit, onLogout: () -> Unit) {
    val me by Session.me.collectAsState()
    val m = me ?: return
    val nav = LocalNav.current
    val entry by nav.currentBackStackEntryAsState()
    val current = entry?.destination?.route
    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState()).statusBarsPadding().padding(16.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically, modifier = Modifier.padding(4.dp)) {
            AppLogo(38.dp)
            Spacer(Modifier.width(10.dp))
            Column {
                Text("SISFOPERS KC", style = MaterialTheme.typography.titleMedium)
                Text("Komponen Cadangan Tri Matra", style = MaterialTheme.typography.bodySmall)
            }
        }
        Spacer(Modifier.height(16.dp))
        Row(
            Modifier.fillMaxWidth().clip(RoundedCornerShape(22.dp)).background(Sx.HeroGradient).padding(14.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Avatar(m.photo, m.fullName, 48.dp, ring = Color.White.copy(0.6f))
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(m.fullName, color = Color.White, fontWeight = FontWeight.ExtraBold, fontSize = 15.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
                Text(m.roleLabel, color = Color.White.copy(0.8f), fontSize = 12.sp, maxLines = 1, overflow = TextOverflow.Ellipsis)
            }
        }
        Spacer(Modifier.height(10.dp))
        Menus.groups(m).forEach { g ->
            Spacer(Modifier.height(14.dp))
            Eyebrow(g.title, color = Sx.Faint, modifier = Modifier.padding(start = 12.dp, bottom = 6.dp))
            g.items.forEach { item ->
                val selected = item.route == current
                Row(
                    Modifier
                        .fillMaxWidth()
                        .clip(RoundedCornerShape(16.dp))
                        .background(if (selected) Sx.BlueSoft else Color.Transparent)
                        .pressable({ onNavigate(item.route) })
                        .padding(horizontal = 12.dp, vertical = 11.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Icon(item.icon, null, tint = if (selected) Sx.Blue else Sx.Muted, modifier = Modifier.size(21.dp))
                    Spacer(Modifier.width(14.dp))
                    Text(item.label, color = if (selected) Sx.Blue else Sx.Ink2, fontWeight = if (selected) FontWeight.Bold else FontWeight.SemiBold, fontSize = 14.sp)
                }
            }
        }
        Spacer(Modifier.height(18.dp))
        Row(
            Modifier.fillMaxWidth().clip(RoundedCornerShape(16.dp)).background(Sx.RedSoft).pressable(onLogout).padding(14.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Icon(Icons.AutoMirrored.Outlined.Logout, null, tint = Sx.Red)
            Spacer(Modifier.width(12.dp))
            Text("Keluar", color = Sx.RedInk, fontWeight = FontWeight.Bold)
        }
        Spacer(Modifier.height(24.dp))
    }
}

@Composable
fun ProvideNav(nav: NavHostController, content: @Composable () -> Unit) {
    CompositionLocalProvider(LocalNav provides nav, content = content)
}
