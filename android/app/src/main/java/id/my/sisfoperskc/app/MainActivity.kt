package id.my.sisfoperskc.app

import android.content.Intent
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.runtime.mutableStateOf
import androidx.core.splashscreen.SplashScreen.Companion.installSplashScreen
import id.my.sisfoperskc.app.data.Demo
import id.my.sisfoperskc.app.push.Notifier
import id.my.sisfoperskc.app.ui.nav.SisfoApp
import id.my.sisfoperskc.app.ui.theme.SisfoTheme

class MainActivity : ComponentActivity() {
    /** URL web tujuan dari notifikasi yang diketuk. */
    val pendingUrl = mutableStateOf<String?>(null)
    val pendingNotificationId = mutableStateOf<String?>(null)

    /** Hanya build tangkapan layar (mode demo): rute yang langsung dibuka. */
    val demoRoute = mutableStateOf<String?>(null)

    override fun onCreate(savedInstanceState: Bundle?) {
        installSplashScreen()
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        if (Demo.enabled) {
            intent?.getStringExtra("demo_role")?.let { Demo.signIn(it) }
            demoRoute.value = intent?.getStringExtra("demo_route")
        }
        handleIntent(intent)
        setContent {
            SisfoTheme {
                SisfoApp(pendingUrl, pendingNotificationId, demoRoute)
            }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleIntent(intent)
    }

    private fun handleIntent(intent: Intent?) {
        val url = intent?.getStringExtra(Notifier.EXTRA_URL) ?: return
        pendingNotificationId.value = intent.getStringExtra(Notifier.EXTRA_NOTIFICATION_ID)
        pendingUrl.value = url
        intent.removeExtra(Notifier.EXTRA_URL)
    }
}
