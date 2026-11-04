package id.my.sisfoperskc.app.ui.pju

import androidx.compose.foundation.layout.padding
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.foundation.layout.Box
import id.my.sisfoperskc.app.ui.admin.PjuPersonelListContent
import id.my.sisfoperskc.app.ui.nav.NotificationBell
import id.my.sisfoperskc.app.ui.nav.ShellScaffold

@Composable
fun PjuPersonelScreen() {
    ShellScaffold(title = "Personel Jajaran", subtitle = "Data kekuatan personel binaan", topLevel = true, actions = { NotificationBell() }) { inner ->
        Box(Modifier.padding(top = inner.calculateTopPadding())) {
            PjuPersonelListContent("/pju/personel")
        }
    }
}
