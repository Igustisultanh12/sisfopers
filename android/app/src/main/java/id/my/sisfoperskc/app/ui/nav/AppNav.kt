package id.my.sisfoperskc.app.ui.nav

import android.Manifest
import android.os.Build
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.scaleIn
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.MutableState
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.navigation.NavBackStackEntry
import androidx.navigation.NavGraphBuilder
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.compose.currentBackStackEntryAsState
import id.my.sisfoperskc.app.data.Api
import id.my.sisfoperskc.app.data.ApiEvent
import id.my.sisfoperskc.app.data.Auth
import id.my.sisfoperskc.app.data.Session
import id.my.sisfoperskc.app.push.ForegroundWatcher
import id.my.sisfoperskc.app.ui.admin.ActivityLogScreen
import id.my.sisfoperskc.app.ui.admin.BroadcastDetailScreen
import id.my.sisfoperskc.app.ui.admin.BroadcastFormScreen
import id.my.sisfoperskc.app.ui.admin.BroadcastListScreen
import id.my.sisfoperskc.app.ui.admin.EducationVerifyScreen
import id.my.sisfoperskc.app.ui.admin.EktaDetailScreen
import id.my.sisfoperskc.app.ui.admin.EktaListScreen
import id.my.sisfoperskc.app.ui.admin.FormBuilderScreen
import id.my.sisfoperskc.app.ui.admin.FormListScreen
import id.my.sisfoperskc.app.ui.admin.FormResponsesScreen
import id.my.sisfoperskc.app.ui.admin.LoginLogScreen
import id.my.sisfoperskc.app.ui.admin.MailGatewayScreen
import id.my.sisfoperskc.app.ui.admin.OtpManualScreen
import id.my.sisfoperskc.app.ui.admin.PengkinianVerifyScreen
import id.my.sisfoperskc.app.ui.admin.PersonelEducationScreen
import id.my.sisfoperskc.app.ui.admin.PersonelFormScreen
import id.my.sisfoperskc.app.ui.admin.PersonelListScreen
import id.my.sisfoperskc.app.ui.admin.PjuManagementScreen
import id.my.sisfoperskc.app.ui.admin.ReportsScreen
import id.my.sisfoperskc.app.ui.admin.SettingsScreen
import id.my.sisfoperskc.app.ui.admin.SkepScreen
import id.my.sisfoperskc.app.ui.admin.TicketsScreen
import id.my.sisfoperskc.app.ui.admin.VerificationDetailScreen
import id.my.sisfoperskc.app.ui.admin.VerificationListScreen
import id.my.sisfoperskc.app.ui.admin.WaLogScreen
import id.my.sisfoperskc.app.ui.auth.ForgotNikcScreen
import id.my.sisfoperskc.app.ui.auth.KewilayahanGateScreen
import id.my.sisfoperskc.app.ui.auth.LoginScreen
import id.my.sisfoperskc.app.ui.auth.MaintenanceScreen
import id.my.sisfoperskc.app.ui.auth.OnboardingScreen
import id.my.sisfoperskc.app.ui.auth.OtpGateScreen
import id.my.sisfoperskc.app.ui.auth.RegisterScreen
import id.my.sisfoperskc.app.ui.auth.ResetPasswordScreen
import id.my.sisfoperskc.app.ui.auth.SinyalmenGateScreen
import id.my.sisfoperskc.app.ui.auth.SkepRequestScreen
import id.my.sisfoperskc.app.ui.auth.SplashScreen
import id.my.sisfoperskc.app.ui.chat.ChatRoomScreen
import id.my.sisfoperskc.app.ui.chat.ChatThreadsScreen
import id.my.sisfoperskc.app.ui.common.AboutScreen
import id.my.sisfoperskc.app.ui.common.AccountScreen
import id.my.sisfoperskc.app.ui.common.HomeScreen
import id.my.sisfoperskc.app.ui.common.MenuScreen
import id.my.sisfoperskc.app.ui.common.NotificationsScreen
import id.my.sisfoperskc.app.ui.personel.MyBroadcastDetailScreen
import id.my.sisfoperskc.app.ui.personel.MyBroadcastScreen
import id.my.sisfoperskc.app.ui.personel.MyEducationScreen
import id.my.sisfoperskc.app.ui.personel.MyEktaScreen
import id.my.sisfoperskc.app.ui.personel.MyFormScreen
import id.my.sisfoperskc.app.ui.personel.MyFormsScreen
import id.my.sisfoperskc.app.ui.personel.MyJobScreen
import id.my.sisfoperskc.app.ui.personel.MyPengkinianScreen
import id.my.sisfoperskc.app.ui.personel.MyTicketsScreen
import id.my.sisfoperskc.app.ui.pju.PjuPersonelScreen
import id.my.sisfoperskc.app.ui.theme.Sx
import id.my.sisfoperskc.app.ui.vicon.ViconListScreen
import id.my.sisfoperskc.app.ui.components.MessageHost
import kotlinx.coroutines.flow.drop

private fun NavBackStackEntry.arg(name: String): String = arguments?.getString(name).orEmpty()

@Composable
fun SisfoApp(pendingUrl: MutableState<String?>, pendingNotificationId: MutableState<String?>) {
    val nav = rememberNavController()
    val context = LocalContext.current
    val me by Session.me.collectAsState()

    // Izin notifikasi Android 13+
    val notifPerm = rememberLauncherForActivityResult(ActivityResultContracts.RequestPermission()) { }
    LaunchedEffect(me?.id) {
        if (me != null && Build.VERSION.SDK_INT >= 33) notifPerm.launch(Manifest.permission.POST_NOTIFICATIONS)
        if (me != null) ForegroundWatcher.start()
    }

    // Peristiwa global dari lapisan API
    LaunchedEffect(Unit) {
        Api.events.collect { e ->
            when (e) {
                ApiEvent.Unauthenticated -> if (Session.token != null) {
                    Auth.clearLocal(context)
                    nav.navigate(Rt.LOGIN) { popUpTo(0) { inclusive = true } }
                }
                ApiEvent.Maintenance -> if (nav.currentDestination?.route != Rt.MAINTENANCE) nav.navigate(Rt.MAINTENANCE)
                is ApiEvent.Gate -> {
                    val target = if (e.code == "needs_otp") Rt.GATE_OTP else Rt.GATE_SINYALMEN
                    if (nav.currentDestination?.route != target) nav.navigate(target) { popUpTo(0) { inclusive = true } }
                }
            }
        }
    }

    LaunchedEffect(Unit) {
        AppState.logoutRequest.drop(1).collect {
            Auth.logout(context)
            nav.navigate(Rt.LOGIN) { popUpTo(0) { inclusive = true } }
        }
    }

    // Navigasi dari notifikasi
    val url = pendingUrl.value
    val entry by nav.currentBackStackEntryAsState()
    LaunchedEffect(url, me?.id, entry?.destination?.route) {
        if (url != null && me != null && entry?.destination?.route !in setOf(null, Rt.SPLASH, Rt.LOGIN, Rt.ONBOARD, Rt.GATE_OTP, Rt.GATE_SINYALMEN, Rt.GATE_WILAYAH)) {
            pendingNotificationId.value?.let { id -> Api.send("/notifications/$id/read") }
            pendingUrl.value = null
            pendingNotificationId.value = null
            nav.go(Rt.fromWebUrl(url, me))
        }
    }

    ProvideNav(nav) {
        Box(Modifier.fillMaxSize().background(Sx.Bg)) {
            NavHost(
                navController = nav,
                startDestination = Rt.SPLASH,
                enterTransition = { slideInHorizontally(tween(320)) { it / 5 } + fadeIn(tween(320)) },
                exitTransition = { fadeOut(tween(200)) },
                popEnterTransition = { fadeIn(tween(260)) + scaleIn(tween(260), initialScale = 0.97f) },
                popExitTransition = { slideOutHorizontally(tween(260)) { it / 5 } + fadeOut(tween(220)) },
            ) {
                authGraph()
                mainGraph()
            }
            MessageHost()
        }
    }
}

private fun NavGraphBuilder.authGraph() {
    composable(Rt.SPLASH, enterTransition = { fadeIn() }, exitTransition = { fadeOut(tween(400)) }) { SplashScreen() }
    composable(Rt.ONBOARD) { OnboardingScreen() }
    composable(Rt.LOGIN) { LoginScreen() }
    composable(Rt.REGISTER) { RegisterScreen() }
    composable(Rt.SKEP_REQUEST) { SkepRequestScreen() }
    composable(Rt.FORGOT_NIKC) { ForgotNikcScreen() }
    composable(Rt.RESET_PASSWORD) { ResetPasswordScreen() }
    composable(Rt.MAINTENANCE) { MaintenanceScreen() }
    composable(Rt.GATE_OTP) { OtpGateScreen() }
    composable(Rt.GATE_SINYALMEN) { SinyalmenGateScreen() }
    composable(Rt.GATE_WILAYAH) { KewilayahanGateScreen() }
}

private fun NavGraphBuilder.tab(route: String, content: @Composable (NavBackStackEntry) -> Unit) {
    composable(
        route,
        enterTransition = { fadeIn(tween(240)) },
        exitTransition = { fadeOut(tween(160)) },
        popEnterTransition = { fadeIn(tween(240)) },
    ) { entry -> content(entry) }
}

private fun NavGraphBuilder.mainGraph() {
    tab(Rt.HOME) { HomeScreen() }
    tab(Rt.NOTIF) { NotificationsScreen() }
    tab(Rt.ACCOUNT) { AccountScreen() }
    tab(Rt.MENU) { MenuScreen() }
    composable(Rt.ABOUT) { AboutScreen() }

    tab(Rt.VERIF) { VerificationListScreen() }
    composable(Rt.VERIF_DETAIL) { VerificationDetailScreen(it.arg("uuid")) }
    tab(Rt.PERSONEL) { PersonelListScreen() }
    composable(Rt.PERSONEL_NEW) { PersonelFormScreen(null) }
    composable(Rt.PERSONEL_EDIT) { PersonelFormScreen(it.arg("uuid")) }
    composable(Rt.PERSONEL_EDU) { PersonelEducationScreen(it.arg("uuid")) }
    composable(Rt.SKEP) { SkepScreen() }
    composable(Rt.PENGKINIAN) { PengkinianVerifyScreen() }
    composable(Rt.EDU_VERIF) { EducationVerifyScreen() }
    composable(Rt.EKTA) { EktaListScreen() }
    composable(Rt.EKTA_DETAIL) { EktaDetailScreen(it.arg("id")) }
    composable(Rt.OTP) { OtpManualScreen(reset = false) }
    composable(Rt.OTP_RESET) { OtpManualScreen(reset = true) }
    composable(Rt.BROADCAST) { BroadcastListScreen() }
    composable(Rt.BROADCAST_NEW) { BroadcastFormScreen(null) }
    composable(Rt.BROADCAST_DETAIL) { BroadcastDetailScreen(it.arg("uuid")) }
    composable(Rt.BROADCAST_EDIT) { BroadcastFormScreen(it.arg("uuid")) }
    composable(Rt.FORMS) { FormListScreen() }
    composable(Rt.FORM_NEW) { FormBuilderScreen(null) }
    composable(Rt.FORM_EDIT) { FormBuilderScreen(it.arg("uuid")) }
    composable(Rt.FORM_RESPONSES) { FormResponsesScreen(it.arg("uuid")) }
    tab(Rt.CHAT) { ChatThreadsScreen() }
    composable(Rt.CHAT_ROOM) { ChatRoomScreen(it.arg("uuid")) }
    tab(Rt.MY_CHAT) { ChatRoomScreen(null) }
    composable(Rt.VICON) { ViconListScreen() }
    composable(Rt.TICKETS) { TicketsScreen() }
    composable(Rt.ACTIVITY) { ActivityLogScreen() }
    composable(Rt.LOGINS) { LoginLogScreen() }
    composable(Rt.WA_LOGS) { WaLogScreen() }
    composable(Rt.REPORTS) { ReportsScreen() }
    composable(Rt.SETTINGS) { SettingsScreen() }
    composable(Rt.PJU_MGMT) { PjuManagementScreen() }
    composable(Rt.MAIL) { MailGatewayScreen() }
    tab(Rt.PJU_PERSONEL) { PjuPersonelScreen() }

    tab(Rt.MY_BROADCAST) { MyBroadcastScreen() }
    composable(Rt.MY_BROADCAST_DETAIL) { MyBroadcastDetailScreen(it.arg("uuid")) }
    composable(Rt.MY_EDU) { MyEducationScreen() }
    composable(Rt.MY_JOB) { MyJobScreen() }
    composable(Rt.MY_PENGKINIAN) { MyPengkinianScreen() }
    composable(Rt.MY_TICKETS) { MyTicketsScreen() }
    composable(Rt.MY_FORMS) { MyFormsScreen() }
    composable(Rt.MY_FORM) { MyFormScreen(it.arg("uuid")) }
    composable(Rt.MY_EKTA) { MyEktaScreen() }
}
